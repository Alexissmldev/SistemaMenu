<?php
$inicio = ($pagina > 0) ? (($registros * $pagina) - $registros) : 0;
$tabla = "";

// 1. CAMPOS DE LA CONSULTA PRINCIPAL
$campos = "p.id_pedido, p.fecha, p.precio_total, p.total_usd, p.estado_pago, p.metodo_pago, p.referencia, p.tipo_orden, c.nombre_cliente, c.apellido_cliente, c.telefono_cliente";

$conexion = conexion();

if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda = limpiar_cadena($_GET['busqueda']);
    $url = "index.php?vista=orders_list&busqueda=" . urlencode($busqueda) . "&page=";
}

$query_base = "FROM pedido p INNER JOIN cliente c ON p.id_cliente = c.id_cliente";

if (isset($busqueda) && $busqueda != "") {
    $condicion = "WHERE p.id_pedido LIKE :busqueda OR c.nombre_cliente LIKE :busqueda OR c.apellido_cliente LIKE :busqueda";
    $consulta_datos = $conexion->prepare("SELECT $campos $query_base $condicion ORDER BY p.fecha DESC LIMIT $inicio, $registros");
    $consulta_datos->execute([':busqueda' => "%$busqueda%"]);
    $consulta_total = $conexion->prepare("SELECT COUNT(p.id_pedido) $query_base $condicion");
    $consulta_total->execute([':busqueda' => "%$busqueda%"]);
} else {
    $consulta_datos = $conexion->prepare("SELECT $campos $query_base ORDER BY p.fecha DESC LIMIT $inicio, $registros");
    $consulta_datos->execute();
    $consulta_total = $conexion->prepare("SELECT COUNT(p.id_pedido) FROM pedido p");
    $consulta_total->execute();
}

$datos = $consulta_datos->fetchAll();
$total = (int) $consulta_total->fetchColumn();
$Npagina = ceil($total / $registros);

// --- INICIO DE LA TABLA HTML (DISEÑO ESCRITORIO) ---
$tabla .= '
<div class="bg-white rounded-lg shadow-md border border-gray-200">
    <div class="hidden md:block">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3"># Pedido</th>
                    <th scope="col" class="px-6 py-3">Cliente</th>
                    <th scope="col" class="px-6 py-3">Resumen (Items)</th>
                    <th scope="col" class="px-6 py-3">Pago / Ref</th>
                    <th scope="col" class="px-6 py-3 text-center">Estado</th>
                    <th scope="col" class="px-6 py-3 text-center">Opciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">';

