<?php
require_once "main.php";

$modulo_buscador = limpiar_cadena($_POST['modulo_buscador']);
// 1. AÑADIMOS "anuncio" A LA LISTA DE MÓDULOS PERMITIDOS
$modulos = ["usuario", "categoria", "producto", "anuncio"];

if (in_array($modulo_buscador, $modulos)) {

    // 2. AÑADIMOS LA RUTA PARA EL MÓDULO "anuncio"
    $modulos_url = [
        "usuario"   => "user_list",
        "categoria" => "category_list",
        "producto"  => "product_list",
        "anuncio"   => "ad_list" // <-- LÍNEA NUEVA
    ];
    $redirect_url = $modulos_url[$modulo_buscador];

    // Start search
    if (isset($_POST['txt_buscador'])) {
        $txt = limpiar_cadena($_POST['txt_buscador']);

        if ($txt == "") {
            // If the search is empty, just redirect to the clean list
            header("Location: ../index.php?vista=$redirect_url");
            exit();
        }

        // You can still validate the search term if you wish
        if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{1,30}", $txt)) {
            // If validation fails, redirect with an error message (optional)
            header("Location: ../index.php?vista=$redirect_url&error=1");
            exit();
        }

        // Redirect back to the list view with the search term in the URL
        header("Location: ../index.php?vista=$redirect_url&busqueda=" . urlencode($txt));
        exit();
    }
} else {
    // If the module is invalid, redirect to a safe page like home
    header("Location: ../index.php?vista=home");
    exit();
}
