<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/* ==== Guard de rol (solo admin) ==== */
if (($_SESSION['rol'] ?? '') !== 'admin') { return; }

/* ==== Datos de sesión ==== */
$nombre      = $_SESSION['nombre']   ?? '';
$apellido    = $_SESSION['apellido'] ?? '';
$username    = $_SESSION['username'] ?? 'Administrador';
$displayName = trim("$nombre $apellido") ?: $username;

/* Foto desde BD/Sesión */
$fotoRel = $_SESSION['foto'] ?? 'IMG/Usuarios/admin.png';

/* Base URL si la app vive en subcarpeta (ej: /SOLMET) */
$BASE_URL = rtrim(str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])), '/');

/* Rutas absolutas seguras para imágenes y logout */
$fotoUrl   = ($BASE_URL ? $BASE_URL : '') . '/' . ltrim($fotoRel,'/');
$fallback  = ($BASE_URL ? $BASE_URL : '') . '/IMG/avatar-default.png';
$logoutAction = ($BASE_URL ? $BASE_URL : '') . '/PRESENTACION/Logout.php';

/* Activo por ruta actual */
$current = base64_decode($_GET['pid'] ?? '', true) ?: '';
$active = function(string $path) use ($current) {
  return $current === $path ? ' active' : '';
};

/* CSRF para logout */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
?>