if ($total >= 1 && $pagina <= $Npagina) {
    foreach ($datos as $rows) {

        // 2. GENERAR RESUMEN INTELIGENTE (Soporte Promos)
        $id_pedido_actual = $rows['id_pedido'];

        // Consulta corregida para traer Nombre de Promo si existe
        $sql_items = "SELECT 
                        d.cantidad, 
                        /* Si es Promo, usa promo_nombre. Si es Variante, nombre_variante. Si no, producto_nombre */
                        COALESCE(v.nombre_variante, p.producto_nombre, promo.promo_nombre) as nombre,
                        d.id_promo
                      FROM pedido_detalle d
                      LEFT JOIN producto p ON d.id_producto = p.producto_id
                      LEFT JOIN variante_producto vp ON d.id_variante_producto = vp.id_variante_producto
                      LEFT JOIN variante v ON vp.id_variante = v.id_variante
                      LEFT JOIN promociones promo ON d.id_promo = promo.promo_id
                      WHERE d.id_pedido = '$id_pedido_actual' LIMIT 3";

        $items_resumen = $conexion->query($sql_items)->fetchAll(PDO::FETCH_ASSOC);

        $texto_resumen = "";
        foreach ($items_resumen as $it) {
            $esPromo = !empty($it['id_promo']) ? " <span class='text-[9px] text-orange-500 bg-orange-50 px-1 rounded'>Promo</span>" : "";
            $texto_resumen .= "<b>" . $it['cantidad'] . "x</b> " . $it['nombre'] . $esPromo . ", ";
        }
        $texto_resumen = rtrim($texto_resumen, ", ");
        if (count($items_resumen) >= 3) $texto_resumen .= "...";


        // 3. BADGES Y ESTILOS
        $estado_badge = '<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Desconocido</span>';
        if (stripos($rows['estado_pago'], 'Aprobado') !== false) {
            $estado_badge = '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Aprobado</span>';
        } elseif (stripos($rows['estado_pago'], 'Rechazado') !== false) {
            $estado_badge = '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Rechazado</span>';
        } elseif (stripos($rows['estado_pago'], 'Entregado') !== false) {
            $estado_badge = '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Entregado</span>';
        } else {
            $estado_badge = '<span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Por Verificar</span>';
        }

        $tipo_badge = (stripos($rows['tipo_orden'], 'llevar') !== false)
            ? '<span class="ml-2 bg-orange-100 text-orange-700 text-[10px] font-bold px-1.5 py-0.5 rounded border border-orange-200"><i class="fas fa-shopping-bag"></i> LLEVAR</span>'
            : '<span class="ml-2 bg-blue-50 text-blue-600 text-[10px] font-bold px-1.5 py-0.5 rounded border border-blue-100"><i class="fas fa-utensils"></i> MESA</span>';

        $icono_pago = (stripos($rows['metodo_pago'], 'Pago Movil') !== false) ? '<i class="fas fa-mobile-alt mr-1"></i>' : '<i class="fas fa-money-bill mr-1"></i>';

        $html_referencia = !empty($rows['referencia']) ? '<div class="text-[10px] text-indigo-600 font-bold mt-0.5 select-all">Ref: ' . $rows['referencia'] . '</div>' : "";

        $tabla .= '
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 font-mono text-gray-500">
                    #' . $rows['id_pedido'] . '
                    <div class="text-[10px] text-gray-400 mt-1">' . date("d/m H:i", strtotime($rows['fecha'])) . '</div>
                    <div class="mt-1">' . $tipo_badge . '</div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-medium text-gray-900">' . htmlspecialchars($rows['nombre_cliente'] . ' ' . $rows['apellido_cliente']) . '</div>
                    <div class="text-xs text-gray-400">' . htmlspecialchars($rows['telefono_cliente']) . '</div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-xs text-gray-600 max-w-[220px] leading-relaxed">
                        ' . $texto_resumen . '
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="font-bold text-gray-800">' . number_format($rows['precio_total'], 2) . ' Bs</div>
                    <div class="text-xs text-gray-400">($' . number_format($rows['total_usd'], 2) . ')</div>
                    <div class="text-[10px] text-gray-500 mt-1">' . $icono_pago . $rows['metodo_pago'] . '</div>
                    ' . $html_referencia . '
                </td>
                <td class="px-6 py-4 text-center">
                    ' . $estado_badge . '
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center space-x-3">
                        <a href="index.php?vista=order_detail&order_id=' . $rows['id_pedido'] . '" class="text-gray-500 hover:text-indigo-600" title="Ver Detalle">
                            <i class="fa fa-eye fa-lg"></i>
                        </a>                        
                        <a href="./php/ticket.php?id=' . $rows['id_pedido'] . '" target="_blank" class="text-gray-400 hover:text-gray-600" title="Imprimir Ticket">
                            <i class="fa fa-print fa-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>';
    }
} else {
    $tabla .= '<tr><td colspan="6" class="text-center py-12 text-gray-500">No hay pedidos registrados.</td></tr>';
}

$tabla .= '
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-gray-200">';

