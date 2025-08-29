<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';

/* ---- Autorización (solo admin puede crear) ---- */
$rol = $_SESSION['rol'] ?? null;
if ($rol !== 'admin') {
  include __DIR__ . '/../Noautorizado.php';
  exit;
}

/* ---- Menú ---- */
include_once __DIR__ . '/../Admin/menuAdmin.php';

/* ---- Helpers ---- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function is_valid_id($s){ return (bool)preg_match('/^[A-Za-z0-9\-\_]{1,25}$/', $s); } // mismo largo que la columna
function ok($msg){ return '<div class="alert alert-success my-2">'.$msg.'</div>'; }
function err($msg){ return '<div class="alert alert-danger my-2">'.$msg.'</div>'; }

/* ---- CSRF ---- */
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

/* ---- Estados permitidos ---- */
$ESTADOS = ['activa','inactiva'];

/* ---- POST handler ---- */
$errors = [];
$okMsg  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tkn  = $_POST['csrf'] ?? '';
  if (!hash_equals($csrf, $tkn)) {
    $errors[] = 'Token de seguridad inválido. Recarga la página.';
  } else {
    $id_maquina = trim($_POST['id_maquina'] ?? '');
    $nombre     = trim($_POST['nombre'] ?? '');
    $estado     = trim($_POST['estado'] ?? 'activa');

    // Validaciones
    if ($id_maquina === '' || !is_valid_id($id_maquina)) {
      $errors[] = 'ID de máquina requerido (máx 25, solo letras/números/guion/guion bajo).';
    }
    if ($nombre === '') {
      $errors[] = 'El nombre es requerido.';
    }
    if (!in_array($estado, $ESTADOS, true)) {
      $estado = 'activa';
    }

    if (!$errors) {
      $cx = new Conexion();
      try {
        $cx->abrir();

        // ¿Existe ya?
        $cx->ejecutar("SELECT 1 FROM maquina WHERE id_maquina = ?", [$id_maquina]);
        $existe = $cx->registro();
        if ($existe) {
          $errors[] = 'Ya existe una máquina con ese ID.';
        } else {
          // Insert
          $sql = "INSERT INTO maquina (id_maquina, nombre, estado) VALUES (?, ?, ?)";
          $cx->ejecutar($sql, [$id_maquina, $nombre, $estado]);

          $okMsg = '¡Máquina creada correctamente!';
          // Limpia valores del formulario
          $_POST = ['estado' => 'activa'];
        }
      } catch (Throwable $e) {
        $errors[] = 'Error al guardar: '.$e->getMessage();
      } finally {
        $cx->cerrar();
      }
    }
  }
}

/* ---- URLS ---- */
$URL_LISTAR = '?pid=' . base64_encode('PRESENTACION/Maquina/listar.php');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Crear Máquina</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="/assets/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/fontawesome.min.css">
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
  .hero{ border-radius:18px; border:1px solid var(--border); box-shadow:var(--shadow); padding:18px; background:#fff; }
  .eyebrow{ letter-spacing:.12em; font-size:.75rem; color:var(--g700); font-weight:700; text-transform:uppercase; }
  .hero-title{ font-weight:800; color:var(--g800); }

  /* Card */
  .card-elev{ background:#fff; border:1px solid var(--border); border-radius:18px; box-shadow:var(--shadow); overflow:hidden; }
  .card-elev .card-body{ padding:18px; }
  .card-head{
    padding:12px 16px; font-weight:800; color:var(--g800);
    background:linear-gradient(0deg, var(--g50), #fff); border-bottom:1px solid var(--border);
  }
  .card-head i{ color:var(--g600); }

  /* Inputs */
  .soft-input{ border-radius:12px; border:1px solid #dfeae4; background:#fff; transition:.2s ease; }
  .soft-input:focus{ border-color:var(--g600); box-shadow:0 0 0 .2rem rgba(33,178,107,.12); }
  .input-hint{ font-size:.82rem; color:var(--muted); }

  /* Buttons */
  .btn-accent{ background:var(--g600); border-color:var(--g600); color:#fff; border-radius:12px; }
  .btn-accent:hover{ background:var(--g700); border-color:var(--g700); color:#fff; }
  .btn-ghost{ background:#fff; border:1px solid var(--border); border-radius:12px; }
  .btn-ghost:hover{ background:var(--g100); }
  </style>
</head>
<body>
<div class="container-xxl px-3 px-md-4 px-lg-5 my-3 page-wrap">

  <!-- HERO -->
  <div class="hero mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="eyebrow mb-1">Máquinas</div>
        <h3 class="hero-title mb-0">Crear máquina</h3>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-ghost" href="<?= h($URL_LISTAR) ?>">
          <i class="fa-solid fa-list me-1"></i> Listado
        </a>
      </div>
    </div>

    <?php
      if ($okMsg)   echo ok(h($okMsg));
      foreach ($errors as $e) echo err(h($e));
    ?>
  </div>

  <!-- FORM -->
  <div class="card-elev">
    <div class="card-head">
      <i class="fa-solid fa-gears me-1"></i> Datos de la máquina
    </div>
    <div class="card-body">
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

        <div class="col-12 col-md-4">
          <label class="form-label">ID de máquina</label>
          <input
            type="text"
            name="id_maquina"
            maxlength="25"
            class="form-control soft-input"
            value="<?= h($_POST['id_maquina'] ?? '') ?>"
            placeholder="Ej: ISPH9502"
            required
          >
          <div class="input-hint mt-1">Máx 25 caracteres (letras, números, guion y guion bajo).</div>
        </div>

        <div class="col-12 col-md-6">
          <label class="form-label">Nombre</label>
          <input
            type="text"
            name="nombre"
            maxlength="100"
            class="form-control soft-input"
            value="<?= h($_POST['nombre'] ?? '') ?>"
            placeholder="Ej: SAPHIR 95"
            required
          >
        </div>

        <div class="col-12 col-md-2">
          <label class="form-label">Estado</label>
          <select name="estado" class="form-select soft-input">
            <?php
              $sel = $_POST['estado'] ?? 'activa';
              foreach ($ESTADOS as $op){
                $s = $sel===$op ? 'selected' : '';
                echo '<option value="'.h($op).'" '.$s.'>'.h(ucfirst($op)).'</option>';
              }
            ?>
          </select>
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
          <button class="btn btn-accent">
            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
          </button>
          <a class="btn btn-ghost" href="<?= h($URL_LISTAR) ?>">
            <i class="fa-solid fa-list me-1"></i> Ir al listado
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>
