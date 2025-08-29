<?php
// PRESENTACION/Inserto/listar.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';

/* ---- Autorización ---- */
$rol = $_SESSION['rol'] ?? null;
if (!in_array($rol, ['admin', 'operador'], true)) {
  include __DIR__ . '/../Noautorizado.php';
  exit;
}

/* ---- Menú ---- */
if ($rol === 'admin') include_once __DIR__ . '/../Admin/menuAdmin.php';
else                 include_once __DIR__ . '/../Operador/menuOperador.php';

/* ---- CSRF ---- */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

/* ---- Helpers ---- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function badgeActivo($a){
  return $a
    ? '<span class="badge rounded-pill bg-success">Activo</span>'
    : '<span class="badge rounded-pill bg-secondary">Inactivo</span>';
}
function linkP($p){
  $qs = $_GET;
  $qs['p'] = (int)$p;
  return '?' . http_build_query($qs);
}

/* ---- URLs ---- */
$URL_LISTAR = '?pid=' . base64_encode('PRESENTACION/Inserto/listar.php');
$URL_CREAR  = '?pid=' . base64_encode('PRESENTACION/Inserto/crear.php');
$URL_EDIT   = '?pid=' . base64_encode('PRESENTACION/Inserto/editar.php');
$URL_DEL = '?pid=' . base64_encode('PRESENTACION/Inserto/eliminar.php');
/* ---- Filtros ---- */
$q        = trim($_GET['q'] ?? '');
$estado   = trim($_GET['estado'] ?? ''); // 'activo' | 'inactivo' | ''
$pag      = max(1, (int)($_GET['p'] ?? 1));
$PER_PAGE = 20;
$off      = ($pag - 1) * $PER_PAGE;

/* ---- Datos ---- */
$rows = [];
$total = 0;

$cx = new Conexion();
$cx->abrir();

try {
  $where  = [];
  $params = [];

  if ($q !== '') {
    $where[] = "(i.descripcion LIKE ? OR i.id_inserto LIKE ? OR i.id_molde LIKE ?)";
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like);
  }
  if ($estado !== '') {
    $where[] = "i.activo = ?";
    $params[] = $estado === 'activo' ? 1 : 0;
  }
  $wSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

  // Total
  $cx->ejecutar("SELECT COUNT(*) FROM inserto i $wSQL", $params);
  $total = (int)($cx->registro()[0] ?? 0);

  // Página
  $sql = "
    SELECT i.id_inserto, i.id_molde, i.descripcion, i.activo,
           DATE_FORMAT(i.created_at,'%Y-%m-%d') AS creado
      FROM inserto i
      $wSQL
     ORDER BY i.descripcion ASC
     LIMIT $PER_PAGE OFFSET $off
  ";
  $cx->ejecutar($sql, $params);
  while ($r = $cx->registro()) $rows[] = $r;
} finally {
  $cx->cerrar();
}

$totPages = max(1, (int)ceil($total / $PER_PAGE));
?>
<style>
/* … deja aquí tus estilos tal cual los tenías … */
</style>

<div class="container-xxl px-3 px-md-4 px-lg-5 my-3 page-wrap">
  <!-- HERO -->
  <div class="hero mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="eyebrow mb-1">Insertos</div>
        <h3 class="hero-title mb-0">Listado de insertos</h3>
      </div>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card-elev mb-3">
    <div class="card-body">
      <form class="row g-3 g-md-2 align-items-end" method="get">
        <input type="hidden" name="pid" value="<?= h(base64_encode('PRESENTACION/Inserto/listar.php')) ?>">
        <div class="col-12 col-md-6">
          <label class="form-label small text-muted mb-1">Buscar</label>
          <div class="input-with-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" value="<?= h($q) ?>" class="form-control soft-input" placeholder="Descripción / ID inserto / ID molde">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small text-muted mb-1">Estado</label>
          <select name="estado" class="form-select soft-input">
            <option value="">Todos</option>
            <option value="activo" <?= $estado === 'activo' ? 'selected' : '' ?>>Activo</option>
            <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
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
            <i class="fa-solid fa-plus me-1"></i> Nuevo inserto
          </a>
        </div>
      <?php endif; ?>

      <div class="table-responsive table-wrap">
        <table class="table align-middle sticky-head">
          <thead>
            <tr>
              <th style="width:18%">ID</th>
              <th style="width:14%">Molde</th>
              <th>Descripción</th>
              <th style="width:12%">Estado</th>
              <th style="width:14%">Creado</th>
              <th class="text-end" style="width:18%">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  <div class="d-flex flex-column align-items-center gap-2">
                    <i class="fa-regular fa-folder-open" style="font-size:26px; color:var(--g600);"></i>
                    <div>No hay insertos para los filtros aplicados.</div>
                  </div>
                </td>
              </tr>
              <?php else: foreach ($rows as $r):
                $idIns   = $r['id_inserto'] ?? '';
                $idMolde = $r['id_molde'] ?? '';
                $desc    = $r['descripcion'] ?? '';
                $activo  = (int)($r['activo'] ?? 0);
                $creado  = $r['creado'] ?? '';
              ?>
                <tr>
                  <td class="fw-semibold"><span class="code-chip"><?= h($idIns) ?></span></td>
                  <td><span class="code-chip"><?= h($idMolde) ?></span></td>
                  <td><?= h($desc) ?></td>
                  <td><?= badgeActivo($activo) ?></td>
                  <td><?= h($creado) ?></td>
                  <td class="text-end">
                    <div class="btn-group gap-1">
                      <?php if ($rol === 'admin'): ?>
                        <a class="btn btn-sm btn-ghost" href="<?= h($URL_EDIT) . '&id=' . urlencode($idIns) ?>" title="Editar">
                          <i class="fa-solid fa-pen"></i>
                        </a>
                        <form class="frmDeleteInserto d-inline" method="post" action="<?= h($URL_DEL) ?>">
                          <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
                          <input type="hidden" name="id_inserto" value="<?= h($idIns) ?>">
                          <button type="submit" class="btn btn-sm btn-ghost" title="Eliminar">
                            <i class="fa-solid fa-trash" style="color:#c0392b;"></i>
                          </button>
                        </form>
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
          $from = $total ? ($off + 1) : 0;
          $to   = min($off + $PER_PAGE, $total);
          echo "Mostrando $from-$to de $total";
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

<script>
document.querySelectorAll('.frmDeleteInserto').forEach(f => {
  f.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!confirm('¿Eliminar este inserto?')) return;
    try {
      const r = await fetch(f.action, {
        method: 'POST',
        body: new FormData(f),
        headers: {'Accept':'application/json'}
      });
      const j = await r.json();
      console.log(j);
      if (!r.ok || !j.ok) throw new Error(j.msg || 'Error al eliminar');
      f.closest('tr').remove();
    } catch (err) {
      alert(err.message);
    }
  });
});
</script>
