<?php
// PRESENTACION/MateriaPrima/listar.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';
require_once __DIR__ . '/../../persistencia/MateriaPrimaDAO.php';

/* ---- Autorización ---- */
$rol = $_SESSION['rol'] ?? null;
if (!in_array($rol, ['admin','operador'], true)) {
  include __DIR__ . '/../Noautorizado.php';
  exit;
}

/* ---- Menú ---- */
if ($rol === 'admin') include_once __DIR__ . '/../Admin/menuAdmin.php';
else                 include_once __DIR__ . '/../Operador/menuOperador.php';

/* ---- Helpers ---- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function badgeEstadoMP($e){
  $map = [
    'original'   => 'primary',
    'peletizado' => 'info',
    'molido'     => 'warning',
  ];
  $cls = $map[strtolower((string)$e)] ?? 'secondary';
  return '<span class="badge rounded-pill bg-'.$cls.'">'.h(ucfirst($e)).'</span>';
}
function badgeActivo($a){
  return ((int)$a===1)
    ? '<span class="badge rounded-pill bg-success">Activo</span>'
    : '<span class="badge rounded-pill bg-secondary">Inactivo</span>';
}
function linkP($p){ $qs=$_GET; $qs['p']=(int)$p; return '?'.http_build_query($qs); }

/* ---- URLs ---- */
$URL_LISTAR = '?pid=' . base64_encode('PRESENTACION/MateriaPrima/listar.php');
$URL_CREAR  = '?pid=' . base64_encode('PRESENTACION/MateriaPrima/crear.php');
$URL_EDIT   = '?pid=' . base64_encode('PRESENTACION/MateriaPrima/editar.php');
$URL_DEL    = '?pid=' . base64_encode('PRESENTACION/MateriaPrima/eliminar.php');

/* ---- Filtros ---- */
$q        = trim($_GET['q']        ?? '');
$estado   = trim($_GET['estado']   ?? '');
$polimero = trim($_GET['polimero'] ?? '');
$activo   = trim($_GET['activo']   ?? '');

$pag      = max(1, (int)($_GET['p'] ?? 1));
$PER_PAGE = 20;
$off      = ($pag - 1) * $PER_PAGE;

/* ---- Datos ---- */
$rows = [];
$total = 0;

$cx = new Conexion();
$cx->abrir();

try {
  $filtros = [
    'q'        => $q,
    'estado'   => $estado,
    'polimero' => $polimero,
    'activo'   => ($activo !== '' ? (string)(int)$activo : ''),
  ];

  [$sqlC, $parC] = MateriaPrimaDAO::contarConFiltros($filtros);
  $cx->ejecutar($sqlC, $parC);
  $total = (int)($cx->registro()[0] ?? 0);

  [$sqlL, $parL] = MateriaPrimaDAO::listarConFiltros($filtros, $PER_PAGE, $off);
  $cx->ejecutar($sqlL, $parL);
  while ($r = $cx->registro()) $rows[] = $r;

} finally {
  $cx->cerrar();
}

