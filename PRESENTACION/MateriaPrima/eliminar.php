<?php
// PRESENTACION/MateriaPrima/eliminar.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../persistencia/Conexion.php';
require_once __DIR__ . '/../../persistencia/MateriaPrimaDAO.php';

/* ---- Autorización: solo admin ---- */
$rol = $_SESSION['rol'] ?? null;
if ($rol !== 'admin') {
  include __DIR__ . '/../Noautorizado.php';
  exit;
}

/* ---- Helpers ---- */
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirectList($msg = '', $ok = true){
  if ($msg !== '') {
    $_SESSION['flash_mp'] = ['ok'=>$ok, 'msg'=>$msg];
  }
  $URL_LISTAR = '?pid=' . base64_encode('PRESENTACION/MateriaPrima/listar.php');
  header("Location: $URL_LISTAR");
  exit;
}

/* ---- Entrada ---- */
$codigo = trim($_GET['codigo'] ?? ($_GET['id'] ?? '')); // admite ?codigo= o ?id=
if ($codigo === '') {
  redirectList('Código de materia prima no recibido.', false);
}

/* ---- Eliminar (baja lógica: activo=0) ---- */
$cx = new Conexion();
$cx->abrir();

try {
  // Si viene ?hard=1 intentará borrar físico; si falla por FK, cae a desactivar.
  $hard = isset($_GET['hard']) && (int)$_GET['hard'] === 1;

  if ($hard) {
    [$sql, $params] = MateriaPrimaDAO::eliminarFisico($codigo);
    try {
      $cx->ejecutar($sql, $params);
      $cx->cerrar();
      redirectList("Materia prima '$codigo' eliminada definitivamente.");
    } catch (Throwable $e) {
      // Fallback a baja lógica cuando hay restricciones
    }
  }

  // Baja lógica
  [$sql, $params] = MateriaPrimaDAO::desactivar($codigo);
  $cx->ejecutar($sql, $params);
  $cx->cerrar();

  redirectList("Materia prima '$codigo' desactivada correctamente.");
} catch (Throwable $e) {
  $cx->cerrar();
  redirectList('No se pudo eliminar la materia prima: '.$e->getMessage(), false);
}
