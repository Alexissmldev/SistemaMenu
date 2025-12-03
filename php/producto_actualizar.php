<?php
require_once "../inc/session_start.php";
require_once "main.php";

$conexion = conexion();

// --- 1. RECIBIR DATOS ---
$id          = limpiar_cadena($_POST['producto_id']);
$nombre      = limpiar_cadena($_POST['producto_nombre']);
$precio      = limpiar_cadena($_POST['producto_precio']);
$categoria   = limpiar_cadena($_POST['producto_categoria']);
$descripcion = limpiar_cadena($_POST['producto_descripcion']);
$estado      = limpiar_cadena($_POST['producto_estado']);

// --- 2. VERIFICAR SI EL PRODUCTO EXISTE ---
$check_producto = $conexion->prepare("SELECT * FROM producto WHERE producto_id = :id");
$check_producto->execute([':id' => $id]);

if ($check_producto->rowCount() <= 0) {
    enviar_respuesta_json("error", "Error", "El producto no existe.");
    exit();
} else {
    $datos = $check_producto->fetch();
}

// --- 3. VERIFICAR CAMPOS OBLIGATORIOS ---
if ($nombre == "" || $precio == "" || $categoria == "") {
    enviar_respuesta_json("error", "Campos vacíos", "Nombre, precio y categoría son obligatorios.");
    exit();
}

// --- 4. VERIFICAR DUPLICIDAD DE NOMBRE (Solo si cambió) ---
if ($nombre != $datos['producto_nombre']) {
    $check_nombre = $conexion->prepare("SELECT producto_nombre FROM producto WHERE producto_nombre = :nombre");
    $check_nombre->execute([':nombre' => $nombre]);
    if ($check_nombre->rowCount() > 0) {
        enviar_respuesta_json("error", "Nombre duplicado", "Ese nombre ya existe en otro producto.");
        exit();
    }
}

