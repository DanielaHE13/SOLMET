<?php
// PRESENTACION/OrdenProduccion/pdf.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

/* ================== DEBUG / ERRORES ================== */
$DEBUG = isset($_GET['debug']) && $_GET['debug'] == '1';
if ($DEBUG) {
  ini_set('display_errors', '1');
  error_reporting(E_ALL);
} else {
  ini_set('display_errors', '0');
  error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
}

/* ================== INCLUDES ================== */
require_once __DIR__ . '/../../PERSISTENCIA/Conexion.php';

/* ================== AUTORIZACIÓN ================== */
if (!isset($_SESSION['uid']) && !$DEBUG) {
  http_response_code(401);
  echo "No autorizado (sesión no iniciada). Añade ?debug=1 temporalmente para probar.";
  exit;
}

/* ================== FPDF ================== */
define('APP_ROOT', dirname(__DIR__, 2)); // raíz del proyecto
$fpdfPath = APP_ROOT . DIRECTORY_SEPARATOR . 'fpdf186' . DIRECTORY_SEPARATOR . 'fpdf.php';
if (!is_file($fpdfPath)) {
  http_response_code(500);
  echo "No se encontró FPDF en: " . $fpdfPath;
  exit;
}
require_once $fpdfPath;

/* ================== INPUT ================== */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo "ID inválido";
  exit;
}

/* ================== BD: CONSULTAS ================== */
$cx = new Conexion();
$cx->abrir();

