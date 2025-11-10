<?php
require_once "../inc/session_start.php";
require_once "main.php";

$conexion = conexion();

// --- ALMACENANDO DATOS DEL FORMULARIO ---
$id        = limpiar_cadena($_POST['producto_id']);
$nombre    = limpiar_cadena($_POST['producto_nombre']);
$precio    = limpiar_cadena($_POST['producto_precio']);
$categoria = limpiar_cadena($_POST['producto_categoria']);
$descripcion= limpiar_cadena($_POST['producto_descripcion']);
$estado    = limpiar_cadena($_POST['producto_estado']);

// --- VERIFICAR PRODUCTO A ACTUALIZAR ---
$check_producto = $conexion->prepare("SELECT * FROM producto WHERE producto_id = :id");
$check_producto->execute([':id' => $id]);

if ($check_producto->rowCount() <= 0) {
    enviar_respuesta_json("error", "Error", "El producto no existe en el sistema.");
} else {
    $datos = $check_producto->fetch();
}

// --- VERIFICAR CAMPOS OBLIGATORIOS ---
if ($nombre == "" || $precio == "" || $categoria == "" || $descripcion=="") {
    enviar_respuesta_json("error", "Campos incompletos", "No has llenado todos los campos que son obligatorios.");
}

// --- VERIFICAR INTEGRIDAD DE LOS DATOS ---
// (Aquí iría tu lógica con la función verificar_datos si la necesitas)

// --- VERIFICAR DUPLICADOS DE FORMA SEGURA ---

// Verificando Código de Barras (solo si ha cambiado)
// if ($codigo != $datos['producto_codigo']) {
//     $check_codigo = $conexion->prepare("SELECT producto_codigo FROM producto WHERE producto_codigo = :codigo");
//     $check_codigo->execute([':codigo' => $codigo]);
//     if ($check_codigo->rowCount() > 0) {
//         enviar_respuesta_json("error", "Código duplicado", "El código de barras ingresado ya existe, por favor ingrese otro.");
//     }
// }

// Verificando Nombre (solo si ha cambiado)
if ($nombre != $datos['producto_nombre']) {
    $check_nombre = $conexion->prepare("SELECT producto_nombre FROM producto WHERE producto_nombre = :nombre");
    $check_nombre->execute([':nombre' => $nombre]);
    if ($check_nombre->rowCount() > 0) {
        enviar_respuesta_json("error", "Nombre duplicado", "El nombre ingresado ya existe, por favor ingrese otro.");
    }
}

// Verificando Categoría
$check_categoria = $conexion->prepare("SELECT categoria_id FROM categoria WHERE categoria_id = :categoria");
$check_categoria->execute([':categoria' => $categoria]);
if ($check_categoria->rowCount() <= 0) {
    enviar_respuesta_json("error", "Categoría inválida", "La categoría seleccionada no existe.");
}

// --- ACTUALIZAR DATOS DEL PRODUCTO ---
$actualizar_producto = $conexion->prepare("UPDATE producto SET producto_nombre=:nombre, producto_precio=:precio,descripcion_producto=:descripcion, producto_estado=:estado, categoria_id=:categoria WHERE producto_id=:id");

$marcadores = [
    ":nombre"    => $nombre,
    ":precio"    => $precio,
    ":estado"    => $estado,
    ":categoria" => $categoria,
    ":descripcion" => $descripcion,
    ":id"        => $id
];

if ($actualizar_producto->execute($marcadores)) {
    enviar_respuesta_json("success", "¡Producto Actualizado!", "El producto se actualizó con éxito.");
} else {
    enviar_respuesta_json("error", "Error", "No se pudo actualizar el producto, por favor intente nuevamente.");
}

$conexion = null;