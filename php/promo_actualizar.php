<?php
require_once "../inc/session_start.php";
require_once "main.php";

//============================================================
// 1. RECIBIR Y LIMPIAR DATOS
//============================================================
$conexion = conexion();

// ID de la promo que se está editando
$promo_id = limpiar_cadena($_POST['promo_id']);

// Datos principales
$nombre = limpiar_cadena($_POST['promo_nombre']);
$precio = limpiar_cadena($_POST['promo_precio']);
$hora_inicio = limpiar_cadena($_POST['hora_inicio']);
$hora_fin = limpiar_cadena($_POST['hora_fin']);
$estado = limpiar_cadena($_POST['estado']);
$prioridad = limpiar_cadena($_POST['prioridad']);

// Fechas Opcionales
$fecha_inicio_raw = (isset($_POST['fecha_inicio']) && trim($_POST['fecha_inicio']) !== "") ? trim($_POST['fecha_inicio']) : null;
$fecha_fin_raw = (isset($_POST['fecha_fin']) && trim($_POST['fecha_fin']) !== "") ? trim($_POST['fecha_fin']) : null;
$fecha_inicio_db = null;
$fecha_fin_db = null;

// Vínculos
$productos_vinculados = isset($_POST['productos_vinculados']) ? (array)$_POST['productos_vinculados'] : [];

// Directorio de imágenes (desde /php/ -> ../img/anuncios/)
$img_dir_base = "../img/anuncios/";

//============================================================
// 2. VALIDAR LOS DATOS
//============================================================

// Campos obligatorios
if ($promo_id == "" || $nombre == "" || $precio == "" || $hora_inicio == "" || $hora_fin == "" || $estado == "" || $prioridad == "") {
    enviar_respuesta_json("error", "Campos vacíos", "No has llenado todos los campos obligatorios");
}

// Formatos
if (verificar_datos(".{1,100}", $nombre)) {
    enviar_respuesta_json("error", "Formato inválido", "El nombre no puede tener más de 100 caracteres");
}
if (!is_numeric($precio) || $precio < 0) {
    enviar_respuesta_json("error", "Formato inválido", "El precio debe ser un número positivo.");
}
if (!is_numeric($hora_inicio) || $hora_inicio < 0 || $hora_inicio > 23) {
    enviar_respuesta_json("error", "Formato inválido", "La hora de inicio debe ser un número entre 0 y 23");
}
if (!is_numeric($hora_fin) || $hora_fin < 0 || $hora_fin > 23) {
    enviar_respuesta_json("error", "Formato inválido", "La hora de fin debe ser un número entre 0 y 23");
}
if ((int)$hora_fin <= (int)$hora_inicio) {
    enviar_respuesta_json("error", "Horario inválido", "La hora de fin debe ser mayor que la hora de inicio");
}
if (!in_array($estado, ['0', '1'])) {
    enviar_respuesta_json("error", "Formato inválido", "El estado no es válido");
}
if (empty($productos_vinculados)) {
    enviar_respuesta_json("error", "Datos incompletos", "Debes seleccionar al menos un producto para la promoción.");
}

