<?php
require_once "main.php"; // Asegúrate de que tu función enviar_respuesta_json() esté aquí

// --- 1. Recibir y limpiar los datos del formulario ---
$id = limpiar_cadena($_POST['categoria_id']);
$nombre = limpiar_cadena($_POST['categoria_nombre']);
// El estado debe ser 1 (vigente) o 0 (no vigente)
$estado = limpiar_cadena($_POST['categoria_estado']);

// --- 2. Validar los datos recibidos ---
if (empty($nombre)) {
    enviar_respuesta_json('error', "Campo Vacío", "El nombre de la categoría es obligatorio.");
}
if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}", $nombre)) {
    enviar_respuesta_json('error', "Formato Inválido", "El nombre no coincide con el formato solicitado.");
}
if (!in_array($estado, ['0', '1'])) {
    enviar_respuesta_json('error', 'Estado Inválido', 'El estado de la categoría no es válido.');
}

// --- 3. Verificar que la categoría exista ---
$conexion = conexion();
$check_categoria = $conexion->prepare("SELECT * FROM categoria WHERE categoria_id = :id");
$check_categoria->execute([':id' => $id]);

if ($check_categoria->rowCount() <= 0) {
    enviar_respuesta_json('error', 'No Encontrada', 'La categoría que intenta actualizar no existe.');
} else {
    $datos = $check_categoria->fetch();
}

// --- 4. Verificar si el nombre ha cambiado y si el nuevo nombre ya existe ---
if ($nombre != $datos['categoria_nombre']) {
    $check_nombre = $conexion->prepare("SELECT categoria_nombre FROM categoria WHERE categoria_nombre = :nombre AND categoria_id != :id");
    $check_nombre->execute([':nombre' => $nombre, ':id' => $id]);
    if ($check_nombre->rowCount() > 0) {
        enviar_respuesta_json('error', 'Nombre Duplicado', 'Ese nombre de categoría ya está en uso. Por favor, elige otro.');
    }
}

// --- 5. Actualizar los datos en la base de datos ---
$actualizar_categoria = $conexion->prepare("UPDATE categoria SET categoria_nombre = :nombre, categoria_estado = :estado WHERE categoria_id = :id");

$marcadores = [
    ":nombre" => $nombre,
    ":estado" => $estado,
    ":id" => $id
];

if ($actualizar_categoria->execute($marcadores)) {
    enviar_respuesta_json('success', '¡Categoría Actualizada!', 'La categoría se actualizó con éxito.');
} else {
    enviar_respuesta_json('error', 'Error al Actualizar', 'No se pudo actualizar la categoría. Intente nuevamente.');
}

$conexion = null;