// --- 5. ACTUALIZAR DATOS PRINCIPALES ---
$actualizar = $conexion->prepare("
    UPDATE producto 
    SET producto_nombre=:nombre, 
        producto_precio=:precio, 
        descripcion_producto=:descripcion, 
        producto_estado=:estado, 
        categoria_id=:categoria 
    WHERE producto_id=:id
");

$marcadores = [
    ":nombre"      => $nombre,
    ":precio"      => $precio,
    ":descripcion" => $descripcion,
    ":estado"      => $estado,
    ":categoria"   => $categoria,
    ":id"          => $id
];

if ($actualizar->execute($marcadores)) {

    /* =================================================================================
       6. LÓGICA DE VARIANTES (BORRAR VIEJAS Y GUARDAR NUEVAS)
       ================================================================================= */

    // (Misma lógica de variantes que tenías...)
    $borrar_viejas = $conexion->prepare("DELETE FROM variante_producto WHERE producto_id = :id");
    $borrar_viejas->execute([':id' => $id]);

    if (isset($_POST['variante_nombre']) && is_array($_POST['variante_nombre'])) {
        $nombres_variantes = $_POST['variante_nombre'];
        $precios_variantes = $_POST['variante_precio'];

        foreach ($nombres_variantes as $index => $nombre_var) {
            $nombre_v = limpiar_cadena($nombre_var);
            $precio_v = (isset($precios_variantes[$index]) && $precios_variantes[$index] != "") ? limpiar_cadena($precios_variantes[$index]) : 0;

            if ($nombre_v != "") {
                $check_v_global = $conexion->prepare("SELECT id_variante FROM variante WHERE nombre_variante = :nombre");
                $check_v_global->execute([':nombre' => $nombre_v]);

                if ($check_v_global->rowCount() > 0) {
                    $fila = $check_v_global->fetch();
                    $id_variante_global = $fila['id_variante'];
                } else {
                    $crear_v = $conexion->prepare("INSERT INTO variante(nombre_variante) VALUES(:nombre)");
                    $crear_v->execute([':nombre' => $nombre_v]);
                    $id_variante_global = $conexion->lastInsertId();
                }

                $insertar_relacion = $conexion->prepare("INSERT INTO variante_producto(producto_id, id_variante, precio_variante) VALUES(:prod_id, :var_id, :precio)");
                $insertar_relacion->execute([':prod_id' => $id, ':var_id' => $id_variante_global, ':precio' => $precio_v]);
            }
        }
    }

    /* =================================================================================
       7. LÓGICA DE IMAGEN AVANZADA (IGUAL A GUARDAR PRODUCTO)
       ================================================================================= */

    if (isset($_FILES['producto_foto']) && $_FILES['producto_foto']['name'] != "" && $_FILES['producto_foto']['size'] > 0) {

        // 1. Definir directorios (Igual que en guardar)
        $dir_base = '../img/producto/';
        $dir_original = $dir_base . 'original/';
        $dir_large = $dir_base . 'large/';
        $dir_thumbs = $dir_base . 'thumb/';

        // Creación de directorios si no existen
        if (!file_exists($dir_original)) mkdir($dir_original, 0777, true);
        if (!file_exists($dir_large)) mkdir($dir_large, 0777, true);
        if (!file_exists($dir_thumbs)) mkdir($dir_thumbs, 0777, true);

        // 2. Validaciones
        if (mime_content_type($_FILES['producto_foto']['tmp_name']) != "image/jpeg" && mime_content_type($_FILES['producto_foto']['tmp_name']) != "image/png") {
            enviar_respuesta_json("error", "Formato inválido", "La imagen debe ser JPG o PNG");
            exit();
        }

        if (($_FILES['producto_foto']['size'] / 1024) > 3072) {
            enviar_respuesta_json("error", "Imagen demasiado grande", "La imagen supera el límite de 3MB");
            exit();
        }

        // 3. Preparar nombres
        $img_nombre = renombrar_foto($nombre); // Usa el nombre (posiblemente nuevo) del producto
        $foto_nueva = $img_nombre . '.webp';
        $archivos_creados = [];

        try {
            // 4. Procesar la imagen nueva (Función de main.php)
            procesar_imagen_optimizada(
                $_FILES['producto_foto'],
                $img_nombre,
                $dir_original,
                $dir_large,
                $dir_thumbs,
                $archivos_creados
            );

            // 5. Eliminar la imagen ANTIGUA de las 3 carpetas (Si existe y no es la misma)
            // Nota: Asumimos que la foto anterior también tenía variantes, si no, esto simplemente falla silenciosamente
            if ($datos['producto_foto'] != "" && $datos['producto_foto'] != $foto_nueva) {
                $foto_antigua = $datos['producto_foto'];
                if (is_file($dir_original . $foto_antigua)) unlink($dir_original . $foto_antigua);
                if (is_file($dir_large . $foto_antigua)) unlink($dir_large . $foto_antigua);
                if (is_file($dir_thumbs . $foto_antigua)) unlink($dir_thumbs . $foto_antigua);
            }

            // 6. Actualizar la base de datos con el nuevo nombre
            $update_img = $conexion->prepare("UPDATE producto SET producto_foto=:foto WHERE producto_id=:id");
            $update_img->execute([':foto' => $foto_nueva, ':id' => $id]);
        } catch (Exception $e) {
            // Si falló el procesamiento, borramos lo que se haya creado
            foreach ($archivos_creados as $archivo) {
                if (is_file($archivo)) unlink($archivo);
            }
            enviar_respuesta_json("error", "Error al procesar imagen", $e->getMessage());

            exit();
        }
    }

    $respuesta = [
        "tipo" => "success",
        "titulo" => "¡Actualizado!",
        "texto" => "El producto se actualizó correctamente.",
        "url" => "product_list"
    ];
    echo json_encode($respuesta);
    exit();
} else {
    enviar_respuesta_json("error", "Error", "Ocurrió un error al actualizar el producto.");
}

$conexion = null;
