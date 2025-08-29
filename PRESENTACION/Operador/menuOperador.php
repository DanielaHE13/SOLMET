<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/* ==== Guard de rol (solo operador) ==== */
if (($_SESSION['rol'] ?? '') !== 'operador') {
  return;
}

/* ==== Datos de sesión ==== */
$nombre   = $_SESSION['nombre']   ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$username = $_SESSION['username'] ?? 'Usuario';
$display  = trim("$nombre $apellido") ?: $username;

/* Foto de perfil */
$fotoRel     = $_SESSION['foto'] ?? 'IMG/Usuarios/ingeniero2.png';
$fallbackRel = 'IMG/avatar-default.png';

/* BASE_URL para apps en subcarpeta (/SOLMET3, etc.) */
$BASE_URL   = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$basePrefix = $BASE_URL ? $BASE_URL . '/' : '/';
$fotoUrl    = $basePrefix . ltrim($fotoRel, '/');
$fallback   = $basePrefix . ltrim($fallbackRel, '/');

/* Ruta activa por pid */
$current = base64_decode($_GET['pid'] ?? '', true) ?: '';
$active  = fn(string $p) => $current === $p ? ' active" aria-current="page' : '"';

/* CSRF para logout (si usas POST) */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];
?>

<nav class="navbar navbar-expand-lg navbar-light py-2 px-4 flex-wrap"
  role="navigation" aria-label="Menú principal operador"
  style="margin:10px auto; max-width:98%; background:#d6f0e0; border-radius:20px; box-shadow:0 2px 10px rgba(0,0,0,.1);">

  <!-- Perfil -->
  <div class="d-flex align-items-center">
    <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="Foto de perfil" width="56" height="56"
      class="rounded-circle shadow-sm me-3" style="object-fit:cover;"
      onerror="this.onerror=null;this.src='<?= htmlspecialchars($fallback) ?>';">
    <div>
      <h3 class="mb-1 d-flex align-items-center gap-2">
        <strong class="text-success">Hola, <?= htmlspecialchars($display) ?></strong>
        <a class="link-success text-decoration-none" href="?pid=<?= base64_encode('PRESENTACION/editarFoto.php') ?>" title="Editar perfil">
          <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i><span class="visually-hidden">Editar perfil</span>
        </a>
      </h3>
      <p class="mb-0 text-success"><i class="fa-solid fa-user me-2" aria-hidden="true"></i>@<?= htmlspecialchars($username) ?></p>
    </div>
  </div>

  <!-- Toggler -->
  <button class="navbar-toggler d-lg-none ms-auto" type="button" data-bs-toggle="collapse"
    data-bs-target="#navbarSolmet" aria-controls="navbarSolmet" aria-expanded="false" aria-label="Mostrar navegación">
    <span class="navbar-toggler-icon"></span>
  </button>

  <!-- Menú -->
  <div class="collapse navbar-collapse mt-3 mt-lg-0" id="navbarSolmet">
    <ul class="navbar-nav ms-auto">

      <!-- Inicio -->
      <li class="nav-item me-3">
        <a class="nav-link fw-bold fs-5 text-success<?= $active('PRESENTACION/Operador/sesionOperador.php') ?>"
          href="?pid=<?= base64_encode('PRESENTACION/Operador/sesionOperador.php') ?>" title="Inicio">
          <i class="fa-solid fa-house"></i><span class="visually-hidden">Inicio</span>
        </a>
      </li>

      <!-- Órdenes -->
      <li class="nav-item dropdown me-3">
        <a class="nav-link dropdown-toggle fw-bold fs-5 text-success" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="ddOrdenes">
          <i class="fa-solid fa-list-check me-1" aria-hidden="true"></i> Órdenes
        </a>
        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="ddOrdenes" style="background:#e8f6ee;">
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/OrdenProduccion/crear.php') ?>" href="?pid=<?= base64_encode('PRESENTACION/OrdenProduccion/crear.php') ?>">Crear OP</a></li>
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/OrdenProduccion/ver.php') ?>" href="?pid=<?= base64_encode('PRESENTACION/OrdenProduccion/ver.php') ?>">Ver OP(s)</a></li>
        </ul>
      </li>

      <!-- Máquinas -->
      <li class="nav-item dropdown me-3">
        <a class="nav-link dropdown-toggle fw-bold fs-5 text-success" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="ddMaquinas">
          <i class="fa-solid fa-gears me-1" aria-hidden="true"></i> Máquinas
        </a>
        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="ddMaquinas" style="background:#e8f6ee;">
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/Maquina/listar.php') ?>" href="?pid=<?= base64_encode('PRESENTACION/Maquina/listar.php') ?>">Listar</a></li>
        </ul>
      </li>

      <!-- Moldes -->
      <li class="nav-item dropdown me-3">
        <a class="nav-link dropdown-toggle fw-bold fs-5 text-success" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="ddMoldes">
          <i class="fa-solid fa-toolbox me-1" aria-hidden="true"></i> Moldes
        </a>
        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="ddMoldes" style="background:#e8f6ee;">
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/Molde/listar.php') ?>" href="?pid=<?= base64_encode('PRESENTACION/Molde/listar.php') ?>">Listar</a></li>
        </ul>
      </li>

      <!-- Insertos -->
      <li class="nav-item dropdown me-3">
        <a class="nav-link dropdown-toggle fw-bold fs-5 text-success" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="ddInsertos">
          <i class="fa-solid fa-screwdriver-wrench me-1" aria-hidden="true"></i> Insertos
        </a>
        <ul class="dropdown-menu border-0 shadow-sm" aria-labelledby="ddInsertos" style="background:#e8f6ee;">
          <li><a class="dropdown-item text-success<?= $active('PRESENTACION/Inserto/listar.php') ?>" href="?pid=<?= base64_encode('PRESENTACION/Inserto/listar.php') ?>">Listar</a></li>
        </ul>
      </li>

      <!-- Salir -->
      <li class="nav-item d-flex align-items-center">
        <button type="button" class="btn text-white fw-bold px-4 py-2 ms-2"
          style="background:#1ea257; border-radius:12px;"
          data-bs-toggle="modal" data-bs-target="#modalCerrarSesion">
          <i class="fa-solid fa-right-from-bracket me-2 fa-lg"></i>Salir
        </button>
      </li>
    </ul>
  </div>
