<?php
/* ==========================================================================
   BACKEND API PARA ESTADÍSTICAS (JSON)
   ========================================================================== */
require_once "../inc/session_start.php";
require_once "main.php";

// Aseguramos respuesta JSON
header('Content-Type: application/json');

// 1. RECIBIR FILTROS (Desde JS loadStats)
$tipo_vista = $_POST['tipo'] ?? 'mes';
$sel_anio   = $_POST['anio'] ?? date('Y');
$sel_mes    = $_POST['mes'] ?? date('m');
$custom_ini = $_POST['inicio'] ?? date('Y-m-01');
$custom_fin = $_POST['fin'] ?? date('Y-m-d');

// 2. CONFIGURAR RANGO SQL
$inicio_sql = "";
$fin_sql = "";
$rango_texto = "";

// Lógica de fechas idéntica a la vista para consistencia
if ($tipo_vista == 'custom') {
    $inicio_sql = $custom_ini . " 00:00:00";
    $fin_sql    = $custom_fin . " 23:59:59";
    $rango_texto = date("d/m", strtotime($custom_ini)) . " - " . date("d/m", strtotime($custom_fin));
} elseif ($tipo_vista == 'semana') {
    $lunes = strtotime('monday this week');
    $domingo = strtotime('sunday this week');
    $inicio_sql = date('Y-m-d 00:00:00', $lunes);
    $fin_sql = date('Y-m-d 23:59:59', $domingo);
    $rango_texto = "Esta Semana";
} elseif ($tipo_vista == 'anio') {
    $inicio_sql = "$sel_anio-01-01 00:00:00";
    $fin_sql = "$sel_anio-12-31 23:59:59";
    $rango_texto = "Año $sel_anio";
} else {
    $dias_mes = date("t", strtotime("$sel_anio-$sel_mes-01"));
    $inicio_sql = "$sel_anio-$sel_mes-01 00:00:00";
    $fin_sql = "$sel_anio-$sel_mes-$dias_mes 23:59:59";
    $meses = ["01"=>"Enero","02"=>"Febrero","03"=>"Marzo","04"=>"Abril","05"=>"Mayo","06"=>"Junio","07"=>"Julio","08"=>"Agosto","09"=>"Septiembre","10"=>"Octubre","11"=>"Noviembre","12"=>"Diciembre"];
    $rango_texto = $meses[$sel_mes] . " " . $sel_anio;
}

$conexion = conexion();

// --- EJECUTAR CONSULTAS ---

