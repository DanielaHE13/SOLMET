<?php
// PRESENTACION/Admin/crearUsuario.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();

/* ==== Guard (solo admin) ==== */
if (($_SESSION['rol'] ?? '') !== 'admin') { include __DIR__.'/../Noautorizado.php'; return; }

require_once __DIR__ . '/../../persistencia/Conexion.php';

/* ==== Helpers ==== */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect_js(string $url){
    // Usamos JS porque este archivo se incluye dentro de index.php
    echo "<script>window.location.href='" . $url . "';</script>";
    exit;
}

/* ==== CSRF ==== */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

/* ==== Conexión ==== */
$cx = new Conexion();
$cx->abrir();

/* ==== Cargar roles ==== */
$roles = [];
$cx->ejecutar("SELECT id_rol, nombre FROM rol ORDER BY nombre ASC");
while ($r = $cx->registro()) $roles[] = $r;

/* ==== Detectar edición ==== */
$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$modo = $id > 0 ? 'edit' : 'new';

$user = [
  'id_usuario' => 0,
  'username'   => '',
  'nombre'     => '',
  'apellido'   => '',
  'foto'       => null,
  'id_rol'     => ($roles[0]['id_rol'] ?? 1),
  'activo'     => 1,
];

if ($modo === 'edit') {
  $cx->ejecutar("SELECT id_usuario, username, nombre, apellido, foto, id_rol, activo FROM usuario WHERE id_usuario = ?", [$id]);
  if ($row = $cx->registro()) {
    $user = array_merge($user, $row);
  } else {
    $cx->cerrar();
    $_SESSION['flash_error'] = 'Usuario no encontrado.';
    redirect_js('index.php?pid='.base64_encode('PRESENTACION/Admin/listarUsuarios.php'));
  }
}

