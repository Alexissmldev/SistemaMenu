<?php
// --- 1. ACTIVAR REPORTES DE ERROR (Solo para depuración) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

// --- 2. CARGA SEGURA DE SESIÓN ---
// Verificamos que el archivo exista para evitar Error 500
if (file_exists("inc/session_start.php")) {
    require "inc/session_start.php";
} else {
    die("Error Fatal: No se encuentra 'inc/session_start.php'. Revisa la carpeta 'inc'.");
}

// --- 3. LIMPIEZA DE LA VISTA (IMPORTANTE PARA HTACCESS) ---
if (isset($_GET['vista'])) {
    // Quita las barras al final: "home/" se convierte en "home"
    $_GET['vista'] = rtrim($_GET['vista'], '/');
}

// --- 4. LÓGICA DE PDF ---
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
    <?php 
    if(file_exists("inc/head.php")){
        include "inc/head.php";
    } else {
        echo "";
    }
    ?>
</head>

<body>
    <?php

    if (!isset($_GET['vista']) || $_GET['vista'] == "") {
        $_GET['vista'] = "login";
    }

    if (is_file("./vistas/" . $_GET['vista'] . ".php") && $_GET['vista'] != "login" && $_GET['vista'] != "404") {

        # 1. Verificar si hay sesión activa
        if ((!isset($_SESSION['id']) || $_SESSION['id'] == "")) {
            // No uses session_destroy si ya no hay sesión, a veces causa bugs.
            // session_destroy(); 
            
            if (headers_sent()) {
                echo "<script> window.location.href='login';</script>";
            } else {
                header("Location: login");
            }
            exit();
        }

        # 2. CONTROL DE SEGURIDAD RBAC
        // Cargamos main.php para la conexión y funciones
        if(file_exists("./php/main.php")){
            require_once "./php/main.php"; 
        } else {
            die("Error: No se encuentra php/main.php");
        }

        // Verificamos que la función de permisos exista para no romper el sitio
        if(!function_exists('tiene_permiso')){
            die("Error: La función 'tiene_permiso()' no existe en php/main.php");
        }

        $v = $_GET['vista'];

        // A. Protección del HOME/DASHBOARD
        if ($v == "home" && !tiene_permiso('estadisticas.operativas')) {
            echo "<script> window.location.href='orders_kanban';</script>";
            exit();
        }

        // B. Protección de ADMIN (Usuarios)
        if (strpos($v, 'user_') === 0 && !tiene_permiso('usuarios.gestionar')) {
            include "vistas/404.php";
            exit();
        }

        // C. Protección de INVENTARIO
        if ((strpos($v, 'product_') === 0 || strpos($v, 'category_') === 0) && !tiene_permiso('inventario.ver')) {
            include "vistas/404.php";
            exit();
        }

        // D. Protección de MARKETING
        if ((strpos($v, 'promo_') === 0 || strpos($v, 'ad_') === 0) && !tiene_permiso('campanas.gestionar')) {
            include "vistas/404.php";
            exit();
        }

        // E. Protección de CONFIGURACIÓN
        if ($v == 'configuration' && isset($_GET['tab']) && $_GET['tab'] == 'tienda' && !tiene_permiso('config.negocio')) {
            echo "<script> window.location.href='configuration&tab=personal';</script>";
            exit();
        }
        
        // --- FIN CONTROL DE SEGURIDAD ---

        # 3. Carga de la interfaz
        if (!isset($_GET['ajax'])) {
            if(file_exists("inc/navbar.php")) include "inc/navbar.php";
        }

        include "vistas/" . $_GET['vista'] . ".php";

        if (!isset($_GET['ajax'])) {
            if(file_exists("inc/script.php")) include "inc/script.php";
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