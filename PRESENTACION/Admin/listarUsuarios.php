<?php
// PRESENTACION/Admin/listarUsuarios.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';

/* ---- Autorización ---- */
if (($_SESSION['rol'] ?? '') !== 'admin') {
  include __DIR__ . '/../Noautorizado.php';
  exit;
}

/* ---- Utils ---- */
function h($s)
{
  return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/** Chips de estado */
function chipActivo($a)
{
  $a = (int)$a;
  $txt = $a ? 'Activo' : 'Inactivo';
  $cls = $a ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle';
  return '<span class="badge rounded-pill px-3 py-2 ' . $cls . '">' . $txt . '</span>';
}

/** ID válido: 1-25, alfanumérico, guion y guion bajo */
function is_valid_id($s)
{
  return (bool)preg_match('/^[A-Za-z0-9\-_]{1,25}$/', (string)$s);
}

/** Helpers de rutas (por si la app vive en subcarpeta, ej: /SOLMET3) */
function base_url(): string
{
  $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
  return $dir === '' ? '' : $dir;
}
function asset_url(string $rel): string
{
  return (base_url() ? base_url() : '') . '/' . ltrim($rel, '/');
}

/* ---- Rutas ---- */
$RUTA_LISTAR = 'PRESENTACION/Admin/listarUsuarios.php';
$URL_LISTAR  = '?pid=' . base64_encode($RUTA_LISTAR);
$URL_CREAR   = '?pid=' . base64_encode('PRESENTACION/Admin/crearUsuario.php');

/* ---- CSRF ---- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* ================== ACCIÓN: ELIMINAR (POST) ================== */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'del') {
  $msg = ['tipo' => 'danger', 'txt' => 'Acción inválida.'];
  $cx = new Conexion();

  try {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
      throw new RuntimeException('Token CSRF inválido.');
    }

    $idDel = trim((string)($_POST['id'] ?? ''));
    if (!is_valid_id($idDel)) throw new RuntimeException('ID inválido.');

    // Evitar auto-borrado
    $myId = (string)($_SESSION['id_usuario'] ?? '');
    if ($idDel === $myId) {
      throw new RuntimeException('No puedes eliminar tu propio usuario.');
    }

    $cx->abrir();

    // --- Transacción + bloqueos para evitar condición de carrera ---
    $cx->ejecutar("START TRANSACTION");

    // Verificar que exista (y bloquear fila)
    $cx->ejecutar("SELECT id_usuario, id_rol FROM usuario WHERE id_usuario = ? FOR UPDATE", [$idDel]);
    $u = $cx->registro();
    if (!$u) throw new RuntimeException('Usuario no encontrado.');

    // Si es admin, impedir dejar el sistema sin admins
    if ((int)$u['id_rol'] === 1) {
      $cx->ejecutar("SELECT COUNT(*) AS c FROM usuario WHERE id_rol = 1 FOR UPDATE");
      $admins = (int)($cx->registro()['c'] ?? 0);
      if ($admins <= 1) throw new RuntimeException('No puedes eliminar el último admin.');
    }

    // Eliminar
    $cx->ejecutar("DELETE FROM usuario WHERE id_usuario = ?", [$idDel]);

    $cx->ejecutar("COMMIT");
    $msg = ['tipo' => 'success', 'txt' => 'Usuario eliminado correctamente.'];
  } catch (Throwable $e) {
    // Intentar revertir si se abrió transacción
    try {
      $cx->ejecutar("ROLLBACK");
    } catch (Throwable $ignore) {
    }

    // Manejo amable de errores de BD (FKs, etc.)
    $txt = 'No se pudo eliminar: ' . $e->getMessage();
    if (method_exists($e, 'getCode') && $e->getCode() === '23000') {
      $txt = 'No se puede eliminar: el usuario tiene información relacionada.';
    }
    $msg = ['tipo' => 'danger', 'txt' => $txt];
  } finally {
    $cx->cerrar();
  }

  // Guardar flash y redirigir (PRG)
  $_SESSION['flash'] = $msg;

  // Conservar filtros en la redirección
  $qs = $_GET;
  $qs['pid'] = base64_encode($RUTA_LISTAR);
  header('Location: index.php?' . http_build_query($qs));
  exit;
}

/* ---- Filtros (GET) ---- */
$q        = trim($_GET['q'] ?? '');
$rol_f    = trim($_GET['rol'] ?? '');
$activo_f = trim($_GET['activo'] ?? '');
$pag      = max(1, (int)($_GET['p'] ?? 1));
$PER_PAGE = 20;
$off      = ($pag - 1) * $PER_PAGE;

