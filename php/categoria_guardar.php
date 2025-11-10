<?php
require_once "main.php"; 
header('Content-Type: application/json');

$conexion = conexion(); 
// --- Recibir y validar datos ---
$nombre = limpiar_cadena($_POST['categoria_nombre']);

if ($nombre == "") {
    enviar_respuesta_json('error', "Campo vacío", "El nombre de la categoría es obligatorio.");
}
if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}", $nombre)) {
    enviar_respuesta_json('error', "Formato inválido", "El nombre no coincide con el formato solicitado.");
}

// --- Verificar duplicados
$check_categoria = $conexion->prepare("SELECT categoria_nombre FROM categoria WHERE categoria_nombre = :nombre");
$check_categoria->execute([':nombre' => $nombre]);
if ($check_categoria->rowCount() > 0) {
    enviar_respuesta_json('error', "Categoría duplicada", "Ya existe esta categoría, por favor ingrese otro nombre.");
}

//  Guardar la categoría 
try {
    $guardar = $conexion->prepare("INSERT INTO categoria (categoria_nombre, categoria_estado) VALUES (:nombre, 1)");
    $guardar->execute([':nombre' => $nombre]);

    if ($guardar->rowCount() == 1) {
        $categoria_id = $conexion->lastInsertId();

        $datos_nueva_categoria = ["id" => $categoria_id, "nombre" => $nombre];

        enviar_respuesta_json('success', '¡Éxito!', 'Categoría registrada correctamente.', $datos_nueva_categoria);
    } else {
        enviar_respuesta_json('error', 'Error', 'No se pudo guardar la categoría.');
    }
} catch (PDOException $e) {
    enviar_respuesta_json('error', 'Error de base de datos', 'No se pudo registrar la categoría.');
}
