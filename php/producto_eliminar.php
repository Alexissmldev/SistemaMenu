<?php
require_once "main.php"; // Asegúrate de que tu función enviar_respuesta_json() esté aquí

// 1. Limpiar el ID del producto a eliminar
$product_id_del = limpiar_cadena($_POST['product_id_del']);

// 2. Verificar el producto con una consulta preparada (más seguro)
$conexion = conexion();
$check_producto = $conexion->prepare("SELECT producto_foto FROM producto WHERE producto_id = :id");
$check_producto->execute([':id' => $product_id_del]);

if ($check_producto->rowCount() == 1) {
    $datos = $check_producto->fetch();

    // 3. Preparar y ejecutar la eliminación del producto de la base de datos
    $eliminar_producto = $conexion->prepare("DELETE FROM producto WHERE producto_id = :id");
    $eliminar_producto->execute([':id' => $product_id_del]);

    // Comprobar si la eliminación en la base de datos fue exitosa
    if ($eliminar_producto->rowCount() == 1) {
        // Si se eliminó de la DB, intentar eliminar el archivo de imagen si existe
        $foto_a_eliminar = $datos['producto_foto'];
        if (!empty($foto_a_eliminar) && is_file("./img/producto/" . $foto_a_eliminar)) {
            unlink("./img/producto/" . $foto_a_eliminar);
        }
        enviar_respuesta_json('success', '¡Producto Eliminado!', 'El producto ha sido eliminado correctamente.');
    } else {
        enviar_respuesta_json('error', 'Error al Eliminar', 'No se pudo eliminar el producto de la base de datos.');
    }
} else {
    // Si el producto no se encontró en la base de datos
    enviar_respuesta_json('error', 'Producto no Encontrado', 'El producto que intenta eliminar no existe.');
}

$conexion = null;
