<?php
// PRESENTACION/Molde/listar.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';
require_once __DIR__ . '/../../logica/Molde.php'; // para usar listarDisponibles()

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
function fmt_n($n,$d=2){ $x=is_numeric($n)?(float)$n:0; return number_format($x,$d,',','.'); }
function badgeEstadoMolde($e){
  $e = strtolower((string)$e);
  $map = [
    'disponible'      => 'success',
    'mantenimiento'   => 'warning',
    'fuera_servicio'  => 'secondary'
  ];
  $labels = [
    'disponible'      => 'Disponible',
    'mantenimiento'   => 'Mantenimiento',
    'fuera_servicio'  => 'Fuera de servicio'
  ];
  $cls = $map[$e] ?? 'secondary';
  $txt = $labels[$e] ?? '—';
  return '<span class="badge rounded-pill bg-'.$cls.'">'.h($txt).'</span>';
}
function linkP($p){ $qs=$_GET; $qs['p']=(int)$p; return '?'.http_build_query($qs); }

/* ---- URLs ---- */
$URL_LISTAR = '?pid=' . base64_encode('PRESENTACION/Molde/listar.php');
$URL_CREAR  = '?pid=' . base64_encode('PRESENTACION/Molde/crear.php');
$URL_EDIT   = '?pid=' . base64_encode('PRESENTACION/Molde/editar.php');
$URL_DEL    = '?pid=' . base64_encode('PRESENTACION/Molde/eliminar.php');

/* ---- Filtros ---- */
$q        = trim($_GET['q'] ?? '');
$estado   = trim($_GET['estado'] ?? '');
$estValid = ['disponible','mantenimiento','fuera_servicio'];

$pag      = max(1, (int)($_GET['p'] ?? 1));
$PER_PAGE = 20;
$off      = ($pag - 1) * $PER_PAGE;

/* ---- Datos ---- */
$rows = [];
$total = 0;

$cx = new Conexion();
$cx->abrir();

try {
  // Caso “liviano”: sin búsqueda y estado=disponible -> usar lógica existente
  if ($q === '' && $estado === 'disponible') {
    // Molde::listarDisponibles() debería devolver al menos: id, nombre, colada_g
    $disponibles = Molde::listarDisponibles();
    // Paginación manual sobre el array
    $total = count($disponibles);
    $slice = array_slice($disponibles, $off, $PER_PAGE);
    foreach ($slice as $m) {
      $rows[] = [
        'id_molde' => $m['id']         ?? ($m['id_molde'] ?? ''),
        'nombre'   => $m['nombre']     ?? '',
        'colada_g' => $m['colada_g']   ?? ($m['peso_colada_g'] ?? null),
        'estado'   => 'disponible', // por definición “disponibles”
      ];
    }

  } else {
    // Fallback SQL con filtros (q / estado)
    $where  = [];
    $params = [];

    if ($q !== '') {
      $where[]  = "(mo.nombre LIKE ? OR mo.id_molde LIKE ?)";
      $like = '%'.$q.'%';
      $params[] = $like; $params[] = $like;
    }
    if ($estado !== '' && in_array($estado, $estValid, true)) {
      $where[]  = "mo.estado = ?";
      $params[] = $estado;
    }
    $wSQL = $where ? ('WHERE '.implode(' AND ', $where)) : '';

    // Total
    $cx->ejecutar("SELECT COUNT(*) FROM molde mo $wSQL", $params);
    $total = (int)($cx->registro()[0] ?? 0);

    // Página
    $sql = "
      SELECT mo.id_molde, mo.nombre, mo.peso_colada_g AS colada_g,
             COALESCE(mo.estado,'') AS estado
        FROM molde mo
        $wSQL
       ORDER BY mo.nombre ASC
       LIMIT $PER_PAGE OFFSET $off
    ";
    $cx->ejecutar($sql, $params);
    while ($r = $cx->registro()) $rows[] = $r;
  }
} finally {
  $cx->cerrar();
}