// 1. KPI GLOBAL
$kpi = $conexion->query("SELECT 
                            SUM(total_usd) as total_ingresos, 
                            COUNT(*) as total_pedidos, 
                            AVG(total_usd) as ticket_promedio 
                        FROM pedido 
                        WHERE estado_pago != 'Rechazado' AND fecha BETWEEN '$inicio_sql' AND '$fin_sql'")->fetch(PDO::FETCH_ASSOC);

// 2. TENDENCIA (GRÁFICA LÍNEA)
$labels_trend = [];
$data_trend = [];

if ($tipo_vista == 'anio') {
    // Agrupar por Mes
    $sql_trend = "SELECT MONTH(fecha) as periodo, SUM(total_usd) as total 
                  FROM pedido WHERE estado_pago != 'Rechazado' AND fecha BETWEEN '$inicio_sql' AND '$fin_sql' 
                  GROUP BY periodo ORDER BY periodo";
    $res_trend = $conexion->query($sql_trend)->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $meses_cortos = ["", "Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic"];
    for($i=1; $i<=12; $i++) {
        $labels_trend[] = $meses_cortos[$i];
        $data_trend[] = $res_trend[$i] ?? 0;
    }
} else {
    // Agrupar por Día
    $sql_trend = "SELECT DATE(fecha) as dia, SUM(total_usd) as total 
                  FROM pedido WHERE estado_pago != 'Rechazado' AND fecha BETWEEN '$inicio_sql' AND '$fin_sql' 
                  GROUP BY dia ORDER BY dia";
    $res_trend = $conexion->query($sql_trend)->fetchAll(PDO::FETCH_KEY_PAIR);

    // Llenar días vacíos con 0
    $period = new DatePeriod(
        new DateTime(substr($inicio_sql,0,10)), 
        new DateInterval('P1D'), 
        (new DateTime(substr($fin_sql,0,10)))->modify('+1 day')
    );
    
    $dias_es = ["Sun"=>"Dom", "Mon"=>"Lun", "Tue"=>"Mar", "Wed"=>"Mié", "Thu"=>"Jue", "Fri"=>"Vie", "Sat"=>"Sáb"];
    
    foreach ($period as $dt) {
        $fecha_str = $dt->format("Y-m-d");
        $dia_txt = $dias_es[$dt->format("D")] . " " . $dt->format("d"); // Ej: Lun 12
        
        $labels_trend[] = ($tipo_vista == 'semana') ? $dia_txt : $dt->format("d");
        $data_trend[] = $res_trend[$fecha_str] ?? 0;
    }
}

// 3. HORAS PICO (BARRAS)
$sql_horas = "SELECT HOUR(fecha) as hora, COUNT(*) as transacciones 
              FROM pedido WHERE estado_pago != 'Rechazado' AND fecha BETWEEN '$inicio_sql' AND '$fin_sql' 
              GROUP BY hora ORDER BY hora ASC";
$res_horas = $conexion->query($sql_horas)->fetchAll(PDO::FETCH_KEY_PAIR);

$labels_horas = [];
$data_horas = [];
for($i=0; $i<24; $i+=2) { // Saltos de 2 en 2 como pediste (0, 2, 4...)
    // Formato amigable: 2 PM
    $labels_horas[] = date("g A", strtotime("$i:00")); 
    // Sumamos la hora actual y la siguiente para agrupar (opcional) o solo mostramos la hora exacta
    // Para simplificar, mostramos el dato de esa hora
    $data_horas[] = ($res_horas[$i] ?? 0) + ($res_horas[$i+1] ?? 0); 
}

// Nota: Si quieres mostrar las 24 horas pero que el eje solo pinte algunas, eso se hace en JS. 
// Si quieres enviar solo 12 barras (agrupando de 2 en 2 horas), usa el bucle de arriba.
// Si prefieres enviar las 24 horas:
$labels_horas_full = [];
$data_horas_full = [];
for($i=0; $i<24; $i++) {
    $labels_horas_full[] = date("g A", strtotime("$i:00"));
    $data_horas_full[] = $res_horas[$i] ?? 0;
}


// 4. TOP PRODUCTOS
$sql_top = "SELECT p.producto_nombre, SUM(d.cantidad) as cantidad_total 
            FROM pedido_detalle d 
            INNER JOIN pedido ped ON d.id_pedido = ped.id_pedido 
            LEFT JOIN producto p ON d.id_producto = p.producto_id 
            WHERE ped.estado_pago != 'Rechazado' AND ped.fecha BETWEEN '$inicio_sql' AND '$fin_sql' AND d.id_promo IS NULL 
            GROUP BY p.producto_id ORDER BY cantidad_total DESC LIMIT 5";
$res_top = $conexion->query($sql_top)->fetchAll(PDO::FETCH_ASSOC);
$labels_top = []; $data_top = [];
foreach($res_top as $t){ 
    $labels_top[] = substr($t['producto_nombre'], 0, 15); 
    $data_top[] = $t['cantidad_total']; 
}

// 5. TOP CATEGORÍAS
$sql_cat = "SELECT cat.categoria_nombre, SUM(d.cantidad) as total_items 
            FROM pedido_detalle d 
            INNER JOIN pedido ped ON d.id_pedido = ped.id_pedido 
            INNER JOIN producto p ON d.id_producto = p.producto_id 
            INNER JOIN categoria cat ON p.categoria_id = cat.categoria_id 
            WHERE ped.estado_pago != 'Rechazado' AND ped.fecha BETWEEN '$inicio_sql' AND '$fin_sql' 
            GROUP BY cat.categoria_id ORDER BY total_items DESC LIMIT 5";
try {
    $res_cat = $conexion->query($sql_cat)->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $res_cat = []; }

$labels_cat = []; $data_cat = [];
foreach($res_cat as $c){ 
    $labels_cat[] = $c['categoria_nombre']; 
    $data_cat[] = $c['total_items']; 
}

// 6. LISTAS (CLIENTES Y PROMOS)
$sql_clientes = "SELECT c.nombre_cliente, c.apellido_cliente, COUNT(p.id_pedido) as compras, SUM(p.total_usd) as gastado 
                 FROM pedido p INNER JOIN cliente c ON p.id_cliente = c.id_cliente 
                 WHERE p.estado_pago != 'Rechazado' AND p.fecha BETWEEN '$inicio_sql' AND '$fin_sql' 
                 GROUP BY p.id_cliente ORDER BY gastado DESC LIMIT 5";
$top_clientes = $conexion->query($sql_clientes)->fetchAll(PDO::FETCH_ASSOC);

$sql_promos = "SELECT pro.promo_nombre, COUNT(d.id_detalle) as vendidas 
               FROM pedido_detalle d INNER JOIN pedido ped ON d.id_pedido = ped.id_pedido 
               INNER JOIN promociones pro ON d.id_promo = pro.promo_id 
               WHERE ped.estado_pago != 'Rechazado' AND ped.fecha BETWEEN '$inicio_sql' AND '$fin_sql' 
               GROUP BY d.id_promo ORDER BY vendidas DESC LIMIT 5";
$top_promos = $conexion->query($sql_promos)->fetchAll(PDO::FETCH_ASSOC);

// --- ARMAR RESPUESTA JSON ---
$response = [
    'status' => 'success',
    'rango' => $rango_texto,
    'kpi' => [
        'ingresos' => number_format($kpi['total_ingresos'] ?? 0, 2),
        'pedidos' => number_format($kpi['total_pedidos'] ?? 0),
        'ticket' => number_format($kpi['ticket_promedio'] ?? 0, 2)
    ],
    'chartTrend' => [
        'labels' => $labels_trend,
        'data' => $data_trend
    ],
    'chartHoras' => [
        // Usamos el full (24h) y dejamos que JS haga el "autoSkip: false" o configuracion visual
        'labels' => $labels_horas_full, 
        'data' => $data_horas_full
    ],
    'chartTop' => [
        'labels' => $labels_top,
        'data' => $data_top
    ],
    'chartCat' => [
        'labels' => $labels_cat,
        'data' => $data_cat
    ],
    'listas' => [
        'clientes' => $top_clientes,
        'promos' => $top_promos
    ]
];

echo json_encode($response);
?>