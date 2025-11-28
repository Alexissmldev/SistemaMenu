


<?php
    require_once "./php/main.php";

    $id = (isset($_GET['order_id'])) ? $_GET['order_id'] : 0;
    $id = limpiar_cadena($id);
    $conexion = conexion();

    // 1. CONSULTA CABECERA
    $query_pedido = "SELECT p.*, c.nombre_cliente, c.apellido_cliente, c.telefono_cliente, c.cedula_cliente 
                     FROM pedido p 
                     INNER JOIN cliente c ON p.id_cliente = c.id_cliente 
                     WHERE p.id_pedido = '$id'";
    $pedido = $conexion->query($query_pedido)->fetch();

    if(!$pedido){
        echo '<div class="flex items-center justify-center h-screen text-slate-500 font-bold">Pedido no encontrado</div>';
        exit();
    }

    // 2. CONSULTA DETALLES
    $sql_detalle = "SELECT 
                        d.cantidad, d.precio_unitario, d.nota, d.id_promo,
                        COALESCE(v.nombre_variante, pr.producto_nombre, promo.promo_nombre) as nombre_item,
                        pr.producto_nombre as nombre_padre
                    FROM pedido_detalle d
                    LEFT JOIN producto pr ON d.id_producto = pr.producto_id
                    LEFT JOIN variante_producto vp ON d.id_variante_producto = vp.id_variante_producto
                    LEFT JOIN variante v ON vp.id_variante = v.id_variante
                    LEFT JOIN promociones promo ON d.id_promo = promo.promo_id
                    WHERE d.id_pedido = '$id'";
    $detalles = $conexion->query($sql_detalle)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="h-screen flex flex-col bg-slate-50 font-sans overflow-hidden">

    <div class="h-16 bg-white border-b border-slate-200 px-6 flex justify-between items-center shrink-0 z-20 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="index.php?vista=orders_list" class="w-9 h-9 flex items-center justify-center bg-slate-100 text-slate-500 rounded-lg hover:bg-slate-200 transition-colors">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-black text-slate-800 leading-none">Pedido #<?php echo $pedido['id_pedido']; ?></h1>
                <p class="text-xs text-slate-500 mt-0.5"><?php echo date("d M Y • h:i A", strtotime($pedido['fecha'])); ?></p>
            </div>
        </div>
        
        <div class="flex gap-2">
            <a href="./php/ticket.php?id=<?php echo $id; ?>" target="_blank" class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-bold rounded-lg hover:bg-slate-50 uppercase tracking-wide">
                <i class="fas fa-print mr-1"></i> Ticket
            </a>
            <?php if($pedido['estado_pago'] != "Entregado" && $pedido['estado_pago'] != "Rechazado"): ?>
                <a href="index.php?vista=order_update&order_id=<?php echo $id; ?>" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 uppercase tracking-wide shadow-md">
                    <i class="fas fa-edit mr-1"></i> Estado
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 flex flex-col lg:flex-row overflow-hidden">

        <div class="flex-1 flex flex-col bg-white lg:border-r border-slate-200 relative">
            
            <div class="px-6 py-3 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Productos</h3>
                <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo count($detalles); ?> ÍTEMS</span>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
                <table class="w-full text-left border-collapse">
                    <thead class="sticky top-0 bg-white shadow-sm z-10 text-xs text-slate-400 uppercase font-bold">
                        <tr>
                            <th class="px-6 py-3 w-16 text-center bg-slate-50">Cant</th>
                            <th class="px-4 py-3 bg-slate-50">Descripción</th>
                            <th class="px-6 py-3 text-right bg-slate-50">Precio</th>
                            <th class="px-6 py-3 text-right bg-slate-50">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($detalles as $item): 
                            $total_linea = $item['cantidad'] * $item['precio_unitario'];
                        ?>
                        <tr class="hover:bg-indigo-50/30 transition-colors group">
                            <td class="px-6 py-3 text-center align-top">
                                <span class="inline-block w-7 h-7 leading-7 text-center font-bold text-indigo-700 bg-indigo-50 rounded-md text-xs border border-indigo-100">
                                    <?php echo $item['cantidad']; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-col">
                                    <span class="font-bold text-slate-700 text-sm"><?php echo $item['nombre_item']; ?></span>
                                    
                                    <?php if($item['nombre_item'] != $item['nombre_padre'] && $item['nombre_padre'] && empty($item['id_promo'])): ?>
                                        <span class="text-[10px] text-slate-400">Base: <?php echo $item['nombre_padre']; ?></span>
                                    <?php endif; ?>

                                    <?php if(!empty($item['id_promo'])): ?>
                                        <?php 
                                            $idPromo = $item['id_promo'];
                                            $sqlContenido = "SELECT pp.cantidad, p.producto_nombre FROM promocion_productos pp INNER JOIN producto p ON pp.producto_id = p.producto_id WHERE pp.promo_id = '$idPromo'";
                                            $contenido = $conexion->query($sqlContenido)->fetchAll();
                                        ?>
                                        <div class="mt-1 pl-2 border-l-2 border-orange-200">
                                            <?php foreach($contenido as $sub): ?>
                                                <div class="text-[10px] text-slate-500">
                                                    + <b><?php echo $sub['cantidad']; ?></b> <?php echo $sub['producto_nombre']; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($item['nota'])): ?>
                                        <span class="mt-1 text-[10px] text-red-500 bg-red-50 px-1.5 py-0.5 rounded w-fit">
                                            Nota: <?php echo $item['nota']; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-right text-slate-400 font-mono text-xs align-top">
                                <?php echo number_format($item['precio_unitario'], 2); ?>
                            </td>
                            <td class="px-6 py-3 text-right font-bold text-slate-700 font-mono text-sm align-top">
                                <?php echo number_format($total_linea, 2); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-slate-50 border-t border-slate-200 p-4 shrink-0">
                <div class="flex justify-between items-end">
                    <div class="text-xs text-slate-400">
                        <p>Subtotal: <span class="font-mono text-slate-600"><?php echo number_format($pedido['precio_total'], 2); ?> Bs</span></p>
                        <p class="font-bold text-indigo-600 mt-1">Ref USD: $<?php echo number_format($pedido['total_usd'], 2); ?></p>
                    </div>
                    <div class="text-right">
                        <span class="block text-[10px] uppercase font-bold text-slate-400 mb-1">Total a Pagar</span>
                        <span class="text-3xl font-black text-slate-800 leading-none"><?php echo number_format($pedido['precio_total'], 2); ?> <small class="text-sm font-bold text-slate-500">Bs</small></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-96 bg-slate-50 h-full overflow-y-auto custom-scrollbar p-6 border-l border-slate-200 shadow-inner">
            
            <div class="mb-6">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Estado del Pedido</h4>
                <?php
                    $st = $pedido['estado_pago'];
                    $estilo = "bg-white text-gray-600 border-gray-200";
                    if(stripos($st,'Aprobado')!==false) $estilo="bg-green-100 text-green-700 border-green-200";
                    elseif(stripos($st,'Rechazado')!==false) $estilo="bg-red-100 text-red-700 border-red-200";
                    elseif(stripos($st,'Entregado')!==false) $estilo="bg-blue-100 text-blue-700 border-blue-200";
                    elseif(stripos($st,'Pendiente')!==false || stripos($st,'Verificar')!==false) $estilo="bg-yellow-100 text-yellow-800 border-yellow-200";
                ?>
                <div class="p-4 rounded-xl border <?php echo $estilo; ?> flex items-center justify-between shadow-sm">
                    <span class="font-bold text-sm uppercase"><?php echo $st; ?></span>
                    <?php if(stripos($st,'Aprobado')!==false): ?>
                        <i class="fas fa-check-circle text-xl"></i>
                    <?php else: ?>
                        <i class="fas fa-circle text-xl animate-pulse"></i>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-6 bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-50 pb-2">Cliente</h4>
                
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                        <?php echo strtoupper(substr($pedido['nombre_cliente'], 0, 1)); ?>
                    </div>
                    <div class="overflow-hidden">
                        <p class="font-bold text-slate-800 truncate text-sm" title="<?php echo $pedido['nombre_cliente']." ".$pedido['apellido_cliente']; ?>">
                            <?php echo $pedido['nombre_cliente']." ".$pedido['apellido_cliente']; ?>
                        </p>
                        <p class="text-xs text-slate-400">CI: <?php echo $pedido['cedula_cliente']; ?></p>
                    </div>
                </div>

                <div class="flex justify-between items-center bg-slate-50 p-2 rounded-lg border border-slate-100">
                    <span class="text-xs font-mono font-bold text-slate-600"><?php echo $pedido['telefono_cliente']; ?></span>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $pedido['telefono_cliente']); ?>" target="_blank" class="text-green-600 hover:text-green-700 transition-colors">
                        <i class="fab fa-whatsapp text-lg"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-3 border-b border-slate-50 pb-2">Pago</h4>
                
                <div class="flex justify-between mb-2">
                    <span class="text-xs text-slate-500">Método</span>
                    <span class="text-xs font-bold text-slate-800"><?php echo $pedido['metodo_pago']; ?></span>
                </div>
                
                <div class="flex justify-between mb-3">
                    <span class="text-xs text-slate-500">Tipo</span>
                    <?php if(stripos($pedido['tipo_orden'], 'llevar')!==false): ?>
                        <span class="text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded font-bold uppercase">Para Llevar</span>
                    <?php else: ?>
                        <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-bold uppercase">Mesa</span>
                    <?php endif; ?>
                </div>

                <?php if(!empty($pedido['referencia'])): ?>
                    <div class="mt-3 pt-3 border-t border-slate-100 text-center">
                        <p class="text-[10px] text-slate-400 uppercase font-bold mb-1">Referencia</p>
                        <p class="font-mono text-lg font-black text-slate-800 tracking-widest select-all bg-slate-50 py-1 rounded">
                            <?php echo $pedido['referencia']; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<style>
    /* Ocultar barra de scroll en navegadores Webkit pero permitir scroll */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
</style>