// Validación de Fechas (copiada de tu referencia)
if ($fecha_inicio_raw !== null) {
    $fecha_str = str_replace('/', '-', $fecha_inicio_raw);
    if (verificar_datos("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $fecha_str)) {
        $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_str);
        if ($fecha_obj && $fecha_obj->format('Y-m-d') === $fecha_str) {
            $fecha_inicio_db = $fecha_obj->format('Y-m-d');
        }
    } elseif (verificar_datos("^[0-9]{2}-[0-9]{2}-[0-9]{4}$", $fecha_str)) {
        $fecha_obj = DateTime::createFromFormat('d-m-Y', $fecha_str);
        if ($fecha_obj && $fecha_obj->format('d-m-Y') === $fecha_str) {
            $fecha_inicio_db = $fecha_obj->format('Y-m-d');
        }
    }
    if ($fecha_inicio_db === null) {
        enviar_respuesta_json("error", "Formato inválido", "La fecha de inicio no es válida. Use DD-MM-YYYY o YYYY-MM-DD.");
    }
}
if ($fecha_fin_raw !== null) {
    $fecha_str = str_replace('/', '-', $fecha_fin_raw);
    if (verificar_datos("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $fecha_str)) {
        $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_str);
        if ($fecha_obj && $fecha_obj->format('Y-m-d') === $fecha_str) {
            $fecha_fin_db = $fecha_obj->format('Y-m-d');
        }
    } elseif (verificar_datos("^[0-9]{2}-[0-9]{2}-[0-9]{4}$", $fecha_str)) {
        $fecha_obj = DateTime::createFromFormat('d-m-Y', $fecha_str);
        if ($fecha_obj && $fecha_obj->format('d-m-Y') === $fecha_str) {
            $fecha_fin_db = $fecha_obj->format('Y-m-d');
        }
    }
    if ($fecha_fin_db === null) {
        enviar_respuesta_json("error", "Formato inválido", "La fecha de fin no es válida. Use DD-MM-YYYY o YYYY-MM-DD.");
    }
}


//============================================================
// 3. VERIFICAR PROMOCIÓN Y GESTIONAR FOTO
//============================================================

// Verificar que la promoción exista y obtener la foto antigua
$check_promo = $conexion->prepare("SELECT promo_foto FROM promociones WHERE promo_id = :id");
$check_promo->execute([':id' => $promo_id]);

if ($check_promo->rowCount() <= 0) {
    enviar_respuesta_json('error', 'No Encontrado', 'La promoción que intenta actualizar no existe.');
}
$datos_promo_actual = $check_promo->fetch();
$nombre_foto_db = $datos_promo_actual['promo_foto']; // Por defecto, mantenemos la foto antigua

$check_promo = null;
$archivos_creados = []; // Para rastrear archivos nuevos

// Comprobar si se subió una NUEVA foto
if (isset($_FILES['promo_foto']) && $_FILES['promo_foto']['error'] === UPLOAD_ERR_OK && $_FILES['promo_foto']['size'] > 0) {

    // --- INICIO DE LA LÓGICA DE IMAGEN CORREGIDA ---
    // (Basada en tu referencia de producto_guardar.php)

    // Definir directorios
    $dir_original = $img_dir_base . 'original/';
    $dir_large = $img_dir_base . 'large/';
    $dir_thumbs = $img_dir_base . 'thumb/';

    // Crear directorios (por si acaso)
    if (!file_exists($dir_original)) mkdir($dir_original, 0777, true);
    if (!file_exists($dir_large)) mkdir($dir_large, 0777, true);
    if (!file_exists($dir_thumbs)) mkdir($dir_thumbs, 0777, true);

    // Validar tipo y tamaño
    if (mime_content_type($_FILES['promo_foto']['tmp_name']) != "image/jpeg" && mime_content_type($_FILES['promo_foto']['tmp_name']) != "image/png" && mime_content_type($_FILES['promo_foto']['tmp_name']) != "image/webp") {
        enviar_respuesta_json("error", "Formato inválido", "La imagen debe ser JPG, PNG o WEBP");
    }
    if (($_FILES['promo_foto']['size'] / 1024) > 3072) {
        enviar_respuesta_json("error", "Imagen demasiado grande", "La imagen supera el límite de 3MB");
    }

    // Generar nuevo nombre (basado en el nombre de la promo y .webp)
    $img_nombre_base = renombrar_foto($nombre);
    $nombre_foto_db_nuevo = $img_nombre_base . '.webp';

    try {
        // Llamar a la función de procesamiento
        procesar_imagen_optimizada(
            $_FILES['promo_foto'],
            $img_nombre_base,
            $dir_original,
            $dir_large,
            $dir_thumbs,
            $archivos_creados
        );
    } catch (Exception $e) {
        // Si la función 'procesar_imagen_optimizada' falla
        foreach ($archivos_creados as $archivo) {
            if (is_file($archivo)) {
                unlink($archivo);
            }
        }
        enviar_respuesta_json("error", "Error al procesar imagen", $e->getMessage());
    }

    // Si la nueva imagen se procesó con éxito, eliminamos la FOTO ANTIGUA
    $foto_antigua = $datos_promo_actual['promo_foto'];
    if (!empty($foto_antigua) && $foto_antigua != 'default-offer.png') {
        // Borramos todas las versiones de la foto antigua
        if (is_file($dir_original . $foto_antigua)) unlink($dir_original . $foto_antigua);
        if (is_file($dir_large . $foto_antigua)) unlink($dir_large . $foto_antigua);
        if (is_file($dir_thumbs . $foto_antigua)) unlink($dir_thumbs . $foto_antigua);
    }

    // Actualizamos el nombre de la foto para la DB
    $nombre_foto_db = $nombre_foto_db_nuevo;

    // --- FIN DE LA LÓGICA DE IMAGEN CORREGIDA ---
}