if ($total >= 1 && $pagina <= $Npagina) {
    foreach ($datos as $rows) {

        // REPETIR LÓGICA DE RESUMEN PARA MOVIL
        $id_pedido_actual = $rows['id_pedido'];
        $sql_items = "SELECT 
                        d.cantidad, 
                        COALESCE(v.nombre_variante, p.producto_nombre, promo.promo_nombre) as nombre,
                        d.id_promo
                      FROM pedido_detalle d
                      LEFT JOIN producto p ON d.id_producto = p.producto_id
                      LEFT JOIN variante_producto vp ON d.id_variante_producto = vp.id_variante_producto
                      LEFT JOIN variante v ON vp.id_variante = v.id_variante
                      LEFT JOIN promociones promo ON d.id_promo = promo.promo_id
                      WHERE d.id_pedido = '$id_pedido_actual' LIMIT 3";

        $items_resumen = $conexion->query($sql_items)->fetchAll(PDO::FETCH_ASSOC);
        $texto_resumen = "";
        foreach ($items_resumen as $it) {
            $esPromo = !empty($it['id_promo']) ? " <span class='text-[9px] text-orange-500 bg-orange-50 px-1 rounded'>Promo</span>" : "";
            $texto_resumen .= "<b>" . $it['cantidad'] . "x</b> " . $it['nombre'] . $esPromo . ", ";
        }
        $texto_resumen = rtrim($texto_resumen, ", ");
        if (count($items_resumen) >= 3) $texto_resumen .= "...";

        // Estilos Movil
        $estado_class = (stripos($rows['estado_pago'], 'Aprobado') !== false) ? 'bg-green-100 text-green-800' : ((stripos($rows['estado_pago'], 'Rechazado') !== false) ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800');

        $tipo_icon = (stripos($rows['tipo_orden'], 'llevar') !== false)
            ? '<span class="text-orange-600 font-bold text-[10px] ml-2"><i class="fas fa-shopping-bag"></i> LLEVAR</span>'
            : '<span class="text-blue-600 font-bold text-[10px] ml-2"><i class="fas fa-utensils"></i> MESA</span>';

        $tabla .= '
            <div class="p-4 bg-white">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="text-xs font-bold text-gray-400">#' . $rows['id_pedido'] . '</span> ' . $tipo_icon . '
                        <h3 class="font-bold text-gray-900">' . htmlspecialchars($rows['nombre_cliente']) . '</h3>
                        <p class="text-xs text-gray-500">' . date("d/m h:i A", strtotime($rows['fecha'])) . '</p>
                    </div>
                    <div class="text-right">
                        <span class="block font-bold text-gray-900">' . number_format($rows['precio_total'], 2) . ' Bs</span>
                        <div class="mt-1">
                             <span class="' . $estado_class . ' text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                ' . $rows['estado_pago'] . '
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-2 rounded text-xs text-gray-600 mb-3 border border-gray-100">
                    ' . $texto_resumen . '
                </div>
                
                <div class="flex items-center justify-between pt-2 border-t border-gray-50">
                    <div class="text-xs text-gray-500 font-medium">
                        <div><i class="fas fa-wallet mr-1"></i> ' . $rows['metodo_pago'] . '</div>
                        ' . (!empty($rows['referencia']) ? '<div class="text-indigo-600 font-bold mt-0.5 select-all">Ref: ' . $rows['referencia'] . '</div>' : '') . '
                    </div>
                    
                    <div class="flex items-center space-x-4">
                         <a href="index.php?vista=order_detail&order_id=' . $rows['id_pedido'] . '" class="text-indigo-600 text-sm font-medium">
                            Ver Detalles
                        </a>
                        <a href="./php/ticket.php?id=' . $rows['id_pedido'] . '" target="_blank" class="text-gray-400 hover:text-gray-600">
                            <i class="fa fa-print fa-lg"></i>
                        </a>
                    </div>
                </div>
            </div>';
    }
} else {
    $tabla .= '<div class="p-8 text-center text-gray-500">No hay pedidos encontrados.</div>';
}

$tabla .= '
    </div>
</div>';

$conexion = null;
echo $tabla;

if ($total >= 1 && $pagina <= $Npagina) {
    echo paginador_tablas($pagina, $Npagina, $url, 6);
}
