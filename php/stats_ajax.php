<?php
/* Archivo: php/stats_ajax.php */
require_once "main.php";
$conexion = conexion();

// 1. Recibir parámetros
$metric = $_POST['metric'] ?? 'ingresos';
$filter = $_POST['filter'] ?? 'mes'; // dia, semana, mes, anio
$date   = $_POST['date'] ?? date('Y-m-d');
$month  = $_POST['month'] ?? date('m');
$year   = $_POST['year'] ?? date('Y');

// 2. Configurar Rango de Fechas
$inicio = ""; $fin = "";
if($filter == 'dia'){
    $inicio = "$date 00:00:00";
    $fin    = "$date 23:59:59";
} elseif($filter == 'semana'){
    // Calcular inicio y fin de la semana de la fecha dada
    $ts = strtotime($date);
    $start = (date('w', $ts) == 1) ? $ts : strtotime('last monday', $ts);
    $inicio = date('Y-m-d 00:00:00', $start);
    $fin = date('Y-m-d 23:59:59', strtotime('next sunday', $start));
} elseif($filter == 'mes'){
    $dias = date("t", strtotime("$year-$month-01"));
    $inicio = "$year-$month-01 00:00:00";
    $fin    = "$year-$month-$dias 23:59:59";
} elseif($filter == 'anio'){
    $inicio = "$year-01-01 00:00:00";
    $fin    = "$year-12-31 23:59:59";
}

// 3. Switch por Métrica
$html = "";

if($metric == 'ingresos' || $metric == 'transacciones' || $metric == 'ticket'){
    
    // Consulta de Pedidos
    $sql = "SELECT p.id_pedido, p.fecha, c.nombre_cliente, c.apellido_cliente, p.total_usd, p.estado_pago, p.metodo_pago 
            FROM pedido p 
            LEFT JOIN cliente c ON p.id_cliente = c.id_cliente
            WHERE p.estado_pago != 'Rechazado' AND p.fecha BETWEEN '$inicio' AND '$fin'
            ORDER BY p.fecha DESC";
    $rows = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    $html .= '<table class="w-full text-sm text-left"><thead class="text-xs text-slate-400 uppercase bg-slate-50"><tr>
                <th class="px-4 py-3">#</th><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Cliente</th><th class="px-4 py-3">Pago</th><th class="px-4 py-3 text-right">Total</th>
              </tr></thead><tbody class="divide-y divide-slate-100">';
    
    foreach($rows as $r){
        $total += $r['total_usd'];
        $html .= "<tr class='hover:bg-indigo-50 transition-colors'>
                    <td class='px-4 py-3 font-bold'>#{$r['id_pedido']}</td>
                    <td class='px-4 py-3'>".date('d/m/Y H:i', strtotime($r['fecha']))."</td>
                    <td class='px-4 py-3'>{$r['nombre_cliente']} {$r['apellido_cliente']}</td>
                    <td class='px-4 py-3'><span class='text-[10px] px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200'>{$r['metodo_pago']}</span></td>
                    <td class='px-4 py-3 text-right font-bold text-slate-700'>$".number_format($r['total_usd'], 2)."</td>
                  </tr>";
    }
    if(empty($rows)) $html .= "<tr><td colspan='5' class='text-center p-4 text-slate-400'>No hay datos en este rango</td></tr>";
    
    $html .= '</tbody><tfoot class="bg-slate-50 font-bold text-slate-700"><tr><td colspan="4" class="px-4 py-3 text-right uppercase">Total Periodo</td><td class="px-4 py-3 text-right text-indigo-600">$'.number_format($total,2).'</td></tr></tfoot></table>';

} elseif($metric == 'productos') {
    
    // Consulta de Productos
    $sql = "SELECT p.producto_nombre, cat.categoria_nombre, SUM(d.cantidad) as cantidad, SUM(d.cantidad * d.precio_unitario) as total_venta
            FROM pedido_detalle d
            INNER JOIN pedido ped ON d.id_pedido = ped.id_pedido
            LEFT JOIN producto p ON d.id_producto = p.producto_id
            LEFT JOIN categoria cat ON p.categoria_id = cat.categoria_id
            WHERE ped.estado_pago != 'Rechazado' AND ped.fecha BETWEEN '$inicio' AND '$fin' AND d.id_promo IS NULL
            GROUP BY p.producto_id ORDER BY cantidad DESC";
    $rows = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    $html .= '<table class="w-full text-sm text-left"><thead class="text-xs text-slate-400 uppercase bg-slate-50"><tr>
                <th class="px-4 py-3">Producto</th><th class="px-4 py-3">Categoría</th><th class="px-4 py-3 text-center">Cant.</th><th class="px-4 py-3 text-right">Ingreso Est.</th>
              </tr></thead><tbody class="divide-y divide-slate-100">';
    
    foreach($rows as $r){
        $html .= "<tr class='hover:bg-indigo-50 transition-colors'>
                    <td class='px-4 py-3 font-medium text-slate-800'>{$r['producto_nombre']}</td>
                    <td class='px-4 py-3 text-slate-500 text-xs'>{$r['categoria_nombre']}</td>
                    <td class='px-4 py-3 text-center font-bold bg-slate-50'>{$r['cantidad']}</td>
                    <td class='px-4 py-3 text-right text-slate-600'>$".number_format($r['total_venta'], 2)."</td>
                  </tr>";
    }
    if(empty($rows)) $html .= "<tr><td colspan='4' class='text-center p-4 text-slate-400'>No hay ventas de productos individuales</td></tr>";
    $html .= '</tbody></table>';

} elseif($metric == 'pagos') {
     $sql = "SELECT metodo_pago, COUNT(*) as txs, SUM(total_usd) as total FROM pedido 
             WHERE estado_pago != 'Rechazado' AND fecha BETWEEN '$inicio' AND '$fin' GROUP BY metodo_pago ORDER BY total DESC";
     $rows = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
     
     $html .= '<table class="w-full text-sm text-left"><thead class="text-xs text-slate-400 uppercase bg-slate-50"><tr>
                <th class="px-4 py-3">Método</th><th class="px-4 py-3 text-center">Transacciones</th><th class="px-4 py-3 text-right">Monto Total</th>
              </tr></thead><tbody class="divide-y divide-slate-100">';
    foreach($rows as $r){
        $html .= "<tr class='hover:bg-indigo-50'>
                    <td class='px-4 py-3 font-bold'>{$r['metodo_pago']}</td>
                    <td class='px-4 py-3 text-center'>{$r['txs']}</td>
                    <td class='px-4 py-3 text-right font-bold text-green-600'>$".number_format($r['total'], 2)."</td>
                  </tr>";
    }
    $html .= '</tbody></table>';
}

echo $html;
?>