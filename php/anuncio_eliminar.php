<?php
// 1. Incluimos las funciones y la conexión
require_once "main.php";

// 2. Obtenemos el ID del 'FormData' enviado por fetch
$ad_id = limpiar_cadena($_POST['ad_id_del']);

// 3. Verificamos que el ID no esté vacío
if (empty($ad_id)) {
    enviar_respuesta_json('error', 'Error', 'No se ha proporcionado un ID válido.');
}

// 4. Conectamos a la BD
$conexion = conexion();

try {
    // 5. Verificamos que el anuncio exista
    $check_anuncio = $conexion->prepare("SELECT anuncio_id FROM anuncios WHERE anuncio_id = :id");
    $check_anuncio->execute([':id' => $ad_id]);

    if ($check_anuncio->rowCount() <= 0) {
        enviar_respuesta_json('error', 'No Encontrado', 'El anuncio que intenta eliminar no existe.');
    }

    // 6. Procedemos a eliminar el anuncio
    $eliminar_anuncio = $conexion->prepare("DELETE FROM anuncios WHERE anuncio_id = :id");
    $eliminar_anuncio->execute([':id' => $ad_id]);

    if ($eliminar_anuncio->rowCount() == 1) {
     
        enviar_respuesta_json('success', '¡Anuncio Eliminado!', 'El anuncio se eliminó correctamente.');
    } else {
        enviar_respuesta_json('error', 'Error al Eliminar', 'No se pudo eliminar el anuncio. Intente nuevamente.');
    }
} catch (PDOException $e) {
    enviar_respuesta_json('error', 'Error de BD', 'Error al procesar la solicitud: ' . $e->getMessage());
}

$conexion = null;
