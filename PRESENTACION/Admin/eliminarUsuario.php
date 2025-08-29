<?php
// ============================================================
//  PRESENTACION/Admin/eliminarUsuario.php
//  Procesa la eliminación de un usuario (solo admin)
//  - Valida método y CSRF
//  - Reglas: no borrarse a sí mismo / no borrar último admin
//  - Elimina y redirige SIEMPRE al listado por el router raíz
// ============================================================
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../persistencia/Conexion.php';

/* ---------- Helpers ---------- */
function redirect_listado(): void {
  // Calcula /<base>/index.php?pid=...
  $basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/PRESENTACION/Admin/eliminarUsuario.php')), '/');
  $basePath = $basePath === '' ? '' : $basePath;
  $dest = $basePath . '/index.php?pid=' . base64_encode('PRESENTACION/Admin/listarUsuarios.php');

  if (!headers_sent()) {
    header('Location: ' . $dest);
    exit;
  }
  // Fallback (por si algo imprimió antes)
  echo '<script>location.href=' . json_encode($dest) . ';</script>';
  exit;
}

/* ---------- Autorización ---------- */
if (($_SESSION['rol'] ?? '') !== 'admin') {
  $_SESSION['flash'] = ['tipo'=>'danger','txt'=>'No autorizado.'];
  redirect_listado();
}

/* ---------- Método y CSRF ---------- */
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  $_SESSION['flash'] = ['tipo'=>'danger','txt'=>'Método inválido.'];
  redirect_listado();
}
$csrfSession = $_SESSION['csrf'] ?? '';
$csrfPost    = $_POST['csrf'] ?? '';
if (!$csrfSession || !$csrfPost || !hash_equals($csrfSession, $csrfPost)) {
  $_SESSION['flash'] = ['tipo'=>'danger','txt'=>'Solicitud inválida (CSRF).'];
  redirect_listado();
}

/* ---------- ID ---------- */
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash'] = ['tipo'=>'danger','txt'=>'ID inválido.'];
  redirect_listado();
}

/* ---------- Reglas de seguridad ---------- */
if ($id === (int)($_SESSION['id_usuario'] ?? 0)) {
  $_SESSION['flash'] = ['tipo'=>'danger','txt'=>'No puedes eliminar tu propio usuario.'];
  redirect_listado();
}
if ($id === 1) {
  $_SESSION['flash'] = ['tipo'=>'danger','txt'=>'El usuario principal (ID 1) no se puede eliminar.'];
  redirect_listado();
}

/* ---------- Eliminar en BD ---------- */
try {
  $cx = new Conexion();
  $cx->abrir();

  // Existe y datos básicos
  $cx->ejecutar("SELECT username, id_rol FROM usuario WHERE id_usuario = ?", [$id]);
  $u = $cx->registro();
  if (!$u) {
    $cx->cerrar();
    $_SESSION['flash'] = ['tipo'=>'danger','txt'=>'Usuario no encontrado.'];
    redirect_listado();
  }

  // Si es admin, no dejar el sistema sin admins
  if ((int)$u['id_rol'] === 1) {
    $cx->ejecutar("SELECT COUNT(*) FROM usuario WHERE id_rol = 1");
    $admins = (int)($cx->registro()[0] ?? 0);
    if ($admins <= 1) {
      $cx->cerrar();
      $_SESSION['flash'] = ['tipo'=>'danger','txt'=>'No puedes eliminar el último admin.'];
      redirect_listado();
    }
  }

  // Eliminar
  $cx->ejecutar("DELETE FROM usuario WHERE id_usuario = ?", [$id]);
  $cx->cerrar();

  $_SESSION['flash'] = ['tipo'=>'success','txt'=>"Usuario @{$u['username']} eliminado correctamente."];

} catch (Throwable $e) {
  $_SESSION['flash'] = ['tipo'=>'danger','txt'=>'Error: '.$e->getMessage()];
}

/* ---------- Volver al listado ---------- */
redirect_listado();