$totPages = max(1, (int)ceil($total / $PER_PAGE));
$estadosValid = MateriaPrimaDAO::estadosValidos();
?>
<style>
:root{
  --g50:#f3fbf7; --g100:#e7f6ee; --g200:#d6f0e0; --g300:#c4ead3;
  --g600:#1ea257; --g700:#188249; --g800:#0f5a32;
  --txt:#28323a; --muted:#6c7b86; --border:#e3eee7;
  --shadow:0 8px 24px rgba(16,80,54,.08);
}
body{ background:var(--g50); }
.page-wrap{ color:var(--txt); }
.hero{ border-radius:18px; border:1px solid var(--border); box-shadow:var(--shadow); padding:18px; background:#fff; }
.eyebrow{ letter-spacing:.12em; font-size:.75rem; color:var(--g700); font-weight:700; text-transform:uppercase; }
.hero-title{ font-weight:800; color:var(--g800); }
.card-elev{ background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:var(--shadow); overflow:hidden; }
.card-elev .card-body{ padding:16px; }
.soft-input{ border-radius:12px; border:1px solid #dfeae4; background:#fff; transition:.2s ease; }
.soft-input:focus{ border-color:var(--g600); box-shadow:0 0 0 .2rem rgba(33,178,107,.12); }
.btn-accent{ background:var(--g600); border-color:var(--g600); color:#fff; border-radius:12px; }
.btn-accent:hover{ background:var(--g700); }
.btn-ghost{ background:#fff; border:1px solid var(--border); border-radius:12px; }
.btn-ghost:hover{ background:var(--g100); }
.table-wrap{ border-radius:14px; overflow:hidden; }
.table tbody tr:hover{ background:#f6fbf8; }
.code-chip{ display:inline-block; padding:6px 10px; border-radius:10px; background:#f1f5f4; border:1px solid #e0e7e4; color:#40514a; font-weight:600; }
</style>

<div class="container-xxl px-3 px-md-4 px-lg-5 my-3 page-wrap">
  <div class="hero mb-3 d-flex justify-content-between align-items-center">
    <div>
      <div class="eyebrow mb-1">Materia prima</div>
      <h3 class="hero-title mb-0">Listado de materia prima</h3>
    </div>
    
  </div>

  <!-- Filtros -->
  <div class="card-elev mb-3">
    <div class="card-body">
      <form class="row g-3" method="get">
        <input type="hidden" name="pid" value="<?= h(base64_encode('PRESENTACION/MateriaPrima/listar.php')) ?>">
        <div class="col-md-4">
          <label class="form-label small">Buscar</label>
          <input type="text" name="q" value="<?= h($q) ?>" class="form-control soft-input" placeholder="Código / Referencia / Polímero">
        </div>
        <div class="col-md-2">
          <label class="form-label small">Estado</label>
          <select name="estado" class="form-select soft-input">
            <option value="">Todos</option>
            <?php foreach ($estadosValid as $e): ?>
              <option value="<?= h($e) ?>" <?= $estado===$e?'selected':'' ?>><?= h(ucfirst($e)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small">Polímero</label>
          <input type="text" name="polimero" value="<?= h($polimero) ?>" class="form-control soft-input">
        </div>
        <div class="col-md-2">
          <label class="form-label small">Activo</label>
          <select name="activo" class="form-select soft-input">
            <option value="">Todos</option>
            <option value="1" <?= $activo==='1'?'selected':'' ?>>Activo</option>
            <option value="0" <?= $activo==='0'?'selected':'' ?>>Inactivo</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-success-soft w-100"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card-elev">
    <div class="card-body">
      <div class="table-responsive table-wrap">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Código</th>
              <th>Polímero</th>
              <th>Referencia</th>
              <th>Estado</th>
              <th>Color</th>
              <th>Activo</th>
              <th>Creado</th>
              
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr><td colspan="8" class="text-center text-muted">No hay registros</td></tr>
            <?php else: foreach($rows as $r): 
              $codigo = $r['codigo'] ?? '';
            ?>
              <tr>
                <td><span class="code-chip"><?= h($codigo) ?></span></td>
                <td><?= h($r['polimero'] ?? '') ?></td>
                <td><?= h($r['referencia'] ?? '') ?></td>
                <td><?= badgeEstadoMP($r['estado'] ?? '') ?></td>
                <td><?= h($r['color'] ?? '—') ?></td>
                <td><?= badgeActivo($r['activo'] ?? 0) ?></td>
                <td><?= h($r['creado'] ?? '') ?></td>
                
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="small text-muted">
          <?php
          $from = $total ? ($off+1) : 0;
          $to   = min($off + $PER_PAGE, $total);
          echo "Mostrando $from-$to de $total";
          ?>
        </div>
        <nav>
          <ul class="pagination custom-pg mb-0">
            <li class="page-item <?= $pag<=1?'disabled':'' ?>">
              <a class="page-link" href="<?= h(linkP(max(1,$pag-1))) ?>">«</a>
            </li>
            <li class="page-item active"><span class="page-link"><?= (int)$pag ?></span></li>
            <li class="page-item <?= $pag>=$totPages?'disabled':'' ?>">
              <a class="page-link" href="<?= h(linkP(min($totPages,$pag+1))) ?>">»</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>
