<?php
// PRESENTACION/Molde/eliminar.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';

/* ---- Autorización ---- */
$rol = $_SESSION['rol'] ?? null;
if ($rol !== 'admin') { 
  include __DIR__ . '/../Noautorizado.php';
  exit;
}

/* ---- Menú ---- */
include_once __DIR__ . '/../Admin/menuAdmin.php';

/* ---- Helpers ---- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function backToListUrl(){ return '?pid=' . base64_encode('PRESENTACION/Molde/listar.php'); }
function csrf_token(){
  if (empty($_SESSION['csrf_molde_del'])) {
    $_SESSION['csrf_molde_del'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['csrf_molde_del'];
}
function csrf_check($t){ return hash_equals($_SESSION['csrf_molde_del'] ?? '', (string)$t); }

/* ---- Params ---- */
$id = trim($_GET['id'] ?? $_POST['id'] ?? '');

/* ---- Datos ---- */
$molde = null;
$dep   = ['maquinas'=>0,'insertos'=>0,'ordenes'=>0];
$errors = [];
$done = false;

if ($id === '') {
  $errors[] = 'ID de molde no proporcionado.';
} else {
  $cx = new Conexion();
  $cx->abrir();
  try {
    $cx->ejecutar("SELECT id_molde, nombre, peso_colada_g, estado FROM molde WHERE id_molde = ?", [$id]);
    $molde = $cx->registro();

    if (!$molde) {
      $errors[] = 'El molde no existe.';
    } else {
      $cx->ejecutar("SELECT COUNT(*) FROM maquina_molde WHERE id_molde = ?", [$id]);
      $dep['maquinas'] = (int)($cx->registro()[0] ?? 0);

      $cx->ejecutar("SELECT COUNT(*) FROM inserto WHERE id_molde = ?", [$id]);
      $dep['insertos'] = (int)($cx->registro()[0] ?? 0);

      $cx->ejecutar("SELECT COUNT(*) FROM orden_produccion WHERE id_molde = ?", [$id]);
      $dep['ordenes'] = (int)($cx->registro()[0] ?? 0);
    }

    if (empty($errors) && ($_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['confirm'])) {
      if (!csrf_check($_POST['csrf'] ?? '')) {
        $errors[] = 'Token CSRF inválido. Vuelve a intentarlo.';
      } else {
        if ($dep['maquinas']>0 || $dep['insertos']>0 || $dep['ordenes']>0) {
          $errors[] = 'No se puede eliminar porque tiene dependencias.';
        } else {
          $cx->ejecutar("DELETE FROM molde WHERE id_molde = ?", [$id]);
          $done = true;
          unset($_SESSION['csrf_molde_del']);
        }
      }
    }
  } finally { $cx->cerrar(); }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Eliminar Molde</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/assets/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/fontawesome.min.css">
  <style>
  body{background:linear-gradient(135deg,#e7f6ee,#fff);font-family:system-ui;}
  .card{border-radius:18px;box-shadow:0 8px 24px rgba(16,80,54,.08);}
  .btn-accent{background:#1ea257;color:#fff;border-radius:12px;}
  .btn-accent:hover{background:#188249;}
  .btn-ghost{border:1px solid #e3eee7;border-radius:12px;}
  .btn-ghost:hover{background:#f3fbf7;}
  .btn-danger{background:#c0392b;color:#fff;border-radius:12px;border:none;}
  .btn-danger:hover{background:#922b21;}
  .alert-danger-soft{
    border-radius:14px; border:1px solid #f3d6d6; background:#fff5f5; color:#8a1f1f; padding:12px 14px;
  }
  .badge-deps{background:#eef3f1;border:1px solid #e0e7e4;color:#2d4c40;border-radius:999px;padding:.25rem .5rem;font-weight:600;}
  .code-chip{display:inline-block;padding:6px 10px;border-radius:10px;background:#f1f5f4;border:1px solid #e0e7e4;color:#40514a;font-weight:600;}
  </style>
</head>
<body>
<div class="container-xxl px-3 px-md-4 my-3">

  <div class="card p-4">
    <h3 class="mb-3 text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Eliminar Molde</h3>

    <?php if (!empty($errors)): ?>
      <div class="alert-danger-soft mb-3"><?= implode('<br>', array_map('h',$errors)) ?></div>
    <?php endif; ?>

    <?php if ($done): ?>
      <p>✅ El molde fue eliminado correctamente.</p>
      <a class="btn btn-accent mt-2" href="<?= h(backToListUrl()) ?>">Volver al listado</a>

    <?php elseif($molde): ?>
      <p>¿Está seguro que desea eliminar el molde
        <span class="code-chip"><?= h($molde['id_molde']) ?></span>
        — <strong><?= h($molde['nombre']) ?></strong>?</p>

      <ul>
        <li><span class="badge-deps">Máquinas vinculadas:</span> <?= (int)$dep['maquinas'] ?></li>
        <li><span class="badge-deps">Insertos:</span> <?= (int)$dep['insertos'] ?></li>
        <li><span class="badge-deps">Órdenes de producción:</span> <?= (int)$dep['ordenes'] ?></li>
      </ul>

      <?php if ($dep['maquinas']>0 || $dep['insertos']>0 || $dep['ordenes']>0): ?>
        <div class="alert-danger-soft mb-3">
          ❌ No puedes eliminar este molde porque tiene dependencias.<br>
          Considera marcarlo como <strong>fuera_servicio</strong>.
        </div>
        <a href="<?= h(backToListUrl()) ?>" class="btn btn-ghost">Volver</a>
      <?php else: ?>
        <form method="post" class="d-flex gap-2">
          <input type="hidden" name="id" value="<?= h($molde['id_molde']) ?>">
          <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
          <button type="submit" name="confirm" value="1" class="btn btn-danger">
            <i class="fa-solid fa-trash me-1"></i> Sí, eliminar
          </button>
          <a href="<?= h(backToListUrl()) ?>" class="btn btn-ghost">Cancelar</a>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
