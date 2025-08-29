<?php
// PRESENTACION/MateriaPrima/editar.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';
require_once __DIR__ . '/../../persistencia/MateriaPrimaDAO.php';

/* ---------- Autorización ---------- */
$rol = $_SESSION['rol'] ?? null;
if ($rol !== 'admin') { include __DIR__ . '/../Noautorizado.php'; exit; }

/* ---------- Helpers ---------- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/* ---------- CSRF ---------- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* ---------- URLs ---------- */
$RUTA_EDITAR   = 'PRESENTACION/MateriaPrima/editar.php';
$RUTA_LISTAR   = 'PRESENTACION/MateriaPrima/listar.php';
$URL_ACTUALIZAR= '?pid=' . base64_encode($RUTA_EDITAR);
$URL_LISTAR    = '?pid=' . base64_encode($RUTA_LISTAR);

/* =========================================================
   POST: actualizar registro
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    // CSRF
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
      throw new RuntimeException('Token inválido.');
    }

    // Datos obligatorios
    $codigo     = trim($_POST['codigo']     ?? '');
    $polimero   = trim($_POST['polimero']   ?? '');
    $referencia = trim($_POST['referencia'] ?? '');
    $estado     = trim($_POST['estado']     ?? '');

    if ($codigo === '' || $polimero === '' || $referencia === '' || $estado === '') {
      throw new RuntimeException('Campos obligatorios faltantes.');
    }

    // Datos opcionales
    $color       = substr(trim($_POST['color']       ?? ''), 0, 50);
    $procedencia = substr(trim($_POST['procedencia'] ?? ''), 0, 100);
    $uso_final   = substr(trim($_POST['uso_final']   ?? ''), 0, 100);
    $activo      = (int)($_POST['activo'] ?? 1);

    // Arreglo esperado por el DAO
    $data = [
      'codigo'      => $codigo,
      'polimero'    => $polimero,
      'referencia'  => $referencia,
      'estado'      => $estado,
      'color'       => $color,
      'procedencia' => $procedencia,
      'uso_final'   => $uso_final,
      'activo'      => $activo
    ];

    // Ejecutar actualización
    [$sql, $params] = MateriaPrimaDAO::actualizar($data);
    $cx = new Conexion(); $cx->abrir();
    $cx->ejecutar($sql, $params);
    $cx->cerrar();

    $_SESSION['flash_success'] = 'Materia prima actualizada correctamente.';
    header('Location: '.$URL_LISTAR);
    exit;

  } catch (Throwable $e) {
    // Deja el error para mostrarlo en la vista GET (abajo)
    $errorMsg = $e->getMessage();
    // Y continúa a cargar el registro para re-renderizar el formulario
    $_GET['codigo'] = $_POST['codigo'] ?? '';
  }
}

/* =========================================================
   GET: cargar registro + catálogos
   ========================================================= */
$codigo = trim($_GET['codigo'] ?? '');
if ($codigo === '') {
  echo '<div class="alert alert-danger m-3">Falta el código de materia prima.</div>';
  exit;
}

$cx = new Conexion();
$cx->abrir();

// Detalle por código
[$sqlItem, $pItem] = MateriaPrimaDAO::obtenerPorCodigo($codigo);
$cx->ejecutar($sqlItem, $pItem);
$item = $cx->registro();
if (!$item) {
  $cx->cerrar();
  echo '<div class="alert alert-danger m-3">Código inválido o no existe.</div>';
  exit;
}

// Polímeros sugeridos: base fija + distintos de BD
$polimerosBase = ['PP','PE','ABS','POM','PVC','PET','PA'];
$polimerosBD = [];
try {
  $sqlPol = "SELECT DISTINCT polimero FROM materia_prima ORDER BY polimero ASC";
  $cx->ejecutar($sqlPol, []);
  while ($r = $cx->registro()) {
    if (!empty($r['polimero'])) $polimerosBD[] = $r['polimero'];
  }
} catch (Throwable $e) {
  // silencioso
}
$polimerosSugeridos = array_values(array_unique(array_merge($polimerosBase, $polimerosBD)));

