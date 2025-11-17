<?php
require_once "../inc/session_start.php";
require_once "main.php"; 

$conexion = conexion();

// Almacenando datos 
$nombre    = limpiar_cadena($_POST['promo_nombre']);
$precio    = limpiar_cadena($_POST['promo_precio']);
$hora_inicio = limpiar_cadena($_POST['hora_inicio']);
$hora_fin    = limpiar_cadena($_POST['hora_fin']);
$estado      = limpiar_cadena($_POST['estado']);
$prioridad   = limpiar_cadena($_POST['prioridad']);
$fecha_inicio_raw = (isset($_POST['fecha_inicio']) && trim($_POST['fecha_inicio']) !== "") ? trim($_POST['fecha_inicio']) : null;
$fecha_fin_raw    = (isset($_POST['fecha_fin']) && trim($_POST['fecha_fin']) !== "") ? trim($_POST['fecha_fin']) : null;
$fecha_inicio_db = null;
$fecha_fin_db    = null;
$productos_vinculados = isset($_POST['productos_vinculados']) ? (array)$_POST['productos_vinculados'] : [];

//Verificando campos obligatorios
if ($nombre == "" || $precio == "" || $hora_inicio == "" || $hora_fin == "" || $estado == "" || $prioridad == "") {
    enviar_respuesta_json("error", "Campos vacíos", "No has llenado todos los campos obligatorios");
}

if (empty($productos_vinculados)) {
    enviar_respuesta_json("error", "Campos vacíos", "Debes seleccionar al menos un producto para la promoción.");
}

// Verificando integridad de los datos 
if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,100}", $nombre)) {
    enviar_respuesta_json("error", "Formato inválido", "El nombre no cumple el formato requerido");
}
if (verificar_datos("[0-9.]{1,25}", $precio)) {
    enviar_respuesta_json("error", "Formato inválido", "El precio no cumple el formato requerido");
}
// Horas
if (!is_numeric($hora_inicio) || $hora_inicio < 0 || $hora_inicio > 23) {
    enviar_respuesta_json("error", "Formato inválido", "La hora de inicio debe ser un número entre 0 y 23");
}
if (!is_numeric($hora_fin) || $hora_fin < 0 || $hora_fin > 23) {
    enviar_respuesta_json("error", "Formato inválido", "La hora de fin debe ser un número entre 0 y 23");
}
if ((int)$hora_fin <= (int)$hora_inicio) {
    enviar_respuesta_json("error", "Horario inválido", "La hora de fin debe ser mayor que la hora de inicio");
}
// Estado
if (!in_array($estado, ['0', '1'])) {
    enviar_respuesta_json("error", "Formato inválido", "El estado no es válido");
}

// Fechas 
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
        enviar_respuesta_json("error", "Formato inválido", "La fecha de inicio no es válida.");
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
        enviar_respuesta_json("error", "Formato inválido", "La fecha de fin no es válida.");
    }
}

// Verificando nombre duplicado 
$check_nombre = $conexion->prepare("SELECT promo_nombre FROM promociones WHERE promo_nombre = :nombre");
$check_nombre->execute([':nombre' => $nombre]);
if ($check_nombre->rowCount() > 0) {
    enviar_respuesta_json("error", "Nombre duplicado", "El nombre de promoción ingresado ya existe");
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

//Procesamiento de imagen
$foto = "";
$archivos_creados = [];

if (isset($_FILES['promo_foto']) && $_FILES['promo_foto']['name'] != "" && $_FILES['promo_foto']['size'] > 0) {

    $dir_base = '../img/anuncios/'; 
    $dir_original = $dir_base . 'original/';
    $dir_large = $dir_base . 'large/';
    $dir_thumbs = $dir_base . 'thumb/';

    if (!file_exists($dir_original) && !mkdir($dir_original, 0777, true)) {
        enviar_respuesta_json("error", "Error en directorio", "No se pudo crear el directorio 'original'");
    }
    if (!file_exists($dir_large) && !mkdir($dir_large, 0777, true)) {
        enviar_respuesta_json("error", "Error en directorio", "No se pudo crear el directorio 'large'");
    }
    if (!file_exists($dir_thumbs) && !mkdir($dir_thumbs, 0777, true)) {
        enviar_respuesta_json("error", "Error en directorio", "No se pudo crear el directorio 'thumb'");
    }

    if (mime_content_type($_FILES['promo_foto']['tmp_name']) != "image/jpeg" && mime_content_type($_FILES['promo_foto']['tmp_name']) != "image/png" && mime_content_type($_FILES['promo_foto']['tmp_name']) != "image/webp") {
        enviar_respuesta_json("error", "Formato inválido", "La imagen debe ser JPG, PNG o WEBP");
    }

    if (($_FILES['promo_foto']['size'] / 1024) > 3072) {
        enviar_respuesta_json("error", "Imagen demasiado grande", "La imagen supera el límite de 3MB");
    }

    $img_nombre = renombrar_foto($nombre);
    $foto = $img_nombre . '.webp';

    try {
        procesar_imagen_optimizada(
            $_FILES['promo_foto'],
            $img_nombre,
            $dir_original,
            $dir_large,
            $dir_thumbs,
            $archivos_creados
        );
    } catch (Exception $e) {
        foreach ($archivos_creados as $archivo) {
            if (is_file($archivo)) {
                unlink($archivo);
            }
        }
        enviar_respuesta_json("error", "Error al procesar imagen", $e->getMessage());
    }
} 

//Guardando Promoción
try {
    $conexion->beginTransaction();
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

    if (!empty($productos_vinculados)) {
        $stmt_prod = $conexion->prepare("INSERT INTO promocion_productos (promo_id, producto_id) VALUES (:promo_id, :producto_id)");
        foreach ($productos_vinculados as $prod_id) {
            $stmt_prod->execute([
                ':promo_id'  => $promo_id,
                ':producto_id' => (int)$prod_id
            ]);
        }
    }
    $conexion->commit();
    enviar_respuesta_json("success", "¡Promoción registrada!", "La promoción se registró con éxito");
} catch (Exception $e) {
    $conexion->rollBack();

    foreach ($archivos_creados as $archivo) {
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }
    enviar_respuesta_json("error", "Error inesperado", "No se pudo registrar la promoción. Detalles: " . $e->getMessage());
}
$conexion = null;