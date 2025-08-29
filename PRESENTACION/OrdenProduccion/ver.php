<?php
// PRESENTACION/OrdenProduccion/ver.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';
require_once __DIR__ . '/../../logica/Molde.php';
require_once __DIR__ . '/../../logica/Maquina.php';

/* ---- Autorización ---- */
$rol = $_SESSION['rol'] ?? null;
if (!in_array($rol, ['admin', 'operador'], true)) {
  include __DIR__ . '/../Noautorizado.php';
  return;
}

/* ---- Menú por rol ---- */
if ($rol === 'admin') include_once __DIR__ . '/../Admin/menuAdmin.php';
else                 include_once __DIR__ . '/../Operador/menuOperador.php';

/* ---- CSRF ---- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* ---- Helpers ---- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function fmt_n($n, $d = 3){ $x = is_numeric($n) ? (float)$n : 0; return number_format($x, $d, ',', '.'); }
function badgeEstado($e){
  $e = (string)$e;
  $map = [
    'creada' => 'secondary',
    'programada' => 'info',
    'en_proceso' => 'warning',
    'finalizada' => 'success',
    'cancelada' => 'danger',
    // compat
    'pausada' => 'secondary',
    'anulada' => 'dark',
  ];
  $cls = $map[$e] ?? 'secondary';
  return '<span class="badge rounded-pill bg-' . $cls . '">' . h(str_replace('_',' ',$e)) . '</span>';
}

/* ---- Helpers de introspección de esquema ---- */
function tableExists($cx, $table){
  $cx->ejecutar("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?", [$table]);
  return (int)($cx->registro()[0] ?? 0) > 0;
}
function colExists($cx, $table, $col){
  $cx->ejecutar("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?", [$table,$col]);
  return (int)($cx->registro()[0] ?? 0) > 0;
}

/**
 * Devuelve ['perfil'=>string|null, 'usuario'=>string|null] del creador de la OP.
 * Compatibilidad con varios esquemas:
 *  - columnas en orden_produccion: creado_por (tu esquema), id_usuario_crea, usuario_crea, rol_crea/perfil_crea/creado_por_rol
 *  - tabla usuario: intenta rol/perfil/tipo + nombre/nombre_completo/usuario/email
 */
function obtenerPerfilCreadorOP($cx, $id_op){
  $res = ['perfil'=>null, 'usuario'=>null];

  // A) ¿Hay campos de texto directo con el rol/perfil en la OP?
  foreach (['rol_crea','perfil_crea','creado_por_rol'] as $col) {
    if (colExists($cx,'orden_produccion',$col)) {
      $cx->ejecutar("SELECT $col FROM orden_produccion WHERE id_op=? LIMIT 1",[$id_op]);
      if ($r = $cx->registro()) {
        $val = $r[$col] ?? ($r[0] ?? null);
        if ($val) { $res['perfil'] = (string)$val; break; }
      }
    }
  }

  // B) ¿Hay texto con el nombre/usuario guardado en la OP?
  if (colExists($cx,'orden_produccion','usuario_crea')) {
    $cx->ejecutar("SELECT usuario_crea FROM orden_produccion WHERE id_op=? LIMIT 1",[$id_op]);
    if ($r = $cx->registro()) {
      $val = $r['usuario_crea'] ?? ($r[0] ?? null);
      if ($val) $res['usuario'] = (string)$val;
    }
  }

  // C) id del usuario creador (compat antiguo: id_usuario_crea)
  $userId = null;
  if (colExists($cx,'orden_produccion','creado_por')) {           // ← tu esquema
    $cx->ejecutar("SELECT creado_por FROM orden_produccion WHERE id_op=? LIMIT 1",[$id_op]);
    $tmp = $cx->registro();
    if ($tmp) $userId = (int)($tmp['creado_por'] ?? ($tmp[0] ?? 0));
  } elseif (colExists($cx,'orden_produccion','id_usuario_crea')) { // compat
    $cx->ejecutar("SELECT id_usuario_crea FROM orden_produccion WHERE id_op=? LIMIT 1",[$id_op]);
    $tmp = $cx->registro();
    if ($tmp) $userId = (int)($tmp['id_usuario_crea'] ?? ($tmp[0] ?? 0));
  }

  if ($userId && $userId > 0 && tableExists($cx,'usuario')) {
    // Columnas candidatas
    $rolCol = null;
    foreach (['rol','perfil','tipo'] as $c){ if (colExists($cx,'usuario',$c)) { $rolCol = $c; break; } }
    $nomCol = null;
    foreach (['nombre_completo','nombre','usuario','email'] as $c){ if (colExists($cx,'usuario',$c)) { $nomCol = $c; break; } }

    if ($rolCol || $nomCol) {
      $sel = implode(',', array_filter([$rolCol,$nomCol]));
      $cx->ejecutar("SELECT $sel FROM usuario WHERE id_usuario=? LIMIT 1",[$userId]);
      if ($u = $cx->registro()) {
        if ($rolCol) $res['perfil']  = $res['perfil']  ?: ($u[$rolCol] ?? ($u[0] ?? null));
        if ($nomCol) $res['usuario'] = $res['usuario'] ?: ($u[$nomCol] ?? ($rolCol ? ($u[1] ?? null) : ($u[0] ?? null)));
      }
    } else {
      // No sabemos columnas, pero al menos mostramos el ID
      $res['usuario'] = $res['usuario'] ?: ('#'.$userId);
    }
  } elseif (!$res['usuario'] && $userId) {
    // No hay tabla usuario; mostramos el ID como fallback
    $res['usuario'] = '#'.$userId;
  }

  // normalizar
  if ($res['perfil'])  $res['perfil']  = (string)$res['perfil'];
  if ($res['usuario']) $res['usuario'] = (string)$res['usuario'];
  return $res;
}

