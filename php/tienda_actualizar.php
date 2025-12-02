<?php
/* ==========================================================================
   1. CONECTAR CON LA SESIÓN DEL SISTEMA
   Usamos "../" para salir de la carpeta 'php' y buscar 'inc/session_start.php'
   Esto asegura que leamos la misma sesión del login.
   ========================================================================== */
require_once "../inc/session_start.php";
require_once "main.php";

/*-- Verificar si la sesión recuperó el ID --*/
if (empty($_SESSION['id'])) {
    enviar_respuesta_json("error", "Sesión Caducada", "No se detecta tu usuario. Por favor recarga la página e inicia sesión.");
}

/*-- Recibir ID de la tienda --*/
$id = limpiar_cadena($_POST['id_tienda']);

/*-- Verificando tienda en BD --*/
$conexion = conexion(); // Creamos la conexión aquí
$check_tienda = $conexion->query("SELECT * FROM tiendas WHERE id_tienda='$id'");

if ($check_tienda->rowCount() <= 0) {
    enviar_respuesta_json(
        "error",
        "¡Ocurrió un error inesperado!",
        "La tienda no existe en el sistema."
    );
} else {
    $datos_tienda = $check_tienda->fetch();
}
$check_tienda = null;

/*-- Recibir Datos del Formulario --*/
$nombre = limpiar_cadena($_POST['nombre_tienda']);
$rif = limpiar_cadena($_POST['rif_tienda']);
$telefono = limpiar_cadena($_POST['telefono_tienda']);
$direccion = limpiar_cadena($_POST['direccion_tienda']);
$color = limpiar_cadena($_POST['color_principal']);

// Datos Pago Móvil
$pm_banco = limpiar_cadena($_POST['pm_banco']);
$pm_tel = limpiar_cadena($_POST['pm_tel']);
$pm_ced = limpiar_cadena($_POST['pm_ced']);

/*-- Validaciones Básicas --*/
if ($nombre == "") {
    enviar_respuesta_json(
        "error",
        "¡Ocurrió un error!",
        "El nombre de la tienda es obligatorio."
    );
}

/*-- Preparar Datos para Actualizar --*/
$datos_update = [
    ":nombre" => $nombre,
    ":rif" => $rif,
    ":telefono" => $telefono,
    ":direccion" => $direccion,
    ":color" => $color,
    ":pm_banco" => $pm_banco,
    ":pm_tel" => $pm_tel,
    ":pm_ced" => $pm_ced,
    ":id" => $id
];

/*-- Procesar Logo (Imagen) --*/
$sql_logo_part = "";

if (isset($_FILES['logo_tienda']) && $_FILES['logo_tienda']['name'] != "" && $_FILES['logo_tienda']['size'] > 0) {

    $img_dir = '../img/logo/';

    // Crear directorio si no existe
    if (!file_exists($img_dir)) {
        if (!mkdir($img_dir, 0777)) {
            enviar_respuesta_json(
                "error",
                "¡Ocurrió un error!",
                "Error al crear el directorio de logos."
            );
        }
    }

    // Validar formato
    if (mime_content_type($_FILES['logo_tienda']['tmp_name']) != "image/jpeg" && mime_content_type($_FILES['logo_tienda']['tmp_name']) != "image/png") {
        enviar_respuesta_json(
            "error",
            "¡Ocurrió un error!",
            "Formato de imagen no válido (Use JPG o PNG)."
        );
    }

    // Validar peso (3MB máx)
    if (($_FILES['logo_tienda']['size'] / 1024) > 3072) {
        enviar_respuesta_json(
            "error",
            "¡Ocurrió un error!",
            "La imagen es muy pesada (Máx 3MB)."
        );
    }

    // Nombre único para la imagen
    // Detectamos extensión
    $mime = mime_content_type($_FILES['logo_tienda']['tmp_name']);
    $extension = ($mime == 'image/png') ? ".png" : ".jpg";

    $foto_nombre = "logo_" . $id . "_" . rand(0, 100) . $extension;

    // Subir imagen
    if (!move_uploaded_file($_FILES['logo_tienda']['tmp_name'], $img_dir . $foto_nombre)) {
        enviar_respuesta_json(
            "error",
            "¡Ocurrió un error!",
            "No se pudo cargar la imagen al servidor."
        );
    }

    // Borrar logo anterior si existe y no es el default
    if (is_file($img_dir . $datos_tienda['logo_tienda']) && $datos_tienda['logo_tienda'] != "logo_default.png") {
        unlink($img_dir . $datos_tienda['logo_tienda']);
    }

    $sql_logo_part = ", logo_tienda = :logo";
    $datos_update[':logo'] = $foto_nombre;
}

/*-- Ejecutar Update en BD --*/
// Nota: $conexion ya fue creada arriba para verificar la tienda
$update = $conexion->prepare("UPDATE tiendas SET 
    nombre_tienda = :nombre,
    rif_tienda = :rif,
    telefono_tienda = :telefono,
    direccion_tienda = :direccion,
    color_principal = :color,
    pm_banco = :pm_banco,
    pm_telefono = :pm_tel,
    pm_cedula = :pm_ced
    $sql_logo_part
    WHERE id_tienda = :id");

if ($update->execute($datos_update)) {
    enviar_respuesta_json(
        "success",
        "¡TIENDA ACTUALIZADA!",
        "Los datos se han guardado correctamente."
    );
} else {
    enviar_respuesta_json(
        "error",
        "¡Ocurrió un error!",
        "No se pudo actualizar la base de datos."
    );
}
$conexion = null;