$totPages = max(1, (int)ceil($total / $PER_PAGE));
?>
<style>
:root{
  --g50:#f3fbf7; --g100:#e7f6ee; --g200:#d6f0e0; --g300:#c4ead3;
  --g600:#1ea257; --g700:#188249; --g800:#0f5a32;
  --txt:#28323a; --muted:#6c7b86; --border:#e3eee7;
  --shadow:0 8px 24px rgba(16,80,54,.08);
}
html,body{ min-height:100vh; }
body{
  background:
    radial-gradient(1200px 200px at -20% -50%, #ffffff 0%, transparent 60%),
    linear-gradient(135deg, var(--g100) 0%, var(--g50) 60%, #fff 100%);
  background-attachment:fixed;
  color:var(--txt);
  font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,"Noto Sans","Liberation Sans",sans-serif;
}
.page-wrap{ color:var(--txt); }

/* Hero */
.hero{ border-radius:18px; border:1px solid var(--border); box-shadow:var(--shadow); padding:18px; }
.eyebrow{ letter-spacing:.12em; font-size:.75rem; color:var(--g700); font-weight:700; text-transform:uppercase; }
.hero-title{ font-weight:800; color:var(--g800); }

/* Cards */
.card-elev{ background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:var(--shadow); overflow:hidden; }
.card-elev .card-body{ padding:16px; }
.card-head{
  padding:12px 16px; font-weight:800; color:var(--g800);
  background:linear-gradient(0deg, var(--g50), #fff); border-bottom:1px solid var(--border);
}
.card-head i{ color:var(--g600); }

/* Inputs */
.soft-input{ border-radius:12px; border:1px solid #dfeae4; background:#fff; transition:.2s ease; }
.soft-input:focus{ border-color:var(--g600); box-shadow:0 0 0 .2rem rgba(33,178,107,.12); }
.input-with-icon{ position:relative; }
.input-with-icon>i{ position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#6c7b86; }
.input-with-icon>input{ padding-left:38px; }

/* Buttons */
.btn-accent{ background:var(--g600); border-color:var(--g600); color:#fff; border-radius:12px; }
.btn-accent:hover{ background:var(--g700); border-color:var(--g700); color:#fff; }
.btn-success-soft{ background:var(--g100); color:var(--g700); border:1px solid var(--g200); border-radius:12px; }
.btn-success-soft:hover{ background:var(--g200); color:var(--g800); }
.btn-ghost{ background:#fff; border:1px solid var(--border); border-radius:12px; }
.btn-ghost:hover{ background:var(--g100); }

/* Table */
.table-wrap{ border-radius:14px; overflow:hidden; }
.table.sticky-head thead th{ position:sticky; top:0; z-index:1; }
.table thead th{
  font-size:.78rem; text-transform:uppercase; letter-spacing:.04em;
  background:linear-gradient(0deg, var(--g50), #fff);
  color:#335648; border-bottom:1px solid var(--border);
}
.table> :not(caption)>*>*{ padding:12px 14px; vertical-align:middle; }
.table tbody tr{ transition:.15s ease; }
.table tbody tr:hover{ background:#f6fbf8; }

/* Chips */
.code-chip{
  display:inline-block; padding:6px 10px; border-radius:10px;
  background:#f1f5f4; border:1px solid #e0e7e4; color:#40514a; font-weight:600;
}

/* Pagination */
.pagination.custom-pg .page-link{
  border-radius:12px; margin:0 3px; border:1px solid #dbe9e1; color:#365949; padding:.45rem .75rem;
}
.pagination.custom-pg .page-item.active .page-link{ background:var(--g600); border-color:var(--g600); color:#fff; }
.pagination.custom-pg .page-item.disabled .page-link{ opacity:.55; }
</style>

<div class="container-xxl px-3 px-md-4 px-lg-5 my-3 page-wrap">
  <!-- HERO -->
  <div class="hero mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="eyebrow mb-1">Moldes</div>
        <h3 class="hero-title mb-0">Listado de moldes</h3>
      </div>
      <?php if ($rol === 'admin'): ?>
      <a class="btn btn-accent" href="<?= h($URL_CREAR) ?>">
        <i class="fa-solid fa-plus me-1"></i> Nuevo molde
      </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card-elev mb-3">
    <div class="card-body">
      <form class="row g-3 g-md-2 align-items-end" method="get">
        <input type="hidden" name="pid" value="<?= h(base64_encode('PRESENTACION/Molde/listar.php')) ?>">
        <div class="col-12 col-md-5">
          <label class="form-label small text-muted mb-1">Buscar</label>
          <div class="input-with-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" value="<?= h($q) ?>" class="form-control soft-input" placeholder="Nombre / ID molde">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small text-muted mb-1">Estado</label>
          <select name="estado" class="form-select soft-input">
            <option value="">Todos</option>
            <?php foreach ($estValid as $e): ?>
              <option value="<?= h($e) ?>" <?= $estado===$e?'selected':'' ?>>
                <?= h($e==='fuera_servicio' ? 'Fuera de servicio' : ucfirst($e)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-2">
          <button class="btn btn-success-soft w-100">
            <i class="fa-solid fa-filter me-1"></i> Filtrar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card-elev">
    <div class="card-body">
      <?php if ($rol === 'admin'): ?>
        <div class="d-flex justify-content-end mb-2">
          <a class="btn btn-accent" href="<?= h($URL_CREAR) ?>">
            <i class="fa-solid fa-plus me-1"></i> Nuevo molde
          </a>
        </div>
      <?php endif; ?>

      <div class="table-responsive table-wrap">
        <table class="table align-middle sticky-head">
          <thead>
            <tr>
              <th style="width:14%">ID</th>
              <th>Nombre</th>
              <th class="text-end" style="width:18%">Colada (g/tiro)</th>
              <th style="width:18%">Estado</th>
              <th class="text-end" style="width:18%">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  <div class="d-flex flex-column align-items-center gap-2">
                    <i class="fa-regular fa-folder-open" style="font-size:26px; color:var(--g600);"></i>
                    <div>No hay moldes para los filtros aplicados.</div>
                  </div>
                </td>
              </tr>
            <?php else: foreach ($rows as $r):
              $id      = $r['id_molde'] ?? $r['id'] ?? $r[0] ?? '';
              $nombre  = $r['nombre']   ?? $r[1] ?? '';
              $colada  = $r['colada_g'] ?? $r['peso_colada_g'] ?? $r[2] ?? null;
              $estR    = $r['estado']   ?? $r[3] ?? '';
            ?>
              <tr>
                <td class="fw-semibold"><span class="code-chip"><?= h($id) ?></span></td>
                <td><?= h($nombre) ?></td>
                <td class="text-end"><?= $colada!==null ? fmt_n($colada,3) : '—' ?></td>
                <td><?= badgeEstadoMolde((string)$estR) ?></td>
                <td class="text-end">
                  <div class="btn-group gap-1">
                    <?php if ($rol === 'admin'): ?>
                      <a class="btn btn-sm btn-ghost" href="<?= h($URL_EDIT) . '&id=' . urlencode($id) ?>" title="Editar">
                        <i class="fa-solid fa-pen"></i>
                      </a>
                      <a
  class="btn btn-sm btn-ghost"
  href="<?= h($URL_DEL) . '&id=' . urlencode($id) ?>"
  title="Eliminar"
  onclick="return confirm(<?= json_encode("¿Eliminar el molde $id? Esta acción no se puede deshacer.") ?>);"
>
  <i class="fa-solid fa-trash" style="color:#c0392b;"></i>
</a>

                    <?php endif; ?>
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
