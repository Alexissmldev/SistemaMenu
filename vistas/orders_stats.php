<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans bg-slate-50 min-h-screen">
    
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">Tablero de Control</h2>
            <p class="mt-1 text-sm text-slate-500">Métricas financieras, operativas y estratégicas del negocio.</p>
        </div>
        <div class="flex gap-2">
            <a href="index.php?vista=orders_list" class="inline-flex items-center justify-center px-4 py-2 border border-slate-200 shadow-sm text-sm font-medium rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
            <button onclick="window.print()" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-xl text-white bg-slate-800 hover:bg-slate-900 transition-all">
                <i class="fas fa-print mr-2"></i> Reporte
            </button>
        </div>
    </div>

    <?php
        require_once "./php/main.php";
        $conexion = conexion();

        // --- 1. CONFIGURACIÓN DE FECHAS ---
        $tipo_vista = $_POST['tipo_vista'] ?? 'mes'; 
        $custom_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
        $custom_fin    = $_POST['fecha_fin'] ?? date('Y-m-d');
        $sel_anio = $_POST['sel_anio'] ?? date('Y');
        $sel_mes  = $_POST['sel_mes'] ?? date('m');

        $inicio_sql = ""; $fin_sql = ""; $rango_texto = "";

        if ($tipo_vista == 'custom') {
            $inicio_sql = $custom_inicio . " 00:00:00";
            $fin_sql    = $custom_fin . " 23:59:59";
            $rango_texto = date("d/m", strtotime($custom_inicio)) . " al " . date("d/m", strtotime($custom_fin));
        } elseif ($tipo_vista == 'semana') {
            $lunes = strtotime('monday this week'); $domingo = strtotime('sunday this week');
            $inicio_sql = date('Y-m-d 00:00:00', $lunes); $fin_sql = date('Y-m-d 23:59:59', $domingo);
            $rango_texto = "Esta Semana";
        } elseif ($tipo_vista == 'anio') {
            $inicio_sql = "$sel_anio-01-01 00:00:00"; $fin_sql = "$sel_anio-12-31 23:59:59";
            $rango_texto = "Año $sel_anio";
        } else {
            // Usamos date("t") para mayor compatibilidad en lugar de cal_days_in_month
            $dias_mes = date("t", strtotime("$sel_anio-$sel_mes-01"));
            $inicio_sql = "$sel_anio-$sel_mes-01 00:00:00"; $fin_sql = "$sel_anio-$sel_mes-$dias_mes 23:59:59";
            $rango_texto = "Mes $sel_mes / $sel_anio";
        }

        // --- 2. CONSULTAS SQL (LOGICA DE NEGOCIO) ---

        // A. KPI PRINCIPALES
        $sql_kpi = "SELECT 
                        SUM(total_usd) as total_ingresos,
                        COUNT(*) as total_pedidos,
                        AVG(total_usd) as ticket_promedio
                    FROM pedido 
                    WHERE estado_pago != 'Rechazado' AND fecha BETWEEN '$inicio_sql' AND '$fin_sql'";
        $kpi = $conexion->query($sql_kpi)->fetch();

        // B. MÉTODOS DE PAGO
        $sql_pagos = "SELECT metodo_pago, SUM(total_usd) as total FROM pedido 
                      WHERE estado_pago != 'Rechazado' AND fecha BETWEEN '$inicio_sql' AND '$fin_sql' 
                      GROUP BY metodo_pago";
        $res_pagos = $conexion->query($sql_pagos)->fetchAll(PDO::FETCH_ASSOC);
        $labels_pagos = []; $data_pagos = [];
        foreach($res_pagos as $p){ $labels_pagos[] = $p['metodo_pago']; $data_pagos[] = $p['total']; }

        // C. TOP PRODUCTOS ESTRELLA
        $sql_top_prod = "SELECT p.producto_nombre, SUM(d.cantidad) as cantidad_total 
                         FROM pedido_detalle d
                         INNER JOIN pedido ped ON d.id_pedido = ped.id_pedido
                         LEFT JOIN producto p ON d.id_producto = p.producto_id
                         WHERE ped.estado_pago != 'Rechazado' 
                           AND ped.fecha BETWEEN '$inicio_sql' AND '$fin_sql'
                           AND d.id_promo IS NULL 
                         GROUP BY p.producto_id 
                         ORDER BY cantidad_total DESC LIMIT 5";
        $res_top = $conexion->query($sql_top_prod)->fetchAll(PDO::FETCH_ASSOC);
        $labels_top = []; $data_top = [];
        foreach($res_top as $t){ $labels_top[] = substr($t['producto_nombre'],0,20); $data_top[] = $t['cantidad_total']; }

        // D. PICOS DE VENTA (HORAS)
        $sql_horas = "SELECT HOUR(fecha) as hora, COUNT(*) as transacciones 
                      FROM pedido 
                      WHERE estado_pago != 'Rechazado' AND fecha BETWEEN '$inicio_sql' AND '$fin_sql'
                      GROUP BY hora ORDER BY hora ASC";
        $res_horas = $conexion->query($sql_horas)->fetchAll(PDO::FETCH_KEY_PAIR);
        $labels_horas = range(0, 23); 
        $data_horas = [];
        foreach($labels_horas as $h){ $data_horas[] = $res_horas[$h] ?? 0; }

        // E. TIPO DE CONSUMO
        $sql_tipo = "SELECT tipo_orden, COUNT(*) as cantidad FROM pedido 
                     WHERE estado_pago != 'Rechazado' AND fecha BETWEEN '$inicio_sql' AND '$fin_sql' 
                     GROUP BY tipo_orden";
        $res_tipo = $conexion->query($sql_tipo)->fetchAll(PDO::FETCH_ASSOC);
        $labels_tipo = []; $data_tipo = [];
        foreach($res_tipo as $row){ $labels_tipo[] = $row['tipo_orden']; $data_tipo[] = $row['cantidad']; }

        // F. TOP CLIENTES
        $sql_clientes = "SELECT c.nombre_cliente, c.apellido_cliente, COUNT(p.id_pedido) as compras, SUM(p.total_usd) as gastado
                         FROM pedido p
                         INNER JOIN cliente c ON p.id_cliente = c.id_cliente
                         WHERE p.estado_pago != 'Rechazado' AND p.fecha BETWEEN '$inicio_sql' AND '$fin_sql'
                         GROUP BY p.id_cliente
                         ORDER BY gastado DESC LIMIT 5";
        $top_clientes = $conexion->query($sql_clientes)->fetchAll(PDO::FETCH_ASSOC);

        // G. EFECTIVIDAD PROMOCIONES (CORREGIDO)
        // Se cambió d.id_pedido_detalle por d.id_detalle
        $sql_promos = "SELECT pro.promo_nombre, COUNT(d.id_detalle) as vendidas
                       FROM pedido_detalle d
                       INNER JOIN pedido ped ON d.id_pedido = ped.id_pedido
                       INNER JOIN promociones pro ON d.id_promo = pro.promo_id
                       WHERE ped.estado_pago != 'Rechazado' AND ped.fecha BETWEEN '$inicio_sql' AND '$fin_sql'
                       GROUP BY d.id_promo
                       ORDER BY vendidas DESC LIMIT 5";
        $top_promos = $conexion->query($sql_promos)->fetchAll(PDO::FETCH_ASSOC);
        
        // H. VENTAS POR CATEGORÍA
        $sql_cat = "SELECT cat.categoria_nombre, SUM(d.cantidad) as total_items
                    FROM pedido_detalle d
                    INNER JOIN pedido ped ON d.id_pedido = ped.id_pedido
                    INNER JOIN producto p ON d.id_producto = p.producto_id
                    INNER JOIN categoria cat ON p.categoria_id = cat.categoria_id
                    WHERE ped.estado_pago != 'Rechazado' AND ped.fecha BETWEEN '$inicio_sql' AND '$fin_sql'
                    GROUP BY cat.categoria_id";
        try {
            $res_cat = $conexion->query($sql_cat)->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e) { $res_cat = []; }
        
        $labels_cat = []; $data_cat = [];
        foreach($res_cat as $c){ $labels_cat[] = $c['categoria_nombre']; $data_cat[] = $c['total_items']; }

    ?>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <form id="filterForm" method="POST" action="index.php?vista=orders_stats">
            <input type="hidden" name="tipo_vista" id="tipo_vista" value="<?php echo $tipo_vista; ?>">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Vistas Rápidas</label>
                    <div class="flex flex-wrap gap-3 items-start">
                        <div class="flex bg-slate-100 p-1 rounded-xl">
                            <button type="button" onclick="cambiarVista('semana')" class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo $tipo_vista == 'semana' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">Semana</button>
                            <button type="button" onclick="cambiarVista('mes')" class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo $tipo_vista == 'mes' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">Mes</button>
                            <button type="button" onclick="cambiarVista('anio')" class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo $tipo_vista == 'anio' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'; ?>">Año</button>
                        </div>
                        <select name="sel_mes" onchange="cambiarVista('mes')" class="rounded-xl border-slate-200 bg-slate-50 text-slate-700 text-xs py-2 focus:ring-indigo-500">
                            <?php foreach(["01"=>"Ene","02"=>"Feb","03"=>"Mar","04"=>"Abr","05"=>"May","06"=>"Jun","07"=>"Jul","08"=>"Ago","09"=>"Sep","10"=>"Oct","11"=>"Nov","12"=>"Dic"] as $k=>$v) echo "<option value='$k' ".($k==$sel_mes?'selected':'').">$v</option>"; ?>
                        </select>
                        <select name="sel_anio" onchange="cambiarVista('anio')" class="rounded-xl border-slate-200 bg-slate-50 text-slate-700 text-xs py-2 focus:ring-indigo-500">
                            <?php for($y=2024;$y<=date('Y');$y++) echo "<option value='$y' ".($y==$sel_anio?'selected':'').">$y</option>"; ?>
                        </select>
                    </div>
                </div>
                <div class="xl:border-l xl:border-slate-100 xl:pl-8">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Rango Personalizado</label>
                    <div class="flex items-center gap-2">
                        <input type="date" name="fecha_inicio" value="<?php echo $custom_inicio; ?>" class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:ring-indigo-500">
                        <span class="text-slate-400">-</span>
                        <input type="date" name="fecha_fin" value="<?php echo $custom_fin; ?>" class="block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:ring-indigo-500">
                        <button type="button" onclick="cambiarVista('custom')" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2 shadow-md"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center justify-between relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ingresos Totales</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">$<?php echo number_format($kpi['total_ingresos'] ?? 0, 2); ?></h3>
                <p class="text-[10px] text-green-600 font-bold bg-green-50 inline-block px-2 py-0.5 rounded-full mt-2">
                    <i class="fas fa-calendar-alt mr-1"></i> <?php echo $rango_texto; ?>
                </p>
            </div>
            <div class="p-4 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:scale-110 transition-transform">
                <i class="fas fa-wallet text-3xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center justify-between relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ticket Promedio</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1">$<?php echo number_format($kpi['ticket_promedio'] ?? 0, 2); ?></h3>
                <p class="text-[10px] text-slate-400 mt-2">Promedio de gasto por cliente</p>
            </div>
            <div class="p-4 bg-orange-50 rounded-2xl text-orange-600 group-hover:scale-110 transition-transform">
                <i class="fas fa-receipt text-3xl"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex items-center justify-between relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transacciones</p>
                <h3 class="text-3xl font-black text-slate-800 mt-1"><?php echo number_format($kpi['total_pedidos'] ?? 0); ?></h3>
                <p class="text-[10px] text-slate-400 mt-2">Pedidos completados</p>
            </div>
            <div class="p-4 bg-blue-50 rounded-2xl text-blue-600 group-hover:scale-110 transition-transform">
                <i class="fas fa-shopping-bag text-3xl"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-bold text-slate-700 uppercase mb-6 border-b pb-2">Métodos de Pago</h3>
            <div class="relative h-64">
                <?php if(empty($data_pagos)): ?>
                    <div class="flex h-full items-center justify-center text-slate-400 text-xs">Sin datos</div>
                <?php else: ?>
                    <canvas id="chartPagos"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 lg:col-span-2">
            <h3 class="text-sm font-bold text-slate-700 uppercase mb-6 border-b pb-2 flex justify-between">
                <span>Horas Pico (Operatividad)</span>
                <span class="text-xs text-slate-400 normal-case">00:00 - 23:00 Hrs</span>
            </h3>
            <div class="relative h-64 w-full">
                <canvas id="chartHoras"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-bold text-slate-700 uppercase mb-6 border-b pb-2">Productos Estrella (Top 5)</h3>
            <div class="relative h-72">
                 <?php if(empty($data_top)): ?>
                    <div class="flex h-full items-center justify-center text-slate-400 text-xs">Sin ventas de productos base</div>
                <?php else: ?>
                    <canvas id="chartTop"></canvas>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-sm font-bold text-slate-700 uppercase mb-6 border-b pb-2">Rendimiento por Categoría</h3>
            <div class="relative h-72">
                <?php if(empty($data_cat)): ?>
                    <div class="flex h-full items-center justify-center text-slate-400 text-xs">Requiere configurar categorías</div>
                <?php else: ?>
                    <canvas id="chartCat"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden col-span-1 lg:col-span-1">
            <div class="p-4 bg-slate-50 border-b border-slate-200">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Clientes VIP</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-400 uppercase bg-white border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3 text-center">Cant.</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($top_clientes as $c): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-700 truncate max-w-[120px]">
                                <?php echo $c['nombre_cliente']." ".$c['apellido_cliente']; ?>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-500"><?php echo $c['compras']; ?></td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">$<?php echo number_format($c['gastado'],0); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($top_clientes)) echo "<tr><td colspan='3' class='p-4 text-center text-xs text-slate-400'>No hay datos</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden col-span-1 lg:col-span-1">
            <div class="p-4 bg-slate-50 border-b border-slate-200">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Top Promociones</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-slate-400 uppercase bg-white border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-3">Promo</th>
                            <th class="px-4 py-3 text-right">Vendidas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach($top_promos as $pro): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium text-slate-700">
                                <span class="bg-orange-100 text-orange-700 text-[10px] px-2 py-0.5 rounded-full font-bold mr-1">PROMO</span>
                                <?php echo $pro['promo_nombre']; ?>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-700"><?php echo $pro['vendidas']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                         <?php if(empty($top_promos)) echo "<tr><td colspan='2' class='p-4 text-center text-xs text-slate-400'>No hay promos vendidas</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col items-center justify-center">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Modalidad de Consumo</h3>
             <div class="relative h-40 w-full">
                <?php if(empty($data_tipo)): ?>
                    <div class="flex h-full items-center justify-center text-slate-400 text-xs">Sin datos</div>
                <?php else: ?>
                    <canvas id="chartTipo"></canvas>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
    function cambiarVista(t){ 
        document.getElementById('tipo_vista').value=t; 
        document.getElementById('filterForm').submit(); 
    }

    // Configuración Global de Charts
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';
    
    // 1. CHART PAGOS
    <?php if(!empty($data_pagos)): ?>
    new Chart(document.getElementById('chartPagos'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($labels_pagos); ?>,
            datasets: [{
                data: <?php echo json_encode($data_pagos); ?>,
                backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#6366f1'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, usePointStyle: true } }
            },
            cutout: '70%'
        }
    });
    <?php endif; ?>

    // 2. CHART HORAS PICO
    new Chart(document.getElementById('chartHoras'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels_horas); ?>,
            datasets: [{
                label: 'Transacciones',
                data: <?php echo json_encode($data_horas); ?>,
                backgroundColor: (ctx) => {
                    const v = ctx.raw;
                    return v > 5 ? '#4f46e5' : '#c7d2fe';
                },
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                x: { grid: { display: false } }
            }
        }
    });

    // 3. CHART TOP PRODUCTOS
    <?php if(!empty($data_top)): ?>
    new Chart(document.getElementById('chartTop'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels_top); ?>,
            datasets: [{
                label: 'Cantidad Vendida',
                data: <?php echo json_encode($data_top); ?>,
                backgroundColor: '#10b981',
                borderRadius: 4,
                barThickness: 20
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true } }
        }
    });
    <?php endif; ?>

    // 4. CHART CATEGORIAS
    <?php if(!empty($data_cat)): ?>
    new Chart(document.getElementById('chartCat'), {
        type: 'polarArea',
        data: {
            labels: <?php echo json_encode($labels_cat); ?>,
            datasets: [{
                data: <?php echo json_encode($data_cat); ?>,
                backgroundColor: ['rgba(79, 70, 229, 0.6)', 'rgba(16, 185, 129, 0.6)', 'rgba(245, 158, 11, 0.6)', 'rgba(239, 68, 68, 0.6)']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { boxWidth: 10 } } },
            scales: { r: { ticks: { display: false } } }
        }
    });
    <?php endif; ?>

    // 5. CHART TIPO CONSUMO
    <?php if(!empty($data_tipo)): ?>
    new Chart(document.getElementById('chartTipo'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($labels_tipo); ?>,
            datasets: [{
                data: <?php echo json_encode($data_tipo); ?>,
                backgroundColor: ['#3b82f6', '#f97316']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } }
        }
    });
    <?php endif; ?>

</script>