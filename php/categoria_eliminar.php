<?php
require_once "main.php"; // Asegúrate de que tu función enviar_respuesta_json() esté aquí

// Recibimos el ID desde el AJAX (que envía por POST)
$category_id_del = limpiar_cadena($_POST['category_id_del']);

$conexion = conexion();

// 1. Verificar que la categoría exista (usando una consulta preparada)
$check_categoria = $conexion->prepare("SELECT categoria_id FROM categoria WHERE categoria_id = :id");
$check_categoria->execute([':id' => $category_id_del]);

if ($check_categoria->rowCount() == 1) {
    // 2. Verificar que la categoría no tenga productos asociados
    $check_productos = $conexion->prepare("SELECT categoria_id FROM producto WHERE categoria_id = :id LIMIT 1");
    $check_productos->execute([':id' => $category_id_del]);

    if ($check_productos->rowCount() <= 0) {
        // 3. Si no tiene productos, proceder a eliminar la categoría
        $eliminar_categoria = $conexion->prepare("DELETE FROM categoria WHERE categoria_id = :id");
        $eliminar_categoria->execute([':id' => $category_id_del]);

        if ($eliminar_categoria->rowCount() == 1) {
            enviar_respuesta_json('success', '¡Categoría Eliminada!', 'La categoría ha sido eliminada correctamente.');
        } else {
            enviar_respuesta_json('error', 'Error al Eliminar', 'No se pudo eliminar la categoría. Intente nuevamente.');
        }
    } else {
        // Si tiene productos, enviar un error claro
        enviar_respuesta_json('error', 'Acción Denegada', 'No se puede eliminar la categoría porque tiene productos asociados.');
    }
} else {
    // Si la categoría no existe
    enviar_respuesta_json('error', 'No Encontrada', 'La categoría que intenta eliminar no existe.');
}

$conexion = null;