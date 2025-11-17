<?php
require_once "main.php"; 

$product_id_del = limpiar_cadena($_POST['product_id_del']);

$conexion = conexion();

// --- INICIO DE MODIFICACIÓN ---
// Usaremos una transacción. Si algo falla (paso 2, 3 o 4), 
// la base de datos revierte todo (paso 1).
try {
    
    $conexion->beginTransaction();

    // 1. Verificar el producto Y OBTENER LA FOTO
    $check_producto = $conexion->prepare("SELECT producto_foto FROM producto WHERE producto_id = :id");
    $check_producto->execute([':id' => $product_id_del]);

    if ($check_producto->rowCount() != 1) {
         throw new Exception('El producto que intenta eliminar no existe.');
    }
    
    $datos = $check_producto->fetch(PDO::FETCH_ASSOC);
    $foto_a_eliminar = $datos['producto_foto']; // ej: "mi-producto.webp"

    // 2. Encontrar qué variantes están asociadas ANTES de borrar nada
    $find_variantes = $conexion->prepare("SELECT id_variante FROM variante_producto WHERE producto_id = :id");
    $find_variantes->execute([':id' => $product_id_del]);
    $variantes_a_chequear = $find_variantes->fetchAll(PDO::FETCH_COLUMN); // Guarda los IDs [10, 11, 12]

    // 3. Eliminar los ENLACES en 'variante_producto'
    $eliminar_enlaces = $conexion->prepare("DELETE FROM variante_producto WHERE producto_id = :id");
    $eliminar_enlaces->execute([':id' => $product_id_del]);

    // 4. Eliminar el PRODUCTO principal
    $eliminar_producto = $conexion->prepare("DELETE FROM producto WHERE producto_id = :id");
    $eliminar_producto->execute([':id' => $product_id_del]);

    if ($eliminar_producto->rowCount() != 1) {
         throw new Exception('No se pudo eliminar el producto principal.');
    }

    // 5. Limpiar variantes HUÉRFANAS (Opcional pero recomendado)
    if (!empty($variantes_a_chequear)) {
        // Prepara los placeholders (?,?,?)
        $placeholders = implode(',', array_fill(0, count($variantes_a_chequear), '?'));
        
        // Esta consulta borra las variantes (ej: "Pequeño", "Mediano")
        // SÓLO SI ya no están siendo usadas por NINGÚN OTRO producto.
        $sql_cleanup = "
            DELETE FROM variante
            WHERE id_variante IN ($placeholders)
            AND id_variante NOT IN (
                SELECT id_variante FROM variante_producto
            )
        ";
        
        $limpiar_variantes = $conexion->prepare($sql_cleanup);
        $limpiar_variantes->execute($variantes_a_chequear);
    }
    
    // 6. Si todo salió bien en la DB, confirmar los cambios
    $conexion->commit();

    // 7. Eliminar archivos de imagen (después de confirmar la DB)
    // Tu código de guardado crea 3 o 4 versiones, hay que borrarlas todas.
    if (!empty($foto_a_eliminar)) {
        
        $base_path = '../img/producto/'; // Ruta correcta (según tu producto_guarda.php)
        $base_name = pathinfo($foto_a_eliminar, PATHINFO_FILENAME); // ej: "mi-producto"

        // Las versiones .webp que creaste
        $ruta_large = $base_path . 'large/' . $foto_a_eliminar; 
        $ruta_thumb = $base_path . 'thumb/' . $foto_a_eliminar;

        // El archivo original (no sabemos si era jpg o png, borramos ambos por si acaso)
        $ruta_orig_jpg = $base_path . 'original/' . $base_name . '.jpg';
        $ruta_orig_jpeg = $base_path . 'original/' . $base_name . '.jpeg';
        $ruta_orig_png = $base_path . 'original/' . $base_name . '.png';

        if (is_file($ruta_large)) { unlink($ruta_large); }
        if (is_file($ruta_thumb)) { unlink($ruta_thumb); }
        if (is_file($ruta_orig_jpg)) { unlink($ruta_orig_jpg); }
        if (is_file($ruta_orig_jpeg)) { unlink($ruta_orig_jpeg); }
        if (is_file($ruta_orig_png)) { unlink($ruta_orig_png); }
    }
    
    enviar_respuesta_json('success', '¡Producto Eliminado!', 'El producto y sus variantes han sido eliminados correctamente.');

} catch (Exception $e) {
    // 8. Si algo falló (pasos 1, 3, 4, o 5), revertir todo
    $conexion->rollBack();
    enviar_respuesta_json('error', 'Error al Eliminar', $e->getMessage());
}
// --- FIN DE MODIFICACIÓN ---

$conexion = null;