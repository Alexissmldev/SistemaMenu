<?php
require_once "main.php";
header('Content-Type: application/json');

$conexion = conexion();

// --- 1. Recibir y validar datos ---
$nombre = limpiar_cadena($_POST['categoria_nombre']);

// Recibimos los nuevos campos. Si no vienen, asignamos valores por defecto.
$hora_inicio = (isset($_POST['categoria_hora_inicio']) && $_POST['categoria_hora_inicio'] !== "") ? (int)limpiar_cadena($_POST['categoria_hora_inicio']) : 0;
$hora_fin    = (isset($_POST['categoria_hora_fin']) && $_POST['categoria_hora_fin'] !== "") ? (int)limpiar_cadena($_POST['categoria_hora_fin']) : 23;
$estado      = (isset($_POST['categoria_estado']) && $_POST['categoria_estado'] !== "") ? (int)limpiar_cadena($_POST['categoria_estado']) : 1;

// --- Validaciones de Nombre ---
if ($nombre == "") {
    enviar_respuesta_json('error', "Campo vacío", "El nombre de la categoría es obligatorio.");
}

if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}", $nombre)) {
    enviar_respuesta_json('error', "Formato inválido", "El nombre no coincide con el formato solicitado.");
}

// --- Validaciones de Horario ---
if ($hora_inicio < 0 || $hora_inicio > 23 || $hora_fin < 0 || $hora_fin > 23) {
    enviar_respuesta_json('error', "Horario inválido", "Las horas deben estar comprendidas entre 0 y 23.");
}

// --- Verificar duplicados ---
$check_categoria = $conexion->prepare("SELECT categoria_nombre FROM categoria WHERE categoria_nombre = :nombre");
$check_categoria->execute([':nombre' => $nombre]);
if ($check_categoria->rowCount() > 0) {
    enviar_respuesta_json('error', "Categoría duplicada", "Ya existe esta categoría, por favor ingrese otro nombre.");
}

// --- 2. Guardar la categoría ---
try {
    // Actualizamos la consulta para incluir estado, hora inicio y hora fin
    $guardar = $conexion->prepare("INSERT INTO categoria (categoria_nombre, categoria_estado, categoria_hora_inicio, categoria_hora_fin) VALUES (:nombre, :estado, :inicio, :fin)");

    $guardar->execute([
        ':nombre' => $nombre,
        ':estado' => $estado,
        ':inicio' => $hora_inicio,
        ':fin'    => $hora_fin
    ]);

    if ($guardar->rowCount() == 1) {
        $categoria_id = $conexion->lastInsertId();

        $datos_nueva_categoria = [
            "id" => $categoria_id,
            "nombre" => $nombre,
            "estado" => $estado
        ];

        enviar_respuesta_json('success', '¡Éxito!', 'Categoría registrada correctamente.', $datos_nueva_categoria);
    } else {
        enviar_respuesta_json('error', 'Error', 'No se pudo guardar la categoría.');
    }
} catch (PDOException $e) {
    // Es útil registrar el error real internamente, pero al usuario le mostramos un mensaje genérico
    // error_log($e->getMessage()); 
    enviar_respuesta_json('error', 'Error de base de datos', 'No se pudo registrar la categoría.');
}
