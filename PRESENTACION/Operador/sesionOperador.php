<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * DASHBOARD OPERADOR • SOLMET
 * - KPIs del día
 * - Pie de estados de OP
 * - Serie de OP últimos 7 días (con ceros)
 * - Accesos rápidos
 */

require_once __DIR__ . '/../../persistencia/Conexion.php';
include_once __DIR__ . '/../../PRESENTACION/Operador/menuOperador.php';

/* ---- Autorización ---- */
$rol = $_SESSION['rol'] ?? '';
if ($rol !== 'operador' && $rol !== 'admin') {
    include __DIR__ . '/../Noautorizado.php';
    return;
}





/* ==== Datos de usuario para saludo ==== */
$nombre   = $_SESSION['nombre']   ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$username = $_SESSION['username'] ?? 'Operador';
$uid      = $_SESSION['uid']      ?? null;

$displayName = trim("$nombre $apellido") ?: $username;

/* ==== Helpers de consulta ==== */
function q(Conexion $cx, string $sql, array $p = [])  { $cx->ejecutar($sql,$p); return $cx->registros(); }
function q1(Conexion $cx, string $sql, array $p = []) { $cx->ejecutar($sql,$p); return $cx->registro(); }

/* ==== Consultas con try/finally ==== */
$kpi_activas = $kpi_hoy = $kpi_pausa = $kpi_maquinas = 0;
$estadosData = [];
$serieData   = [];