<!-- ============ MENÚ SUPERIOR • SOLMET (ADMINISTRADOR) ============ -->
<nav class="navbar navbar-expand-lg navbar-light py-2 px-4 flex-wrap"
     role="navigation" aria-label="Menú administrador"
     style="margin:10px auto; max-width:98%; background:#d6f0e0; border-radius:20px; box-shadow:0 2px 10px rgba(0,0,0,.1);">

  <!-- Saludo + foto + usuario -->
  <div class="d-flex align-items-center">
    <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="Foto de perfil" width="56" height="56"
         class="rounded-circle shadow-sm me-3" style="object-fit:cover;"
         onerror="this.onerror=null;this.src='<?= htmlspecialchars($fallback) ?>';">
    <div>
      <h3 class="mb-1"><strong class="text-success">Hola <?= htmlspecialchars($displayName) ?></strong></h3>
      <p class="mb-0 text-success">
        <i class="fa-solid fa-user-shield me-2" aria-hidden="true"></i>@<?= htmlspecialchars($username) ?> (Administrador)
      </p>
    </div>
  </div>

  <!-- Toggler móvil -->
  <button class="navbar-toggler d-lg-none ms-auto" type="button"
          data-bs-toggle="collapse" data-bs-target="#navbarSolmetAdmin"
          aria-controls="navbarSolmetAdmin" aria-expanded="false" aria-label="Mostrar menú">
    <span class="navbar-toggler-icon"></span>
  </button>

  <!-- Menú derecho -->
  <div class="collapse navbar-collapse mt-3 mt-lg-0" id="navbarSolmetAdmin">
    <ul class="navbar-nav ms-auto">

      <!-- Inicio -->
      <li class="nav-item me-3">
        <a class="nav-link fw-bold fs-5 text-success<?= $active('PRESENTACION/Admin/sesionAdmin.php') ?>"
           href="?pid=<?= base64_encode('PRESENTACION/Admin/sesionAdmin.php') ?>" aria-label="Inicio">
          <i class="fa-solid fa-house"></i>
        </a>
      </li>

      <!-- Usuarios -->
      <li class="nav-item dropdown me-3">
        <?php
          $usuariosActive = in_array($current, [
            'PRESENTACION/Admin/listarUsuarios.php',
            'PRESENTACION/Admin/crearUsuario.php'
          ], true) ? ' active' : '';
        ?>
        <a class="nav-link dropdown-toggle fw-bold fs-5 text-success<?= $usuariosActive ?>"
           href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa-solid fa-users me-1"></i> Usuarios
        </a>
        <ul class="dropdown-menu" style="background:#e8f6ee;">
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/Admin/listarUsuarios.php') ?>"
                 href="?pid=<?= base64_encode('PRESENTACION/Admin/listarUsuarios.php') ?>">Listar</a></li>
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/Admin/crearUsuario.php') ?>"
                 href="?pid=<?= base64_encode('PRESENTACION/Admin/crearUsuario.php') ?>">Crear</a></li>
        </ul>
      </li>

      <!-- Órdenes -->
      <li class="nav-item dropdown me-3">
        <?php
          $ordenesActive = in_array($current, [
            'PRESENTACION/OrdenProduccion/crear.php',
            'PRESENTACION/OrdenProduccion/ver.php'
          ], true) ? ' active' : '';
        ?>
        <a class="nav-link dropdown-toggle fw-bold fs-5 text-success<?= $ordenesActive ?>"
           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="ddOrdenes">
          <i class="fa-solid fa-list-check me-1" aria-hidden="true"></i> Órdenes
        </a>
        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="ddOrdenes" style="background:#e8f6ee;">
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/OrdenProduccion/crear.php') ?>"
                 href="?pid=<?= base64_encode('PRESENTACION/OrdenProduccion/crear.php') ?>">Crear OP</a></li>
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/OrdenProduccion/ver.php') ?>"
                 href="?pid=<?= base64_encode('PRESENTACION/OrdenProduccion/ver.php') ?>">Ver OP(s)</a></li>
        </ul>
      </li>

      <!-- Catálogos -->
      <li class="nav-item dropdown me-3">
        <?php
          $catalogosActive = in_array($current, [
            'PRESENTACION/Maquina/listar.php',
            'PRESENTACION/Molde/listar.php',
            'PRESENTACION/Inserto/listar.php',
            'PRESENTACION/MateriaPrima/listar.php'
          ], true) ? ' active' : '';
        ?>
        <a class="nav-link dropdown-toggle fw-bold fs-5 text-success<?= $catalogosActive ?>"
           href="#" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa-solid fa-database me-1"></i> Catálogos
        </a>
        <ul class="dropdown-menu" style="background:#e8f6ee;">
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/Maquina/listar.php') ?>"
                 href="?pid=<?= base64_encode('PRESENTACION/Maquina/listar.php') ?>">Máquinas</a></li>
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/Molde/listar.php') ?>"
                 href="?pid=<?= base64_encode('PRESENTACION/Molde/listar.php') ?>">Moldes</a></li>
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/Inserto/listar.php') ?>"
                 href="?pid=<?= base64_encode('PRESENTACION/Inserto/listar.php') ?>">Insertos</a></li>
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/MateriaPrima/listar.php') ?>"
                 href="?pid=<?= base64_encode('PRESENTACION/MateriaPrima/listar.php') ?>">Materia Prima</a></li>
        </ul>
      </li>

      <!-- Salir -->
      <li class="nav-item d-flex align-items-center">
        <button type="button" class="btn text-white fw-bold px-4 py-2 ms-2"
                style="background:#1ea257; border-radius:12px;"
                data-bs-toggle="modal" data-bs-target="#modalCerrarSesionAdmin">
          <i class="fa-solid fa-right-from-bracket me-2 fa-lg"></i>Salir
        </button>
      </li>

    </ul>
  </div>
</nav>

<!-- Modal salir (POST + CSRF) -->
<div class="modal fade" id="modalCerrarSesionAdmin" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px; background:#edf7f1;">
      <div class="modal-header text-white" style="background:#1ea257; border-top-left-radius:20px; border-top-right-radius:20px;">
        <h5 class="modal-title"><i class="fa-solid fa-circle-exclamation me-2"></i> ¿Cerrar sesión?</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <p class="fs-5 mt-3" style="color:#13663a;">¿Deseas salir de <strong>Solmet</strong>?</p>
      </div>
      <div class="modal-footer justify-content-center">
        <form method="post" action="<?= htmlspecialchars($logoutAction) ?>" id="logoutForm">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <button type="submit" class="btn text-white fw-bold px-4" style="background:#1ea257; border-radius:12px;">
            <i class="fa-solid fa-door-open me-2"></i> Sí, cerrar sesión
          </button>
        </form>
        <button type="button" class="btn text-white fw-bold px-4" style="background:#4fc987; border-radius:12px;" data-bs-dismiss="modal">
          Cancelar
        </button>
      </div>
    </div>
  </div>
</div>
