<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';

/* ---- Autorización (solo admin) ---- */
$rol = $_SESSION['rol'] ?? null;
if ($rol !== 'admin') {
  include __DIR__ . '/../Noautorizado.php';
  exit;
}

/* ---- Menú ---- */
include_once __DIR__ . '/../Admin/menuAdmin.php';

/* ---- Helpers ---- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function ok($m){ return '<div class="alert alert-success my-2">'.$m.'</div>'; }
function err($m){ return '<div class="alert alert-danger my-2">'.$m.'</div>'; }
function is_valid_id($s){ return (bool)preg_match('/^[A-Za-z0-9\-\_]{1,25}$/', $s); }

/* ---- CSRF ---- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* ---- URLs ---- */
$URL_LISTAR = '?pid=' . base64_encode('PRESENTACION/Maquina/listar.php');

/* ---- ID target ---- */
$id = trim($_GET['id'] ?? '');
if ($id === '' || !is_valid_id($id)) {
  echo err('ID de máquina inválido.');
  echo '<div class="p-3"><a class="btn btn-secondary" href="'.h($URL_LISTAR).'">Volver</a></div>'; 
  exit;
}

/* ---- Cargar datos de la máquina ---- */
$cx = new Conexion();
$cx->abrir();
$registro = null;
try {
  $cx->ejecutar("SELECT id_maquina,nombre,estado FROM maquina WHERE id_maquina = ?", [$id]);
  $registro = $cx->registro();
} catch (Throwable $e) {
  $registro = null;
}

if (!$registro) {
  $cx->cerrar();
  echo err('La máquina no existe.');
  echo '<div class="p-3"><a class="btn btn-secondary" href="'.h($URL_LISTAR).'">Volver</a></div>'; 
  exit;
}

$okMsg = ''; 
$errors = [];

/* ---- POST (confirmar eliminación) ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tkn = $_POST['csrf'] ?? '';
  if (!hash_equals($csrf, $tkn)) {
    $errors[] = 'Token inválido, recarga la página.';
  } else {
    try {
      $cx->ejecutar("DELETE FROM maquina WHERE id_maquina = ?", [$id]);
      $okMsg = "Máquina eliminada correctamente.";
      $registro = null;
    } catch (Throwable $e) {
      $errors[] = "No se pudo eliminar: ".$e->getMessage();
    }
  }
}
$cx->cerrar();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Eliminar Máquina</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/assets/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/fontawesome.min.css">
  <style>
  :root{
    --g50:#f3fbf7; --g100:#e7f6ee; --g200:#d6f0e0;
    --g600:#1ea257; --g700:#188249; --g800:#0f5a32;
    --txt:#28323a; --muted:#6c7b86; --border:#e3eee7;
  }
  body{background:linear-gradient(135deg,var(--g100),#fff);font-family:system-ui;}
  .card{border-radius:18px;box-shadow:0 8px 24px rgba(16,80,54,.08);}
  .btn-accent{background:var(--g600);color:#fff;border-radius:12px;}
  .btn-accent:hover{background:var(--g700);}
  .btn-ghost{border:1px solid var(--border);border-radius:12px;}
  .btn-ghost:hover{background:var(--g100);}
  </style>
  <?php if ($okMsg): ?>
    <!-- Redirige automáticamente al listado después de 2s -->
    <meta http-equiv="refresh" content="2;url=<?= h($URL_LISTAR) ?>">
  <?php endif; ?>
</head>
<body>
<div class="container-xxl px-3 px-md-4 my-3">

  <div class="card p-4">
    <h3 class="mb-3 text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Eliminar Máquina</h3>

    <?php if ($okMsg): ?>
      <?= ok(h($okMsg)) ?>
      <p class="mt-3">Serás redirigido al listado en unos segundos…</p>
      <a class="btn btn-accent mt-2" href="<?= h($URL_LISTAR) ?>">Volver ahora</a>
    <?php elseif ($registro): ?>
      <?php foreach ($errors as $e) echo err(h($e)); ?>
      <p>¿Está seguro que desea eliminar la máquina <strong><?= h($registro['nombre']) ?></strong> (ID: <?= h($registro['id_maquina']) ?>)?</p>
      <form method="post" class="d-flex gap-2 mt-3">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
        <button type="submit" class="btn btn-danger">
          <i class="fa-solid fa-trash me-1"></i> Sí, eliminar
        </button>
        <a href="<?= h($URL_LISTAR) ?>" class="btn btn-ghost">Cancelar</a>
      </form>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
