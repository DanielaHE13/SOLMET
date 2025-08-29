<?php
/**
 * =========================================================================
 *  PRESENTACION/OrdenProduccion/crear_ajax.php
 *  Endpoints AJAX usados por el formulario de creación de OP.
 *
 *  Acciones soportadas:
 *    - action=mp_refs&tipo=original|peletizado|molido
 *      Devuelve referencias de materia prima para el tipo solicitado.
 *
 *  Respuesta estándar:
 *    { ok: bool, data?: any, msg?: string }
 * =========================================================================
 */
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

// ⚠️ Requiere sesión iniciada (uid seteado en login)
if (!isset($_SESSION['uid'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
  exit;
}

require_once __DIR__ . '/../../persistencia/Conexion.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
  /* =======================================================
   *  1) Listar referencias de Materia Prima por tipo
   *     GET: ?action=mp_refs&tipo=original|peletizado|molido
   * ======================================================= */
  if ($action === 'mp_refs') {
    // Validación de tipo permitido
    $tipo = strtolower(trim($_GET['tipo'] ?? ''));
    $permitidos = ['original', 'peletizado', 'molido'];
    if (!in_array($tipo, $permitidos, true)) {
      echo json_encode(['ok' => false, 'msg' => 'Tipo inválido']); exit;
    }

    $cx = new Conexion();
    $cx->abrir();

    // Ajusta los nombres de columnas/tabla a tu esquema si difieren.
    // - estado: almacena el tipo (original/peletizado/molido)
    // - activo: bandera 1/0
    $sql = "SELECT codigo, referencia
              FROM materia_prima
             WHERE estado = ?
               AND activo = 1
          ORDER BY referencia ASC";
    $cx->ejecutar($sql, [$tipo]);

    $data = [];
    while ($r = $cx->registro()) {
      $codigo     = $r['codigo']     ?? $r[0] ?? '';
      $referencia = $r['referencia'] ?? $r[1] ?? '';
      $data[] = ['codigo' => $codigo, 'referencia' => $referencia];
    }

    $cx->cerrar();
    echo json_encode(['ok' => true, 'data' => $data], JSON_UNESCAPED_UNICODE); exit;
  }

  /* ===========================
   *  Acción no soportada
   * =========================== */
  echo json_encode(['ok' => false, 'msg' => 'Acción no soportada']); exit;

} catch (Throwable $e) {
  // Manejo de errores inesperados
  error_log('crear_ajax error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Error: ' . $e->getMessage()]);
  exit;
}
