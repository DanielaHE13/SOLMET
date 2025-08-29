<?php
// PRESENTACION/OrdenProduccion/api/cambiar_estado.php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');

try {
  /* ==== Seguridad ==== */
  $rol  = $_SESSION['rol']  ?? null;
  $uid  = $_SESSION['uid']  ?? null;
  $user = $_SESSION['username'] ?? ($_SESSION['nombre'] ?? 'usuario');

  if (!in_array($rol, ['admin','operador'], true)) {
    http_response_code(403);
    echo json_encode(['ok'=>false,'msg'=>'No autorizado']); exit;
  }
  if (empty($_POST['csrf']) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], (string)$_POST['csrf'])) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'msg'=>'CSRF inválido']); exit;
  }

  /* ==== Entrada ==== */
  $id_op  = isset($_POST['id_op']) ? (int)$_POST['id_op'] : 0;
  $estado = strtolower(trim((string)($_POST['estado'] ?? '')));

  $allowed = ['creada','programada','en_proceso','finalizada','cancelada'];
  if ($id_op <= 0 || !in_array($estado, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']); exit;
  }

  /* ==== Conexión ==== */
  require_once __DIR__ . '/../../../persistencia/Conexion.php';
  $cx = new Conexion();
  $cx->abrir();

  /* ==== Traer estado actual ==== */
  $cx->ejecutar("SELECT estado FROM orden_produccion WHERE id_op = ? LIMIT 1", [$id_op]);
  $row = $cx->registro();
  if (!$row) {
    $cx->cerrar();
    http_response_code(404);
    echo json_encode(['ok'=>false,'msg'=>'OP no encontrada']); exit;
  }
  $estado_old = (string)($row['estado'] ?? $row[0] ?? '');

  if ($estado_old === $estado) {
    $cx->cerrar();
    echo json_encode([
      'ok' => true,
      'msg' => 'Sin cambios',
      'estado' => $estado,
      'badge_html' => badge($estado)
    ]); exit;
  }

  /* ==== Actualizar OP ==== */
  // Intentamos actualizar fecha_actualizacion si existe; si falla, actualizamos solo estado.
  try {
    $cx->ejecutar("UPDATE orden_produccion SET estado = ?, fecha_actualizacion = NOW() WHERE id_op = ? LIMIT 1", [$estado, $id_op]);
  } catch (Throwable $e) {
    $cx->ejecutar("UPDATE orden_produccion SET estado = ? WHERE id_op = ? LIMIT 1", [$estado, $id_op]);
  }

  /* ==== Insertar historial (TU TABLA) ==== */
  $cx->ejecutar("
    INSERT INTO orden_estado_historial
      (id_op, estado_anterior, estado_nuevo, cambiado_por, fecha_cambio)
    VALUES
      (?, ?, ?, ?, NOW())
  ", [$id_op, $estado_old, $estado, (int)$uid]);

  $cx->cerrar();

  echo json_encode([
    'ok'         => true,
    'estado'     => $estado,
    'badge_html' => badge($estado)
  ]);
  exit;

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>'Error interno: '.$e->getMessage()]);
  exit;
}

/* ==== badge coherente con ver.php ==== */
function badge(string $e): string {
  $map = [
    'creada'      => 'secondary',
    'programada'  => 'info',
    'en_proceso'  => 'warning',
    'finalizada'  => 'success',
    'cancelada'   => 'danger',
  ];
  $cls = $map[$e] ?? 'secondary';
  $txt = htmlspecialchars(str_replace('_',' ',$e), ENT_QUOTES, 'UTF-8');
  return '<span class="badge rounded-pill bg-'.$cls.'">'.$txt.'</span>';
}
