<?php
// PRESENTACION/OrdenProduccion/guardar.php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../persistencia/Conexion.php';

/* ======= Helpers ======= */
function jerr($msg, $code = 400){
  http_response_code($code);
  echo json_encode(['ok'=>false, 'msg'=>$msg], JSON_UNESCAPED_UNICODE);
  exit;
}
function toFloat($v){
  if ($v === null) return 0.0;
  $s = str_replace(',', '.', (string)$v);
  return is_numeric($s) ? (float)$s : 0.0;
}
function tableExists($cx, $table){
  try{
    $cx->ejecutar("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);
    return (int)($cx->registro()[0] ?? 0) > 0;
  }catch(Throwable $e){ return false; }
}

/* ======= Seguridad básica ======= */
// Rol
$rol = $_SESSION['rol'] ?? null;
if (!in_array($rol, ['admin','operador'], true)) {
  jerr('No autorizado', 403);
}
// CSRF
$csrf = $_POST['csrf'] ?? '';
if (!$csrf || !isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
  jerr('Token CSRF inválido', 419);
}

/* ======= Entrada ======= */
$numero_op  = trim($_POST['numero_op'] ?? '');
$id_molde   = trim($_POST['id_molde'] ?? '');
$id_maquina = trim($_POST['id_maquina'] ?? '');
$observ     = trim($_POST['observaciones'] ?? '');

$kg_a_producir = toFloat($_POST['kg_a_producir'] ?? '0');
$peso_colada_g = toFloat($_POST['peso_colada_g'] ?? '0');
$peso_total_piezas_g = toFloat($_POST['peso_total_piezas_g'] ?? '0'); // por tiro

$peso_teor_total_kg  = toFloat($_POST['peso_teorico_total_kg'] ?? '0');
$devolucion_teor_kg  = toFloat($_POST['devolucion_teorica_kg'] ?? '0');

/* Nota: en tu UI el campo se llama "Duración estimada (h)" pero el name es "duracion_min".
   Trataremos ese valor como HORAS (tal como lo llena tu JS) */
$dur_horas = toFloat($_POST['duracion_min'] ?? '0');  // horas
$dur_mins  = (int)round($dur_horas * 60);

/* Productos y MP */
$productos = json_decode($_POST['productos'] ?? '[]', true);
$mpItems   = json_decode($_POST['mp'] ?? '[]', true);

if (!is_array($productos)) $productos = [];
if (!is_array($mpItems))   $mpItems   = [];

/* ======= Validaciones ======= */
if ($numero_op === '') jerr('Falta el número de OP.');
if ($id_molde === '')  jerr('Debes seleccionar el molde.');
if ($id_maquina === '') jerr('Debes seleccionar la máquina.');

if (count($productos) === 0) jerr('Debes agregar al menos un producto.');
if ($kg_a_producir <= 0) jerr('Ingresa los kilos a producir (> 0).');

/* MP total no puede superar meta */
$mp_total_kg = 0.0;
foreach ($mpItems as $mp) {
  $kg = toFloat($mp['kg_plan'] ?? $mp['kg'] ?? 0);
  if ($kg > 0 && !empty($mp['codigo'] ?? $mp['codigo_mp'] ?? '')) {
    $mp_total_kg += $kg;
  }
}
if ($mp_total_kg - $kg_a_producir > 1e-6) {
  jerr('La suma de Kg de materia prima (' . number_format($mp_total_kg,3,',','.') .
       ') no puede exceder los Kg a producir (' . number_format($kg_a_producir,3,',','.') . ').');
}

/* Recalcular métricas clave en servidor (replica de tu front) */
if ($peso_total_piezas_g <= 0) {
  // fallback si no vino el total por tiro, lo calculamos de los productos
  $suma = 0.0;
  foreach ($productos as $p) {
    $cant = max(0, (int)($p['cantidad'] ?? 0));
    $peso = toFloat($p['peso_g'] ?? 0);
    $suma += $cant * $peso;
  }
  $peso_total_piezas_g = $suma;
}
$cierre_g = max(0.0, $peso_total_piezas_g + $peso_colada_g);
$tiros_est = ($kg_a_producir > 0 && $cierre_g > 0) ? (int)floor(($kg_a_producir * 1000.0) / $cierre_g) : 0;

$piezas_total_orden_kg = ($tiros_est * $peso_total_piezas_g) / 1000.0;
$colada_total_kg       = ($tiros_est * $peso_colada_g) / 1000.0;
$orden_total_kg        = $piezas_total_orden_kg + $colada_total_kg;

/* ======= Fechas requeridas por la tabla ======= */
$now = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha_inicio_prog   = $now->format('Y-m-d H:i:s');
$fecha_fin_estimada  = clone $now;
if ($dur_mins > 0) {
  $fecha_fin_estimada->modify('+' . $dur_mins . ' minutes');
}
$fecha_fin_estimada_str = $fecha_fin_estimada->format('Y-m-d H:i:s'); // NOT NULL en tu tabla

/* Usuario creador (id) */
$creado_por = $_SESSION['id_usuario'] ?? null; // ajusta al nombre real de tu sesión

/* ======= Insert ======= */
$cx = new Conexion();
try {
  $cx->abrir();

  // Transacción
  $cx->ejecutar("START TRANSACTION");

  // 1) Orden base
  $sqlOP = "
    INSERT INTO orden_produccion
      (numero_op, id_molde, id_maquina, fecha_inicio_prog, fecha_fin_estimada, estado, observaciones, creado_por, fecha_creacion, fecha_actualizacion)
    VALUES
      (?, ?, ?, ?, ?, 'creada', ?, ?, NOW(), NOW())
  ";
  $cx->ejecutar($sqlOP, [
    $numero_op,
    $id_molde,
    $id_maquina,
    $fecha_inicio_prog,
    $fecha_fin_estimada_str,
    $observ !== '' ? $observ : null,
    $creado_por
  ]);

  // id generado
  $cx->ejecutar("SELECT LAST_INSERT_ID()");
  $id_op = (int)($cx->registro()[0] ?? 0);
  if ($id_op <= 0) throw new RuntimeException('No se pudo obtener el ID de la OP.');

  // 2) Productos
  if (!empty($productos)) {
    $sqlProd = "
      INSERT INTO orden_producto
        (id_op, id_producto, cantidad_unidades, peso_teorico_g, ciclos_por_min)
      VALUES
        (?, ?, ?, ?, ?)
    ";
    foreach ($productos as $p) {
      $idp  = trim((string)($p['id_producto'] ?? ''));
      $cant = (int)($p['cantidad'] ?? 0);
      if ($idp === '' || $cant <= 0) continue;
      $peso_g = toFloat($p['peso_g'] ?? 0);
      $cpm    = toFloat($p['cpm'] ?? 0);
      $cx->ejecutar($sqlProd, [$id_op, $idp, $cant, $peso_g > 0 ? $peso_g : null, $cpm > 0 ? $cpm : null]);
    }
  }

  // 3) Materia prima
  if (!empty($mpItems)) {
    $sqlMP = "
      INSERT INTO orden_materia_prima
        (id_op, tipo, codigo_mp, kg_plan)
      VALUES
        (?, ?, ?, ?)
    ";
    foreach ($mpItems as $mp) {
      $tipo   = trim((string)($mp['tipo']   ?? ''));
      $codigo = trim((string)($mp['codigo'] ?? $mp['codigo_mp'] ?? ''));
      $kg     = toFloat($mp['kg_plan'] ?? $mp['kg'] ?? 0);
      if ($codigo !== '' && $kg > 0) {
        $cx->ejecutar($sqlMP, [$id_op, $tipo !== '' ? $tipo : 'original', $codigo, $kg]);
      }
    }
  }

  // 4) Métricas (si la tabla existe)
  if (tableExists($cx, 'orden_metricas')) {
    $sqlMet = "
      INSERT INTO orden_metricas
        (id_op, peso_teorico_total_kg, devolucion_teorica_kg, peso_piezas_total_orden_kg, peso_total_orden_kg)
      VALUES
        (?, ?, ?, ?, ?)
      ON DUPLICATE KEY UPDATE
        peso_teorico_total_kg = VALUES(peso_teorico_total_kg),
        devolucion_teorica_kg = VALUES(devolucion_teorica_kg),
        peso_piezas_total_orden_kg = VALUES(peso_piezas_total_orden_kg),
        peso_total_orden_kg = VALUES(peso_total_orden_kg)
    ";
    $cx->ejecutar($sqlMet, [
      $id_op,
      $peso_teor_total_kg,
      $devolucion_teor_kg,
      $piezas_total_orden_kg,
      $orden_total_kg
    ]);
  }

  // listo
  $cx->ejecutar("COMMIT");
  echo json_encode(['ok'=>true, 'id'=>$id_op], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  try { $cx->ejecutar("ROLLBACK"); } catch(Throwable $e2){}
  jerr('Error al guardar la OP: ' . $e->getMessage(), 500);
} finally {
  try { $cx->cerrar(); } catch(Throwable $e3){}
}
