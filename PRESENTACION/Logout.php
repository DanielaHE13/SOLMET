<?php
/**
 * PRESENTACION/Logout.php
 * Cierra sesión de forma segura y redirige a index.php
 */

//// Arrancar sesión si no existe
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

//// Limpiar sesión y cookie
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $p['path']     ?? '/',
        'domain'   => $p['domain']   ?? '',
        'secure'   => $p['secure']   ?? false,
        'httponly' => $p['httponly'] ?? true,
        'samesite' => 'Lax',
    ]);
}
session_destroy();

//// Destino permitido (whitelist opcional)
$destDefecto = 'PRESENTACION/Inicio.php';
$whitelist   = ['PRESENTACION/Inicio.php','PRESENTACION/Autenticar.php'];
$dest        = $destDefecto;

if (isset($_GET['next'])) {
    $next = base64_decode($_GET['next'], true) ?: '';
    if (in_array($next, $whitelist, true)) {
        $dest = $next;
    }
}

//// Construir URL absoluta hacia index.php
$scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/PRESENTACION/Logout.php')), '/');
$basePath = $basePath === '' ? '' : $basePath; // si está en raíz

$url = $scheme . '://' . $host . $basePath . '/index.php?pid=' . base64_encode($dest);
$url = filter_var($url, FILTER_SANITIZE_URL);

//// Redirigir (sin imprimir nada antes)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Location: ' . $url);
exit;
