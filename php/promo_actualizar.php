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

// Vínculos y Cantidades (NUEVO: Recibimos el array de cantidades)
$productos_vinculados = isset($_POST['productos_vinculados']) ? (array)$_POST['productos_vinculados'] : [];
$cantidades = isset($_POST['cantidades']) ? (array)$_POST['cantidades'] : [];

// Directorio de imágenes
$img_dir_base = "../img/anuncios/";

//============================================================
// 2. VALIDAR LOS DATOS
//============================================================

// Campos obligatorios
if ($promo_id == "" || $nombre == "" || $precio == "" || $hora_inicio == "" || $hora_fin == "" || $estado == "" || $prioridad == "") {
    enviar_respuesta_json("error", "Campos vacíos", "No has llenado todos los campos obligatorios");
}

// Verificar límite de 5 promos activas (Seguridad Backend)
// Solo verificamos si el usuario intenta poner estado = 1 (Activa)
if ($estado == '1') {
    $check_limite = $conexion->prepare("SELECT COUNT(promo_id) FROM promociones WHERE estado = 1 AND promo_id != :id");
    $check_limite->execute([':id' => $promo_id]);
    $total_activas = (int) $check_limite->fetchColumn();

    if ($total_activas >= 5) {
        enviar_respuesta_json("error", "Límite Alcanzado", "Ya tienes 5 promociones activas. Debes desactivar otra para activar esta.");
    }
}

// Formatos básicos
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
    enviar_respuesta_json("error", "Horario inválido", "La hora de fin debe ser mayor que la hora de inicio"); // Ojo: Si tu negocio cierra después de media noche, esta lógica cambia.
}
if (empty($productos_vinculados)) {
    enviar_respuesta_json("error", "Datos incompletos", "Debes seleccionar al menos un producto para la promoción.");
}

// Validación de Fechas
if ($fecha_inicio_raw !== null) {
    $fecha_str = str_replace('/', '-', $fecha_inicio_raw);
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_str) ?: DateTime::createFromFormat('d-m-Y', $fecha_str);

    if ($fecha_obj) {
        $fecha_inicio_db = $fecha_obj->format('Y-m-d');
    } else {
        enviar_respuesta_json("error", "Formato inválido", "La fecha de inicio no es válida.");
    }
}
if ($fecha_fin_raw !== null) {
    $fecha_str = str_replace('/', '-', $fecha_fin_raw);
    $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_str) ?: DateTime::createFromFormat('d-m-Y', $fecha_str);

    if ($fecha_obj) {
        $fecha_fin_db = $fecha_obj->format('Y-m-d');
    } else {
        enviar_respuesta_json("error", "Formato inválido", "La fecha de fin no es válida.");
    }
}


//============================================================
// 3. GESTIONAR IMAGEN (NUEVA O EXISTENTE)
//============================================================

// Verificar promo y obtener foto actual
$check_promo = $conexion->prepare("SELECT promo_foto FROM promociones WHERE promo_id = :id");
$check_promo->execute([':id' => $promo_id]);

if ($check_promo->rowCount() <= 0) {
    enviar_respuesta_json('error', 'No Encontrado', 'La promoción no existe.');
}
$datos_promo_actual = $check_promo->fetch();
$nombre_foto_db = $datos_promo_actual['promo_foto'];

$archivos_creados = []; // Array para rollback de archivos

