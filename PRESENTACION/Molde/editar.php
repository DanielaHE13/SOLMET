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
function to_decimal($s){
  // Acepta "10,5" o "10.5" y normaliza a punto
  $s = trim((string)$s);
  // primero quita espacios
  $s = str_replace(' ', '', $s);
  // si trae separadores de miles tipo 1.234,56 -> quita puntos y deja coma como decimal
  if (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $s)) $s = str_replace('.', '', $s);
  // si trae separadores de miles tipo 1,234.56 -> quita comas
  if (preg_match('/^\d{1,3}(,\d{3})+\.\d+$/', $s)) $s = str_replace(',', '', $s);
  // por último cambia coma decimal por punto
  $s = str_replace(',', '.', $s);
  return is_numeric($s) ? $s : '';
}

/* ---- CSRF ---- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* ---- Estados permitidos ---- */
$ESTADOS = ['disponible','mantenimiento','fuera_servicio'];

/* ---- URLs ---- */
$URL_LISTAR = '?pid=' . base64_encode('PRESENTACION/Molde/listar.php');

/* ---- ID target ---- */
$id = trim($_GET['id'] ?? '');
if ($id === '' || !is_valid_id($id)) {
  echo err('ID de molde inválido.');
  echo '<div class="p-3"><a class="btn btn-secondary" href="'.h($URL_LISTAR).'">Volver</a></div>'; 
  exit;
}

/* ---- Cargar registro ---- */
$cx = new Conexion();
$cx->abrir();

$registro = null;
try {
  $cx->ejecutar("SELECT id_molde, nombre, peso_colada_g, estado, updated_at FROM molde WHERE id_molde = ?", [$id]);
  $registro = $cx->registro();
} catch (Throwable $e) {
  $registro = null;
}
if (!$registro) {
  $cx->cerrar();
  echo err('El molde solicitado no existe.');
  echo '<div class="p-3"><a class="btn btn-secondary" href="'.h($URL_LISTAR).'">Volver</a></div>'; 
  exit;
}

$okMsg = '';
$errors = [];

/* ---- POST (guardar cambios) ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tkn = $_POST['csrf'] ?? '';
  if (!hash_equals($csrf, $tkn)) {
    $errors[] = 'Token de seguridad inválido. Recarga la página.';
  } else {
    $nombre   = trim($_POST['nombre'] ?? '');
    $peso_s   = to_decimal($_POST['peso_colada_g'] ?? '');
    $estado   = trim($_POST['estado'] ?? '');
    $prevUp   = $_POST['prev_updated_at'] ?? '';

    if ($nombre === '') {
      $errors[] = 'El nombre es requerido.';
    }

    if ($peso_s === '') {
      $errors[] = 'El peso de colada (g/tiro) es requerido y debe ser numérico.';
    } else {
      $peso = (float)$peso_s;
      if ($peso < 0 || $peso > 1000000) {
        $errors[] = 'El peso de colada debe estar entre 0 y 1.000.000.';
      }
    }

    if (!in_array($estado, $ESTADOS, true)) {
      $errors[] = 'Estado inválido.';
    }

    if (!$errors) {
      try {
        // Concurrencia optimista usando updated_at
        $cx->ejecutar("SELECT updated_at FROM molde WHERE id_molde = ?", [$id]);
        $row = $cx->registro();
        $currUpStr = isset($row['updated_at']) && $row['updated_at'] !== null ? (string)$row['updated_at'] : '';

        if ($currUpStr !== $prevUp) {
          $errors[] = 'El molde fue modificado por otro usuario. Vuelve a cargar la página e inténtalo de nuevo.';
        } else {
          $cx->ejecutar("UPDATE molde SET nombre = ?, peso_colada_g = ?, estado = ? WHERE id_molde = ?",
            [$nombre, $peso_s, $estado, $id]);

          // Recargar
          $cx->ejecutar("SELECT id_molde, nombre, peso_colada_g, estado, updated_at FROM molde WHERE id_molde = ?", [$id]);
          $registro = $cx->registro();
          $okMsg = 'Cambios guardados correctamente.';
        }
      } catch (Throwable $e) {
        $errors[] = 'Error al guardar: '.$e->getMessage();
      }
    }
  }
}

$cx->cerrar();

/* ---- Valores para el form ---- */
$valNombre = $_POST['nombre'] ?? ($registro['nombre'] ?? '');
$valPeso   = $_POST['peso_colada_g'] ?? ($registro['peso_colada_g'] ?? '');
$valEstado = $_POST['estado'] ?? ($registro['estado'] ?? 'disponible');
$valUpdStr = isset($registro['updated_at']) && $registro['updated_at'] !== null ? (string)$registro['updated_at'] : '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar Molde</title>
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
        <div class="eyebrow mb-1">Moldes</div>
        <h3 class="hero-title mb-0">Editar molde</h3>
        <div class="mt-1 text-muted small">ID: <span class="fw-semibold"><?= h($registro['id_molde']) ?></span></div>
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
      <i class="fa-solid fa-cube me-1"></i> Datos del molde
    </div>
    <div class="card-body">
      <form method="post" class="row g-3">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
        <input type="hidden" name="prev_updated_at" value="<?= h($valUpdStr) ?>">

        <div class="col-12 col-md-6">
          <label class="form-label">Nombre</label>
          <input
            type="text"
            name="nombre"
            maxlength="100"
            class="form-control soft-input"
            value="<?= h($valNombre) ?>"
            required
          >
        </div>

        <div class="col-12 col-md-3">
          <label class="form-label">Peso colada (g/tiro)</label>
          <input
            type="number"
            step="0.001"
            min="0"
            max="1000000"
            inputmode="decimal"
            name="peso_colada_g"
            class="form-control soft-input"
            value="<?= h($valPeso) ?>"
            required
          >
          <div class="input-hint mt-1">Acepta coma o punto; se guarda con punto y hasta 3 decimales.</div>
        </div>

        <div class="col-12 col-md-3">
          <label class="form-label">Estado</label>
          <select name="estado" class="form-select soft-input">
            <?php
              foreach ($ESTADOS as $op){
                $s = $valEstado===$op ? 'selected' : '';
                echo '<option value="'.h($op).'" '.$s.'>'.h(ucfirst(str_replace('_',' ', $op))).'</option>';
              }
            ?>
          </select>
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
          <button class="btn btn-accent">
            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar cambios
          </button>
          <a class="btn btn-ghost" href="<?= h($URL_LISTAR) ?>">
            <i class="fa-solid fa-list me-1"></i> Volver al listado
          </a>
        </div>
      </form>
    </div>
  </div>

</div>
</body>
</html>
