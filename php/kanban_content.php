<?php
// Validar acceso si se carga directamente
if (!function_exists('conexion')) {
    require_once "main.php";
}

$conexion = conexion();

// 1. Consultar Pedidos (Cabecera)
$sql = "SELECT p.*, c.nombre_cliente, c.apellido_cliente, c.telefono_cliente 
        FROM pedido p 
        INNER JOIN cliente c ON p.id_cliente = c.id_cliente 
        ORDER BY p.fecha DESC LIMIT 60";

$pedidos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// 2. Clasificar en Columnas
$kanban = [
    'Pendiente' => [],
    'Preparacion' => [],
    'Listo' => [],
    'Historial' => []
];

foreach ($pedidos as $key => $p) {
    $id_pedido = $p['id_pedido'];

    // 3. CONSULTA DETALLADA (Con soporte para Promos)
    $sqlDetalles = "SELECT 
                        d.cantidad, 
                        d.precio_unitario, 
                        d.nota,
                        d.id_promo, /* IMPORTANTE: ID para buscar ingredientes */
                        
                        /* Nombre Inteligente: Variante > Producto > Promo */
                        COALESCE(v.nombre_variante, pr.producto_nombre, promo.promo_nombre) as nombre_item,
                        
                        pr.producto_nombre as nombre_padre
                    FROM pedido_detalle d
                    LEFT JOIN producto pr ON d.id_producto = pr.producto_id
                    LEFT JOIN variante_producto vp ON d.id_variante_producto = vp.id_variante_producto
                    LEFT JOIN variante v ON vp.id_variante = v.id_variante
                    LEFT JOIN promociones promo ON d.id_promo = promo.promo_id 
                    WHERE d.id_pedido = '$id_pedido'";

    $detalles = $conexion->query($sqlDetalles)->fetchAll(PDO::FETCH_ASSOC);

    // --- AQUÍ LA MAGIA: Pre-cargamos los ingredientes de las promos ---
    foreach ($detalles as $k => $item) {
        if (!empty($item['id_promo'])) {
            $idPromo = $item['id_promo'];
            $sqlSub = "SELECT pp.cantidad, p.producto_nombre 
                       FROM promocion_productos pp
                       INNER JOIN producto p ON pp.producto_id = p.producto_id
                       WHERE pp.promo_id = '$idPromo'";
            $detalles[$k]['ingredientes'] = $conexion->query($sqlSub)->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    $p['items_detalle'] = $detalles;

    // 4. Asignar estado
    $st = $p['estado_pago'];
    if (stripos($st, 'Rechazado') !== false || stripos($st, 'Cancelado') !== false || stripos($st, 'Entregado') !== false || stripos($st, 'Finalizado') !== false) {
        $kanban['Historial'][] = $p;
    } elseif (stripos($st, 'Listo') !== false) {
        $kanban['Listo'][] = $p;
    } elseif (stripos($st, 'Aprobado') !== false || stripos($st, 'Preparacion') !== false) {
        $kanban['Preparacion'][] = $p;
    } else {
        $kanban['Pendiente'][] = $p;
    }
}
?>

<div class="flex flex-nowrap gap-4 md:gap-6 h-full min-w-full md:min-w-0 pb-2 md:pb-4 snap-x snap-mandatory">

    <div class="flex flex-col w-[85vw] md:w-[360px] shrink-0 h-full rounded-xl bg-gray-200 border border-gray-300 snap-center shadow-inner">
        <div class="p-3 bg-white rounded-t-xl border-b border-gray-200 flex justify-between items-center shadow-sm sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-yellow-400 border border-yellow-500"></span>
                <h3 class="font-bold text-gray-700 text-sm uppercase">Verificar / Caja</h3>
            </div>
            <span class="bg-gray-800 text-white px-2 py-0.5 rounded text-xs font-bold"><?php echo count($kanban['Pendiente']); ?></span>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
            <?php foreach ($kanban['Pendiente'] as $row): ?>
                <div class="bg-white p-3 rounded-xl shadow-sm border-l-4 border-yellow-400 relative group hover:shadow-md transition-all">

                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="flex gap-2 mb-1">
                                <span class="bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded text-[10px] font-bold">#<?php echo $row['id_pedido']; ?></span>
                                <?php if (stripos($row['tipo_orden'], 'llevar') !== false): ?>
                                    <span class="bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded text-[10px] font-bold flex items-center gap-1"><i class="fas fa-shopping-bag"></i> LLEVAR</span>
                                <?php else: ?>
                                    <span class="bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded text-[10px] font-bold flex items-center gap-1"><i class="fas fa-utensils"></i> MESA</span>
                                <?php endif; ?>
                            </div>
                            <h4 class="font-bold text-gray-800 leading-tight"><?php echo $row['nombre_cliente']; ?></h4>
                        </div>
                        <div class="text-right">
                            <span class="block font-black text-gray-900 text-lg"><?php echo number_format($row['precio_total'], 2); ?> Bs</span>
                        </div>
                    </div>

                    <div class="mb-3 p-2 bg-slate-50 rounded border border-slate-100 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Método:</span>
                            <span class="font-bold text-gray-700"><?php echo $row['metodo_pago']; ?></span>
                        </div>
                        <?php if (!empty($row['referencia'])): ?>
                            <div class="flex justify-between mt-1 pt-1 border-t border-slate-200">
                                <span class="text-indigo-500 font-bold">Referencia:</span>
                                <span class="font-black text-indigo-700 text-sm tracking-wider select-all"><?php echo $row['referencia']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <ul class="text-xs text-gray-600 mb-3 space-y-1 pl-1">
                        <?php foreach ($row['items_detalle'] as $item): ?>
                            <li class="flex flex-col">
                                <div class="flex gap-1">
                                    <b class="text-gray-900"><?php echo $item['cantidad']; ?>x</b>
                                    <span><?php echo $item['nombre_item']; ?></span>
                                </div>
                                <?php if (isset($item['ingredientes'])): ?>
                                    <div class="pl-4 text-[10px] text-gray-400 italic">
                                        <?php foreach ($item['ingredientes'] as $ing): ?>
                                            (+ <?php echo $ing['cantidad'] . ' ' . $ing['producto_nombre']; ?>)
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="actEstado(<?php echo $row['id_pedido']; ?>, 'Rechazado')" class="py-2 border border-red-200 text-red-500 text-xs font-bold rounded hover:bg-red-50 transition-colors">Cancelar</button>
                        <button onclick="actEstado(<?php echo $row['id_pedido']; ?>, 'Aprobado')" class="py-2 bg-indigo-600 text-white text-xs font-bold rounded shadow-sm hover:bg-indigo-700 flex items-center justify-center gap-1 transition-colors">
                            <i class="fas fa-check"></i> Aprobar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex flex-col w-[85vw] md:w-[360px] shrink-0 h-full rounded-xl bg-gray-200 border border-gray-300 snap-center shadow-inner">
        <div class="p-3 bg-white rounded-t-xl border-b border-gray-200 flex justify-between items-center shadow-sm sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-orange-500 animate-pulse"></span>
                <h3 class="font-bold text-gray-700 text-sm uppercase">En Cocina</h3>
            </div>
            <span class="bg-gray-800 text-white px-2 py-0.5 rounded text-xs font-bold"><?php echo count($kanban['Preparacion']); ?></span>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
            <?php foreach ($kanban['Preparacion'] as $row): ?>
                <div class="bg-white p-3 rounded-xl shadow-sm border border-orange-200">

                    <div class="flex justify-between items-center mb-2 pb-2 border-b border-gray-100">
                        <h4 class="font-bold text-gray-800">#<?php echo $row['id_pedido']; ?> <?php echo $row['nombre_cliente']; ?></h4>
                        <?php if (stripos($row['tipo_orden'], 'llevar') !== false): ?>
                            <i class="fas fa-shopping-bag text-orange-500 text-lg animate-bounce" title="Para Llevar"></i>
                        <?php else: ?>
                            <i class="fas fa-utensils text-blue-500 text-lg" title="Comer Aquí"></i>
                        <?php endif; ?>
                    </div>

                    <div class="bg-orange-50 rounded-lg p-2 mb-3 border border-orange-100">
                        <ul class="space-y-3"> <?php foreach ($row['items_detalle'] as $item): ?>
                                <li class="flex flex-col text-sm text-gray-800 border-b border-orange-200/50 last:border-0 pb-2 last:pb-0">
                                    <div class="flex items-start gap-2">
                                        <span class="font-black bg-white w-6 h-6 flex items-center justify-center rounded-md border border-orange-200 text-sm shrink-0 mt-0.5 shadow-sm text-orange-800">
                                            <?php echo $item['cantidad']; ?>
                                        </span>

                                        <div class="leading-tight pt-0.5 w-full">
                                            <span class="font-bold block text-gray-800 text-base"><?php echo $item['nombre_item']; ?></span>

                                            <?php if ($item['nombre_item'] != $item['nombre_padre'] && $item['nombre_padre'] && empty($item['id_promo'])): ?>
                                                <span class="block text-[10px] text-gray-500 italic"><?php echo $item['nombre_padre']; ?></span>
                                            <?php endif; ?>

                                            <?php if (!empty($item['nota'])): ?>
                                                <span class="block text-[10px] text-red-600 font-bold bg-red-50 px-1 rounded mt-1 border border-red-100 w-fit">
                                                    <i class="fas fa-comment-dots"></i> <?php echo $item['nota']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if (isset($item['ingredientes'])): ?>
                                        <div class="mt-2 ml-8 pl-2 border-l-2 border-orange-300 bg-white/50 rounded-r-md p-1">
                                            <?php foreach ($item['ingredientes'] as $ing): ?>
                                                <div class="flex items-center gap-1 text-xs text-slate-700 mb-0.5">
                                                    <i class="fas fa-check text-[8px] text-orange-400"></i>
                                                    <span class="font-bold"><?php echo $ing['cantidad']; ?>x</span>
                                                    <span><?php echo $ing['producto_nombre']; ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <button onclick="actEstado(<?php echo $row['id_pedido']; ?>, 'Listo')" class="w-full py-3 bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold rounded-lg shadow-md flex items-center justify-center gap-2 transform active:scale-95 transition-all">
                        <i class="fas fa-bell"></i> MARCAR LISTO
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex flex-col w-[85vw] md:w-[360px] shrink-0 h-full rounded-xl bg-gray-200 border border-gray-300 snap-center shadow-inner">
        <div class="p-3 bg-white rounded-t-xl border-b border-gray-200 flex justify-between items-center shadow-sm sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <h3 class="font-bold text-gray-700 text-sm uppercase">Listo / Despachar</h3>
            </div>
            <span class="bg-gray-800 text-white px-2 py-0.5 rounded text-xs font-bold"><?php echo count($kanban['Listo']); ?></span>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
            <?php foreach ($kanban['Listo'] as $row): ?>
                <div class="bg-white p-4 rounded-xl shadow-sm border-l-4 border-green-500 flex flex-col h-auto">
                    <div class="text-center mb-3">
                        <div class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold mb-1 animate-bounce">
                            ¡ORDEN LISTA!
                        </div>
                        <h4 class="font-black text-gray-800 text-2xl mb-1">#<?php echo $row['id_pedido']; ?></h4>
                        <p class="text-sm font-bold text-gray-600 truncate"><?php echo $row['nombre_cliente']; ?></p>
                    </div>

                    <div class="mt-auto space-y-2">
                        <?php
                        $telefono = preg_replace('/[^0-9]/', '', $row['telefono_cliente']);
                        $mensaje = "Hola " . $row['nombre_cliente'] . ", su pedido #" . $row['id_pedido'] . " ya está LISTO 🍔. ¡Puede retirarlo!";
                        if (stripos($row['tipo_orden'], 'mesa') !== false) $mensaje = "Su pedido #" . $row['id_pedido'] . " está listo para servir a la mesa.";
                        $link_ws = "https://wa.me/" . $telefono . "?text=" . urlencode($mensaje);
                        ?>

                        <?php if ($telefono && strlen($telefono) > 9): ?>
                            <a href="<?php echo $link_ws; ?>" target="_blank" class="flex items-center justify-center w-full py-2 bg-green-50 border border-green-200 text-green-700 hover:bg-green-100 text-sm font-bold rounded-lg gap-2 transition-colors">
                                <i class="fab fa-whatsapp text-lg"></i> Avisar Cliente
                            </a>
                        <?php endif; ?>

                        <button onclick="actEstado(<?php echo $row['id_pedido']; ?>, 'Entregado')" class="w-full py-3 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-md active:scale-95 transition-all">
                            Entregar y Cerrar <i class="fas fa-check ml-1"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="colHistorial" class="hidden flex-col w-[85vw] md:w-80 shrink-0 h-full rounded-xl bg-gray-300 border border-gray-400 snap-center">
        <div class="p-3 bg-gray-100 rounded-t-xl border-b border-gray-300 flex justify-between items-center shadow-sm sticky top-0 z-10">
            <div class="flex items-center gap-2">
                <i class="fas fa-history text-gray-500"></i>
                <h3 class="font-bold text-gray-700 text-sm uppercase">Historial Reciente</h3>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
            <?php foreach ($kanban['Historial'] as $row): ?>
                <?php $esCancelado = (stripos($row['estado_pago'], 'Rechazado') !== false || stripos($row['estado_pago'], 'Cancelado') !== false); ?>

                <div class="bg-white/80 p-3 rounded-lg border border-gray-300 <?php echo $esCancelado ? 'opacity-60 grayscale' : ''; ?>">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-bold text-gray-500">#<?php echo $row['id_pedido']; ?></span>
                        <?php if ($esCancelado): ?>
                            <span class="text-[10px] bg-red-100 text-red-600 px-1 rounded font-bold">CANCELADO</span>
                        <?php else: ?>
                            <span class="text-[10px] bg-green-100 text-green-600 px-1 rounded font-bold">ENTREGADO</span>
                        <?php endif; ?>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-bold text-gray-700 truncate w-32"><?php echo $row['nombre_cliente']; ?></span>
                        <span class="text-xs text-gray-500"><?php echo date("H:i", strtotime($row['fecha'])); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>