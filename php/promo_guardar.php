<?php
require_once "../inc/session_start.php";
require_once "main.php";

$conexion = conexion();

// --- 1. RECEPCIÓN DE DATOS ---
$nombre      = limpiar_cadena($_POST['promo_nombre']);
$precio      = limpiar_cadena($_POST['promo_precio']);
$hora_inicio = limpiar_cadena($_POST['hora_inicio']);
$hora_fin    = limpiar_cadena($_POST['hora_fin']);
$estado      = limpiar_cadena($_POST['estado']);
$prioridad   = limpiar_cadena($_POST['prioridad']);

// Fechas (Opcionales)
$fecha_inicio_raw = (isset($_POST['fecha_inicio']) && trim($_POST['fecha_inicio']) !== "") ? trim($_POST['fecha_inicio']) : null;
$fecha_fin_raw    = (isset($_POST['fecha_fin']) && trim($_POST['fecha_fin']) !== "") ? trim($_POST['fecha_fin']) : null;
$fecha_inicio_db  = null;
$fecha_fin_db     = null;

// Arrays de Productos y Cantidades
$productos_vinculados = isset($_POST['productos_vinculados']) ? (array)$_POST['productos_vinculados'] : [];
$cantidades           = isset($_POST['cantidades']) ? (array)$_POST['cantidades'] : [];

// --- 2. VALIDACIONES ---

// Campos obligatorios
if ($nombre == "" || $precio == "" || $hora_inicio == "" || $hora_fin == "" || $estado == "" || $prioridad == "") {
    enviar_respuesta_json("error", "Campos vacíos", "No has llenado todos los campos obligatorios");
}

// Validación de productos seleccionados
if (empty($productos_vinculados)) {
    enviar_respuesta_json("error", "Sin productos", "Debes seleccionar al menos un producto para la promoción.");
}

// Formato de texto y números
if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,100}", $nombre)) {
    enviar_respuesta_json("error", "Formato inválido", "El nombre no cumple el formato requerido");
}
if (verificar_datos("[0-9.]{1,25}", $precio)) {
    enviar_respuesta_json("error", "Formato inválido", "El precio no cumple el formato requerido");
}

// Validación de Horas
if (!is_numeric($hora_inicio) || $hora_inicio < 0 || $hora_inicio > 23) {
    enviar_respuesta_json("error", "Hora inválida", "La hora de inicio debe ser entre 0 y 23");
}
if (!is_numeric($hora_fin) || $hora_fin < 0 || $hora_fin > 23) {
    enviar_respuesta_json("error", "Hora inválida", "La hora de fin debe ser entre 0 y 23");
}
if ((int)$hora_fin <= (int)$hora_inicio) {
    enviar_respuesta_json("error", "Horario inválido", "La hora de fin debe ser mayor que la hora de inicio (Ej: De 13 a 14)");
}

// Procesamiento de Fechas
if ($fecha_inicio_raw !== null) {
    $fecha_obj = date_create($fecha_inicio_raw);
    if ($fecha_obj) {
        $fecha_inicio_db = date_format($fecha_obj, 'Y-m-d');
    } else {
        enviar_respuesta_json("error", "Fecha inválida", "La fecha de inicio no es válida.");
    }
}
if ($fecha_fin_raw !== null) {
    $fecha_obj = date_create($fecha_fin_raw);
    if ($fecha_obj) {
        $fecha_fin_db = date_format($fecha_obj, 'Y-m-d');
    } else {
        enviar_respuesta_json("error", "Fecha inválida", "La fecha de fin no es válida.");
    }
}

// Verificando nombre duplicado
$check_nombre = $conexion->prepare("SELECT promo_nombre FROM promociones WHERE promo_nombre = :nombre");
$check_nombre->execute([':nombre' => $nombre]);
if ($check_nombre->rowCount() > 0) {
    enviar_respuesta_json("error", "Nombre duplicado", "Ya existe una promoción con ese nombre");
}
$check_nombre = null;

// Verificando el Límite de 5 Promociones Activas
if ($estado == '1') {
    $check_limite = $conexion->query("SELECT COUNT(promo_id) FROM promociones WHERE estado = 1");
    $total_promos = (int) $check_limite->fetchColumn();

    if ($total_promos >= 5) {
        enviar_respuesta_json("error", "Límite alcanzado", "Ya tienes 5 promociones activas. Desactiva una para poder crear otra.");
    }
}