/* ---- Cabecera de la OP ---- */
$cx->ejecutar("
  SELECT
    op.id_op,
    op.numero_op,
    op.observaciones,
    op.id_molde,
    op.id_maquina,
    m.nombre            AS molde_nombre,
    m.peso_colada_g     AS colada_g,
    q.nombre            AS maquina_nombre
  FROM orden_produccion op
  LEFT JOIN molde   m ON m.id_molde   = op.id_molde
  LEFT JOIN maquina q ON q.id_maquina = op.id_maquina
  WHERE op.id_op = ?
  LIMIT 1
", [$id]);

$hdr = $cx->registro();
if (!$hdr) {
  $cx->cerrar();
  http_response_code(404);
  echo "OP no encontrada (id=$id)";
  exit;
}

$numeroOp = $hdr['numero_op'] ?? $hdr[1] ?? '';
$obs      = trim((string)($hdr['observaciones'] ?? $hdr[2] ?? ''));
$moldeNom = $hdr['molde_nombre'] ?? $hdr[5] ?? '';
$coladaG  = (float)($hdr['colada_g'] ?? $hdr[6] ?? 0);
$maqNom   = $hdr['maquina_nombre'] ?? $hdr[7] ?? '';

/* Fecha desde numero_op (YYYYMMDD###) si aplica */
$fechaOP = '';
if (preg_match('/^(\d{4})(\d{2})(\d{2})/', (string)$numeroOp, $mch)) {
  $fechaOP = sprintf('%02d/%02d/%04d', (int)$mch[3], (int)$mch[2], (int)$mch[1]);
}

/* ---- Métricas teóricas + totales de la orden ---- */
$pesoTeorKg      = 0.0; // histórico (por tiro en tu front)
$devTeorKg       = 0.0; // devolución teórica total orden (kg)
$piezasOrdenKg   = 0.0; // total piezas de la orden (si existe en BD)
$ordenTeorKg     = 0.0; // piezas + colada (si existe en BD)

try {
  $cx->ejecutar("
    SELECT
      peso_teorico_total_kg,
      devolucion_teorica_kg,
      peso_piezas_total_orden_kg,
      peso_total_orden_kg
    FROM orden_metricas
    WHERE id_op = ?
    LIMIT 1
  ", [$id]);
  if ($met = $cx->registro()) {
    $pesoTeorKg    = (float)($met['peso_teorico_total_kg']      ?? $met[0] ?? 0);
    $devTeorKg     = (float)($met['devolucion_teorica_kg']      ?? $met[1] ?? 0);
    $piezasOrdenKg = (float)($met['peso_piezas_total_orden_kg'] ?? $met[2] ?? 0);
    $ordenTeorKg   = (float)($met['peso_total_orden_kg']        ?? $met[3] ?? 0);
    if ($ordenTeorKg <= 0) $ordenTeorKg = $piezasOrdenKg + $devTeorKg;
  }
} catch (Throwable $e) {
  try {
    $cx->ejecutar("
      SELECT peso_teorico_total_kg, devolucion_teorica_kg
        FROM orden_metricas
       WHERE id_op = ?
       LIMIT 1
    ", [$id]);
    if ($met = $cx->registro()) {
      $pesoTeorKg  = (float)($met['peso_teorico_total_kg'] ?? $met[0] ?? 0);
      $devTeorKg   = (float)($met['devolucion_teorica_kg'] ?? $met[1] ?? 0);
      $ordenTeorKg = $pesoTeorKg + $devTeorKg;
      $piezasOrdenKg = max(0.0, $ordenTeorKg - $devTeorKg);
    }
  } catch (Throwable $e2) {
    if ($DEBUG) echo "Aviso métricas: " . $e2->getMessage() . "\n";
    error_log("PDF orden_metricas: " . $e2->getMessage());
  }
}

/* ---- Productos ---- */
$productos = [];
$cx->ejecutar("
  SELECT op.id_producto,
         p.nombre,
         op.cantidad_unidades,
         op.peso_teorico_g,
         op.ciclos_por_min
    FROM orden_producto op
    INNER JOIN producto p ON p.id_producto = op.id_producto
   WHERE op.id_op = ?
   ORDER BY p.nombre ASC
", [$id]);
while ($r = $cx->registro()) {
  $productos[] = [
    'id'       => $r['id_producto']      ?? $r[0],
    'nombre'   => $r['nombre']           ?? $r[1],
    'insertos' => (int)($r['cantidad_unidades'] ?? $r[2]),
    'peso_g'   => (float)($r['peso_teorico_g']  ?? $r[3]),
    'cpm'      => (float)($r['ciclos_por_min']  ?? $r[4]),
  ];
}

/* ---- Materia prima ---- */
$materias = [];
$cx->ejecutar("
  SELECT mp.tipo, mp.codigo_mp, mpr.referencia, mp.kg_plan
    FROM orden_materia_prima mp
    LEFT JOIN materia_prima mpr ON mpr.codigo = mp.codigo_mp
   WHERE mp.id_op = ?
   ORDER BY FIELD(mp.tipo,'original','peletizado','molido'), mpr.referencia ASC
", [$id]);
$totalKgPlan = 0.0;
while ($r = $cx->registro()) {
  $kg = isset($r['kg_plan']) ? (float)$r['kg_plan'] : (isset($r[3]) ? (float)$r[3] : 0.0);
  $materias[] = [
    'tipo'   => $r['tipo']       ?? $r[0],
    'codigo' => $r['codigo_mp']  ?? $r[1],
    'ref'    => $r['referencia'] ?? ($r[2] ?? ''),
    'kg'     => $kg,
  ];
  $totalKgPlan += $kg;
}
$cx->cerrar();

/* ---- Kilos a producir (para mostrar en PDF) ---- */
$kgAProducir = $totalKgPlan > 0 ? $totalKgPlan : ($ordenTeorKg > 0 ? $ordenTeorKg : null);

/* ================== ENCODING HELPER ================== */
function enc(string $s): string
{
  $r = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);
  return $r === false ? $s : $r;
}

/* ================== PDF ================== */
class OP_PDF extends FPDF
{
  public string $titulo     = 'ORDEN DE PRODUCCIÓN';
  public string $empresa    = '.';
  public ?string $logoPath  = null;

  function Header()
  {
    if ($this->logoPath && file_exists($this->logoPath)) {
      $this->Image($this->logoPath, 12, 9, 22);    // logo
      $this->SetXY(40, 12);  // mover cursor a la derecha del logo y un poco más abajo
    } else {
      $this->SetXY(12, 12);
    }

    $this->SetTextColor(34, 139, 34);
    $this->SetFont('Arial', 'B', 13);
    $this->Cell(0, 7, enc($this->titulo), 0, 1, 'L');
    $this->SetTextColor(0, 0, 0);
    $this->SetFont('Arial', '', 9);
    $this->Cell(0, 5, enc($this->empresa), 0, 1, 'L');
    $this->Ln(1);
    $this->SetDrawColor(200, 200, 200);
    $this->Line(12, $this->GetY(), 206, $this->GetY()); // ancho carta con márgenes
    $this->Ln(1);
  }

  function Footer()
  {
    $this->SetY(-12);
    $this->SetDrawColor(220, 220, 220);
    $this->Line(12, $this->GetY(), 206, $this->GetY());
    $this->SetFont('Arial', 'I', 8);
    $this->Cell(0, 9, enc('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'R');
  }

  function sectionTitle(string $txt)
  {
    $this->SetFillColor(234, 246, 238);
    $this->SetDrawColor(200, 200, 200);
    $this->SetTextColor(30, 162, 87);
    $this->SetFont('Arial', 'B', 10);
    $this->Cell(0, 7, enc($txt), 1, 1, 'L', true);
    $this->SetTextColor(0, 0, 0);
    $this->Ln(1);
  }

  // Encabezado de tabla (alto configurable)
  function Th($w, $txt, $align = 'C', $h = 6)
  {
    $this->SetFont('Arial', 'B', 8.5);
    $this->SetFillColor(240, 240, 240);
    $this->SetDrawColor(210, 210, 210);
    $this->Cell($w, $h, enc($txt), 1, 0, $align, true);
  }

  // Celda de tabla (alto configurable)
  function Td($w, $txt, $align = 'L', $fill = false, $h = 5)
  {
    $this->SetFont('Arial', '', 8.5);
    $this->SetDrawColor(230, 230, 230);
    $this->Cell($w, $h, enc($txt), 1, 0, $align, $fill);
  }
}

/* ---- Composición ---- */
$pdf = new OP_PDF('P', 'mm', 'Letter'); // >>> Carta
$pdf->AliasNbPages();

/* Márgenes compactos */
$LEFT_M   = 12;
$TOP_M    = 10;
$RIGHT_M  = 10;
$BOTTOM_M = 10;

$pdf->SetMargins($LEFT_M, $TOP_M, $RIGHT_M);
$pdf->SetAutoPageBreak(false); // >>> forzamos 1 página

// Logo: prioriza /logo.png y fallback a /assets/logo.png
$pdf->logoPath = __DIR__ . '/../assets/logo.png';


$pdf->AddPage();

$pageW   = $pdf->GetPageWidth();
$pageH   = $pdf->GetPageHeight();
$usableW = $pageW - $LEFT_M - $RIGHT_M;

$bottomLimit = $pageH - $BOTTOM_M;

/* helper para limitar filas y mostrar “y N más…” */
$renderLimitedRows = function (array $rows, int $max) {
  $shown = array_slice($rows, 0, max(0, $max));
  $hidden = max(0, count($rows) - count($shown));
  return [$shown, $hidden];
};

/* ========== DATOS BÁSICOS ========== */
$pdf->sectionTitle('Datos básicos');

$halfW   = (int)floor($usableW / 2);
$labelW  = 28;
$valueW  = $halfW - $labelW;

$pdf->SetFont('Arial', '', 9);
$pdf->SetDrawColor(220, 220, 220);

// Fila 1
$pdf->Cell($labelW, 6, enc('N° OP'), 1, 0, 'L');
$pdf->Cell($valueW, 6, enc($numeroOp), 1, 0, 'L');
$pdf->Cell($labelW, 6, enc('Fecha OP'), 1, 0, 'L');
$pdf->Cell($valueW, 6, enc($fechaOP ?: '—'), 1, 1, 'L');

// Fila 2
$pdf->Cell($labelW, 6, enc('Molde'), 1, 0, 'L');
$pdf->Cell($valueW, 6, enc($moldeNom), 1, 0, 'L');
$pdf->Cell($labelW, 6, enc('Máquina'), 1, 0, 'L');
$pdf->Cell($valueW, 6, enc($maqNom), 1, 1, 'L');

// Fila 3
$pdf->Cell($labelW, 6, enc('Colada (g/tiro)'), 1, 0, 'L');
$pdf->Cell($valueW, 6, enc(number_format($coladaG, 2, ',', '.')), 1, 0, 'L');
$pdf->Cell($labelW, 6, enc('Kilos a producir'), 1, 0, 'L');
$pdf->Cell($valueW, 6, enc($kgAProducir !== null ? number_format((float)$kgAProducir, 3, ',', '.') : '—'), 1, 1, 'L');

$pdf->Ln(1);

/* ========== COMPARATIVO DE PESOS ========== */
$pdf->sectionTitle('Comparativo de pesos');

// Anchuras
$numW      = 36;
$wConcept  = $usableW - (3 * $numW);
if ($wConcept < 56) {
  $numW     = (int)floor(($usableW - 56) / 3);
  $wConcept = $usableW - (3 * $numW);
}

// Encabezados
$pdf->Th($wConcept, 'Concepto',       'L', 6);
$pdf->Th($numW,     'Teórico (kg)',   'C', 6);
$pdf->Th($numW,     'Real (kg)',      'C', 6);
$pdf->Th($numW,     'Diferencia (kg)', 'C', 6);
$pdf->Ln();

// Fila: Total orden (piezas + colada)
$pdf->Td($wConcept, 'Total orden (piezas + colada)',         'L', false, 5);
$pdf->Td($numW,     number_format($ordenTeorKg, 3, ',', '.'), 'C', false, 5);
$pdf->Td($numW,     ' ',                                     'C', false, 5);
$pdf->Td($numW,     ' ',                                     'C', false, 5);
$pdf->Ln();

// Fila: Devolución total
$pdf->Td($wConcept, 'Devolución total',                       'L', false, 5);
$pdf->Td($numW,     number_format($devTeorKg, 3, ',', '.'),   'C', false, 5);
$pdf->Td($numW,     ' ',                                     'C', false, 5);
$pdf->Td($numW,     ' ',                                     'C', false, 5);
$pdf->Ln(3);

// Nota
$pdf->SetFont('Arial', '', 7.5);
$pdf->SetTextColor(110, 110, 110);
$pdf->Cell(0, 4, enc('Nota: El operario debe diligenciar las columnas "Real (kg)" y "Diferencia (kg)".'), 0, 1, 'L');
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(1);

/* ========== PRODUCTOS ========== */
$pdf->sectionTitle('Productos');

$wId   = 22;
$wNom  = 78;
$wIns  = 22;
$wPes  = 32;
$wCpm  = $usableW - ($wId + $wNom + $wIns + $wPes);

$pdf->Th($wId,  'ID',       'C', 6);
$pdf->Th($wNom, 'Nombre',   'L', 6);
$pdf->Th($wIns, 'Insertos', 'C', 6);
$pdf->Th($wPes, 'Peso (g)', 'C', 6);
$pdf->Th($wCpm, 'CPM',      'C', 6);
$pdf->Ln();

$MAX_PROD_ROWS = 6;
if (empty($productos)) {
  $pdf->SetFont('Arial', '', 8.5);
  $pdf->Cell($usableW, 6, enc('-- Sin productos --'), 1, 1, 'C');
} else {
  [$prodToShow, $hiddenProd] = $renderLimitedRows($productos, $MAX_PROD_ROWS);
  $fill = false;
  foreach ($prodToShow as $p) {
    $fill = !$fill;
    $pdf->Td($wId,  (string)$p['id'],                           'C', $fill, 5);
    $nom = (string)$p['nombre'];
    if (mb_strlen($nom, 'UTF-8') > 44) {
      $nom = mb_substr($nom, 0, 41, 'UTF-8') . '…';
    }
    $pdf->Td($wNom, $nom,                                        'L', $fill, 5);
    $pdf->Td($wIns, number_format($p['insertos'], 0, ',', '.'),  'C', $fill, 5);
    $pdf->Td($wPes, number_format($p['peso_g'],   2, ',', '.'),  'C', $fill, 5);
    $pdf->Td($wCpm, number_format($p['cpm'],      2, ',', '.'),  'C', $fill, 5);
    $pdf->Ln();
  }
  if ($hiddenProd > 0) {
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell($usableW, 5, enc("… y {$hiddenProd} producto(s) más en el sistema."), 1, 1, 'C');
  }
}
$pdf->Ln(1);

/* ========== MATERIA PRIMA (PLAN) ========== */
$pdf->sectionTitle('Materia prima (plan)');

$wTipo = 28;
$wCod  = 48;
$wRef  = $usableW - ($wTipo + $wCod + 28);
$wKg   = 28;

$pdf->Th($wTipo, 'Tipo',       'C', 6);
$pdf->Th($wCod,  'Código',     'C', 6);
$pdf->Th($wRef,  'Referencia', 'L', 6);
$pdf->Th($wKg,   'Kg plan',    'C', 6);
$pdf->Ln();

$MAX_MAT_ROWS = 5;
if (empty($materias)) {
  $pdf->SetFont('Arial', '', 8.5);
  $pdf->Cell($usableW, 6, enc('-- Sin registros --'), 1, 1, 'C');
} else {
  [$matToShow, $hiddenMat] = $renderLimitedRows($materias, $MAX_MAT_ROWS);
  $fill = false;
  foreach ($matToShow as $mat) {
    $fill = !$fill;
    $pdf->Td($wTipo, ucfirst((string)$mat['tipo']), 'C', $fill, 5);
    $pdf->Td($wCod,  (string)$mat['codigo'],        'C', $fill, 5);
    $ref = (string)($mat['ref'] ?? '');
    if (mb_strlen($ref, 'UTF-8') > 48) {
      $ref = mb_substr($ref, 0, 45, 'UTF-8') . '…';
    }
    $pdf->Td($wRef,  $ref,                                 'L', $fill, 5);
    $pdf->Td($wKg,   number_format((float)$mat['kg'], 3, ',', '.'), 'C', $fill, 5);
    $pdf->Ln();
  }
  if ($hiddenMat > 0) {
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell($usableW, 5, enc("… y {$hiddenMat} registro(s) más en el sistema."), 1, 1, 'C');
  }
  // Total
  $pdf->SetFont('Arial', 'B', 8.5);
  $pdf->Cell($wTipo + $wCod + $wRef, 6, enc('Total Kg plan'), 1, 0, 'R');
  $pdf->Cell($wKg, 6, enc(number_format($totalKgPlan, 3, ',', '.')), 1, 1, 'C');
}
$pdf->Ln(1);

/* ========== OBSERVACIONES (caja compacta) ========== */
$pdf->sectionTitle('Observaciones');

// caja más pequeña para garantizar 1 página
$boxH = 28; // <<< antes 90
$yStart = $pdf->GetY();
$pdf->SetDrawColor(210, 210, 210);
$pdf->Rect($LEFT_M, $yStart, $usableW, $boxH);
$pdf->SetFont('Arial', '', 9);

if ($obs !== '') {
  $pdf->SetXY($LEFT_M + 2.5, $yStart + 2.5);
  $pdf->MultiCell($usableW - 5, 4.2, enc($obs), 0, 'L');
} else {
  for ($lineY = $yStart + 8; $lineY < $yStart + $boxH - 5; $lineY += 6) {
    $pdf->Line($LEFT_M + 3, $lineY, $LEFT_M + $usableW - 3, $lineY);
  }
}
$pdf->SetY($yStart + $boxH + 4);

/* ========== FIRMAS ========== */
$pdf->sectionTitle('Firmas');

$pdf->Ln(1);
$pdf->SetFont('Arial', '', 8.5);

$gap = 10;
$colW = (int)floor(($usableW - (2 * $gap)) / 3);
$ySig = $pdf->GetY();

$pdf->Cell($colW, 6, enc('________________________'), 0, 0, 'C');
$pdf->Cell($gap, 6, '', 0, 0);
$pdf->Cell($colW, 6, enc('________________________'), 0, 0, 'C');
$pdf->Cell($gap, 6, '', 0, 0);
$pdf->Cell($colW, 6, enc('________________________'), 0, 1, 'C');

$pdf->Cell($colW, 5, enc('Responsable de Producción'), 0, 0, 'C');
$pdf->Cell($gap, 5, '', 0, 0);
$pdf->Cell($colW, 5, enc('Calidad'), 0, 0, 'C');
$pdf->Cell($gap, 5, '', 0, 0);
$pdf->Cell($colW, 5, enc('Almacén'), 0, 1, 'C');

/* ================== SALIDA ================== */
while (ob_get_level() > 0) {
  ob_end_clean();
}

$filename = 'OP_' . preg_replace('/[^A-Za-z0-9_\-]/', '', $numeroOp) . '.pdf';
$pdf->Output('I', $filename);
exit;