$cx = new Conexion();
try {
    $cx->abrir();

    /** OP activas (no finalizada/anulada) */
    $row = q1($cx, "SELECT COUNT(*) FROM orden_produccion WHERE estado NOT IN ('finalizada','anulada')");
    $kpi_activas = (int)($row[0] ?? 0);

    /** OP programadas hoy */
    $row = q1($cx, "SELECT COUNT(*) FROM orden_produccion WHERE DATE(fecha_inicio_prog)=CURDATE()");
    $kpi_hoy = (int)($row[0] ?? 0);

    /** OP en pausa */
    $row = q1($cx, "SELECT COUNT(*) FROM orden_produccion WHERE estado='pausada'");
    $kpi_pausa = (int)($row[0] ?? 0);

    /** Máquinas activas */
    $row = q1($cx, "SELECT COUNT(*) FROM maquina WHERE estado='activa'");
    $kpi_maquinas = (int)($row[0] ?? 0);

    /* ==== Pie: estados de OP ==== */
    $estadosRows = q($cx, "SELECT estado, COUNT(*) c FROM orden_produccion GROUP BY estado ORDER BY c DESC");
    foreach ($estadosRows as $r) { $estadosData[] = [$r[0], (int)$r[1]]; }

    /* ==== Serie últimos 7 días ==== */
    $raw = q($cx, "SELECT DATE(fecha_inicio_prog) d, COUNT(*) c
                     FROM orden_produccion
                    WHERE fecha_inicio_prog >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                 GROUP BY DATE(fecha_inicio_prog)
                 ORDER BY d ASC");
    $map = [];
    foreach ($raw as $r) { $map[$r[0]] = (int)$r[1]; }

    for ($i = 6; $i >= 0; $i--) {
      $d = (new DateTime())->modify("-{$i} day")->format('Y-m-d');
      $serieData[] = [$d, $map[$d] ?? 0];
    }
} finally {
    $cx->cerrar();
}

/* Para JS */
$jsEstados = json_encode($estadosData, JSON_UNESCAPED_UNICODE);
$jsSerie   = json_encode($serieData,   JSON_UNESCAPED_UNICODE);
?>

<div class="container-xxl px-3 px-md-4 px-lg-5 my-3">
  <!-- Encabezado -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h2 class="mb-0 fw-bold text-success">Panel del Operador</h2>
      <div class="text-muted">Bienvenido, <?= htmlspecialchars($displayName) ?></div>
    </div>
  </div>

  <!-- KPIs -->
<div class="row g-3 kpi-row">
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card-kpi">
      <div class="icon bg-soft"><i class="fa-solid fa-play"></i></div>
      <div class="meta"><div class="label">OP Activas</div><div class="value"><?= number_format($kpi_activas) ?></div></div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card-kpi">
      <div class="icon bg-soft"><i class="fa-solid fa-calendar-day"></i></div>
      <div class="meta"><div class="label">Programadas hoy</div><div class="value"><?= number_format($kpi_hoy) ?></div></div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card-kpi">
      <div class="icon bg-soft"><i class="fa-solid fa-pause-circle"></i></div>
      <div class="meta"><div class="label">En pausa</div><div class="value"><?= number_format($kpi_pausa) ?></div></div>
    </div>
  </div>
  <div class="col-12 col-md-6 col-lg-4">
    <div class="card-kpi">
      <div class="icon bg-soft"><i class="fa-solid fa-gears"></i></div>
      <div class="meta"><div class="label">Máquinas activas</div><div class="value"><?= number_format($kpi_maquinas) ?></div></div>
    </div>
  </div>
</div>




  <!-- Accesos rápidos -->
  <div class="row g-3 mt-1">

    <?php if ($rol === 'admin'): ?>
    <div class="col-12 col-md-6 col-lg-3">
      <a class="quick-card" href="?pid=<?= base64_encode('PRESENTACION/Maquina/listar.php'); ?>">
        <i class="fa-solid fa-gears"></i><span>Máquinas</span>
      </a>
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <a class="quick-card" href="?pid=<?= base64_encode('PRESENTACION/Molde/listar.php'); ?>">
        <i class="fa-solid fa-toolbox"></i><span>Moldes</span>
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ========== Charts ========== -->
<script>
(function(){
  const estados = <?= $jsEstados ?>;
  const serie   = <?= $jsSerie   ?>;

  google.charts.load('current', {packages:['corechart']});
  google.charts.setOnLoadCallback(drawAll);

  function drawAll(){ drawEstados(); drawSerie(); }

  function drawEstados(){
    const data = new google.visualization.DataTable();
    data.addColumn('string','Estado');
    data.addColumn('number','OP');
    data.addRows(estados || []);
    const opt = { pieHole:.45, legend:{position:'right'}, chartArea:{width:'90%',height:'80%'} };
    new google.visualization.PieChart(document.getElementById('chartEstados')).draw(data,opt);
  }

  function drawSerie(){
    const rows = (serie || []).map(([d,c]) => {
      const [Y,M,D] = d.split('-').map(Number);
      return [new Date(Y, M-1, D), c];
    });
    const data = new google.visualization.DataTable();
    data.addColumn('date','Fecha');
    data.addColumn('number','OP');
    if (rows.length) data.addRows(rows); else data.addRow([new Date(), 0]);

    const opt = {
      legend:{position:'none'},
      chartArea:{width:'90%',height:'80%'},
      curveType:'function',
      pointSize:4,
      hAxis:{ format:'MMM d' }
    };
    new google.visualization.LineChart(document.getElementById('chartSerie')).draw(data,opt);
  }

  let to=null;
  window.addEventListener('resize',()=>{ clearTimeout(to); to=setTimeout(drawAll,120); });
})();
</script>

<style>
:root{
  --g50:#f3fbf7;      /* necesario para el gradiente */
  --g100:#d6f0e0;
  --g600:#1ea257;
  --txt:#37474f;
}

html, body{ min-height:100vh; }

body{
  /* Fondo con el gradiente solicitado */
  background:
    radial-gradient(1200px 200px at -20% -50%, #ffffff 0%, transparent 60%),
    linear-gradient(135deg, var(--g100) 0%, var(--g50) 60%, #fff 100%);
  /* opcional, efecto sutil al hacer scroll */
  background-attachment: fixed;

  font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,"Noto Sans","Liberation Sans",sans-serif;
  color:var(--txt);
}

/* KPI cards */
.card-kpi{ background:#fff; border:1px solid #e4efe8; border-radius:16px; padding:16px; display:flex; gap:12px; align-items:center; box-shadow:0 6px 18px rgba(0,0,0,.06); }
.card-kpi .icon{ width:48px; height:48px; border-radius:12px; display:grid; place-items:center; color:var(--g600); font-size:20px; }
.card-kpi .icon.bg-soft{ background:linear-gradient(180deg, var(--g100), #fff); }
.card-kpi .meta .label{ font-size:.85rem; color:#6b7b86; margin-bottom:2px; }
.card-kpi .meta .value{ font-weight:800; font-size:1.35rem; color:#123; }
/* KPI más grandes (3 por fila en lg) */
.kpi-row .card-kpi{
  border-radius:20px;
  padding:22px 20px;
  min-height:120px;
  box-shadow:0 10px 24px rgba(0,0,0,.08);
}
@media (min-width: 992px){
  .kpi-row .card-kpi{ padding:24px 22px; }
}
.kpi-row .card-kpi .icon{
  width:60px; height:60px; border-radius:14px;
  font-size:24px;
}
.kpi-row .card-kpi .meta .label{
  font-size:.9rem; color:#6b7b86; margin-bottom:4px;
}
.kpi-row .card-kpi .meta .value{
  font-weight:800; font-size:1.6rem; color:#123;
}

/* Charts + quick actions */
.chart-card{ border:1px solid #e4efe8; border-radius:16px; box-shadow:0 6px 18px rgba(0,0,0,.06); }
.quick-card{ display:flex; align-items:center; gap:12px; padding:14px 16px; background:#fff; border:1px solid #e4efe8; border-radius:14px; text-decoration:none; color:#123; box-shadow:0 6px 18px rgba(0,0,0,.06); transition:.2s ease; }
.quick-card:hover{ transform:translateY(-3px); box-shadow:0 12px 24px rgba(0,0,0,.10); }
.quick-card i{ color:var(--g600); font-size:20px; }
.quick-card span{ font-weight:700; color:#123; }
</style>