// --- 3. PROCESAMIENTO DE IMAGEN ---
$foto = "";
$archivos_creados = []; // Para borrar si algo falla

if (isset($_FILES['promo_foto']) && $_FILES['promo_foto']['name'] != "" && $_FILES['promo_foto']['size'] > 0) {

    $dir_base = '../img/promo/'; // Ajustado a carpeta 'promo'

    // Crear directorio si no existe
    if (!file_exists($dir_base) && !mkdir($dir_base, 0777, true)) {
        enviar_respuesta_json("error", "Error servidor", "No se pudo crear el directorio de imágenes");
    }

    // Validar tipo
    $mime = mime_content_type($_FILES['promo_foto']['tmp_name']);
    if (!in_array($mime, ["image/jpeg", "image/png", "image/webp"])) {
        enviar_respuesta_json("error", "Formato inválido", "La imagen debe ser JPG, PNG o WEBP");
    }

    // Validar peso (3MB)
    if (($_FILES['promo_foto']['size'] / 1024) > 3072) {
        enviar_respuesta_json("error", "Imagen pesada", "La imagen supera el límite de 3MB");
    }

    // Generar nombre único
    $img_ext = explode(".", $_FILES['promo_foto']['name']);
    $img_nombre = str_replace(" ", "_", $nombre) . "_" . rand(0, 100) . "." . end($img_ext);
    $foto = $img_nombre;
    $destino = $dir_base . $foto;

    // Mover imagen
    if (move_uploaded_file($_FILES['promo_foto']['tmp_name'], $destino)) {
        $archivos_creados[] = $destino; // Registrar para borrar en rollback
    } else {
        enviar_respuesta_json("error", "Error subida", "No se pudo guardar la imagen");
    }
}

// --- 4. GUARDADO EN BASE DE DATOS ---
try {
    $conexion->beginTransaction();

    // A) Insertar Cabecera (Promociones)
    $guardar_promo = $conexion->prepare(
        "INSERT INTO promociones(promo_nombre, promo_precio, promo_foto, hora_inicio, hora_fin, estado, prioridad, fecha_inicio, fecha_fin) 
         VALUES(:nombre, :precio, :foto, :h_inicio, :h_fin, :estado, :prioridad, :f_inicio, :f_fin)"
    );

    $marcadores = [
        ":nombre"    => $nombre,
        ":precio"    => $precio,
        ":foto"      => $foto,
        ":h_inicio"  => (int)$hora_inicio,
        ":h_fin"     => (int)$hora_fin,
        ":estado"    => (int)$estado,
        ":prioridad" => (int)$prioridad,
        ":f_inicio"  => $fecha_inicio_db,
        ":f_fin"     => $fecha_fin_db
    ];

    $guardar_promo->execute($marcadores);
    $promo_id = $conexion->lastInsertId();

    // B) Insertar Detalles (Productos + Cantidades)
    // Aquí es donde aplicamos la lógica de cantidad
    if (!empty($productos_vinculados)) {
        $stmt_prod = $conexion->prepare("INSERT INTO promocion_productos (promo_id, producto_id, cantidad) VALUES (:promo_id, :producto_id, :cantidad)");

        foreach ($productos_vinculados as $prod_id) {

            $cantidad_producto = 1; // Valor por defecto

            // Buscamos si existe una cantidad enviada para este ID específico
            if (isset($cantidades[$prod_id])) {
                $qty_input = (int)$cantidades[$prod_id];
                if ($qty_input >= 1) {
                    $cantidad_producto = $qty_input;
                }
            }

            $stmt_prod->execute([
                ':promo_id'    => $promo_id,
                ':producto_id' => (int)$prod_id,
                ':cantidad'    => $cantidad_producto
            ]);
        }
    }

    $conexion->commit();
    enviar_respuesta_json("success", "¡Éxito!", "La promoción se registró correctamente");
} catch (Exception $e) {
    $conexion->rollBack();

    // Borrar imagen si hubo error en la BD
    foreach ($archivos_creados as $archivo) {
        if (is_file($archivo)) unlink($archivo);
    }

    enviar_respuesta_json("error", "Error inesperado", "Detalle: " . $e->getMessage());
}

$conexion = null;
