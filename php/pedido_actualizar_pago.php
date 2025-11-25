<?php
require_once "main.php";

$id_pedido = $_POST['id_pedido'];
$nuevo_estado = $_POST['estado']; // 'Aprobado' o 'Rechazado'

if(!$id_pedido || !$nuevo_estado){
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

$conexion = conexion();

$stmt = $conexion->prepare("UPDATE pedido SET estado_pago = :estado WHERE id_pedido = :id");
$resultado = $stmt->execute([
    ':estado' => $nuevo_estado,
    ':id' => $id_pedido
]);

if($resultado){
    echo json_encode(["status" => "success", "message" => "El estado del pago se actualizó a: " . $nuevo_estado]);
} else {
    echo json_encode(["status" => "error", "message" => "No se pudo actualizar el pedido"]);
}
?>