/* ==== Guardar ==== */
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
    $err = 'Token CSRF inválido.';
  } else {
    $username = trim($_POST['username'] ?? '');
    $nombre   = trim($_POST['nombre']   ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $foto     = trim($_POST['foto']     ?? '');
    $id_rol   = (int)($_POST['id_rol']  ?? 0);
    $activo   = isset($_POST['activo']) ? 1 : 0;

    $pass1    = $_POST['password']  ?? '';
    $pass2    = $_POST['password2'] ?? '';

    if ($username === '') $err = 'El nombre de usuario es obligatorio.';
    if (!$err && $id_rol <= 0) $err = 'Debes seleccionar un rol.';

    // Unicidad de username (en alta o si cambia)
    if (!$err) {
      if ($modo === 'new' || $username !== $user['username']) {
        $cx->ejecutar("SELECT COUNT(*) FROM usuario WHERE username = ?", [$username]);
        if ((int)($cx->registro()[0] ?? 0) > 0) $err = 'El usuario ya existe.';
      }
    }

    // Password obligatorio sólo en alta
    if (!$err && $modo === 'new') {
      if ($pass1 === '' || $pass2 === '') $err = 'La contraseña es obligatoria.';
      elseif ($pass1 !== $pass2)          $err = 'Las contraseñas no coinciden.';
      elseif (strlen($pass1) < 6)         $err = 'La contraseña debe tener al menos 6 caracteres.';
    }

    if (!$err) {
      try {
        if ($modo === 'new') {
          $cx->ejecutar("SELECT COALESCE(MAX(id_usuario),0)+1 FROM usuario");
          $newId = (int)($cx->registro()[0] ?? 1);

          $hash = password_hash($pass1, PASSWORD_DEFAULT);
          $cx->ejecutar("
            INSERT INTO usuario (id_usuario, username, nombre, apellido, foto, password_hash, id_rol, activo)
            VALUES (?,?,?,?,?,?,?,?)
          ", [$newId, $username, $nombre, $apellido, ($foto ?: null), $hash, $id_rol, $activo]);

          $_SESSION['flash_success'] = 'Usuario creado exitosamente.';
          redirect_js('index.php?pid='.base64_encode('PRESENTACION/Admin/listarUsuarios.php'));

        } else {
          $cx->ejecutar("
            UPDATE usuario
               SET username=?, nombre=?, apellido=?, foto=?, id_rol=?, activo=?
             WHERE id_usuario=?
          ", [$username, $nombre, $apellido, ($foto ?: null), $id_rol, $activo, $user['id_usuario']]);

          if ($pass1 !== '' || $pass2 !== '') {
            if ($pass1 !== $pass2)  throw new Exception('Las contraseñas no coinciden.');
            if (strlen($pass1) < 6) throw new Exception('La contraseña debe tener al menos 6 caracteres.');
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $cx->ejecutar("UPDATE usuario SET password_hash=? WHERE id_usuario=?", [$hash, $user['id_usuario']]);
          }

          $_SESSION['flash_success'] = 'Usuario actualizado exitosamente.';
          redirect_js('index.php?pid='.base64_encode('PRESENTACION/Admin/listarUsuarios.php'));
        }
      } catch (Throwable $e) {
        $err = 'Error al guardar: '.$e->getMessage();
      }
    }

    $user = array_merge($user, [
      'username'=>$username, 'nombre'=>$nombre, 'apellido'=>$apellido,
      'foto'=>$foto ?: null, 'id_rol'=>$id_rol, 'activo'=>$activo
    ]);
  }
}

$cx->cerrar();

/* === A partir de aquí ya podemos imprimir HTML === */
include_once __DIR__ . '/menuAdmin.php';
?>
<!-- el resto del HTML (formulario) igual que lo tienes -->

<div class="container-xxl px-3 px-md-4 px-lg-5 my-3">
  <!-- Hero -->
  <div class="mb-3" style="border-radius:18px;border:1px solid #e3eee7;padding:16px;background:
    radial-gradient(1200px 200px at -20% -50%, #ffffff 0%, transparent 60%),
    linear-gradient(135deg, #d6f0e0 0%, #f3fbf7 60%, #fff 100%);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
      <div>
        <div style="letter-spacing:.12em;text-transform:uppercase;color:#188249;font-weight:800;font-size:.8rem;">
          <?= $modo==='new' ? 'Crear' : 'Editar' ?> usuario
        </div>
        <h3 class="mb-0" style="font-weight:800;color:#0f5a32;">
          <?= $modo==='new' ? 'Nuevo usuario' : 'Editar usuario #'.(int)$user['id_usuario'] ?>
        </h3>
      </div>
      <a class="btn btn-outline-success" style="border-radius:12px;" href="?pid=<?= base64_encode('PRESENTACION/Admin/listarUsuarios.php') ?>">
        <i class="fa-solid fa-list me-1"></i> Volver al listado
      </a>
    </div>
  </div>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= h($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <?php if ($err!==''): ?>
    <div class="alert alert-danger"><?= h($err) ?></div>
  <?php endif; ?>

  <!-- Form -->
  <div class="card border-0 shadow-sm" style="border-radius:18px;">
    <div class="card-body">
      <form method="post" autocomplete="off">
        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

        <div class="row g-3">
          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Usuario</label>
            <input type="text" name="username" class="form-control" required value="<?= h($user['username']) ?>">
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Nombre</label>
            <input type="text" name="nombre" class="form-control" value="<?= h($user['nombre']) ?>">
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Apellido</label>
            <input type="text" name="apellido" class="form-control" value="<?= h($user['apellido']) ?>">
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Rol</label>
            <select name="id_rol" class="form-select" required>
              <?php foreach($roles as $r): ?>
                <option value="<?= (int)$r['id_rol'] ?>" <?= ((int)$user['id_rol']===(int)$r['id_rol'])?'selected':'' ?>>
                  <?= h($r['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Foto (URL/ruta)</label>
            <input type="text" name="foto" class="form-control" value="<?= h($user['foto'] ?? '') ?>">
            <?php if (!empty($user['foto'])): ?>
              <div class="mt-2"><img src="<?= h($user['foto']) ?>" alt="Foto" style="height:56px;border-radius:8px;"></div>
            <?php endif; ?>
          </div>

          <div class="col-12 col-md-4 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="activo" id="chkActivo" value="1" <?= !empty($user['activo'])? 'checked':'' ?>>
              <label class="form-check-label" for="chkActivo">Usuario activo</label>
            </div>
          </div>

          <div class="col-12"><hr></div>

          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">
              Contraseña <?= $modo==='new' ? '(obligatoria)' : '(dejar vacío para no cambiar)' ?>
            </label>
            <input type="password" name="password" class="form-control" <?= $modo==='new'?'required':'' ?>>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Repetir contraseña</label>
            <input type="password" name="password2" class="form-control" <?= $modo==='new'?'required':'' ?>>
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-3">
          <a class="btn btn-outline-secondary" href="?pid=<?= base64_encode('PRESENTACION/Admin/listarUsuarios.php') ?>">Cancelar</a>
          <button class="btn btn-success"><i class="fa-regular fa-floppy-disk me-1"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>
