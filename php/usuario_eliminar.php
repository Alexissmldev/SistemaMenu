<?php
// Especificamos que la respuesta será JSON
header('Content-Type: application/json');

// Iniciar sesión para saber quién es el admin que borra
require_once "../inc/session_Start.php";
require_once "main.php";

$conexion = conexion();

// 1. Recibir datos del Javascript (FormData)
$user_id_del = limpiar_cadena($_POST['usuario_id']);
$password_admin = limpiar_cadena($_POST['administrador_clave']);

// 2. Verificar datos obligatorios
if ($user_id_del == "" || $password_admin == "") {
    enviar_respuesta_json("error", "Error", "Faltan datos para procesar la solicitud.");
}

// 3. Verificar que no se esté eliminando a sí mismo
if ($user_id_del == $_SESSION['id']) {
    enviar_respuesta_json("error", "Acción denegada", "No puedes eliminar tu propia cuenta.");
}

// --- PASO DE SEGURIDAD: VERIFICAR CONTRASEÑA DEL ADMIN ---
$check_admin = $conexion->prepare("SELECT usuario_clave FROM usuario WHERE usuario_id=:id");
$check_admin->execute([':id' => $_SESSION['id']]);
$datos_admin = $check_admin->fetch();

if (!password_verify($password_admin, $datos_admin['usuario_clave'])) {
    enviar_respuesta_json("error", "Contraseña Incorrecta", "La contraseña de administrador no es válida.");
}

// --- VERIFICAR SI EL USUARIO A ELIMINAR EXISTE ---
$check_usuario = $conexion->prepare("SELECT usuario_id FROM usuario WHERE usuario_id=:id");
$check_usuario->execute([':id' => $user_id_del]);

if ($check_usuario->rowCount() == 1) {

    /* ================================================================
       OPCIÓN 1: REASIGNACIÓN DE PRODUCTOS
       Antes de borrar al usuario, pasamos sus productos a tu nombre.
       ================================================================
    */

    // Actualizamos la tabla producto: El nuevo dueño será $_SESSION['id'] (Tú)
    $reasignar_productos = $conexion->prepare("UPDATE producto SET usuario_id = :nuevo_dueno WHERE usuario_id = :usuario_a_eliminar");

    $reasignar_productos->execute([
        ':nuevo_dueno' => $_SESSION['id'],
        ':usuario_a_eliminar' => $user_id_del
    ]);

    // (Opcional) Podemos saber cuántos productos se movieron con rowCount(), 
    // pero no es estrictamente necesario para continuar.


    /* ================================================================
       ELIMINACIÓN DEL USUARIO
       Ahora que no tiene productos, es seguro eliminarlo.
       ================================================================
    */
    $eliminar_usuario = $conexion->prepare("DELETE FROM usuario WHERE usuario_id=:id");
    $eliminar_usuario->execute([":id" => $user_id_del]);

    if ($eliminar_usuario->rowCount() == 1) {

        // Mensaje de éxito informando la reasignación
        enviar_respuesta_json(
            "success",
            "¡Usuario Eliminado!",
            "El usuario ha sido eliminado. Sus productos registrados (si tenía) han sido transferidos a tu cuenta.",
            "user_list"
        );
    } else {
        enviar_respuesta_json("error", "Error", "No se pudo eliminar el usuario, por favor intente de nuevo.");
    }
} else {
    enviar_respuesta_json("error", "Error", "El usuario que intentas eliminar no existe.");
}

$conexion = null;
