<?php
require_once "../inc/session_Start.php";
require_once "main.php";

$id = limpiar_cadena($_POST['usuario_id']);
$conexion = conexion();

/*== Verificando usuario ==*/
$check_usuario = $conexion->prepare("SELECT * FROM usuario WHERE usuario_id=:id");
$check_usuario->execute([':id' => $id]);

if ($check_usuario->rowCount() <= 0) {
    echo json_encode([
        "tipo" => "error",
        "titulo" => "Error",
        "mensaje" => "El usuario no existe en el sistema"
    ]);
    exit();
} else {
    $datos = $check_usuario->fetch();
}

/*== Verificando credenciales del administrador ==*/
$admin_usuario = limpiar_cadena($_POST['administrador_usuario']);
$admin_clave = limpiar_cadena($_POST['administrador_clave']);

if ($admin_usuario == "" || $admin_clave == "") {
    echo json_encode([
        "tipo" => "error",
        "titulo" => "Campos incompletos",
        "mensaje" => "Debe ingresar su USUARIO y CLAVE de administrador para confirmar los cambios."
    ]);
    exit();
}

// Verificamos que el admin sea el usuario actual de la sesión
$check_admin = $conexion->prepare("SELECT usuario_usuario, usuario_clave FROM usuario WHERE usuario_usuario=:user AND usuario_id=:id");
$check_admin->execute([':user' => $admin_usuario, ':id' => $_SESSION['id']]);

if ($check_admin->rowCount() == 1) {
    $admin_data = $check_admin->fetch();
    if ($admin_data['usuario_usuario'] != $admin_usuario || !password_verify($admin_clave, $admin_data['usuario_clave'])) {
        echo json_encode([
            "tipo" => "error",
            "titulo" => "Error de Autenticación",
            "mensaje" => "USUARIO o CLAVE de administrador incorrectos"
        ]);
        exit();
    }
} else {
    echo json_encode([
        "tipo" => "error",
        "titulo" => "Error de Autenticación",
        "mensaje" => "USUARIO o CLAVE de administrador incorrectos"
    ]);
    exit();
}

/*== Procesando datos del formulario ==*/
$nombre = limpiar_cadena($_POST['usuario_nombre']);
$apellido = limpiar_cadena($_POST['usuario_apellido']);
$usuario = limpiar_cadena($_POST['usuario_usuario']);
$clave_1 = limpiar_cadena($_POST['usuario_clave_1']);
$clave_2 = limpiar_cadena($_POST['usuario_clave_2']);

/*== Verificando campos obligatorios ==*/
if ($nombre == "" || $apellido == "" || $usuario == "") {
    echo json_encode([
        "tipo" => "error",
        "titulo" => "Campos incompletos",
        "mensaje" => "No has llenado todos los campos obligatorios (Nombre, Apellido, Usuario)."
    ]);
    exit();
}

/*== Verificando integridad de los datos (Patrones) ==*/
if (verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $nombre)) {
    echo json_encode(["tipo" => "error", "titulo" => "Formato inválido", "mensaje" => "El NOMBRE no coincide con el formato solicitado (Solo letras)."]);
    exit();
}

if (verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}", $apellido)) {
    echo json_encode(["tipo" => "error", "titulo" => "Formato inválido", "mensaje" => "El APELLIDO no coincide con el formato solicitado (Solo letras)."]);
    exit();
}

if (verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    echo json_encode(["tipo" => "error", "titulo" => "Formato inválido", "mensaje" => "El USUARIO no coincide con el formato solicitado (Alfanumérico, 4-20 carácteres)."]);
    exit();
}

/*== Verificando Usuario Duplicado ==*/
if ($usuario != $datos['usuario_usuario']) {
    $check_user_duplicado = $conexion->prepare("SELECT usuario_usuario FROM usuario WHERE usuario_usuario=:usuario");
    $check_user_duplicado->execute([':usuario' => $usuario]);
    if ($check_user_duplicado->rowCount() > 0) {
        echo json_encode([
            "tipo" => "error",
            "titulo" => "Usuario ocupado",
            "mensaje" => "El nombre de USUARIO ingresado ya se encuentra registrado por otra persona."
        ]);
        exit();
    }
}

/*== Procesando Contraseña ==*/
if ($clave_1 != "" || $clave_2 != "") {
    if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave_1) || verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave_2)) {
        echo json_encode(["tipo" => "error", "titulo" => "Clave inválida", "mensaje" => "Las claves no coinciden con el formato solicitado."]);
        exit();
    }
    if ($clave_1 != $clave_2) {
        echo json_encode(["tipo" => "error", "titulo" => "Claves no coinciden", "mensaje" => "Las nuevas claves ingresadas no coinciden."]);
        exit();
    }
    $clave_para_db = password_hash($clave_1, PASSWORD_BCRYPT, ["cost" => 10]);
} else {
    $clave_para_db = $datos['usuario_clave'];
}

/*== Actualizar datos en la BD ==*/
// Nota: Se eliminó usuario_email de la consulta
$actualizar_usuario = $conexion->prepare("UPDATE usuario SET usuario_nombre=:nombre, usuario_apellido=:apellido, usuario_usuario=:usuario, usuario_clave=:clave WHERE usuario_id=:id");

$marcadores = [
    ":nombre" => $nombre,
    ":apellido" => $apellido,
    ":usuario" => $usuario,
    ":clave" => $clave_para_db,
    ":id" => $id
];

if ($actualizar_usuario->execute($marcadores)) {
    echo json_encode([
        "tipo" => "success", // IMPORTANTE: Tu JS probablemente espera "success" o "limpiar" para redirigir/limpiar
        "titulo" => "¡Usuario Actualizado!",
        "mensaje" => "El usuario se actualizó con éxito en el sistema."
    ]);
} else {
    echo json_encode([
        "tipo" => "error",
        "titulo" => "Error en el servidor",
        "mensaje" => "No se pudo actualizar el usuario, intente nuevamente."
    ]);
}

$conexion = null;
