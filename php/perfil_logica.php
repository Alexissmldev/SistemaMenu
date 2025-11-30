<?php
$id_usuario = $_SESSION['id']; // Asumimos que la sesión está iniciada
$conexion = conexion();

$msg_exito = "";
$msg_error = "";

/* ==============================================
   1. CARGAR DATOS DEL USUARIO (GET)
   ============================================== */
$datos_usuario = $conexion->query("SELECT * FROM usuario WHERE usuario_id = '$id_usuario'");
if ($datos_usuario->rowCount() > 0) {
    $usuario_actual = $datos_usuario->fetch();
} else {
    // Si no encuentra el usuario, cerrar sesión o redirigir
    include "./php/logout.php";
    exit();
}

/* ==============================================
   2. PROCESAR FORMULARIO (POST)
   ============================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {

    $usuario = limpiar_cadena($_POST['username']);
    $telefono = limpiar_cadena($_POST['phone']);

    // Validar campos obligatorios
    if ($usuario == "") {
        $msg_error = "El nombre de usuario no puede estar vacío.";
    } else {

        // Verificar que el usuario no esté repetido (si lo cambió)
        if ($usuario != $usuario_actual['usuario_usuario']) {
            $check_user = $conexion->query("SELECT usuario_usuario FROM usuario WHERE usuario_usuario = '$usuario'");
            if ($check_user->rowCount() > 0) {
                $msg_error = "Ese nombre de usuario ya está registrado.";
            }
        }

        if (empty($msg_error)) {

            // Lógica de Contraseña
            $clave = $usuario_actual['usuario_clave']; // Mantener la vieja por defecto
            $cambiar_clave = false;

            // Si escribió algo en "Nueva Clave"
            if ($_POST['new_password'] != "" || $_POST['confirm_password'] != "") {

                $clave_actual = limpiar_cadena($_POST['current_password']);
                $clave_nueva_1 = limpiar_cadena($_POST['new_password']);
                $clave_nueva_2 = limpiar_cadena($_POST['confirm_password']);

                // 1. Verificar clave actual
                if ($clave_actual == "" || !password_verify($clave_actual, $usuario_actual['usuario_clave'])) {
                    $msg_error = "La contraseña actual es incorrecta. Necesaria para hacer cambios.";
                }
                // 2. Verificar coincidencia nuevas
                elseif ($clave_nueva_1 != $clave_nueva_2) {
                    $msg_error = "Las nuevas contraseñas no coinciden.";
                }
                // 3. Verificar longitud
                elseif (strlen($clave_nueva_1) < 4) { // Ajusta la longitud mínima si quieres
                    $msg_error = "La contraseña debe tener al menos 4 caracteres.";
                } else {
                    // Todo OK, encriptar nueva clave
                    $clave = password_hash($clave_nueva_1, PASSWORD_BCRYPT, ["cost" => 10]);
                    $cambiar_clave = true;
                }
            }

            // Si no hay errores, procedemos al UPDATE
            if (empty($msg_error)) {

                $datos_user_update = [
                    ":usuario" => $usuario,
                    ":telefono" => $telefono,
                    ":clave" => $clave,
                    ":id" => $id_usuario
                ];

                $update_user = $conexion->prepare("UPDATE usuario SET usuario_usuario=:usuario, usuario_telefono=:telefono, usuario_clave=:clave WHERE usuario_id=:id");

                if ($update_user->execute($datos_user_update)) {
                    $msg_exito = "¡Perfil actualizado correctamente!";
                    // Actualizar variable en memoria para que se vea el cambio sin recargar
                    $usuario_actual['usuario_usuario'] = $usuario;
                    $usuario_actual['usuario_telefono'] = $telefono;

                    // Actualizar sesión si cambió el usuario
                    $_SESSION['usuario'] = $usuario;
                } else {
                    $msg_error = "Error al actualizar en la base de datos.";
                }
            }
        }
    }
}
$conexion = null;