//============================================================
// 4. ACTUALIZAR LOS DATOS (TRANSACCIÓN)
//============================================================
try {
    $conexion->beginTransaction();

    // 4.1. Actualizar la tabla principal 'promociones'
    $actualizar_promo = $conexion->prepare(
        "UPDATE promociones SET 
promo_nombre = :nombre, 
promo_precio = :precio,
promo_foto = :foto,
hora_inicio = :h_inicio, 
hora_fin = :h_fin, 
estado = :estado, 
prioridad = :prioridad, 
fecha_inicio = :f_inicio, 
fecha_fin = :f_fin 
WHERE promo_id = :id"
    );

    $marcadores = [
        ":nombre" => $nombre,
        ":precio" => (float)$precio,
        ":foto" => $nombre_foto_db, // Nombre de la foto (nueva .webp o la que ya estaba)
        ":h_inicio" => (int)$hora_inicio,
        ":h_fin" => (int)$hora_fin,
        ":estado" => (int)$estado,
        ":prioridad" => (int)$prioridad,
        ":f_inicio" => $fecha_inicio_db,
        ":f_fin" => $fecha_fin_db,
        ":id" => $promo_id
    ];

    $actualizar_promo->execute($marcadores);

    // 4.2. Actualizar vínculos de Productos (Borrar y Re-insertar)
    $del_prods = $conexion->prepare("DELETE FROM promocion_productos WHERE promo_id = :id");
    $del_prods->execute([':id' => $promo_id]);

    if (!empty($productos_vinculados)) {
        $stmt_prod = $conexion->prepare("INSERT INTO promocion_productos (promo_id, producto_id) VALUES (:promo_id, :producto_id)");
        foreach ($productos_vinculados as $prod_id) {
            $stmt_prod->execute([
                ':promo_id' => $promo_id,
                ':producto_id' => (int)$prod_id
            ]);
        }
    }

    // 4.3. Finalizar transacción
    $conexion->commit();
    $respuesta = [
        "tipo" => "success",
        "titulo" => "¡Actualizado!",
        "texto" => "El producto se actualizó correctamente.",
        "url" => "index.php?vista=promo_list" 
    ];
    echo json_encode($respuesta);
    

} catch (Exception $e) {
    $conexion->rollBack();

    // ==========================================================
    // ¡BLOQUE CATCH CORREGIDO!
    // ==========================================================
    // Si la transacción falla, borramos los archivos NUEVOS que se crearon
    foreach ($archivos_creados as $archivo) {
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }
    enviar_respuesta_json('error', 'Error al Actualizar', 'No se pudo actualizar la promoción. Detalles: ' . $e->getMessage());
}

$conexion = null;
