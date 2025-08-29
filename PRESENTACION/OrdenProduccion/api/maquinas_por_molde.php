<?php
// PRESENTACION/OrdenProduccion/api/maquinas_por_molde.php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

if (!isset($_SESSION['uid'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'No autorizado']);
  exit;
}

require_once __DIR__ . '/../../../logica/Maquina.php';

try {
  $idMolde = trim($_GET['id_molde'] ?? '');
  if ($idMolde === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'id_molde requerido']);
    exit;
  }

  $data = Maquina::listarCompatibles($idMolde); // retorna [['id'=>..,'nombre'=>..], ...]
  echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  error_log('maquinas_por_molde error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Error interno']);
}
