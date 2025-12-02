<?php

require_once "../inc/session_start.php";
require_once "main.php";

/*-- Verificar si la sesión recuperó el ID --*/
if (empty($_SESSION['id'])) {
    enviar_respuesta_json("error", "Sesión Caducada", "No se detecta tu usuario. Por favor recarga la página e inicia sesión.");
}

$id_usuario = $_SESSION['id'];
$conexion = conexion();

/* ==========================================================================
   2. RECIBIR DATOS
   ========================================================================== */
$usuario = limpiar_cadena($_POST['username']);
$telefono = limpiar_cadena($_POST['phone']);

/*-- Validar campos obligatorios --*/
if ($usuario == "") {
    enviar_respuesta_json("error", "¡Ocurrió un error!", "El nombre de usuario no puede estar vacío.");
}

/* ==========================================================================
   3. VERIFICACIONES DE SEGURIDAD
   ========================================================================== */

/*-- Obtener datos actuales de la BD --*/
$check_user_actual = $conexion->query("SELECT * FROM usuario WHERE usuario_id = '$id_usuario'");

if ($check_user_actual->rowCount() <= 0) {
    enviar_respuesta_json("error", "Error Crítico", "El usuario de la sesión no existe en la base de datos.");
} else {
    $datos_actuales = $check_user_actual->fetch();
}

/*-- Verificar si cambió el usuario y si ya existe otro igual --*/
if ($usuario != $datos_actuales['usuario_usuario']) {
    $check_user_duplicado = $conexion->query("SELECT usuario_usuario FROM usuario WHERE usuario_usuario = '$usuario'");
    if ($check_user_duplicado->rowCount() > 0) {
        enviar_respuesta_json("error", "Usuario no disponible", "El nombre de usuario '$usuario' ya está registrado.");
    }
}

/* ==========================================================================
   4. LÓGICA DE CONTRASEÑA
   ========================================================================== */
$clave_final = $datos_actuales['usuario_clave']; // Por defecto, la misma

// Si escribió algo en los campos de nueva clave
if ($_POST['new_password'] != "" || $_POST['confirm_password'] != "") {

    $clave_actual = limpiar_cadena($_POST['current_password']);
    $clave_nueva_1 = limpiar_cadena($_POST['new_password']);
    $clave_nueva_2 = limpiar_cadena($_POST['confirm_password']);

    // A. Validar contraseña actual
    if ($clave_actual == "") {
        enviar_respuesta_json("error", "Seguridad", "Debes escribir tu contraseña actual para hacer cambios.");
    }

    if (!password_verify($clave_actual, $datos_actuales['usuario_clave'])) {
        enviar_respuesta_json("error", "Contraseña Incorrecta", "La contraseña actual que ingresaste no es válida.");
    }

    // B. Validar nuevas contraseñas
    if ($clave_nueva_1 != $clave_nueva_2) {
        enviar_respuesta_json("error", "Error", "Las nuevas contraseñas no coinciden.");
    }

    if (strlen($clave_nueva_1) < 4) {
        enviar_respuesta_json("error", "Seguridad", "La contraseña es muy corta (mínimo 4 caracteres).");
    }

    // C. Encriptar
    $clave_final = password_hash($clave_nueva_1, PASSWORD_BCRYPT, ["cost" => 10]);
}

/* ==========================================================================
   5. ACTUALIZAR BASE DE DATOS
   ========================================================================== */
$datos_update = [
    ":usuario" => $usuario,
    ":telefono" => $telefono,
    ":clave" => $clave_final,
    ":id" => $id_usuario
];

$update = $conexion->prepare("UPDATE usuario SET 
    usuario_usuario = :usuario, 
    usuario_telefono = :telefono, 
    usuario_clave = :clave 
    WHERE usuario_id = :id");

if ($update->execute($datos_update)) {

    // Actualizar variable de sesión para reflejar cambios inmediatamente
    $_SESSION['usuario'] = $usuario;

    enviar_respuesta_json("success", "¡Perfil Actualizado!", "Tus datos se han guardado correctamente.");
} else {
    enviar_respuesta_json("error", "Error de Servidor", "No se pudo actualizar el registro.");
}

$conexion = null;
