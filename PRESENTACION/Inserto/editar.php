<?php
// PRESENTACION/Inserto/editar.php
if (session_status()===PHP_SESSION_NONE) session_start();

/* --- Manejo de errores para evitar HTML en AJAX --- */
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

/* --- Solo ADMIN --- */
if (($_SESSION['rol'] ?? '') !== 'admin') { 
  if (!empty($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json')!==false){
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: application/json; charset=UTF-8',true,403);
    echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit;
  }
  include __DIR__.'/../Noautorizado.php'; exit;
}

require_once __DIR__ . '/../../persistencia/Conexion.php';
require_once __DIR__ . '/../../persistencia/InsertoDAO.php';

/* --- Helpers --- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function is_ajax(){
  return (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'],'application/json')!==false)
  );
}

/* --- CSRF --- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* --- Params/URLs --- */
$id = substr(trim($_GET['id'] ?? ''), 0, 25);
if ($id==='') { echo '<div class="container my-4"><div class="alert alert-danger">ID de inserto faltante.</div></div>'; exit; }

$URL_LISTAR = '?pid=' . base64_encode('PRESENTACION/Inserto/listar.php');
$SELF_BASE  = 'PRESENTACION/Inserto/editar.php';
$SELF_URL   = '?pid=' . base64_encode($SELF_BASE) . '&id=' . urlencode($id);

/* =========================================================
   POST: actualizar
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  header('Vary: Accept');
  try {
    if (($_POST['csrf'] ?? '') !== $csrf) throw new Exception('CSRF inválido.');
    $id_post = substr(trim($_POST['id_inserto'] ?? ''),0,25);
    if ($id_post !== $id) throw new Exception('ID inconsistente.');

    $id_molde    = substr(trim($_POST['id_molde'] ?? ''), 0, 25);
    $descripcion = substr(trim($_POST['descripcion'] ?? ''), 0, 150);
    $activo      = isset($_POST['activo']) ? 1 : 0;

    if ($id_molde==='' || $descripcion==='') throw new Exception('Todos los campos son obligatorios.');

    $cx = new Conexion(); 
    $cx->abrir();

    $cx->ejecutar("SELECT 1 FROM molde WHERE id_molde=? LIMIT 1", [$id_molde]);
    if (!$cx->registro()) { $cx->cerrar(); throw new Exception('El molde seleccionado no existe.'); }

    $cx->ejecutar("SELECT 1 FROM inserto WHERE id_inserto=? LIMIT 1", [$id]);
    if (!$cx->registro()) { $cx->cerrar(); throw new Exception('El inserto no existe.'); }

    $dao = new InsertoDAO($id, $id_molde, $descripcion, $activo);
    [$sql,$params] = $dao->actualizar();
    $cx->ejecutar($sql,$params);
    $cx->cerrar();

    if (is_ajax()) {
      if (ob_get_length()) ob_end_clean();
      header('Content-Type: application/json; charset=UTF-8');
      echo json_encode(['ok'=>true,'msg'=>'Inserto actualizado','id'=>$id]);
      exit;
    } else {
      $_SESSION['flash_ok'] = 'Inserto actualizado correctamente.';
      header('Location: '.$URL_LISTAR); exit;
    }
  } catch (Throwable $e) {
    if (is_ajax()) {
      if (ob_get_length()) ob_end_clean();
      header('Content-Type: application/json; charset=UTF-8', true, 400);
      echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); exit;
    } else {
      $errorMsg = $e->getMessage();
    }
  }
}

/* =========================================================
   GET: traer datos actuales + catálogo de moldes
   ========================================================= */
$cx = new Conexion(); 
$cx->abrir();
$cx->ejecutar("SELECT id_inserto, id_molde, descripcion, activo FROM inserto WHERE id_inserto=? LIMIT 1", [$id]);
$ins = $cx->registro();
if (!$ins) { $cx->cerrar(); echo '<div class="container my-4"><div class="alert alert-danger">Inserto no encontrado.</div></div>'; exit; }
$cx->ejecutar("SELECT id_molde, nombre FROM molde ORDER BY nombre ASC");
$moldes = $cx->registros();
$cx->cerrar();

/* Solo incluir menú en GET (vista) */
include_once __DIR__ . '/../Admin/menuAdmin.php';
?>
<div class="container-xxl px-3 px-md-4 px-lg-5 my-3">
  <div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-header bg-white">
      <h5 class="mb-0 text-success"><i class="fa-solid fa-pen-to-square me-2"></i>Editar inserto</h5>
    </div>
    <div class="card-body">
      <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger"><?= h($errorMsg) ?></div>
      <?php endif; ?>

      <form id="formInsertoEdit" action="<?= $SELF_URL ?>" method="post" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">ID inserto</label>
            <input name="id_inserto" class="form-control" value="<?= h($ins['id_inserto']) ?>" readonly>
          </div>
          <div class="col-md-8">
            <label class="form-label fw-semibold">Molde</label>
            <select name="id_molde" class="form-select" required>
              <option value="">Seleccione...</option>
              <?php foreach($moldes as $m): ?>
                <option value="<?= h($m['id_molde']) ?>" <?= ($ins['id_molde']===$m['id_molde']?'selected':'') ?>>
                  <?= h($m['nombre']) ?> (<?= h($m['id_molde']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Descripción</label>
            <input name="descripcion" class="form-control" required maxlength="150" value="<?= h($ins['descripcion']) ?>">
          </div>
          <div class="col-md-4">
            <div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" name="activo" id="chkActivo" <?= ((int)$ins['activo']===1?'checked':'') ?>>
              <label class="form-check-label" for="chkActivo">Activo</label>
            </div>
          </div>
        </div>
        <div class="d-flex gap-2 justify-content-end mt-3">
          <a class="btn btn-outline-secondary" href="<?= $URL_LISTAR ?>">Cancelar</a>
          <button class="btn btn-success" id="btnSave">
            <i class="fa-regular fa-floppy-disk me-1"></i> Guardar cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const f = document.getElementById('formInsertoEdit');
f?.addEventListener('submit', async (e)=>{
  if (!window.fetch) return; // fallback a submit normal
  e.preventDefault();
  const btn = document.getElementById('btnSave');
  btn.disabled = true;
  try{
    const r = await fetch('<?= $SELF_URL ?>', {
      method:'POST',
      body:new FormData(f),
      headers:{'Accept':'application/json'}
    });
    const j = await r.json();
    if(!r.ok || !j.ok) throw new Error(j.msg || 'Error al actualizar');
    location.href = '<?= $URL_LISTAR ?>';
  }catch(err){ alert(err.message); }
  finally{ btn.disabled=false; }
});
</script>
