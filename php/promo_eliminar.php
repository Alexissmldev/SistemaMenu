<?php
require_once "main.php";

$promo_id = limpiar_cadena($_POST['promo_id_del']);

if (empty($promo_id)) {
    enviar_respuesta_json('error', 'Error', 'No se ha proporcionado un ID válido.');
}

$conexion = conexion();

try {
    // Verificar si la promo existe y obtener la foto
    $check_promo = $conexion->prepare("SELECT promo_foto FROM promociones WHERE promo_id = :id");
    $check_promo->execute([':id' => $promo_id]);

    if ($check_promo->rowCount() <= 0) {
        enviar_respuesta_json('error', 'No Encontrada', 'La promoción que intenta eliminar no existe.');
    }
    $foto = $check_promo->fetchColumn();

    //  Eliminar la promo de la base de datos
    $eliminar_promo = $conexion->prepare("DELETE FROM promociones WHERE promo_id = :id");

    if (!$eliminar_promo->execute([':id' => $promo_id])) {
        enviar_respuesta_json('error', 'Error al Eliminar', 'No se pudo eliminar la promoción.');
        exit();
    }

    if (!empty($foto)) {
        $ruta_foto = '../img/anuncios/' . $foto;
        if (is_file($ruta_foto)) {
            unlink($ruta_foto);
        }
    }

    enviar_respuesta_json('success', '¡Promoción Eliminada!', 'La promoción se eliminó correctamente.');
} catch (PDOException $e) {
    enviar_respuesta_json('error', 'Error de BD', 'Error al procesar la solicitud: ' . $e->getMessage());
}

$conexion = null;
