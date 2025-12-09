<?php
require_once "../inc/session_Start.php";
require_once "main.php";
require_once "api_tasa_usd.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(["status" => "error", "message" => "Sesión cerrada"]);
    exit();
}

$id_usuario = $_SESSION['id'];
$conexion = conexion();

// 1. RECIBIMOS LOS DATOS (BS Y USD)
$bs_dig = floatval($_POST['bs_digital'] ?? 0);
$bs_efe = floatval($_POST['bs_efectivo'] ?? 0);
$usd_efe = floatval($_POST['usd_efectivo'] ?? 0);
$usd_dig = floatval($_POST['usd_digital'] ?? 0);

$tasa = ($tasa_usd_num > 0) ? $tasa_usd_num : 1;

try {
    $conexion->beginTransaction();

    // 2. CONVERTIMOS TODO A USD PARA EL ALMACENAMIENTO ESTÁNDAR
    // (Para que no tengas una mezcla de monedas en la base de datos)

    // Total Efectivo (USD + Bs convertidos a USD)
    $manual_efectivo_usd = $usd_efe + ($bs_efe / $tasa);

    // Total Digital (USD + Bs convertidos a USD)
    $manual_digital_usd = $usd_dig + ($bs_dig / $tasa);

    $total_manual_usd = $manual_efectivo_usd + $manual_digital_usd;

    // 3. OBTENER TOTAL SISTEMA (YA ESTÁ EN USD)
    $sql = "SELECT IFNULL(SUM(total_usd), 0) as total FROM pedido WHERE estado_pago='Entregado' AND cierre_id IS NULL";
    $stmt = $conexion->query($sql);
    $sys_total_usd = $stmt->fetchColumn();

    // Obtener desglose sistema para rellenar campos
    $sql2 = "SELECT 
        IFNULL(SUM(CASE WHEN metodo_pago LIKE '%efectivo%' THEN total_usd ELSE 0 END), 0) as se,
        IFNULL(SUM(CASE WHEN metodo_pago NOT LIKE '%efectivo%' THEN total_usd ELSE 0 END), 0) as sd
        FROM pedido WHERE estado_pago='Entregado' AND cierre_id IS NULL";
    $stmt2 = $conexion->query($sql2);
    $sys_desglose = $stmt2->fetch(PDO::FETCH_ASSOC);

    // 4. CALCULAR DIFERENCIA (EN USD para la BD)
    $diferencia_usd = $total_manual_usd - $sys_total_usd;

    // 5. GUARDAR
    $insert = $conexion->prepare("INSERT INTO cierres_caja (
        usuario_id, fecha_cierre,
        sistema_total_usd, sistema_efectivo, sistema_digital,
        manual_efectivo, manual_digital,
        diferencia, tasa_bcv
    ) VALUES (
        :u, NOW(),
        :st, :se, :sd,
        :me, :md,
        :dif, :tasa
    )");

    $insert->execute([
        ':u'   => $id_usuario,
        ':st'  => $sys_total_usd,
        ':se'  => $sys_desglose['se'],
        ':sd'  => $sys_desglose['sd'],
        ':me'  => $manual_efectivo_usd, // Guardamos la suma equivalente en USD
        ':md'  => $manual_digital_usd,  // Guardamos la suma equivalente en USD
        ':dif' => $diferencia_usd,
        ':tasa' => $tasa
    ]);

    $id_cierre = $conexion->lastInsertId();

    // 6. CERRAR PEDIDOS
    $conexion->query("UPDATE pedido SET cierre_id = $id_cierre WHERE estado_pago = 'Entregado' AND cierre_id IS NULL");

    $conexion->commit();
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    $conexion->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
