<?php
// PRESENTACION/OrdenProduccion/api/productos_por_molde.php
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

if (!isset($_SESSION['uid'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'No autorizado']);
  exit;
}

require_once __DIR__ . '/../../../persistencia/Conexion.php';

/**
 * Devuelve productos para un molde dado si existe una tabla puente,
 * de lo contrario devuelve todos los productos activos.
 * Respuesta: { ok:true, data:[{id, nombre, peso_g, cpm}] }
 */
try {
  $idMolde = trim($_GET['id_molde'] ?? '');

  $cx = new Conexion();
  $cx->abrir();

  // Detectar tabla puente disponible, sin romper si no existe
  $tablaPuente = null;
  foreach (['producto_molde', 'molde_producto'] as $t) {
    try {
      $cx->ejecutar("SELECT 1 FROM {$t} LIMIT 1");
      $cx->registro(); // si no lanza, existe
      $tablaPuente = $t;
      break;
    } catch (Throwable $e) { /* sigue intentando */ }
  }

  if ($tablaPuente && $idMolde !== '') {
    // Filtra por molde usando la tabla puente detectada
    $sql = "SELECT p.id_producto, p.nombre, p.peso_teorico_g, p.ciclos_por_min
              FROM producto p
              INNER JOIN {$tablaPuente} pm ON pm.id_producto = p.id_producto
             WHERE pm.id_molde = ?
               AND p.activo = 1
          ORDER BY p.nombre ASC";
    $cx->ejecutar($sql, [$idMolde]);
  } else {
    // Fallback: todos activos
    $sql = "SELECT id_producto, nombre, peso_teorico_g, ciclos_por_min
              FROM producto
             WHERE activo = 1
          ORDER BY nombre ASC";
    $cx->ejecutar($sql, []);
  }

  $data = [];
  while ($r = $cx->registro()) {
    // Soporta driver que devuelva por índice o nombre
    $data[] = [
      'id'     => $r['id_producto']   ?? $r[0],
      'nombre' => $r['nombre']        ?? $r[1],
      'peso_g' => (float)($r['peso_teorico_g'] ?? $r[2]),
      'cpm'    => (float)($r['ciclos_por_min'] ?? $r[3]),
    ];
  }

  $cx->cerrar();
  echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  error_log('productos_por_molde error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['ok' => false, 'error' => 'Error interno']);
}
