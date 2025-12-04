<?php
require_once "../inc/session_Start.php";
require_once "main.php";

/*== Almacenando datos del POST ==*/
$nombre = limpiar_cadena($_POST['usuario_nombre']);
$apellido = limpiar_cadena($_POST['usuario_apellido']);
$usuario = limpiar_cadena($_POST['usuario_usuario']);
$clave_1 = limpiar_cadena($_POST['usuario_clave_1']);
$clave_2 = limpiar_cadena($_POST['usuario_clave_2']);
$rol = limpiar_cadena($_POST['usuario_rol']);

/*== Verificando campos obligatorios ==*/
if($nombre=="" || $apellido=="" || $usuario=="" || $clave_1=="" || $clave_2=="" || $rol==""){
    echo json_encode(["tipo" => "error", "titulo" => "Campos incompletos", "mensaje" => "No has llenado todos los campos que son obligatorios"]);
    exit();
}

/*== Verificando integridad de los datos ==*/
if(verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$nombre)){
    echo json_encode(["tipo" => "error", "titulo" => "Formato inválido", "mensaje" => "El NOMBRE no coincide con el formato solicitado"]);
    exit();
}

if(verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{3,40}",$apellido)){
    echo json_encode(["tipo" => "error", "titulo" => "Formato inválido", "mensaje" => "El APELLIDO no coincide con el formato solicitado"]);
    exit();
}

if(verificar_datos("[a-zA-Z0-9]{4,20}",$usuario)){
    echo json_encode(["tipo" => "error", "titulo" => "Formato inválido", "mensaje" => "El USUARIO no coincide con el formato solicitado"]);
    exit();
}

if(verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$clave_1) || verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$clave_2)){
    echo json_encode(["tipo" => "error", "titulo" => "Clave inválida", "mensaje" => "Las CLAVES no coinciden con el formato solicitado"]);
    exit();
}

/*== Verificando claves ==*/
if($clave_1!=$clave_2){
    echo json_encode(["tipo" => "error", "titulo" => "Error de clave", "mensaje" => "Las claves ingresadas no coinciden"]);
    exit();
} else {
    $clave = password_hash($clave_1, PASSWORD_BCRYPT, ["cost"=>10]);
}

/*== Verificando usuario duplicado ==*/
$conexion = conexion();
$check_usuario = $conexion->prepare("SELECT usuario_usuario FROM usuario WHERE usuario_usuario=:usuario");
$check_usuario->execute([':usuario' => $usuario]);
if($check_usuario->rowCount()>0){
    echo json_encode(["tipo" => "error", "titulo" => "Usuario duplicado", "mensaje" => "El nombre de USUARIO ingresado ya se encuentra registrado"]);
    exit();
}
$check_usuario = null;

/*== Obteniendo id_tienda del usuario en sesión ==*/
$check_tienda = $conexion->prepare("SELECT id_tienda FROM usuario WHERE usuario_id=:id");
$check_tienda->execute([':id' => $_SESSION['id']]);
$datos_tienda = $check_tienda->fetch();
$id_tienda = $datos_tienda['id_tienda']; // Tomamos el ID de la tienda del admin actual
$check_tienda = null;

/*== Guardando datos ==*/
$guardar_usuario = $conexion->prepare("INSERT INTO usuario(id_tienda, usuario_nombre, usuario_apellido, usuario_usuario, usuario_clave, rol_id) VALUES(:id_tienda, :nombre, :apellido, :usuario, :clave, :rol)");

$marcadores = [
    ":id_tienda" => $id_tienda,
    ":nombre" => $nombre,
    ":apellido" => $apellido,
    ":usuario" => $usuario,
    ":clave" => $clave,
    ":rol" => $rol
];

if($guardar_usuario->execute($marcadores)){
    // CAMBIO AQUÍ: Tipo 'redireccionar' y agregamos la URL de destino
    echo json_encode([
        "tipo" => "success", 
        "url" => "user_list",
        "titulo" => "¡Usuario Registrado!", 
        "mensaje" => "El usuario se registró correctamente. Redirigiendo..."
    ]);
}else{
    echo json_encode(["tipo" => "error", "titulo" => "Error al guardar", "mensaje" => "No se pudo registrar el usuario, por favor intente nuevamente"]);
}

$conexion = null;
?>