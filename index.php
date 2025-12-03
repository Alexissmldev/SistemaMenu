<?php
ob_start();
require "inc/session_start.php";

// Si es una descarga de PDF, cargamos directo
if (isset($_GET['vista']) && $_GET['vista'] === 'reportes' && isset($_GET['descargar']) && $_GET['descargar'] === 'pdf') {
    if (is_file("./vistas/reportes.php")) {
        require_once "vistas/reportes.php";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "inc/head.php" ?>
</head>

<body>
    <?php

    if (!isset($_GET['vista']) || $_GET['vista'] == "") {
        $_GET['vista'] = "login";
    }

    if (is_file("./vistas/" . $_GET['vista'] . ".php") && $_GET['vista'] != "login" && $_GET['vista'] != "404") {

        # 1. Verificar si hay sesión activa
        if ((!isset($_SESSION['id']) || $_SESSION['id'] == "")) {
            session_destroy();
            if (headers_sent()) {
                echo "<script> window.location.href='login';</script>";
            } else {
                header("Location: login");
            }
            exit();
        }

        # 2. CONTROL DE SEGURIDAD RBAC (Protección de URL)
        require_once "./php/main.php"; // Necesario para usar tiene_permiso()
        $v = $_GET['vista'];

        // A. Protección del HOME/DASHBOARD:
        // Si intenta entrar a 'home' pero es Cajero (no tiene permiso de estadísticas), lo mandamos al Kanban
        if ($v == "home" && !tiene_permiso('estadisticas.operativas')) {
            echo "<script> window.location.href='orders_kanban';</script>";
            exit();
        }

        // B. Protección de ADMIN (Usuarios)
        // Si la vista empieza con 'user_' (ej: user_list, user_new) y no es Admin
        if (strpos($v, 'user_') === 0 && !tiene_permiso('usuarios.gestionar')) {
            include "vistas/404.php";
            exit();
        }

        // C. Protección de INVENTARIO (Productos y Categorías)
        if ((strpos($v, 'product_') === 0 || strpos($v, 'category_') === 0) && !tiene_permiso('inventario.ver')) {
            include "vistas/404.php";
            exit();
        }

        // D. Protección de MARKETING (Promos y Anuncios)
        if ((strpos($v, 'promo_') === 0 || strpos($v, 'ad_') === 0) && !tiene_permiso('campanas.gestionar')) {
            include "vistas/404.php";
            exit();
        }

        // E. Protección de CONFIGURACIÓN DE TIENDA
        // Si intenta guardar datos de tienda sin permiso (aunque la vista configuration.php ya tiene protección interna, esto es doble seguridad)
        if ($v == 'configuration' && isset($_GET['tab']) && $_GET['tab'] == 'tienda' && !tiene_permiso('config.negocio')) {
            echo "<script> window.location.href='configuration&tab=personal';</script>";
            exit();
        }

        // --- FIN DEL CONTROL DE SEGURIDAD ---


        # 3. Carga de la interfaz
        if (!isset($_GET['ajax'])) {
            include "inc/navbar.php";
        }

        include "vistas/" . $_GET['vista'] . ".php";

        if (!isset($_GET['ajax'])) {
            include "inc/script.php";
        }
    } else {

        if ($_GET['vista'] == "login") {
            include "vistas/login.php";
        } else {
            include "vistas/404.php";
        }
    }
    ?>
</body>

</html>
<?php ob_end_flush(); ?>