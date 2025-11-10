<?php
require_once "main.php"; // Asegúrate que 'main.php' contenga tus funciones de imagen

$product_id = limpiar_cadena($_POST['img_up_id']);

// Verificar el producto
$check_producto = conexion();
$check_producto_query = $check_producto->prepare("SELECT * FROM producto WHERE producto_id = :id");
$check_producto_query->execute([':id' => $product_id]);

if ($check_producto_query->rowCount() == 1) {
    $datos = $check_producto_query->fetch();
} else {
    enviar_respuesta_json('error', 'Error de Producto', 'El producto que intenta actualizar no existe en el sistema.');
}
$check_producto = null;

// --- SECCIÓN MODIFICADA: PROCESAMIENTO DE IMAGEN ---

// Comprobar si se seleccionó una imagen 
if (!isset($_FILES['producto_foto']) || $_FILES['producto_foto']['error'] != UPLOAD_ERR_OK) {
    enviar_respuesta_json('error', 'Error de Archivo', 'No ha seleccionado ninguna imagen válida.');
}

// NUEVO: Directorios para imágenes optimizadas
$dir_base = '../img/producto/';
$dir_original = $dir_base . 'original/';
$dir_large = $dir_base . 'large/';
$dir_thumbs = $dir_base . 'thumb/';

// NUEVO: Crear directorios si no existen (recursivo 'true')
if (!file_exists($dir_original) && !mkdir($dir_original, 0777, true)) {
    enviar_respuesta_json('error', 'Error de Servidor', 'No se pudo crear el directorio "original".');
}
if (!file_exists($dir_large) && !mkdir($dir_large, 0777, true)) {
    enviar_respuesta_json('error', 'Error de Servidor', 'No se pudo crear el directorio "large".');
}
if (!file_exists($dir_thumbs) && !mkdir($dir_thumbs, 0777, true)) {
    enviar_respuesta_json('error', 'Error de Servidor', 'No se pudo crear el directorio "thumb".');
}

// Verificación del formato y peso de la imagen (antes de procesar)
// Ajustado para incluir GIF, ya que tu función lo soporta
$allowed_types = ["image/jpeg", "image/png", "image/gif"];
if (!in_array($_FILES['producto_foto']['type'], $allowed_types)) {
    enviar_respuesta_json('error', 'Formato Inválido', 'La imagen tiene un formato no permitido (solo JPG, PNG o GIF).');
}

if (($_FILES['producto_foto']['size'] / 1024) > 3072) {
    enviar_respuesta_json('error', 'Archivo muy grande', 'La imagen supera el peso permitido de 3MB.');
}

// MODIFICADO: Procesar y guardar la nueva imagen
$img_nombre = renombrar_foto($datos['producto_nombre']);
$foto_nueva = $img_nombre . '.webp'; // El nombre que se guardará en la DB
$archivos_creados_nuevos = []; // Para rastrear y eliminar si falla la DB

try {
    procesar_imagen_optimizada(
        $_FILES['producto_foto'], // El archivo subido
        $img_nombre,              // El nombre base (ej: "nombre-producto-1")
        $dir_original,            // Directorio para el original
        $dir_large,               // Directorio para la versión large
        $dir_thumbs,              // Directorio para la versión thumb
        $archivos_creados_nuevos  // Array (pasado por referencia)
    );
} catch (Exception $e) {
    // Si la función falla, borra cualquier archivo que haya alcanzado a crear
    foreach ($archivos_creados_nuevos as $archivo) {
        if (is_file($archivo)) { unlink($archivo); }
    }
    enviar_respuesta_json('error', 'Error al Procesar', $e->getMessage());
}

// NUEVO: Eliminar imagen anterior (todas sus versiones)
$foto_anterior = $datos['producto_foto']; // ej: "producto-viejo.webp"

// Nos aseguramos que tuviera una foto y que sea diferente a la nueva
if ($foto_anterior != "" && $foto_anterior != $foto_nueva) {
    
    // 1. Obtener el nombre base (ej: "producto-viejo")
    $nombre_base_anterior = pathinfo($foto_anterior, PATHINFO_FILENAME);

    // 2. Eliminar large y thumb (rutas conocidas)
    if (is_file($dir_large . $foto_anterior)) {
        unlink($dir_large . $foto_anterior);
    }
    if (is_file($dir_thumbs . $foto_anterior)) {
        unlink($dir_thumbs . $foto_anterior);
    }

    // 3. Eliminar original (extensión desconocida, usamos glob)
    $originales_anteriores = glob($dir_original . $nombre_base_anterior . ".*");
    if ($originales_anteriores) {
        foreach ($originales_anteriores as $original) {
            if (is_file($original)) { unlink($original); }
        }
    }
}


// Actualizar la base de datos 
$actualizar_producto = conexion();
$actualizar_producto_query = $actualizar_producto->prepare("UPDATE producto SET producto_foto = :foto WHERE producto_id = :id");

$marcadores = [
    ":foto" => $foto_nueva, // Guardamos el nombre .webp
    ":id" => $product_id
];

if ($actualizar_producto_query->execute($marcadores)) {
    enviar_respuesta_json('success', '¡Imagen Actualizada!', 'La imagen del producto se actualizó con éxito.');
} else {
    // Si la base de datos falla, se eliminan las imágenes que acabamos de subir
    foreach ($archivos_creados_nuevos as $archivo) {
        if (is_file($archivo)) { unlink($archivo); }
    }
    enviar_respuesta_json('error', 'Error de Base de Datos', 'No se pudo actualizar el registro. Se revirtieron los cambios de imagen.');
}
?>