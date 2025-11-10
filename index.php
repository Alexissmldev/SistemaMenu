<?php
ob_start(); 
require "inc/session_start.php";



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
        #cerrar sesion 
        if ((!isset($_SESSION['id']) || $_SESSION['id'] == "")) {
            session_destroy();

            if (headers_sent()) {
                echo "<script> window.location.href='index.php?vista=login';</script>";
            } else {
                header("Location: index.php?vista=login");
            }
        }

        if (!isset($_GET['ajax'])) {
            include "inc/navbar.php";
        }
        
        // La vista principal siempre se incluye
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