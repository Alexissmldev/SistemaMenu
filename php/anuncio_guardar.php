<?php
require_once "../inc/session_start.php";
require_once "main.php";

$conexion = conexion();

// Almacenando datos 
$mensaje     = limpiar_cadena($_POST['anuncio_mensaje']);
$hora_inicio = limpiar_cadena($_POST['anuncio_hora_inicio']);
$hora_fin    = limpiar_cadena($_POST['anuncio_hora_fin']);
$tipo        = limpiar_cadena($_POST['anuncio_tipo']);
$estado      = limpiar_cadena($_POST['anuncio_estado']);
$prioridad   = limpiar_cadena($_POST['anuncio_prioridad']);
$fecha_inicio_raw = (isset($_POST['anuncio_fecha_inicio']) && trim($_POST['anuncio_fecha_inicio']) !== "") ? trim($_POST['anuncio_fecha_inicio']) : null;
$fecha_fin_raw    = (isset($_POST['anuncio_fecha_fin']) && trim($_POST['anuncio_fecha_fin']) !== "") ? trim($_POST['anuncio_fecha_fin']) : null;
$fecha_inicio_db = null;
$fecha_fin_db    = null;
$categorias_vinculadas = isset($_POST['categorias_vinculadas']) ? (array)$_POST['categorias_vinculadas'] : [];
$productos_vinculados  = isset($_POST['productos_vinculados']) ? (array)$_POST['productos_vinculados'] : [];

// Verificando campos obligatorios 
if ($mensaje == "" || $hora_inicio == "" || $hora_fin == "" || $tipo == "" || $estado == "" || $prioridad == "") {
  enviar_respuesta_json("error", "Campos vacíos", "No has llenado todos los campos obligatorios");
}

// Verificando integridad de los datos 

if (verificar_datos(".{1,255}", $mensaje)) {
  enviar_respuesta_json("error", "Formato inválido", "El mensaje no puede estar vacío o tener más de 255 caracteres");
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
if (!in_array($tipo, ['info', 'alerta'])) {
  enviar_respuesta_json("error", "Formato inválido", "El tipo de anuncio no es válido (solo 'info' o 'alerta').");
}
if (!in_array($estado, ['0', '1'])) {
  enviar_respuesta_json("error", "Formato inválido", "El estado no es válido");
}
if (!is_numeric($prioridad)) {
  enviar_respuesta_json("error", "Formato inválido", "La prioridad debe ser un número (ej: 0)");
}

 
//  Validación de fechas
if ($fecha_inicio_raw !== null) {
    $fecha_str = str_replace('/', '-', $fecha_inicio_raw); 
    if (verificar_datos("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $fecha_str)) {
        $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_str);
        if ($fecha_obj && $fecha_obj->format('Y-m-d') === $fecha_str) { $fecha_inicio_db = $fecha_obj->format('Y-m-d'); }
    } elseif (verificar_datos("^[0-9]{2}-[0-9]{2}-[0-9]{4}$", $fecha_str)) {
        $fecha_obj = DateTime::createFromFormat('d-m-Y', $fecha_str);
        if ($fecha_obj && $fecha_obj->format('d-m-Y') === $fecha_str) { $fecha_inicio_db = $fecha_obj->format('Y-m-d'); }
    }
    if ($fecha_inicio_db === null) {
        enviar_respuesta_json("error","Formato inválido","La fecha de inicio no es válida. Use DD-MM-YYYY o YYYY-MM-DD.");
    }
}
if ($fecha_fin_raw !== null) {
    $fecha_str = str_replace('/', '-', $fecha_fin_raw); 
    if (verificar_datos("^[0-9]{4}-[0-9]{2}-[0-9]{2}$", $fecha_str)) {
        $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha_str);
        if ($fecha_obj && $fecha_obj->format('Y-m-d') === $fecha_str) { $fecha_fin_db = $fecha_obj->format('Y-m-d'); }
    } elseif (verificar_datos("^[0-9]{2}-[0-9]{2}-[0-9]{4}$", $fecha_str)) {
        $fecha_obj = DateTime::createFromFormat('d-m-Y', $fecha_str);
        if ($fecha_obj && $fecha_obj->format('d-m-Y') === $fecha_str) { $fecha_fin_db = $fecha_obj->format('Y-m-d'); }
    }
    if ($fecha_fin_db === null) {
         enviar_respuesta_json("error","Formato inválido","La fecha de fin no es válida. Use DD-MM-YYYY o YYYY-MM-DD.");
    }
}

try {
  $conexion->beginTransaction();

  // Insertar en la tabla principal 
  $guardar_anuncio = $conexion->prepare(
    "INSERT INTO anuncios (anuncio_mensaje, anuncio_hora_inicio, anuncio_hora_fin, anuncio_tipo, anuncio_estado, anuncio_prioridad, anuncio_fecha_inicio, anuncio_fecha_fin) 
    VALUES (:mensaje, :h_inicio, :h_fin, :tipo, :estado, :prioridad, :f_inicio, :f_fin)"
  );

  $marcadores = [
    ":mensaje"  => $mensaje,
    ":h_inicio" => (int)$hora_inicio,
    ":h_fin"   => (int)$hora_fin,
    ":tipo"   => $tipo,
    ":estado"  => (int)$estado,
    ":prioridad" => (int)$prioridad,
    ":f_inicio" => $fecha_inicio_db, 
    ":f_fin"   => $fecha_fin_db     
  ];

  $guardar_anuncio->execute($marcadores);

  $anuncio_id = $conexion->lastInsertId();

    // Lógica de vínculos 
  if (!empty($categorias_vinculadas)) {
    $stmt_cat = $conexion->prepare("INSERT INTO anuncio_categorias (anuncio_id, categoria_id) VALUES (:anuncio_id, :categoria_id)");
    foreach ($categorias_vinculadas as $cat_id) {
      $stmt_cat->execute([
        ':anuncio_id'  => $anuncio_id,
        ':categoria_id' => (int)$cat_id
      ]);
    }
  }

  if (!empty($productos_vinculados)) {
    $stmt_prod = $conexion->prepare("INSERT INTO anuncio_productos (anuncio_id, producto_id) VALUES (:anuncio_id, :producto_id)");
    foreach ($productos_vinculados as $prod_id) {
      $stmt_prod->execute([
        ':anuncio_id' => $anuncio_id,
        ':producto_id' => (int)$prod_id
      ]);
    }
  }

  $conexion->commit();
  enviar_respuesta_json("success", "¡Anuncio registrado!", "El anuncio se registró con éxito");
} catch (Exception $e) {
  $conexion->rollBack();
  enviar_respuesta_json("error", "Error inesperado", "No se pudo registrar el anuncio. Detalles: " . $e->getMessage());
}

$conexion = null;
?>