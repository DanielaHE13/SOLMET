<?php
// PRESENTACION/Inserto/eliminar.php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=UTF-8');

if (($_SESSION['rol'] ?? '') !== 'admin') {
  http_response_code(403);
  echo json_encode(['ok'=>false,'msg'=>'No autorizado']);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'msg'=>'Método no permitido']);
  exit;
}

require_once __DIR__ . '/../../persistencia/Conexion.php';

try {
  // CSRF
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    throw new Exception('CSRF inválido');
  }

  // ID
  $id = substr(trim($_POST['id_inserto'] ?? ''), 0, 25);
  if ($id === '') throw new Exception('ID de inserto faltante');

  // DELETE
  $cx = new Conexion();
  $cx->abrir();
  $cx->ejecutar("DELETE FROM inserto WHERE id_inserto = ?", [$id]);
  $cx->cerrar();

  echo json_encode(['ok'=>true,'msg'=>"Inserto $id eliminado correctamente",'id'=>$id]);
  exit;

} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
  exit;
}
