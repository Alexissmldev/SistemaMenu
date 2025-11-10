<?php
require_once "main.php"; 

$product_id = limpiar_cadena($_POST['img_del_id']);

$conexion = conexion();
$check_producto_query = $conexion->prepare("SELECT producto_foto FROM producto WHERE producto_id = :id");
$check_producto_query->execute([':id' => $product_id]);

if ($check_producto_query->rowCount() == 1) {
    $datos = $check_producto_query->fetch();
} else {
    enviar_respuesta_json('error', 'Error de Producto', 'El producto no existe en el sistema.');
}

$img_dir = "../img/producto/";
$foto_a_eliminar = $datos['producto_foto'];

// Comprobar si  hay una imagen que borrar
if (empty($foto_a_eliminar) || !is_file($img_dir . $foto_a_eliminar)) {
    enviar_respuesta_json('info', 'Nada que hacer', 'Este producto ya no tiene una imagen para eliminar.');
}

// Intentar borrar el archivo físico
if (!unlink($img_dir . $foto_a_eliminar)) {
    enviar_respuesta_json('error', 'Error de Permisos', 'No se pudo eliminar el archivo de imagen. Revise los permisos de la carpeta en el servidor.');
}

// Actualizar la base de datos
$actualizar_producto_query = $conexion->prepare("UPDATE producto SET producto_foto = :foto WHERE producto_id = :id");
$marcadores = [
    ":foto" => "",
    ":id" => $product_id
];

if ($actualizar_producto_query->execute($marcadores)) {
    enviar_respuesta_json('success', '¡Imagen Eliminada!', 'La imagen del producto ha sido eliminada con éxito.');
} else {
    enviar_respuesta_json('error', 'Error de Base de Datos', 'El archivo fue eliminado, pero no se pudo actualizar la base de datos.');
}

$conexion = null;