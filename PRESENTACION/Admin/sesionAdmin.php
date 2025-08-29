<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * DASHBOARD ADMIN • SOLMET (contenido-only)
 */

require_once __DIR__ . '/../../persistencia/Conexion.php';
include_once __DIR__ . '/../../PRESENTACION/Admin/menuAdmin.php';


/* ---- Helpers ---- */
function q(Conexion $cx, string $sql, array $p = [])  { $cx->ejecutar($sql,$p); return $cx->registros(); }
function q1(Conexion $cx, string $sql, array $p = []) { $cx->ejecutar($sql,$p); return $cx->registro(); }

$cx = new Conexion(); $cx->abrir();

/* ==== KPIs ==== */
$row = q1($cx, "SELECT COUNT(*) FROM orden_produccion WHERE estado NOT IN ('finalizada','anulada')");
$kpi_activas = (int)($row[0] ?? 0);

$row = q1($cx, "SELECT COUNT(*) FROM orden_produccion
                 WHERE estado='finalizada'
                   AND YEAR(fecha_fin_real)=YEAR(CURDATE())
                   AND MONTH(fecha_fin_real)=MONTH(CURDATE())");
$kpi_finalizadas_mes = (int)($row[0] ?? 0);

$row = q1($cx, "SELECT COUNT(*) FROM orden_produccion WHERE DATE(fecha_inicio_prog)=CURDATE()");
$kpi_hoy = (int)($row[0] ?? 0);

$row = q1($cx, "SELECT COUNT(*) FROM usuario WHERE activo=1");
$kpi_usuarios = (int)($row[0] ?? 0);

/* ==== Pie estados ==== */
$estadosRows = q($cx, "SELECT estado, COUNT(*) c FROM orden_produccion GROUP BY estado ORDER BY c DESC");
$estadosData = [];
foreach ($estadosRows as $r) { $estadosData[] = [$r[0], (int)$r[1]]; }

/* ==== Serie últimos 14 días ==== */
$rawSerie = q($cx, "SELECT DATE(fecha_inicio_prog) d, COUNT(*) c
                      FROM orden_produccion
                     WHERE fecha_inicio_prog >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
                  GROUP BY DATE(fecha_inicio_prog)
                  ORDER BY d ASC");
$map = [];
foreach ($rawSerie as $r) { $map[$r[0]] = (int)$r[1]; }
$serieData = [];
for ($i = 13; $i >= 0; $i--) {
  $d = (new DateTime())->modify("-{$i} day")->format('Y-m-d');
  $serieData[] = [$d, $map[$d] ?? 0];
}
$cx->cerrar();

/* Para JS */
$jsEstados = json_encode($estadosData, JSON_UNESCAPED_UNICODE);
$jsSerie   = json_encode($serieData,   JSON_UNESCAPED_UNICODE);

/* Nombre admin */
$display = trim(($_SESSION['nombre'] ?? '').' '.($_SESSION['apellido'] ?? ''));
if (!$display) $display = $_SESSION['username'] ?? 'Administrador';
?>

<div class="container-xxl px-3 px-md-4 px-lg-5 my-3">
  <!-- Encabezado -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h2 class="mb-0 fw-bold text-success">Panel de Administración</h2>
      <div class="text-muted">Bienvenido, <?= htmlspecialchars($display) ?></div>
    </div>
  </div>

  <!-- KPIs (3 por fila en XL) -->
  <div class="row g-3 kpi-grid">
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card-kpi kpi-lg">
        <div class="icon bg-soft"><i class="fa-solid fa-play"></i></div>
        <div class="meta">
          <div class="label">OP Activas</div>
          <div class="value"><?= number_format($kpi_activas) ?></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card-kpi kpi-lg">
        <div class="icon bg-soft"><i class="fa-solid fa-flag-checkered"></i></div>
        <div class="meta">
          <div class="label">Finalizadas (mes)</div>
          <div class="value"><?= number_format($kpi_finalizadas_mes) ?></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card-kpi kpi-lg">
        <div class="icon bg-soft"><i class="fa-solid fa-calendar-day"></i></div>
        <div class="meta">
          <div class="label">Programadas hoy</div>
          <div class="value"><?= number_format($kpi_hoy) ?></div>
        </div>
      </div>
    </div>

    <!-- La cuarta KPI cae a la siguiente fila naturalmente -->
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card-kpi kpi-lg">
        <div class="icon bg-soft"><i class="fa-solid fa-user-check"></i></div>
        <div class="meta">
          <div class="label">Usuarios activos</div>
          <div class="value"><?= number_format($kpi_usuarios) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Accesos rápidos -->
  <div class="row g-3 mt-1">
    <div class="col-12 col-md-6 col-xl-3">
      <a class="quick-card" href="?pid=<?= base64_encode('PRESENTACION/Admin/listarUsuarios.php'); ?>">
        <i class="fa-solid fa-users-gear"></i><span>Gestionar usuarios</span>
      </a>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <a class="quick-card" href="?pid=<?= base64_encode('PRESENTACION/Maquina/listar.php'); ?>">
        <i class="fa-solid fa-gears"></i><span>Catálogo de máquinas</span>
      </a>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
      <a class="quick-card" href="?pid=<?= base64_encode('PRESENTACION/Molde/listar.php'); ?>">
        <i class="fa-solid fa-toolbox"></i><span>Catálogo de moldes</span>
      </a>
    </div>
  </div>
</div>

<!-- Google Charts -->
<script>
(function() {
  const estados = <?= $jsEstados ?>;
  const serie   = <?= $jsSerie   ?>;

  google.charts.load('current', { packages:['corechart'] });
  google.charts.setOnLoadCallback(drawAll);

  function drawAll(){ drawEstados(); drawSerie(); }

  function drawEstados() {
    const data = new google.visualization.DataTable();
    data.addColumn('string','Estado');
    data.addColumn('number','OP');
    data.addRows(estados || []);

    const opt = {
      pieHole: 0.45,
      legend: { position:'right' },
      chartArea: { width:'90%', height:'80%' },
      // Paleta en tonos verdes
      colors: ['#1ea257','#5ec28d','#aee0c5','#188249','#79b49b','#ccebdd']
    };
    new google.visualization.PieChart(document.getElementById('chartEstados')).draw(data, opt);
  }

  function drawSerie() {
    const rows = (serie || []).map(([d,c]) => {
      const [Y,M,D] = d.split('-').map(Number);
      return [new Date(Y, M-1, D), c];
    });

    const data = new google.visualization.DataTable();
    data.addColumn('date','Fecha');
    data.addColumn('number','OP');
    if (rows.length) data.addRows(rows); else data.addRow([new Date(), 0]);

    const opt = {
      legend:{ position:'none' },
      chartArea:{ width:'90%', height:'80%' },
      curveType:'function',
      pointSize:5,
      hAxis:{ format:'MMM d' },
      colors:['#1ea257']
    };
    new google.visualization.LineChart(document.getElementById('chartSerie')).draw(data, opt);
  }

  let to=null;
  window.addEventListener('resize', ()=>{ clearTimeout(to); to=setTimeout(drawAll,140); });
})();
</script>

<style>
:root{
  --g50:#f3fbf7;
  --g100:#d6f0e0;
  --g600:#1ea257;
  --g700:#188249;
  --txt:#37474f;
}

/* Fondo con gradiente verde (sutil) */
html, body{ min-height:100vh; }
body{
  background:
    radial-gradient(1200px 200px at -20% -50%, #ffffff 0%, transparent 60%),
    linear-gradient(135deg, var(--g100) 0%, var(--g50) 60%, #fff 100%);
  background-attachment: fixed;
  color:var(--txt);
  font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,"Noto Sans","Liberation Sans",sans-serif;
}

/* KPI cards más grandes */
.card-kpi{
  background:#fff; border:1px solid #e4efe8; border-radius:18px;
  padding:20px; display:flex; gap:14px; align-items:center;
  box-shadow:0 8px 22px rgba(0,0,0,.07);
}
.card-kpi.kpi-lg{ padding:24px; }
.card-kpi .icon{
  width:56px; height:56px; border-radius:14px; display:grid; place-items:center;
  color:var(--g600); font-size:22px;
}
.card-kpi .icon.bg-soft{
  background:linear-gradient(180deg, var(--g100), #fff);
  border:1px solid #e9f3ee;
}
.card-kpi .meta .label{ font-size:.9rem; color:#6b7b86; margin-bottom:2px; font-weight:600; }
.card-kpi .meta .value{ font-weight:800; font-size:1.6rem; color:#143; line-height:1; }

/* Tarjetas de gráficos y accesos rápidos */
.chart-card{ border:1px solid #e4efe8; border-radius:18px; box-shadow:0 8px 22px rgba(0,0,0,.07); }
.quick-card{
  display:flex; align-items:center; gap:12px; padding:16px 18px;
  background:#fff; border:1px solid #e4efe8; border-radius:16px; text-decoration:none; color:#123;
  box-shadow:0 8px 22px rgba(0,0,0,.07); transition:.18s ease;
}
.quick-card:hover{ transform:translateY(-3px); box-shadow:0 14px 26px rgba(0,0,0,.10); }
.quick-card i{ color:var(--g600); font-size:22px; }
.quick-card span{ font-weight:700; color:#123; }

/* Separación vertical más aire */
.kpi-grid > [class*="col-"]{ display:flex; }
.kpi-grid .card-kpi{ width:100%; }

/* Títulos de tarjetas */
.card .card-title{ font-weight:800; color:#0f5a32; }
</style>