/* ---- Rutas útiles ---- */
$URL_CREAR  = '?pid=' . base64_encode('PRESENTACION/OrdenProduccion/crear.php');
$URL_VER    = '?pid=' . base64_encode('PRESENTACION/OrdenProduccion/ver.php');
$URL_PDF    = 'PRESENTACION/OrdenProduccion/pdf.php';
$API_CAMBIO = 'PRESENTACION/OrdenProduccion/api/cambiar_estado.php';

$estValid = ['creada','programada','en_proceso','finalizada','cancelada'];
?>
<style>
  :root{
    --g50:#f3fbf7; --g100:#e7f6ee; --g200:#d6f0e0; --g300:#c4ead3;
    --g500:#25b06b; --g600:#1ea257; --g700:#188249; --g800:#0f5a32;
    --txt:#28323a; --muted:#6c7b86; --bg:#e4efe8; --border:#e3eee7;
    --shadow:0 8px 24px rgba(16,80,54,.08);
  }
  body{min-height:100vh;background:
    radial-gradient(1200px 200px at -20% -50%, #fff 0%, transparent 60%),
    linear-gradient(135deg, var(--g100) 0%, var(--g50) 60%, #fff 100%);}
  .page-wrap{color:var(--txt)}
  .hero{border-radius:18px;border:1px solid var(--border);box-shadow:var(--shadow);padding:18px;background:
    radial-gradient(1200px 200px at -20% -50%, #fff 0%, transparent 60%),
    linear-gradient(135deg, var(--g100) 0%, var(--g50) 60%, #fff 100%)}
  .hero-green{background:
    radial-gradient(1200px 200px at -20% -50%, #fff 0%, transparent 60%),
    linear-gradient(135deg, var(--g100) 0%, var(--g50) 60%, #fff 100%)}
  .eyebrow{letter-spacing:.12em;font-size:.75rem;color:var(--g700);font-weight:700;text-transform:uppercase}
  .hero-title{font-weight:800;color:var(--g800)}
  .card-elev{background:#fff;border:1px solid var(--border);border-radius:18px;box-shadow:var(--shadow);overflow:hidden}
  .card-elev .card-body{padding:16px}
  .card-head{padding:12px 16px;font-weight:800;color:var(--g800);background:linear-gradient(0deg,var(--g50),#fff);border-bottom:1px solid var(--border)}
  .card-head i{color:var(--g600)}
  .soft-input{border-radius:12px;border:1px solid #dfeae4;background:#fff;transition:.2s ease}
  .soft-input:focus{border-color:var(--g600);box-shadow:0 0 0 .2rem rgba(33,178,107,.12)}
  .input-with-icon{position:relative}
  .input-with-icon>i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6c7b86}
  .input-with-icon>input{padding-left:38px}
  .btn-accent{background:var(--g600);border-color:var(--g600);color:#fff;border-radius:12px}
  .btn-accent:hover{background:var(--g700);border-color:var(--g700);color:#fff}
  .btn-success-soft{background:var(--g100);color:var(--g700);border:1px solid var(--g200);border-radius:12px}
  .btn-success-soft:hover{background:var(--g200);color:var(--g800)}
  .btn-secondary-soft{background:#fff;border:1px solid var(--border);color:#365949;border-radius:12px}
  .btn-secondary-soft:hover{background:var(--g100)}
  .btn-outline-success-soft{color:var(--g700);border:1px solid var(--g600);border-radius:12px;background:#fff}
  .btn-outline-success-soft:hover{background:var(--g600);color:#fff}
  .btn-ghost{background:#fff;border:1px solid var(--border);border-radius:12px}
  .btn-ghost:hover{background:var(--g100)}
  .apply-row-state i,#btnAplicarEstado i{transition:transform .15s ease}
  .apply-row-state:hover i,#btnAplicarEstado:hover i{transform:rotate(-25deg)}
  .table-wrap{border-radius:14px;overflow:hidden}
  .modern-table thead th,.table.sticky-head thead th{font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;background:linear-gradient(0deg,var(--g50),#fff);color:#335648;border-bottom:1px solid var(--border)}
  .table>:not(caption)>*>*{padding:12px 14px;vertical-align:middle}
  .table tbody tr{transition:.15s ease}
  .table tbody tr:hover{background:#f6fbf8}
  .op-chip{display:inline-block;padding:6px 10px;border-radius:999px;background:var(--g50);border:1px solid var(--g200);color:#184c32;font-weight:700}
  .date-chip{display:inline-block;padding:6px 10px;border-radius:10px;background:#f1f5f4;border:1px solid #e0e7e4;color:#40514a;font-weight:600}
  .chip{display:inline-block;padding:6px 12px;border-radius:999px;background:var(--g50);border:1px solid var(--g200);color:#184c32;font-weight:700}
  #estadoBadge .badge{padding:.45rem .6rem;border-radius:999px;font-weight:700}
  .empty-state{display:flex;flex-direction:column;align-items:center;gap:6px;color:#7b8b86}
  .empty-state i{font-size:26px;color:var(--g600)}
  .pagination.custom-pg .page-link{border-radius:12px;margin:0 3px;border:1px solid #dbe9e1;color:#365949;padding:.45rem .75rem}
  .pagination.custom-pg .page-item.active .page-link{background:var(--g600);border-color:var(--g600);color:#fff}
  .pagination.custom-pg .page-item.disabled .page-link{opacity:.55}
  .text-muted{color:var(--muted)!important}
  .fw-semibold{font-weight:700}
</style>
<?php
/* ---- Si viene ?id, mostrar DETALLE ---- */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$cx = new Conexion();
$cx->abrir();

if ($id > 0) {
  /* ===================== DETALLE ===================== */
  // Cabecera
  $cx->ejecutar("
    SELECT
      op.id_op, op.numero_op, op.observaciones,
      op.id_molde, op.id_maquina,
      op.fecha_inicio_prog, op.fecha_fin_estimada,
      op.fecha_creacion, op.estado,
      m.nombre AS molde_nombre,
      m.peso_colada_g AS colada_g,
      q.nombre AS maquina_nombre
    FROM orden_produccion op
    LEFT JOIN molde   m ON m.id_molde   = op.id_molde
    LEFT JOIN maquina q ON q.id_maquina = op.id_maquina
    WHERE op.id_op = ?
    LIMIT 1
  ", [$id]);
  $hdr = $cx->registro();
  if (!$hdr) {
    $cx->cerrar();
    echo '<div class="container my-4"><div class="alert alert-danger">OP no encontrada.</div></div>';
    return;
  }

  $numeroOp = $hdr['numero_op'] ?? '';
  $obs      = trim((string)($hdr['observaciones'] ?? ''));
  $moldeNom = $hdr['molde_nombre'] ?? '';
  $maqNom   = $hdr['maquina_nombre'] ?? '';
  $coladaG  = (float)($hdr['colada_g'] ?? 0);
  $estado   = $hdr['estado'] ?? 'creada';
  $fecCrea  = $hdr['fecha_creacion'] ?? $hdr['fecha_inicio_prog'] ?? null;

  // Perfil/Usuario que creó la OP (usa orden_produccion.creado_por)
  $creador = obtenerPerfilCreadorOP($cx, $id);
  $perfilCreador  = $creador['perfil']  ?: null;
  $usuarioCreador = $creador['usuario'] ?: null;

  // Fecha amigable desde numero_op (YYYYMMDD###)
  $fechaOP = '';
  if (preg_match('/^(\d{4})(\d{2})(\d{2})/', (string)$numeroOp, $m)) {
    $fechaOP = sprintf('%02d/%02d/%04d', (int)$m[3], (int)$m[2], (int)$m[1]);
  }

  // Métricas / totales
  $pesoTeorKg = 0.0; $devTeorKg = 0.0; $pzasOrdKg = 0.0; $ordenTotKg = 0.0;
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
      $pesoTeorKg = (float)($met['peso_teorico_total_kg']      ?? $met[0] ?? 0);
      $devTeorKg  = (float)($met['devolucion_teorica_kg']      ?? $met[1] ?? 0);
      $pzasOrdKg  = (float)($met['peso_piezas_total_orden_kg'] ?? $met[2] ?? 0);
      $ordenTotKg = (float)($met['peso_total_orden_kg']        ?? $met[3] ?? 0);
      if ($ordenTotKg <= 0) $ordenTotKg = $pzasOrdKg + $devTeorKg;
    }
  } catch (Throwable $e) {
    // esquema legado
    $cx->ejecutar("
      SELECT peso_teorico_total_kg, devolucion_teorica_kg
      FROM orden_metricas
      WHERE id_op = ? LIMIT 1
    ", [$id]);
    if ($met = $cx->registro()) {
      $pesoTeorKg = (float)($met['peso_teorico_total_kg'] ?? $met[0] ?? 0);
      $devTeorKg  = (float)($met['devolucion_teorica_kg'] ?? $met[1] ?? 0);
      $ordenTotKg = $pesoTeorKg + $devTeorKg;
      $pzasOrdKg  = max(0.0, $ordenTotKg - $devTeorKg);
    }
  }

  // Productos
  $productos = [];
  $cx->ejecutar("
    SELECT op.id_producto, p.nombre, op.cantidad_unidades, op.peso_teorico_g, op.ciclos_por_min
    FROM orden_producto op
    INNER JOIN producto p ON p.id_producto = op.id_producto
    WHERE op.id_op = ?
    ORDER BY p.nombre ASC
  ", [$id]);
  while ($r = $cx->registro()) $productos[] = $r;

  // Materia prima (y total)
  $materias = []; $totalKgPlan = 0.0;
  $cx->ejecutar("
    SELECT mp.tipo, mp.codigo_mp, mpr.referencia, mp.kg_plan
    FROM orden_materia_prima mp
    LEFT JOIN materia_prima mpr ON mpr.codigo = mp.codigo_mp
    WHERE mp.id_op = ?
    ORDER BY FIELD(mp.tipo,'original','peletizado','molido'), mpr.referencia ASC
  ", [$id]);
  while ($r = $cx->registro()) {
    $kg = isset($r['kg_plan']) ? (float)$r['kg_plan'] : (isset($r[3]) ? (float)$r[3] : 0.0);
    $totalKgPlan += $kg;
    $materias[] = $r;
  }

  // Kilos a producir (fallback si no hay campo dedicado)
  $kgAProducir = $totalKgPlan > 0 ? $totalKgPlan : ($ordenTotKg > 0 ? $ordenTotKg : null);

  $cx->cerrar();
?>
  <!-- Detalle OP -->
  <div class="container-xxl px-3 px-md-4 px-lg-5 my-3 page-wrap">
    <!-- HERO -->
    <div class="hero mb-3">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <div class="eyebrow mb-1">Orden de producción</div>
          <h3 class="hero-title mb-0">OP <?= h($numeroOp) ?></h3>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-secondary-soft" href="<?= h($URL_VER) ?>">
            <i class="fa-solid fa-list me-1"></i> Todas
          </a>
          <a class="btn btn-outline-success-soft" href="<?= h($URL_CREAR) ?>">
            <i class="fa-solid fa-plus me-1"></i> Nueva
          </a>
          <a class="btn btn-accent" target="_blank" href="<?= h($URL_PDF) . '?id=' . (int)$id ?>">
            <i class="fa-solid fa-file-pdf me-1"></i> PDF
          </a>
        </div>
      </div>
    </div>

    <!-- Datos básicos -->
    <div class="card-elev mb-3">
      <div class="card-head"><i class="fa-solid fa-sliders me-2"></i>Datos básicos</div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-6 col-lg-3">
            <div class="small text-muted">N° OP</div>
            <div class="fw-semibold"><?= h($numeroOp) ?></div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="small text-muted">Fecha OP</div>
            <div class="fw-semibold"><?= $fechaOP ? h($fechaOP) : '—' ?></div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="small text-muted">Molde</div>
            <div class="fw-semibold"><?= h($moldeNom) ?></div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="small text-muted">Máquina</div>
            <div class="fw-semibold"><?= h($maqNom) ?></div>
          </div>

          <div class="col-6 col-lg-3">
            <div class="small text-muted">Colada (g/tiro)</div>
            <div class="fw-semibold"><?= fmt_n($coladaG, 2) ?></div>
          </div>
          <div class="col-6 col-lg-3">
            <div class="small text-muted">Kilos a producir</div>
            <div class="fw-semibold"><?= $kgAProducir !== null ? fmt_n($kgAProducir, 3) : '—' ?></div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="small text-muted mb-1">Estado</div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <div id="estadoBadge"><?= badgeEstado($estado) ?></div>
              <select id="selEstado" class="form-select form-select-sm soft-input" style="max-width:220px;">
                <?php foreach ($estValid as $e): ?>
                  <option value="<?= h($e) ?>" <?= $estado === $e ? 'selected' : '' ?>><?= h(ucfirst(str_replace('_',' ',$e))) ?></option>
                <?php endforeach; ?>
              </select>
              <button id="btnAplicarEstado" class="btn btn-success-soft btn-sm">
                <i class="fa-solid fa-rotate me-1"></i> Aplicar
              </button>
              <small class="text-muted">Los cambios quedan auditados (usuario y fecha/hora).</small>
            </div>
          </div>

          <div class="col-12 col-lg-6">
            <div class="row g-2">
              <div class="col-12 col-md-6">
                <div class="small text-muted">Creación</div>
                <div class="fw-semibold"><?= h($fecCrea ?? '—') ?></div>
              </div>
              <div class="col-12 col-md-6">
                <?php if ($perfilCreador || $usuarioCreador): ?>
                  <div class="small text-muted">Creado por</div>
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php if ($usuarioCreador): ?>
                      <span class="chip"><strong><?= h($usuarioCreador) ?></strong></span>
                    <?php endif; ?>
                    <?php if ($perfilCreador): ?>
                      <span class="chip">Perfil: <strong><?= h(ucfirst($perfilCreador)) ?></strong></span>
                    <?php endif; ?>
                  </div>
                <?php else: ?>
                  <div class="small text-muted">Creado por</div>
                  <div class="text-muted">—</div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Comparativo -->
    <div class="card-elev mb-3">
      <div class="card-head"><i class="fa-solid fa-scale-balanced me-2"></i>Comparativo de pesos</div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle sticky-head modern-table">
            <thead>
              <tr>
                <th>Concepto</th>
                <th class="text-center" style="width:20%">Teórico (kg)</th>
                <th class="text-center" style="width:20%">Real (kg)</th>
                <th class="text-center" style="width:20%">Diferencia (kg)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Total orden (piezas + colada)</td>
                <td class="text-center"><?= fmt_n($ordenTotKg, 3) ?></td>
                <td class="text-center"></td>
                <td class="text-center"></td>
              </tr>
              <tr>
                <td>Devolución total</td>
                <td class="text-center"><?= fmt_n($devTeorKg, 3) ?></td>
                <td class="text-center"></td>
                <td class="text-center"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Productos -->
    <div class="card-elev mb-3">
      <div class="card-head"><i class="fa-solid fa-boxes-stacked me-2"></i>Productos</div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle sticky-head modern-table">
            <thead>
              <tr>
                <th style="width:12%">ID</th>
                <th>Nombre</th>
                <th class="text-end" style="width:12%">Insertos</th>
                <th class="text-end" style="width:14%">Peso (g)</th>
                <th class="text-end" style="width:14%">CPM</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($productos)): ?>
                <tr><td colspan="5" class="text-center text-muted">— Sin productos —</td></tr>
              <?php else: foreach ($productos as $p): ?>
                <tr>
                  <td><?= h($p['id_producto'] ?? $p[0]) ?></td>
                  <td><?= h($p['nombre'] ?? $p[1]) ?></td>
                  <td class="text-end"><?= number_format((int)($p['cantidad_unidades'] ?? $p[2]), 0, ',', '.') ?></td>
                  <td class="text-end"><?= fmt_n(($p['peso_teorico_g'] ?? $p[3]), 2) ?></td>
                  <td class="text-end"><?= fmt_n(($p['ciclos_por_min'] ?? $p[4]), 2) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Materia prima -->
    <div class="card-elev mb-4">
      <div class="card-head d-flex justify-content-between align-items-center">
        <span><i class="fa-solid fa-flask me-2"></i>Materia prima (plan)</span>
        <span class="chip">Total: <strong><?= fmt_n($totalKgPlan, 3) ?> kg</strong></span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle sticky-head modern-table">
            <thead>
              <tr>
                <th style="width:16%">Tipo</th>
                <th>Código</th>
                <th>Referencia</th>
                <th class="text-end" style="width:18%">Kg plan</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($materias)): ?>
                <tr><td colspan="4" class="text-center text-muted">— Sin registros —</td></tr>
              <?php else: foreach ($materias as $m): ?>
                <tr>
                  <td class="text-capitalize"><?= h($m['tipo'] ?? $m[0]) ?></td>
                  <td><?= h($m['codigo_mp'] ?? $m[1]) ?></td>
                  <td><?= h($m['referencia'] ?? ($m[2] ?? '')) ?></td>
                  <td class="text-end"><?= fmt_n(($m['kg_plan'] ?? $m[3]), 3) ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Observaciones -->
    <div class="card-elev mb-5">
      <div class="card-head"><i class="fa-solid fa-comment-dots me-2"></i>Observaciones</div>
      <div class="card-body">
        <?= $obs !== '' ? nl2br(h($obs)) : '<span class="text-muted">— Sin observaciones —</span>' ?>
      </div>
    </div>
  </div>

  <!-- CSRF p/ JS -->
  <input type="hidden" id="csrf" value="<?= h($csrf) ?>">
  <script>
    (function(){
      const btn   = document.getElementById('btnAplicarEstado');
      const sel   = document.getElementById('selEstado');
      const badge = document.getElementById('estadoBadge');
      if (!btn || !sel || !badge) return;

      btn.addEventListener('click', async (e)=>{
        e.preventDefault();
        const nuevo = sel.value;
        if (!nuevo) return;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Guardando...';
        try{
          const fd = new FormData();
          fd.set('csrf','<?= h($csrf) ?>');
          fd.set('id_op','<?= (int)$id ?>');
          fd.set('estado', nuevo);
          const r = await fetch('<?= h($API_CAMBIO) ?>',{ method:'POST', body: fd });
          const txt = await r.text();
          let j = {};
          try{ j = JSON.parse(txt); }catch{ throw new Error('Respuesta no JSON: '+txt.slice(0,120)); }
          if (!r.ok || !j.ok) throw new Error(j.msg || 'No fue posible cambiar el estado');
          badge.innerHTML = j.badge || ('<span class="badge rounded-pill bg-success">'+(j.estado || nuevo)+'</span>');
          btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Guardado';
          setTimeout(()=>{ btn.innerHTML = '<i class="fa-solid fa-rotate me-1"></i> Aplicar'; btn.disabled=false; }, 1000);
        }catch(err){
          alert(err.message || 'Error al actualizar estado');
          btn.innerHTML = '<i class="fa-solid fa-rotate me-1"></i> Aplicar';
          btn.disabled = false;
        }
      });
    })();
  </script>
<?php
  return;
}

/* ===================== LISTADO ===================== */
/* Filtros y paginación */
$q         = trim($_GET['q'] ?? '');
$estado_f  = trim($_GET['estado'] ?? '');
$maquina_f = trim($_GET['maquina'] ?? '');
$molde_f   = trim($_GET['molde'] ?? '');

$f1       = trim($_GET['f1'] ?? ''); // fecha desde (YYYY-MM-DD)
$f2       = trim($_GET['f2'] ?? ''); // fecha hasta  (YYYY-MM-DD)
$pag      = max(1, (int)($_GET['p'] ?? 1));
$PER_PAGE = 20;
$off      = ($pag - 1) * $PER_PAGE;

$where  = [];
$params = [];

$cx->ejecutar("SELECT id_maquina, nombre FROM maquina ORDER BY nombre ASC");
$maquinas = $cx->registros();
$cx->ejecutar("SELECT id_molde, nombre FROM molde ORDER BY nombre ASC");
$moldes = $cx->registros();

/* Búsqueda general */
if ($q !== '') {
  $where[]  = "(op.numero_op LIKE ? OR m.nombre LIKE ? OR q.nombre LIKE ?)";
  $like = '%' . $q . '%';
  $params[] = $like; $params[] = $like; $params[] = $like;
}
/* Estado */
if ($estado_f !== '' && in_array($estado_f, ['creada','programada','en_proceso','finalizada','cancelada'], true)) {
  $where[] = "op.estado = ?"; $params[] = $estado_f;
}
/* Máquina */
if ($maquina_f !== '') { $where[] = "op.id_maquina = ?"; $params[] = $maquina_f; }
/* Molde   */
if ($molde_f   !== '') { $where[] = "op.id_molde = ?";  $params[] = $molde_f; }

/* Rango de fechas (usa fecha_creacion) */
if ($f1 !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$f1)) { $where[] = "DATE(op.fecha_creacion) >= ?"; $params[] = $f1; }
if ($f2 !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$f2)) { $where[] = "DATE(op.fecha_creacion) <= ?"; $params[] = $f2; }

$wSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* Total */
$cx->ejecutar("
  SELECT COUNT(*)
  FROM orden_produccion op
  LEFT JOIN molde   m ON m.id_molde   = op.id_molde
  LEFT JOIN maquina q ON q.id_maquina = op.id_maquina
  $wSQL
", $params);
$totRows  = (int)($cx->registro()[0] ?? 0);
$totPages = max(1, (int)ceil($totRows / $PER_PAGE));

/* Listado */
$sql = "
  SELECT
    op.id_op, op.numero_op, op.estado, op.fecha_creacion,
    m.nombre AS molde_nombre,
    q.nombre AS maquina_nombre,
    COALESCE(SUM(mp.kg_plan),0) AS kg_plan_total
  FROM orden_produccion op
  LEFT JOIN molde   m  ON m.id_molde   = op.id_molde
  LEFT JOIN maquina q  ON q.id_maquina = op.id_maquina
  LEFT JOIN orden_materia_prima mp ON mp.id_op = op.id_op
  $wSQL
  GROUP BY op.id_op
  ORDER BY op.fecha_creacion DESC, op.id_op DESC
  LIMIT $PER_PAGE OFFSET $off
";
$cx->ejecutar($sql, $params);

$rows = [];
while ($r = $cx->registro()) $rows[] = $r;

$cx->cerrar();

/* Helper paginación: conserva pid y filtros */
function linkP($p){
  global $URL_VER;
  $qs = $_GET; $qs['p'] = $p;
  return $URL_VER . '&' . http_build_query($qs);
}
?>
<div class="container-xxl px-3 px-md-4 px-lg-5 my-3 page-wrap">
  <div class="hero hero-green mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="eyebrow mb-1">Órdenes</div>
        <h3 class="hero-title mb-0">Órdenes de Producción</h3>
      </div>
      <a class="btn btn-accent" href="<?= h($URL_CREAR) ?>">
        <i class="fa-solid fa-plus me-1"></i> Nueva orden
      </a>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card-elev mb-3">
    <div class="card-body">
      <?php $PID_OP = base64_encode('PRESENTACION/OrdenProduccion/ver.php'); ?>
      <form method="get" action="index.php" class="row g-3 g-md-2 align-items-end">
        <input type="hidden" name="pid" value="<?= h($PID_OP) ?>">

        <div class="col-12 col-md-4">
          <label class="form-label small text-muted mb-1">Buscar</label>
          <div class="input-with-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" value="<?= h($q) ?>" class="form-control soft-input"
              placeholder="N° OP / Molde / Máquina">
          </div>
        </div>

        <div class="col-6 col-md-3">
          <label class="form-label small text-muted mb-1">Estado</label>
          <select name="estado" class="form-select soft-input">
            <option value="">Todos</option>
            <option value="creada"      <?= $estado_f==='creada'      ? 'selected' : '' ?>>Creada</option>
            <option value="programada"  <?= $estado_f==='programada'  ? 'selected' : '' ?>>Programada</option>
            <option value="en_proceso"  <?= $estado_f==='en_proceso'  ? 'selected' : '' ?>>En proceso</option>
            <option value="finalizada"  <?= $estado_f==='finalizada'  ? 'selected' : '' ?>>Finalizada</option>
            <option value="cancelada"   <?= $estado_f==='cancelada'   ? 'selected' : '' ?>>Cancelada</option>
          </select>
        </div>

        <div class="col-6 col-md-3">
          <label class="form-label small text-muted mb-1">Máquina</label>
          <select name="maquina" class="form-select soft-input">
            <option value="">Todas</option>
            <?php foreach ($maquinas as $m): ?>
              <option value="<?= h($m['id_maquina']) ?>" <?= $maquina_f===$m['id_maquina'] ? 'selected' : '' ?>>
                <?= h($m['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-6 col-md-3">
          <label class="form-label small text-muted mb-1">Molde</label>
          <select name="molde" class="form-select soft-input">
            <option value="">Todos</option>
            <?php foreach ($moldes as $m): ?>
              <option value="<?= h($m['id_molde']) ?>" <?= $molde_f===$m['id_molde'] ? 'selected' : '' ?>>
                <?= h($m['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-6 col-md-2">
          <button type="submit" class="btn btn-success-soft w-100">
            <i class="fa-solid fa-filter me-1"></i> Filtrar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card-elev">
    <div class="card-body">
      <div class="table-responsive table-wrap">
        <table class="table align-middle sticky-head modern-table">
          <thead>
            <tr>
              <th style="width:14%">N° OP</th>
              <th>Molde</th>
              <th>Máquina</th>
              <th class="text-end" style="width:14%">Kg plan</th>
              <th style="width:18%">Estado</th>
              <th style="width:18%">Creación</th>
              <th style="width:16%"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <div class="empty-state">
                    <i class="fa-regular fa-folder-open"></i>
                    <div>No hay órdenes para los filtros aplicados.</div>
                  </div>
                </td>
              </tr>
            <?php else: foreach ($rows as $r): ?>
              <tr data-id="<?= (int)$r['id_op'] ?>">
                <td class="fw-semibold"><span class="op-chip"><?= h($r['numero_op']) ?></span></td>
                <td><?= h($r['molde_nombre']) ?></td>
                <td><?= h($r['maquina_nombre']) ?></td>
                <td class="text-end"><?= fmt_n($r['kg_plan_total'] ?? 0, 3) ?></td>
                <td>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="estado-badge"><?= badgeEstado($r['estado']) ?></div>
                    <select class="form-select form-select-sm soft-input state-select" style="max-width:190px;">
                      <?php foreach ($estValid as $e): ?>
                        <option value="<?= h($e) ?>" <?= ($r['estado'] === $e ? 'selected' : '') ?>>
                          <?= h(ucfirst(str_replace('_',' ',$e))) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn btn-success-soft btn-sm apply-row-state" title="Aplicar estado">
                      <i class="fa-solid fa-rotate"></i>
                    </button>
                  </div>
                </td>
                <td><span class="date-chip"><?= h($r['fecha_creacion']) ?></span></td>
                <td class="text-end">
                  <div class="btn-group gap-1">
                    <a class="btn btn-sm btn-outline-secondary btn-ghost" target="_blank"
                       href="<?= h($URL_PDF) . '?id=' . (int)$r['id_op'] ?>" title="PDF">
                      <i class="fa-solid fa-file-pdf"></i>
                    </a>
                    <a class="btn btn-sm btn-accent" href="<?= h($URL_VER) . '&id=' . (int)$r['id_op'] ?>">
                      <i class="fa-solid fa-eye me-1"></i> Ver
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div class="small text-muted">
          <?php
          $from = $totRows ? ($off + 1) : 0;
          $to   = min($off + $PER_PAGE, $totRows);
          printf('Mostrando %d–%d de %d', $from, $to, $totRows);
          ?>
        </div>
        <nav>
          <ul class="pagination custom-pg mb-0">
            <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= h(linkP(max(1, $pag - 1))) ?>">«</a>
            </li>
            <li class="page-item active"><span class="page-link"><?= (int)$pag ?></span></li>
            <li class="page-item <?= $pag >= $totPages ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= h(linkP(min($totPages, $pag + 1))) ?>">»</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- CSRF para JS (listado) -->
<input type="hidden" id="csrf" value="<?= h($csrf) ?>">

<script>
  (function(){
    const csrf = document.getElementById('csrf')?.value || '';
    // Botones "aplicar" por fila en el listado
    document.addEventListener('click', async (e)=>{
      const btn = e.target.closest('.apply-row-state');
      if (!btn) return;
      e.preventDefault();
      const tr = btn.closest('tr');
      if (!tr) return;
      const id = tr.getAttribute('data-id');
      const sel = tr.querySelector('.state-select');
      const badge = tr.querySelector('.estado-badge');
      if (!id || !sel || !badge) return;

      const nuevo = sel.value;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
      try{
        const fd = new FormData();
        fd.set('csrf', csrf);
        fd.set('id_op', id);
        fd.set('estado', nuevo);
        const r = await fetch('<?= h($API_CAMBIO) ?>',{ method:'POST', body: fd });
        const txt = await r.text();
        let j = {};
        try{ j = JSON.parse(txt); }catch{ throw new Error('Respuesta no JSON: '+txt.slice(0,120)); }
        if (!r.ok || !j.ok) throw new Error(j.msg || 'No fue posible cambiar el estado');
        badge.innerHTML = j.badge || ('<span class="badge rounded-pill bg-success">'+(j.estado || nuevo)+'</span>');
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        setTimeout(()=>{ btn.innerHTML = '<i class="fa-solid fa-rotate"></i>'; btn.disabled=false; }, 800);
      }catch(err){
        alert(err.message || 'Error al actualizar estado');
        btn.innerHTML = '<i class="fa-solid fa-rotate"></i>';
        btn.disabled = false;
      }
    });
  })();
</script>