// Si se sube nueva foto
if (isset($_FILES['promo_foto']) && $_FILES['promo_foto']['error'] === UPLOAD_ERR_OK && $_FILES['promo_foto']['size'] > 0) {

    $dir_original = $img_dir_base . 'original/';
    $dir_large = $img_dir_base . 'large/';
    $dir_thumbs = $img_dir_base . 'thumb/';

    if (!file_exists($dir_original)) mkdir($dir_original, 0777, true);
    if (!file_exists($dir_large)) mkdir($dir_large, 0777, true);
    if (!file_exists($dir_thumbs)) mkdir($dir_thumbs, 0777, true);

    if (($_FILES['promo_foto']['size'] / 1024) > 3072) {
        enviar_respuesta_json("error", "Imagen demasiado grande", "La imagen supera el límite de 3MB");
    }

    // Generar nombre y procesar
    $img_nombre_base = renombrar_foto($nombre); // Asumo que esta función existe en main.php
    $nombre_foto_db_nuevo = $img_nombre_base . '.webp';

    try {
        // Asumo que esta función existe en main.php según tu código anterior
        procesar_imagen_optimizada(
            $_FILES['promo_foto'],
            $img_nombre_base,
            $dir_original,
            $dir_large,
            $dir_thumbs,
            $archivos_creados
        );

        // Si éxito, marcamos para borrar la vieja DESPUÉS de confirmar transacción DB (o aquí si prefieres)
        // Borrado de foto antigua:
        $foto_antigua = $datos_promo_actual['promo_foto'];
        if (!empty($foto_antigua) && $foto_antigua != 'default-offer.png') {
            if (is_file($dir_original . $foto_antigua)) unlink($dir_original . $foto_antigua);
            if (is_file($dir_large . $foto_antigua)) unlink($dir_large . $foto_antigua);
            if (is_file($dir_thumbs . $foto_antigua)) unlink($dir_thumbs . $foto_antigua);
        }

        $nombre_foto_db = $nombre_foto_db_nuevo;
    } catch (Exception $e) {
        // Limpiar archivos nuevos si falla el proceso de imagen
        foreach ($archivos_creados as $archivo) {
            if (is_file($archivo)) unlink($archivo);
        }
        enviar_respuesta_json("error", "Error imagen", $e->getMessage());
    }
}


//============================================================
// 4. ACTUALIZAR BASE DE DATOS (TRANSACCIÓN)
//============================================================
try {
    $conexion->beginTransaction();

    // 4.1. Actualizar Promo
    $actualizar_promo = $conexion->prepare("UPDATE promociones SET 
        promo_nombre = :nombre, 
        promo_precio = :precio,
        promo_foto = :foto,
        hora_inicio = :h_inicio, 
        hora_fin = :h_fin, 
        estado = :estado, 
        prioridad = :prioridad, 
        fecha_inicio = :f_inicio, 
        fecha_fin = :f_fin 
        WHERE promo_id = :id");

    $actualizar_promo->execute([
        ":nombre" => $nombre,
        ":precio" => (float)$precio,
        ":foto" => $nombre_foto_db,
        ":h_inicio" => (int)$hora_inicio,
        ":h_fin" => (int)$hora_fin,
        ":estado" => (int)$estado,
        ":prioridad" => (int)$prioridad,
        ":f_inicio" => $fecha_inicio_db,
        ":f_fin" => $fecha_fin_db,
        ":id" => $promo_id
    ]);

    // 4.2. Actualizar Productos (Borrar anteriores -> Insertar nuevos con cantidad)
    $del_prods = $conexion->prepare("DELETE FROM promocion_productos WHERE promo_id = :id");
    $del_prods->execute([':id' => $promo_id]);

    if (!empty($productos_vinculados)) {
        // Preparamos la consulta incluyendo la CANTIDAD
        $stmt_prod = $conexion->prepare("INSERT INTO promocion_productos (promo_id, producto_id, cantidad) VALUES (:promo_id, :producto_id, :cantidad)");

        foreach ($productos_vinculados as $prod_id) {
            // Obtenemos la cantidad del array 'cantidades', si no existe o es inválida, ponemos 1
            $qty = (isset($cantidades[$prod_id]) && (int)$cantidades[$prod_id] > 0) ? (int)$cantidades[$prod_id] : 1;

            $stmt_prod->execute([
                ':promo_id' => $promo_id,
                ':producto_id' => (int)$prod_id,
                ':cantidad' => $qty
            ]);
        }
    }

    $conexion->commit();

    echo json_encode([
        "tipo" => "success",
        "titulo" => "¡Actualizado!",
        "texto" => "La promoción y sus productos se actualizaron correctamente.",
        "url" => "promo_list"
    ]);
} catch (Exception $e) {
    $conexion->rollBack();

    // Si la DB falla, borramos las fotos NUEVAS creadas en este intento
    foreach ($archivos_creados as $archivo) {
        if (is_file($archivo)) unlink($archivo);
    }

    enviar_respuesta_json('error', 'Error BD', 'No se pudo guardar los cambios: ' . $e->getMessage());
}

$conexion = null;
