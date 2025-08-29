<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start(); // 👈 activa buffer para permitir header() sin warnings

/* ===== Dependencias de la capa lógica ===== */
require_once __DIR__ . "/logica/Usuario.php";
require_once __DIR__ . "/logica/Rol.php";
require_once __DIR__ . "/logica/Molde.php";
require_once __DIR__ . "/logica/Maquina.php";
require_once __DIR__ . "/logica/OrdenProduccion.php";

/* ===== Rutas ===== */
$paginas_sin_autenticacion = [
    "PRESENTACION/Inicio.php",
    "PRESENTACION/Autenticar.php",
    "PRESENTACION/Noautorizado.php",
    "PRESENTACION/Logout.php",
];

$paginas_con_autenticacion = [
    // Sesiones y usuarios
    "PRESENTACION/Admin/sesionAdmin.php",
    "PRESENTACION/Operador/sesionOperador.php",
    "PRESENTACION/Usuario/listarUsuarios.php",
    "PRESENTACION/Admin/crearUsuario.php",
    "PRESENTACION/Admin/listarUsuarios.php",
    "PRESENTACION/Logout.php",

    // Moldes
    "PRESENTACION/Molde/listar.php",
    "PRESENTACION/Molde/crear.php",
    "PRESENTACION/Molde/editar.php",
    "PRESENTACION/Molde/eliminar.php",

    // Máquinas
    "PRESENTACION/Maquina/listar.php",
    "PRESENTACION/Maquina/crear.php",
    "PRESENTACION/Maquina/editar.php",
    "PRESENTACION/Maquina/eliminar.php",

    // Insertos
    "PRESENTACION/Inserto/listar.php",
    "PRESENTACION/Inserto/crear.php",
    "PRESENTACION/Inserto/editar.php",
    "PRESENTACION/Inserto/eliminar.php",

    // Materia prima
    "PRESENTACION/MateriaPrima/listar.php",
    "PRESENTACION/MateriaPrima/crear.php",
    "PRESENTACION/MateriaPrima/editar.php",
    "PRESENTACION/MateriaPrima/eliminar.php",

    // Productos
    "PRESENTACION/Producto/listar.php",
    "PRESENTACION/Producto/crear.php",
    "PRESENTACION/Producto/editar.php",
    "PRESENTACION/Producto/eliminar.php",

    // Orden de producción
    "PRESENTACION/OrdenProduccion/crear.php",
    "PRESENTACION/OrdenProduccion/crear_ajax.php",
    "PRESENTACION/OrdenProduccion/guardar.php",
    "PRESENTACION/OrdenProduccion/ver.php",
    "PRESENTACION/OrdenProduccion/api/maquinas_por_molde.php",
    "PRESENTACION/OrdenProduccion/api/productos_por_molde.php",
];

/* ===== Selección de página ===== */
$pid    = $_GET["pid"] ?? null;
$pagina = "PRESENTACION/Inicio.php"; // default

if ($pid) {
    $decoded = base64_decode($pid);

    if (in_array($decoded, $paginas_sin_autenticacion)) {
        $pagina = $decoded;
    } elseif (in_array($decoded, $paginas_con_autenticacion)) {
        if (!isset($_SESSION["uid"])) {   // 👈 usamos la variable de sesión del login
            $pagina = "PRESENTACION/Autenticar.php";
        } else {
            $pagina = $decoded;
        }
    } else {
        $pagina = null; // error 404
    }
}

/* ===== Caso especial: Logout ===== */
if ($pagina === "PRESENTACION/Logout.php") {
    include __DIR__ . "/" . $pagina;
    ob_end_flush(); // 👈 vacía buffer y envía todo
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Solmet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://use.fontawesome.com/releases/v6.7.2/css/all.css" rel="stylesheet">
</head>

<body class="bg-light">

    <?php
    if ($pagina && file_exists(__DIR__ . "/" . $pagina)) {
        include __DIR__ . "/" . $pagina;
    } else {
        echo "<div class='container my-5'>";
        echo "<div class='alert alert-danger shadow'>";
        echo "<h4>Error 404 - Página no encontrada</h4>";
        echo "<p>La página solicitada no existe o no está autorizada:</p>";
        echo "<code>" . htmlspecialchars($pid ?? '') . "</code>";
        echo "<br><br><a href='index.php' class='btn btn-primary'>Volver al inicio</a>";
        echo "</div></div>";
    }
    ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php ob_end_flush(); // 👈 cierre del buffer ?>
