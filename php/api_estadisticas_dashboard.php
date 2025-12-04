<?php
// Archivo: php/api_estadisticas_dashboard.php

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');

try {
    // 1. Conexión Autónoma
    date_default_timezone_set('America/Caracas');
    $pdo = new PDO('mysql:host=localhost;dbname=sistemamenu;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET time_zone = '-04:00'");

    // 2. Recibir datos
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!$data) throw new Exception('No hay datos de entrada');

    $inicio = $data['inicio'];
    $fin = $data['fin'];
    $inicioSql = $inicio . " 00:00:00";
    $finSql = $fin . " 23:59:59";

    // --- CAMBIO CLAVE: LOGICA NEGATIVA ---
    // En vez de adivinar si se llama "Pagado" o "Entregado",
    // sumamos TODO lo que NO sea "Rechazado" ni "Pendiente".
    // Esto incluirá automáticamente tus pedidos "Entregado", "Pagado", "Aprobado", etc.
    $condicion_pago = "estado_pago NOT IN ('Rechazado', 'Pendiente', 'Anulado')";

    $response = [];

    // --- KPI: Totales ---
    $sql = "SELECT 
                COALESCE(SUM(total_usd), 0) as total, 
                COUNT(*) as cantidad, 
                COALESCE(AVG(total_usd), 0) as ticket 
            FROM pedido 
            WHERE fecha BETWEEN ? AND ? 
            AND $condicion_pago"; 
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$inicioSql, $finSql]);
    $kpi = $stmt->fetch(PDO::FETCH_ASSOC);

    $response['kpi'] = [
        'ingresos' => $kpi['total'],
        'pedidos' => $kpi['cantidad'],
        'ticket' => $kpi['ticket']
    ];

    // --- GRÁFICO 1: TENDENCIA ---
    if ($inicio === $fin) {
        // Un solo día -> Por horas
        $sql = "SELECT HOUR(fecha) as hora, SUM(total_usd) as venta 
                FROM pedido 
                WHERE fecha BETWEEN ? AND ? AND $condicion_pago
                GROUP BY HOUR(fecha)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$inicioSql, $finSql]);
        $ventas_hora = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $trend_data = [];
        for ($h = 0; $h < 24; $h++) {
            $etiqueta = str_pad($h, 2, "0", STR_PAD_LEFT) . ":00";
            $trend_data[] = [
                'dia' => $etiqueta,
                'venta' => isset($ventas_hora[$h]) ? (float)$ventas_hora[$h] : 0
            ];
        }
        $response['trend'] = $trend_data;

    } else {
        // Varios días -> Por fecha
        $sql = "SELECT DATE(fecha) as dia, SUM(total_usd) as venta 
                FROM pedido 
                WHERE fecha BETWEEN ? AND ? AND $condicion_pago
                GROUP BY DATE(fecha)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$inicioSql, $finSql]);
        $ventas_dia = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $periodo = new DatePeriod(
            new DateTime($inicio),
            new DateInterval('P1D'),
            (new DateTime($fin))->modify('+1 day')
        );

        $trend_data = [];
        foreach ($periodo as $dt) {
            $fecha_str = $dt->format('Y-m-d');
            $trend_data[] = [
                'dia' => $fecha_str,
                'venta' => isset($ventas_dia[$fecha_str]) ? (float)$ventas_dia[$fecha_str] : 0
            ];
        }
        $response['trend'] = $trend_data;
    }

    // --- GRÁFICO 2: Horas Pico ---
    $sql = "SELECT HOUR(fecha) as hora, COUNT(*) as cantidad 
            FROM pedido 
            WHERE fecha BETWEEN ? AND ? AND $condicion_pago
            GROUP BY HOUR(fecha)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$inicioSql, $finSql]);
    $horas_bd = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $hours_data = [];
    for ($h = 0; $h < 24; $h++) {
        $hours_data[] = [
            'hora' => $h,
            'cantidad' => isset($horas_bd[$h]) ? (int)$horas_bd[$h] : 0
        ];
    }
    $response['hours'] = $hours_data;

    // --- GRÁFICO 3: Top Productos ---
    $sql = "SELECT p.producto_nombre, SUM(d.cantidad) as cant 
            FROM pedido_detalle d
            JOIN pedido ped ON ped.id_pedido = d.id_pedido
            JOIN producto p ON p.producto_id = d.id_producto
            WHERE ped.fecha BETWEEN ? AND ? AND $condicion_pago
            AND d.id_producto IS NOT NULL 
            GROUP BY p.producto_nombre ORDER BY cant DESC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$inicioSql, $finSql]);
    $response['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- GRÁFICO 4: Categorías ---
    $sql = "SELECT c.categoria_nombre as categoria, SUM(d.cantidad) as cant 
            FROM pedido_detalle d
            JOIN pedido ped ON ped.id_pedido = d.id_pedido
            JOIN producto p ON p.producto_id = d.id_producto
            JOIN categoria c ON c.categoria_id = p.categoria_id
            WHERE ped.fecha BETWEEN ? AND ? AND $condicion_pago
            GROUP BY c.categoria_nombre";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$inicioSql, $finSql]);
    $response['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- TABLAS ---
    $sql = "SELECT CONCAT(c.nombre_cliente, ' ', c.apellido_cliente) as cliente_nombre, 
                   SUM(ped.total_usd) as gastado 
            FROM pedido ped
            JOIN cliente c ON c.id_cliente = ped.id_cliente
            WHERE ped.fecha BETWEEN ? AND ? AND $condicion_pago
            GROUP BY c.id_cliente ORDER BY gastado DESC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$inicioSql, $finSql]);
    $response['clients'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT prom.promo_nombre as producto_nombre, SUM(d.cantidad) as cant 
            FROM pedido_detalle d
            JOIN pedido ped ON ped.id_pedido = d.id_pedido
            JOIN promociones prom ON prom.promo_id = d.id_promo
            WHERE ped.fecha BETWEEN ? AND ? AND $condicion_pago
            AND d.id_promo IS NOT NULL
            GROUP BY prom.promo_nombre ORDER BY cant DESC LIMIT 5";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$inicioSql, $finSql]);
    $response['promos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>