/* ---- DB ---- */
$cx = new Conexion();
$cx->abrir();

/* Roles para el filtro */
$cx->ejecutar("SELECT id_rol, nombre FROM rol ORDER BY nombre ASC");
$roles = $cx->registros();

/* WHERE dinámico */
$where  = [];
$params = [];

if ($q !== '') {
  $where[] = "(u.username LIKE ? OR u.nombre LIKE ? OR u.apellido LIKE ?)";
  $like = '%' . $q . '%';
  array_push($params, $like, $like, $like);
}
if ($rol_f !== '' && ctype_digit($rol_f)) {
  $where[] = "u.id_rol = ?";
  $params[] = (int)$rol_f;
}
if ($activo_f !== '' && ($activo_f === '0' || $activo_f === '1')) {
  $where[] = "u.activo = ?";
  $params[] = (int)$activo_f;
}

$wSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

/* Total */
$cx->ejecutar("
  SELECT COUNT(*)
    FROM usuario u
    LEFT JOIN rol r ON r.id_rol = u.id_rol
  $wSQL
", $params);
$totRows  = (int)($cx->registro()[0] ?? 0);
$totPages = max(1, (int)ceil($totRows / $PER_PAGE));

/* Clamp de página si se pasó del total */
if ($pag > $totPages) {
  $pag = $totPages;
  $off = ($pag - 1) * $PER_PAGE;
}

/* Listado */
$sql = "
  SELECT
    u.id_usuario,
    u.username,
    u.nombre,
    u.apellido,
    u.foto,
    u.id_rol,
    u.activo,
    u.created_at,
    r.nombre AS rol_nombre
  FROM usuario u
  LEFT JOIN rol r ON r.id_rol = u.id_rol
  $wSQL
  ORDER BY u.created_at DESC, u.id_usuario DESC
  LIMIT $PER_PAGE OFFSET $off
";
$cx->ejecutar($sql, $params);

$rows = [];
while ($r = $cx->registro()) $rows[] = $r;

$cx->cerrar();

/* Helper de paginación */
function linkP($p)
{
  $qs = $_GET;
  $qs['p'] = $p;
  return '?' . http_build_query($qs);
}

/* ---- Menú admin (después de manejar POST) ---- */
include_once __DIR__ . '/menuAdmin.php';
?>
<div class="container-xxl px-3 px-md-4 px-lg-5 my-3 page-wrap">
  <!-- HERO -->
  <div class="hero hero-green mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="eyebrow mb-1">Administración</div>
        <h3 class="hero-title mb-0">Usuarios</h3>
      </div>
      <a class="btn btn-accent" href="<?= h($URL_CREAR) ?>">
        <i class="fa-solid fa-user-plus me-1"></i> Nuevo usuario
      </a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= h($flash['tipo']) ?>"><?= h($flash['txt']) ?></div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fa-solid fa-circle-check me-2"></i>
      <?= h($_SESSION['flash_success']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>

  <!-- Filtros -->
  <div class="card-elev mb-3">
    <div class="card-body">
      <!-- ✅ Enviar SIEMPRE al router y conservar el pid -->
      <form method="get" action="index.php" class="row g-3 g-md-2 align-items-end">
        <input type="hidden" name="pid" value="<?= h(base64_encode($RUTA_LISTAR)) ?>">
        <div class="col-12 col-md-4">
          <label class="form-label small text-muted mb-1" for="q">Buscar</label>
          <div class="input-with-icon">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input id="q" type="text" name="q" value="<?= h($q) ?>" class="form-control soft-input" placeholder="Usuario / Nombre / Apellido">
          </div>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small text-muted mb-1" for="rol">Rol</label>
          <select id="rol" name="rol" class="form-select soft-input">
            <option value="">Todos</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= (int)$r['id_rol'] ?>" <?= $rol_f === (string)$r['id_rol'] ? 'selected' : '' ?>>
                <?= h($r['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small text-muted mb-1" for="activo">Estado</label>
          <select id="activo" name="activo" class="form-select soft-input">
            <option value="">Todos</option>
            <option value="1" <?= $activo_f === '1' ? 'selected' : '' ?>>Activos</option>
            <option value="0" <?= $activo_f === '0' ? 'selected' : '' ?>>Inactivos</option>
          </select>
        </div>
        <div class="col-12 col-md-2">
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
              <th scope="col" style="width:12%">Usuario</th>
              <th scope="col">Nombre</th>
              <th scope="col" style="width:18%">Rol</th>
              <th scope="col" style="width:14%">Estado</th>
              <th scope="col" style="width:18%">Creación</th>
              <th scope="col" style="width:20%"></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($rows)): ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  <div class="empty-state">
                    <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                    <div>No hay usuarios con los filtros aplicados.</div>
                  </div>
                </td>
              </tr>
              <?php else: foreach ($rows as $u): ?>
                <tr>
                  <td class="fw-semibold">
                    <div class="d-flex align-items-center gap-2">
                      <?php $foto = $u['foto'] ?: 'IMG/avatar-default.png'; ?>
                      <img src="<?= h(asset_url($foto)) ?>" alt="Foto de perfil" width="36" height="36"
                        class="rounded-circle" style="object-fit:cover;"
                        loading="lazy" decoding="async"
                        onerror="this.onerror=null;this.src='<?= h(asset_url('IMG/avatar-default.png')) ?>';">
                      <span>@<?= h($u['username']) ?></span>
                    </div>
                  </td>
                  <td><?= h(trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '')) ?: '—') ?></td>
                  <td><span class="chip"><?= h($u['rol_nombre'] ?? '—') ?></span></td>
                  <td><?= chipActivo($u['activo']) ?></td>
                  <td>
                    <?php
                    $createdRaw = (string)($u['created_at'] ?? '');
                    $createdFmt = $createdRaw ? date('Y-m-d H:i', strtotime($createdRaw)) : '—';
                    ?>
                    <span class="date-chip"><?= h($createdFmt) ?></span>
                  </td>
                  <td class="text-end">
                    <div class="d-flex justify-content-end gap-2">
                      <a class="btn btn-sm btn-ghost"
                        href="<?= h($URL_CREAR . '&id=' . rawurlencode((string)$u['id_usuario'])) ?>"
                        aria-label="Editar usuario @<?= h($u['username']) ?>">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Editar
                      </a>

                      <!-- Botón Eliminar -->
                      <form method="post" action="index.php?<?= h(http_build_query($_GET)) ?>"
                        onsubmit="return confirm('¿Está seguro de eliminar al usuario @<?= h(addslashes($u['username'])) ?>?');">
                        <input type="hidden" name="pid" value="<?= h(base64_encode($RUTA_LISTAR)) ?>">
                        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                        <input type="hidden" name="do" value="del">
                        <input type="hidden" name="id" value="<?= h((string)$u['id_usuario']) ?>">
                        <?php $self = ((string)$u['id_usuario'] === (string)($_SESSION['id_usuario'] ?? '')); ?>
                        <button type="button" class="btn btn-sm btn-danger"
                          data-bs-toggle="modal" data-bs-target="#modalEliminar"
                          data-user="<?= h($u['username']) ?>"
                          data-id="<?= h((string)$u['id_usuario']) ?>"
                          <?= $self ? 'disabled title="No puedes eliminarte a ti mismo"' : '' ?>>
                          <i class="fa-solid fa-trash-can me-1"></i> Eliminar
                        </button>

                      </form>
                    </div>
                  </td>
                </tr>
            <?php endforeach;
            endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
        <div class="small text-muted">
          <?php
          $from = $totRows ? ($off + 1) : 0;
          $to   = min($off + $PER_PAGE, $totRows);
          echo "Mostrando $from-$to de $totRows";
          ?>
        </div>
        <nav aria-label="Paginación">
          <ul class="pagination custom-pg mb-0">
            <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= h(linkP(max(1, $pag - 1))) ?>" aria-label="Página anterior">«</a>
            </li>
            <li class="page-item active" aria-current="page"><span class="page-link"><?= (int)$pag ?></span></li>
            <li class="page-item <?= $pag >= $totPages ? 'disabled' : '' ?>">
              <a class="page-link" href="<?= h(linkP(min($totPages, $pag + 1))) ?>" aria-label="Página siguiente">»</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px; background:#edf7f1;">
      <div class="modal-header text-white" style="background:#dc3545;">
        <h5 class="modal-title"><i class="fa-solid fa-circle-exclamation me-2"></i> Confirmar eliminación</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <p class="fs-5">¿Está seguro de eliminar al usuario <strong id="delUser"></strong>?</p>
      </div>
      <div class="modal-footer justify-content-center">
        <form method="post" action="index.php?<?= h(http_build_query($_GET)) ?>" id="formEliminar">
          <input type="hidden" name="pid" value="<?= h(base64_encode($RUTA_LISTAR)) ?>">
          <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
          <input type="hidden" name="do" value="del">
          <input type="hidden" name="id" id="delId" value="">
          <button type="submit" class="btn btn-danger">
            <i class="fa-solid fa-trash-can me-1"></i> Sí, eliminar
          </button>
        </form>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<script>
const modalEliminar = document.getElementById('modalEliminar');
modalEliminar.addEventListener('show.bs.modal', event => {
  const button = event.relatedTarget;
  const user = button.getAttribute('data-user');
  const id   = button.getAttribute('data-id');
  document.getElementById('delUser').textContent = '@' + user;
  document.getElementById('delId').value = id;
});
</script>


<style>
  :root {
    --g50: #f3fbf7;
    --g100: #e7f6ee;
    --g200: #d6f0e0;
    --g600: #1ea257;
    --g700: #188249;
    --g800: #0f5a32;
    --txt: #28323a;
    --muted: #6c7b86;
    --bg: #e4efe8;
    --border: #e3eee7;
    --shadow: 0 8px 24px rgba(16, 80, 54, .08);
  }

  body {
    background: var(--bg);
    color: var(--txt);
  }

  .page-wrap {
    color: var(--txt);
  }

  /* HERO */
  .hero {
    border-radius: 18px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    padding: 18px;
  }

  .hero {
    background: radial-gradient(1200px 200px at -20% -50%, #ffffff 0%, transparent 60%), linear-gradient(135deg, var(--g100) 0%, var(--g50) 60%, #fff 100%);
  }

  .eyebrow {
    letter-spacing: .12em;
    font-size: .75rem;
    color: var(--g700);
    font-weight: 700;
    text-transform: uppercase;
  }

  .hero-title {
    font-weight: 800;
    color: var(--g800);
  }

  /* CARDS */
  .card-elev {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 18px;
    box-shadow: var(--shadow);
    overflow: hidden;
  }

  .card-elev .card-body {
    padding: 16px;
  }

  .card-head {
    padding: 12px 16px;
    font-weight: 800;
    color: var(--g800);
    background: linear-gradient(0deg, var(--g50), #fff);
    border-bottom: 1px solid var(--border);
  }

  /* INPUTS */
  .soft-input {
    border-radius: 12px;
    border: 1px solid #dfeae4;
    background: #fff;
    transition: .2s ease;
  }

  .soft-input:focus {
    border-color: var(--g600);
    box-shadow: 0 0 0 .2rem rgba(33, 178, 107, .12);
  }

  .input-with-icon {
    position: relative;
  }

  .input-with-icon>i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c7b86;
  }

  .input-with-icon>input {
    padding-left: 38px;
  }

  /* BUTTONS */
  .btn-accent {
    background: var(--g600);
    border-color: var(--g600);
    color: #fff;
    border-radius: 12px;
  }

  .btn-accent:hover {
    background: var(--g700);
    border-color: var(--g700);
    color: #fff;
  }

  .btn-success-soft {
    background: var(--g100);
    color: var(--g700);
    border: 1px solid var(--g200);
    border-radius: 12px;
  }

  .btn-success-soft:hover {
    background: var(--g200);
    color: var(--g800);
  }

  .btn-ghost {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: 12px;
  }

  .btn-ghost:hover {
    background: var(--g100);
  }

  /* TABLE */
  .table-wrap {
    border-radius: 14px;
    overflow: hidden;
  }

  .table.sticky-head thead th {
    position: sticky;
    top: 0;
    z-index: 1;
  }

  .table.sticky-head thead th {
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    background: linear-gradient(0deg, var(--g50), #fff);
    color: #335648;
    border-bottom: 1px solid var(--border);
  }

  .table> :not(caption)>*>* {
    padding: 12px 14px;
    vertical-align: middle;
  }

  .table tbody tr {
    transition: .15s ease;
  }

  .table tbody tr:hover {
    background: #f6fbf8;
  }

  /* CHIPS */
  .chip {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 999px;
    background: var(--g50);
    border: 1px solid var(--g200);
    color: #184c32;
    font-weight: 700;
  }

  .date-chip {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 10px;
    background: #f1f5f4;
    border: 1px solid #e0e7e4;
    color: #40514a;
    font-weight: 600;
  }

  /* EMPTY STATE */
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: #7b8b86;
  }

  .empty-state i {
    font-size: 26px;
    color: var(--g600);
  }

  /* PAGINATION */
  .pagination.custom-pg .page-link {
    border-radius: 12px;
    margin: 0 3px;
    border: 1px solid #dbe9e1;
    color: #365949;
    padding: .45rem .75rem;
  }

  .pagination.custom-pg .page-item.active .page-link {
    background: var(--g600);
    border-color: var(--g600);
    color: #fff;
  }

  .pagination.custom-pg .page-item.disabled .page-link {
    opacity: .55;
  }
</style>