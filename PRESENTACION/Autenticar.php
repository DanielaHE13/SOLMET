<?php
require_once __DIR__ . '/../persistencia/Conexion.php';
require_once __DIR__ . '/../persistencia/UsuarioDAO.php';
require_once __DIR__ . '/../logica/Usuario.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// CSRF
if (empty($_SESSION['csrf'])) {
  $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

$errorLogin = $infoLogin = $errorReg = $okReg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $form = $_POST['form'] ?? '';

  /* =======================
       LOGIN
    ======================= */

  if ($form === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrfPost = $_POST['csrf'] ?? '';

    if (!hash_equals($_SESSION['csrf'] ?? '', $csrfPost)) {
      $errorLogin = "Token CSRF inválido.";
    } elseif ($username && $password) {
      require_once __DIR__ . '/../logica/Usuario.php';
      $u   = new Usuario();
      $res = $u->login($username, $password);

      if (!$res['ok']) {
        $errorLogin = $res['msg'] ?? 'No fue posible iniciar sesión.';
      } else {
        $usr = $res['usuario'];

        // 🔐 Seguridad: rotar el ID de sesión tras autenticación
        session_regenerate_id(true);

        // ✅ Claves de sesión (router espera 'uid')
        $_SESSION["uid"]         = $usr['id_usuario'];   // <-- clave que el router valida
        $_SESSION["id_usuario"]  = $usr['id_usuario'];   // alias (útil en otras vistas)
        $_SESSION["username"]    = $usr['username'];
        $_SESSION["nombre"]      = $usr['nombre'];
        $_SESSION["apellido"]    = $usr['apellido'];
        $_SESSION["foto"]        = $usr['foto'] ?? null;
        $_SESSION["id_rol"]      = (int)$usr['id_rol'];
        $_SESSION["rol"]         = $_SESSION["id_rol"] === 1 ? "admin" : "operador";

        // 🎯 Redirección según rol
        $redir = $_SESSION["rol"] === "admin"
          ? "PRESENTACION/Admin/sesionAdmin.php"
          : "PRESENTACION/Operador/sesionOperador.php";

        $url = "?pid=" . base64_encode($redir);

        // Redirigir (header) + fallback JS por si hubo salida previa
        header("Location: {$url}");
        echo "<script>location.href='{$url}';</script>";
        exit;
      }
    } else {
      $errorLogin = "Debes ingresar usuario y contraseña.";
    }
  }






  /* =======================
       REGISTRO
    ======================= */
  if ($form === 'register') {
    $id_usuario = $_POST['id_usuario'] ?? '';
    $nombre     = $_POST['nombre'] ?? '';
    $apellido   = $_POST['apellido'] ?? '';
    $username   = $_POST['username'] ?? '';
    $clave1     = $_POST['clave_registro'] ?? '';
    $clave2     = $_POST['confirmar_clave'] ?? '';
    $idRol      = $_POST['id_rol'] ?? '';

    if ($clave1 !== $clave2) {
      $errorReg = "Las contraseñas no coinciden.";
    } elseif (!$id_usuario || !$nombre || !$apellido || !$username || !$clave1 || !$idRol) {
      $errorReg = "Todos los campos son obligatorios.";
    } else {
      // 👇 Usamos directamente Usuario::crear
      $res = Usuario::crear(
        $id_usuario,
        $username,
        $nombre,
        $apellido,
        $clave1,   // Ojo: tu método espera la clave en plano, no el hash
        (string)$idRol,
        null       // foto
      );

      if ($res['ok']) {
        $okReg = "Registro exitoso. Ya puedes iniciar sesión.";
      } else {
        $errorReg = $res['msg'];
      }
    }
  }
}

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
  :root {
    --g50: #edf7f1;
    --g100: #d6f0e0;
    --g200: #ace3c4;
    --g300: #7ed6a6;
    --g400: #4fc987;
    --g500: #27bc69;
    --g600: #1ea257;
    --g700: #188249;
    --g800: #13663a;
    --g900: #0d4529;
  }

  body {
    background:
      radial-gradient(900px 600px at 10% 10%, var(--g200), transparent 60%),
      radial-gradient(900px 600px at 90% 80%, var(--g300), transparent 60%),
      linear-gradient(180deg, var(--g50), var(--g100));
    min-height: 100vh;
  }

  .login-reg-panel {
    position: relative;
    max-width: 980px;
    margin: 40px auto;
    border-radius: 20px;
    background: rgba(255, 255, 255, .7);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(39, 188, 105, .2);
    box-shadow: 0 20px 50px rgba(13, 69, 41, .12);
    overflow: visible;
    min-height: 560px;
  }

  .login-info-box,
  .register-info-box {
    width: 50%;
    padding: 32px;
    color: var(--g900);
    box-sizing: border-box;
    min-height: 100%;
  }

  .login-info-box {
    float: left;
    background: linear-gradient(180deg, var(--g50), var(--g100));
  }

  .register-info-box {
    float: right;
    background: linear-gradient(180deg, #fff, var(--g50));
  }

  .login-info-box h3,
  .register-info-box h3 {
    color: var(--g800);
    font-weight: 800;
  }

  .login-info-box p,
  .register-info-box p {
    color: var(--g700);
  }

  .login-info-box label,
  .register-info-box label {
    display: inline-block;
    margin-top: 8px;
    padding: 8px 16px;
    border-radius: 999px;
    cursor: pointer;
    border: 2px solid var(--g500);
    color: var(--g600);
    font-weight: 700;
    transition: all .2s ease;
  }

  .login-info-box label:hover,
  .register-info-box label:hover {
    background: var(--g500);
    color: #fff;
  }

  .white-panel {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 50%;
    min-height: 560px;
    margin: 0;
    padding: 40px 28px 96px;
    background: #fff;
    border-left: 1px solid rgba(39, 188, 105, .2);
    border-right: 1px solid rgba(39, 188, 105, .2);
    transition: left .4s ease;
    z-index: 2;
    overflow-y: auto;
  }

  .white-panel.right-log {
    left: 50%;
  }

  .login-show,
  .register-show {
    display: none;
  }

  .show-log-panel {
    display: block;
  }

  .form-actions {
    position: sticky;
    bottom: 0;
    background: #fff;
    padding-top: 12px;
    box-shadow: 0 -8px 20px rgba(0, 0, 0, .06);
  }

  .form-control:focus {
    border-color: var(--g400);
    box-shadow: 0 0 0 .2rem rgba(39, 188, 105, .2);
  }

  .btn-success {
    background-color: var(--g600);
    border-color: var(--g600);
  }

  .btn-success:hover {
    background-color: var(--g700);
    border-color: var(--g700);
  }

  @media (max-width: 992px) {

    .login-info-box,
    .register-info-box {
      width: 100%;
      float: none;
      border: none;
      min-height: auto;
    }

    .white-panel {
      position: static;
      width: 100%;
      border: none;
      left: 0;
    }
  }
</style>

<div class="login-reg-panel">
  <!-- INFO IZQUIERDA -->
  <div class="login-info-box text-center">
    <h3>¿Ya tienes cuenta?</h3>
    <p>Accede para gestionar moldes, máquinas y órdenes de producción.</p>
    <label for="log-reg-show"><i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar sesión</label>
    <input type="radio" name="active-log-panel" id="log-reg-show" checked hidden>
  </div>

  <!-- INFO DERECHA -->
  <div class="register-info-box text-center">
    <h3>¿Aún no tienes cuenta?</h3>
    <p>Solicita tu acceso para empezar a usar el sistema de producción Solmet.</p>
    <label for="log-login-show"><i class="fa-solid fa-user-plus me-2"></i> Registrarse</label>
    <input type="radio" name="active-log-panel" id="log-login-show" hidden>
  </div>

  <!-- PANEL BLANCO -->
  <div class="white-panel">
    <!-- LOGIN -->
    <div class="login-show show-log-panel">
      <h2 class="fw-bold text-success mb-4">Iniciar sesión</h2>
      <form method="post" action="">
        <input type="hidden" name="form" value="login">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

        <div class="mb-3">
          <label for="username" class="form-label fw-semibold">Usuario</label>
          <input type="text" id="username" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Contraseña</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">Entrar</button>
      </form>

      <?php if ($errorLogin): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($errorLogin) ?></div>
      <?php endif; ?>
      <?php if ($infoLogin): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($infoLogin) ?></div>
      <?php endif; ?>
    </div>

    <!-- REGISTRO -->
    <div class="register-show" id="registro">
      <h2 class="fw-bold text-success mb-4">Registrarse</h2>
      <form method="post" autocomplete="off">
        <input type="hidden" name="form" value="register">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

        <div class="mb-3">
          <label for="id_rol" class="form-label">Tipo de usuario</label>
          <select id="id_rol" name="id_rol" class="form-control" required>
            <option value="">Seleccione una opción</option>
            <option value="2">Operador</option>
            <option value="1">Administrador</option>
          </select>
        </div>

        <div class="mb-3">
          <label for="id_usuario" class="form-label">Cédula</label>
          <input type="text" id="id_usuario" name="id_usuario" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="nombre" class="form-label">Nombre</label>
          <input type="text" id="nombre" name="nombre" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="apellido" class="form-label">Apellido</label>
          <input type="text" id="apellido" name="apellido" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="username_reg" class="form-label">Usuario</label>
          <input type="text" id="username_reg" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="clave_registro" class="form-label">Contraseña</label>
          <input type="password" id="clave_registro" name="clave_registro" class="form-control" required>
        </div>

        <div class="mb-3">
          <label for="confirmar_clave" class="form-label">Confirmar contraseña</label>
          <input type="password" id="confirmar_clave" name="confirmar_clave" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">Registrarse</button>
      </form>

      <?php if ($errorReg): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($errorReg) ?></div>
      <?php endif; ?>
      <?php if ($okReg): ?>
        <div class="alert alert-success mt-3"><?= htmlspecialchars($okReg) ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const regRadio = document.getElementById("log-login-show");
    const logRadio = document.getElementById("log-reg-show");
    const whitePanel = document.querySelector(".white-panel");
    const loginShow = document.querySelector(".login-show");
    const registerShow = document.querySelector(".register-show");

    regRadio.addEventListener("change", () => {
      whitePanel.classList.add("right-log");
      loginShow.classList.remove("show-log-panel");
      registerShow.classList.add("show-log-panel");
    });

    logRadio.addEventListener("change", () => {
      whitePanel.classList.remove("right-log");
      registerShow.classList.remove("show-log-panel");
      loginShow.classList.add("show-log-panel");
    });
  });
</script>