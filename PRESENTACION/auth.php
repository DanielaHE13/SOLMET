<?php
// PRESENTACION/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

function is_admin(): bool    { return ($_SESSION['rol'] ?? '') === 'admin'; }
function is_operador(): bool { return ($_SESSION['rol'] ?? '') === 'operador'; }

function require_admin_page(): void {
  if (!is_admin()) {
    http_response_code(403);
    include __DIR__ . '/Noautorizado.php';
    exit;
  }
}

function require_admin_api(): void {
  if (!is_admin()) {
    http_response_code(403);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['ok'=>false,'msg'=>'No autorizado (admin requerido)']);
    exit;
  }
}
