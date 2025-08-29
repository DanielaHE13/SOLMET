<?php
// PRESENTACION/MateriaPrima/crear.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/MateriaPrimaDAO.php';

/* ---- Autorización ---- */
$rol = $_SESSION['rol'] ?? null;
if ($rol !== 'admin') { include __DIR__ . '/../Noautorizado.php'; exit; }

/* ---- Menú ---- */
include_once __DIR__ . '/../Admin/menuAdmin.php';

/* ---- Helpers y URLs ---- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$URL_GUARDAR = '?pid=' . base64_encode('PRESENTACION/MateriaPrima/guardar.php');
$URL_LISTAR  = '?pid=' . base64_encode('PRESENTACION/MateriaPrima/listar.php');

/* ---- CSRF ---- */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* ---- Datos UI ---- */
$polimerosSugeridos = ['PP','PE','ABS','POM','PVC','PET','PA'];
$estados = ['original','peletizado','molido'];

/* ---- Flash (opcional) ---- */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <title>Crear Materia Prima • Solmet</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <link rel="stylesheet" href="/assets/bootstrap.min.css">
  <link rel="stylesheet" href="/assets/fontawesome.min.css">

  <style>
  :root{
    --g25:#f7fcf9; --g50:#f3fbf7; --g100:#e7f6ee; --g150:#def2e7; --g200:#d6f0e0;
    --g300:#c4ead3; --g600:#1ea257; --g650:#1a9450; --g700:#188249; --g800:#0f5a32;
    --txt:#243038; --muted:#687781; --border:#e3eee7; --shadow:0 12px 26px rgba(16,80,54,.12);
  }
  html,body{ min-height:100vh; background:linear-gradient(180deg,var(--g100),#fff 60%); }
  body{ color:var(--txt); font-family:system-ui,-apple-system,"Segoe UI",Roboto,Arial,"Noto Sans","Liberation Sans",sans-serif; }
  .page-wrap{ max-width:1200px; margin-inline:auto; }

  .hero{
    background:linear-gradient(180deg,#fff,var(--g50));
    border:1px solid var(--border);
    border-radius:20px;
    box-shadow:var(--shadow);
    padding:18px;
  }
  .eyebrow{ letter-spacing:.14em; font-size:.74rem; color:var(--g700); font-weight:800; text-transform:uppercase; }
  .hero-title{ font-weight:900; color:var(--g800); }
  .crumbs{ font-size:.92rem; color:var(--muted); }
  .crumbs a{ color:var(--g700); text-decoration:none; }
  .crumbs a:hover{ text-decoration:underline; }

  .card-elev{ background:#fff; border:1px solid var(--border); border-radius:20px; box-shadow:var(--shadow); overflow:hidden; }
  .card-head{
    padding:14px 18px; font-weight:800; color:var(--g800);
    background:linear-gradient(0deg,var(--g25),#fff); border-bottom:1px solid var(--border);
    display:flex; align-items:center; gap:.6rem;
  }
  .card-head i{ color:var(--g600); }

  .soft-input{ border-radius:12px; border:1px solid #dfeae4; background:#fff; transition:.2s ease; }
  .soft-input:focus{ border-color:var(--g600); box-shadow:0 0 0 .2rem rgba(33,178,107,.14); }
  .input-hint{ font-size:.82rem; color:var(--muted); }

  .btn-accent{ background:var(--g600); border-color:var(--g600); color:#fff; border-radius:12px; padding:.6rem 1rem; font-weight:700; }
  .btn-accent:hover{ background:var(--g650); border-color:var(--g650); }
  .btn-ghost{ background:#fff; border:1px solid var(--border); border-radius:12px; padding:.6rem 1rem; font-weight:700; }
  .btn-ghost:hover{ background:var(--g100); }

  @media (max-width: 576px){
    .sticky-actions{
      position: sticky; bottom: .5rem; z-index: 5; display:flex; gap:.5rem;
      background: linear-gradient(180deg, transparent, #ffffffdd 40%, #ffffff);
      padding-top:.5rem; padding-bottom:.2rem;
    }
  }
  </style>
</head>
<body>
<div class="container-xxl px-3 px-md-4 px-lg-5 my-3 page-wrap">

  <!-- HERO -->
  <div class="hero mb-3">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div class="eyebrow mb-1">Materia prima</div>
        <h3 class="hero-title mb-0"><i class="fa-solid fa-flask me-2 text-success"></i>Crear materia prima</h3>
        <div class="crumbs mt-1">
          <a href="<?= h($URL_LISTAR) ?>"><i class="fa-solid fa-list me-1"></i>Listado</a>
          <span class="mx-2">/</span><span class="text-secondary">Crear</span>
        </div>
      </div>
      <a class="btn btn-ghost" href="<?= h($URL_LISTAR) ?>"><i class="fa-solid fa-arrow-left me-1"></i> Volver</a>
    </div>
  </div>

  <?php if (!empty($flash)): ?>
    <div class="alert alert-<?= h($flash['type'] ?? 'info') ?>"><?= h($flash['msg'] ?? '') ?></div>
  <?php endif; ?>

  <!-- FORM CARD -->
  <div class="card-elev">
    <div class="card-head">
      <i class="fa-solid fa-sliders"></i><span>Datos de la materia prima</span>
    </div>
    <div class="card-body p-3 p-md-4">
      <form method="post" action="<?= h($URL_GUARDAR) ?>" class="row g-3" id="formMP" novalidate>
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

        <div class="col-md-4">
          <label class="form-label fw-semibold">Código *</label>
          <input name="codigo" class="form-control soft-input" maxlength="25" required
                 placeholder="Ej: PPHOPEBL007" autofocus pattern="[A-Za-z0-9\-_]{1,25}">
          <div class="input-hint mt-1">Máx 25 (letras, números, guion y guion bajo).</div>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">
            Polímero * <i class="fa-regular fa-circle-question ms-1" data-bs-toggle="tooltip" title="Resina base (PP, PE, ABS, POM, PVC, PET, PA)."></i>
          </label>
          <input class="form-control soft-input" name="polimero" list="dlPolimeros" required placeholder="Ej: PP">
          <datalist id="dlPolimeros">
            <?php foreach ($polimerosSugeridos as $p): ?>
              <option value="<?= h($p) ?>"></option>
            <?php endforeach; ?>
          </datalist>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Referencia *</label>
          <input name="referencia" class="form-control soft-input" maxlength="100" required placeholder="Ej: HOMO 60H / LD / Acetal">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Estado del material *</label>
          <select name="estado" class="form-select soft-input" required>
            <?php foreach ($estados as $e): ?>
              <option value="<?= h($e) ?>"><?= h(ucfirst($e)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Color</label>
          <input name="color" class="form-control soft-input" maxlength="50" placeholder="Ej: NATURAL / NEGRO / AZUL">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Procedencia</label>
          <input name="procedencia" class="form-control soft-input" maxlength="100" placeholder="Ej: PROPILCO / SOLMET / SALFER">
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Uso final</label>
          <input name="uso_final" class="form-control soft-input" maxlength="100" placeholder="Ej: PERILLA / TUERCA / ACCESORIOS">
        </div>

        <div class="col-md-3">
          <label class="form-label fw-semibold">Activo</label>
          <select name="activo" class="form-select soft-input">
            <option value="1" selected>Sí</option>
            <option value="0">No</option>
          </select>
        </div>

        <div class="col-12 d-flex gap-2 mt-2 sticky-actions">
          <button class="btn btn-accent" id="btnGuardar" type="submit">
            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar
          </button>
          <a class="btn btn-ghost" href="<?= h($URL_LISTAR) ?>">
            Cancelar
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="/assets/bootstrap.bundle.min.js"></script>
<script>
(() => {
  // tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

  // Evitar doble envío
  const form = document.getElementById('formMP');
  const btn  = document.getElementById('btnGuardar');
  if (form && btn) {
    form.addEventListener('submit', (e) => {
      if (!form.checkValidity()) {
        e.preventDefault(); e.stopPropagation();
        form.classList.add('was-validated');
        return;
      }
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...';
    });
  }
})();
</script>
</body>
</html>