</nav>

<!-- Modal: Cerrar sesión -->
<div class="modal fade" id="modalCerrarSesion" tabindex="-1" aria-labelledby="modalCerrarSesionLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px; background:#edf7f1;">
      <div class="modal-header text-white" style="background:#1ea257; border-top-left-radius:20px; border-top-right-radius:20px;">
        <h5 class="modal-title" id="modalCerrarSesionLabel">
          <i class="fa-solid fa-circle-exclamation me-2" aria-hidden="true"></i> ¿Cerrar sesión?
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <p class="fs-5 mt-3" style="color:#13663a;">¿Deseas salir de <strong>Solmet</strong>?</p>
      </div>
      <div class="modal-footer justify-content-center">
        <!-- Opción A: POST + CSRF (seguro, con $basePrefix) -->
        <form method="post" action="?pid=<?= base64_encode('PRESENTACION/Logout.php') ?>" class="m-0">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
          <button type="submit" class="btn text-white fw-bold px-4" style="background:#1ea257; border-radius:12px;">
            <i class="fa-solid fa-door-open me-2" aria-hidden="true"></i> Sí, cerrar sesión
          </button>
        </form>


        <!-- Opción B: router por GET -->
        <!-- <a href="?pid=<?= base64_encode('PRESENTACION/Logout.php') ?>" class="btn text-white fw-bold px-4" style="background:#1ea257; border-radius:12px;">
          <i class="fa-solid fa-door-open me-2" aria-hidden="true"></i> Sí, cerrar sesión
        </a> -->

        <button type="button" class="btn text-white fw-bold px-4" style="background:#4fc987; border-radius:12px;" data-bs-dismiss="modal">
          Cancelar
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  :root {
    --g100: #d6f0e0;
    --g600: #1ea257;
    --g700: #188249;
  }

  .dropdown-item.text-success:hover {
    background: #dff3e7;
  }
</style>