// Estados válidos desde el DAO
$estados = MateriaPrimaDAO::estadosValidos();

$cx->cerrar();

/* ---- Menú (después de manejar POST) ---- */
include_once __DIR__ . '/../Admin/menuAdmin.php';
?>
<style>
/* Estilos coherentes con tu tema "verde" */
.hero{ border-radius:18px; border:1px solid #e3eee7; box-shadow:0 8px 24px rgba(16,80,54,.08); padding:18px; }
.eyebrow{ letter-spacing:.12em; font-size:.75rem; color:#188249; font-weight:700; text-transform:uppercase; }
.hero-title{ font-weight:800; color:#0f5a32; }
.card-elev{ background:#fff; border:1px solid #e3eee7; border-radius:18px; box-shadow:0 8px 24px rgba(16,80,54,.08); overflow:hidden; }
.card-elev .card-body{ padding:16px; }
.btn-ghost{ background:#fff; border:1px solid #e3eee7; border-radius:12px; }
.btn-ghost:hover{ background:#e7f6ee; }
.btn-accent{ background:#1ea257; border-color:#1ea257; color:#fff; border-radius:12px; }
.btn-accent:hover{ background:#188249; border-color:#188249; color:#fff; }
.soft-input{ border-radius:12px; border:1px solid #dfeae4; background:#fff; transition:.2s ease; }
.soft-input:focus{ border-color:#1ea257; box-shadow:0 0 0 .2rem rgba(33,178,107,.12); }
</style>

<div class="container-xxl px-3 px-md-4 px-lg-5 my-3">
  <div class="hero mb-3 d-flex justify-content-between align-items-center">
    <div>
      <div class="eyebrow mb-1">Materia prima</div>
      <h3 class="hero-title mb-0">Editar: <?= h($item['codigo']) ?></h3>
    </div>
    <a class="btn btn-ghost" href="<?= h($URL_LISTAR) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Volver</a>
  </div>

  <div class="card-elev">
    <div class="card-body">
      <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger"><?= h($errorMsg) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= h($URL_ACTUALIZAR) ?>" class="row g-3">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
        <input type="hidden" name="codigo" value="<?= h($item['codigo']) ?>">

        <div class="col-md-3">
          <label class="form-label">Polímero *</label>
          <select name="polimero" class="form-select soft-input" required>
            <?php foreach ($polimerosSugeridos as $p): ?>
              <option value="<?= h($p) ?>" <?= ($item['polimero']===$p)?'selected':'' ?>><?= h($p) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-5">
          <label class="form-label">Referencia *</label>
          <input name="referencia" class="form-control soft-input" value="<?= h($item['referencia']) ?>" required maxlength="100">
        </div>

        <div class="col-md-4">
          <label class="form-label">Estado del material *</label>
          <select name="estado" class="form-select soft-input" required>
            <?php foreach ($estados as $e): ?>
              <option value="<?= h($e) ?>" <?= ($item['estado']===$e)?'selected':'' ?>><?= h(ucfirst($e)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">Color</label>
          <input name="color" class="form-control soft-input" value="<?= h((string)$item['color']) ?>" maxlength="50">
        </div>

        <div class="col-md-3">
          <label class="form-label">Procedencia</label>
          <input name="procedencia" class="form-control soft-input" value="<?= h((string)$item['procedencia']) ?>" maxlength="100">
        </div>

        <div class="col-md-3">
          <label class="form-label">Uso final</label>
          <input name="uso_final" class="form-control soft-input" value="<?= h((string)$item['uso_final']) ?>" maxlength="100">
        </div>

        <div class="col-md-3">
          <label class="form-label">Activo</label>
          <select name="activo" class="form-select soft-input">
            <option value="1" <?= ((int)$item['activo']===1)?'selected':'' ?>>Sí</option>
            <option value="0" <?= ((int)$item['activo']===0)?'selected':'' ?>>No</option>
          </select>
        </div>

        <div class="col-12 d-flex gap-2">
          <button class="btn btn-accent"><i class="fa-solid fa-floppy-disk me-1"></i> Guardar cambios</button>
          <a class="btn btn-ghost" href="<?= h($URL_LISTAR) ?>">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
