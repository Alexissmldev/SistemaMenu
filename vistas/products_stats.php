<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Reporte de Productos</h2>
            <p class="mt-1 text-sm text-gray-500">Analiza el volumen de venta y ganancias por ítem.</p>
        </div>
        <div class="flex gap-2">
            <a href="index.php?vista=orders_stats" class="inline-flex items-center justify-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-all">
                <i class="fas fa-chart-line mr-2"></i> Ir a Financiero
            </a>
            <a href="index.php?vista=orders_list" class="inline-flex items-center justify-center px-4 py-2 border border-gray-200 shadow-sm text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>

    <?php
    require_once "./php/main.php";
    $conexion = conexion();

    // --- 1. LÓGICA DE FILTROS DE FECHA ---
    $tipo_vista = $_POST['tipo_vista'] ?? 'mes';

    // Si el usuario elige "Día específico" (usando el rango custom con la misma fecha inicio/fin)
    $custom_inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
    $custom_fin    = $_POST['fecha_fin'] ?? date('Y-m-d');

    $sel_anio = $_POST['sel_anio'] ?? date('Y');
    $sel_mes  = $_POST['sel_mes'] ?? date('m');

    $inicio_sql = "";
    $fin_sql = "";
    $periodo_txt = "";

    if ($tipo_vista == 'custom') {
        $inicio_sql = $custom_inicio . " 00:00:00";
        $fin_sql    = $custom_fin . " 23:59:59";
        if ($custom_inicio == $custom_fin) {
            $periodo_txt = "Día: " . date("d/m/Y", strtotime($custom_inicio));
        } else {
            $periodo_txt = "del " . date("d/m", strtotime($custom_inicio)) . " al " . date("d/m", strtotime($custom_fin));
        }
    } elseif ($tipo_vista == 'semana') {
        $inicio_sql = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $fin_sql = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $periodo_txt = "Esta Semana";
    } elseif ($tipo_vista == 'anio') {
        $inicio_sql = "$sel_anio-01-01 00:00:00";
        $fin_sql = "$sel_anio-12-31 23:59:59";
        $periodo_txt = "Año $sel_anio";
    } else {
        // Mes
        $dias = cal_days_in_month(CAL_GREGORIAN, $sel_mes, $sel_anio);
        $inicio_sql = "$sel_anio-$sel_mes-01 00:00:00";
        $fin_sql = "$sel_anio-$sel_mes-$dias 23:59:59";
        $periodo_txt = "Mes $sel_mes / $sel_anio";
    }

    // --- 2. CONSULTA SQL AGRUPADA POR PRODUCTO ---
    // Obtenemos: Nombre, Cantidad de veces que aparece (Frecuencia) y Total Dinero
    $sql_prod = "SELECT 
                        pr.producto_nombre,
                        COUNT(pd.id_producto) as cantidad_vendida,
                        SUM(pd.id_monto) as total_dinero
                     FROM pedido_detalle pd
                     INNER JOIN pedido p ON pd.id_pedido = p.id_pedido
                     INNER JOIN producto pr ON pd.id_producto = pr.producto_id
                     WHERE p.estado_pago != 'Rechazado' 
                     AND p.fecha BETWEEN :inicio AND :fin
                     GROUP BY pd.id_producto 
                     ORDER BY cantidad_vendida DESC"; // Ordenado por cantidad por defecto

    $stmt = $conexion->prepare($sql_prod);
    $stmt->execute([':inicio' => $inicio_sql, ':fin' => $fin_sql]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --- 3. PREPARAR DATOS PARA GRÁFICAS (TOP 5) ---
    // Gráfica de Cantidad (Volumen)
    $labels_qty = [];
    $data_qty = [];
    // Gráfica de Dinero (Ingresos)
    $labels_money = [];
    $data_money = [];

    // Clonamos array para ordenar por dinero para la segunda gráfica
    $productos_por_dinero = $productos;
    usort($productos_por_dinero, function ($a, $b) {
        return $b['total_dinero'] <=> $a['total_dinero'];
    });

    $i = 0;
    foreach ($productos as $p) {
        if ($i < 5) {
            $labels_qty[] = substr($p['producto_nombre'], 0, 15);
            $data_qty[] = $p['cantidad_vendida'];
        }
        $i++;
    }
    $i = 0;
    foreach ($productos_por_dinero as $p) {
        if ($i < 5) {
            $labels_money[] = substr($p['producto_nombre'], 0, 15);
            $data_money[] = $p['total_dinero'];
        }
        $i++;
    }
    ?>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
        <form id="filterForm" method="POST" action="">
            <input type="hidden" name="tipo_vista" id="tipo_vista" value="<?php echo $tipo_vista; ?>">
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Vistas Rápidas</label>
                    <div class="flex flex-wrap gap-3 items-start">
                        <div class="flex bg-gray-100 p-1.5 rounded-xl">
                            <button type="button" onclick="cambiarVista('semana')" class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo $tipo_vista == 'semana' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'; ?>">Semana</button>
                            <button type="button" onclick="cambiarVista('mes')" class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo $tipo_vista == 'mes' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'; ?>">Mes</button>
                            <button type="button" onclick="cambiarVista('anio')" class="px-4 py-2 text-xs font-bold rounded-lg transition-all <?php echo $tipo_vista == 'anio' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'; ?>">Año</button>
                        </div>
                        <select name="sel_mes" onchange="cambiarVista('mes')" class="rounded-xl border-gray-200 bg-gray-50 text-gray-700 text-xs py-2"><?php foreach (["01" => "Ene", "02" => "Feb", "03" => "Mar", "04" => "Abr", "05" => "May", "06" => "Jun", "07" => "Jul", "08" => "Ago", "09" => "Sep", "10" => "Oct", "11" => "Nov", "12" => "Dic"] as $k => $v) echo "<option value='$k' " . ($k == $sel_mes ? 'selected' : '') . ">$v</option>"; ?></select>
                        <select name="sel_anio" onchange="cambiarVista('anio')" class="rounded-xl border-gray-200 bg-gray-50 text-gray-700 text-xs py-2"><?php for ($y = 2024; $y <= date('Y'); $y++) echo "<option value='$y' " . ($y == $sel_anio ? 'selected' : '') . ">$y</option>"; ?></select>
                    </div>
                </div>

                <div class="xl:border-l xl:border-gray-100 xl:pl-8">
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">
                        <i class="fas fa-calendar-day mr-1"></i> Rango / Día Específico
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="date" name="fecha_inicio" value="<?php echo $custom_inicio; ?>" class="block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:ring-indigo-500">
                        <span class="text-gray-400">-</span>
                        <input type="date" name="fecha_fin" value="<?php echo $custom_fin; ?>" class="block w-full rounded-xl border-gray-200 text-sm shadow-sm focus:ring-indigo-500">
                        <button type="button" onclick="cambiarVista('custom')" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2 shadow-md transition-colors"><i class="fas fa-search"></i></button>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1">*Para ver un solo día, pon la misma fecha en ambos campos.</p>
                </div>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-cubes text-blue-500"></i> Top 5 Por Volumen (Cantidad)
            </h3>
            <div class="relative w-full h-64">
                <canvas id="chartQty"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-dollar-sign text-green-500"></i> Top 5 Por Ingresos ($)
            </h3>
            <div class="relative w-full h-64">
                <canvas id="chartMoney"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Detalle de Ventas</h3>
                <p class="text-xs text-gray-500"><?php echo $periodo_txt; ?></p>
            </div>

            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="buscadorProductos" onkeyup="filtrarTabla()" placeholder="Buscar producto..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-200 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-indigo-500 sm:text-sm transition-colors">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left" id="tablaProductos">
                <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3 cursor-pointer hover:bg-gray-100" onclick="ordenarTabla(0)">Producto <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-center cursor-pointer hover:bg-gray-100" onclick="ordenarTabla(1)">Cantidad Vendida <i class="fas fa-sort ml-1"></i></th>
                        <th class="px-6 py-3 text-right cursor-pointer hover:bg-gray-100" onclick="ordenarTabla(2)">Total ($) <i class="fas fa-sort ml-1"></i></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (count($productos) > 0): foreach ($productos as $p): ?>
                            <tr class="hover:bg-gray-50 transition-colors fila-producto">
                                <td class="px-6 py-4 font-medium text-gray-900 nombre-prod">
                                    <?php echo $p['producto_nombre']; ?>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo $p['cantidad_vendida']; ?> un.
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-emerald-600">
                                    $<?php echo number_format($p['total_dinero'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach;
                    else: ?>
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">No hay ventas en este rango de fechas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // 1. Envío de filtros
    function cambiarVista(t) {
        document.getElementById('tipo_vista').value = t;
        document.getElementById('filterForm').submit();
    }

    // 2. Buscador en Tabla (Javascript Puro)
    function filtrarTabla() {
        let input = document.getElementById('buscadorProductos');
        let filter = input.value.toUpperCase();
        let tr = document.querySelectorAll('.fila-producto');

        tr.forEach(fila => {
            let td = fila.querySelector('.nombre-prod');
            if (td) {
                let txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    fila.style.display = "";
                } else {
                    fila.style.display = "none";
                }
            }
        });
    }

    // 3. Configuración Gráficas
    const ctxQty = document.getElementById('chartQty').getContext('2d');
    new Chart(ctxQty, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels_qty); ?>,
            datasets: [{
                label: 'Unidades',
                data: <?php echo json_encode($data_qty); ?>,
                backgroundColor: '#3b82f6',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });

    const ctxMoney = document.getElementById('chartMoney').getContext('2d');
    new Chart(ctxMoney, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labels_money); ?>,
            datasets: [{
                label: 'Ingresos USD',
                data: <?php echo json_encode($data_money); ?>,
                backgroundColor: '#10b981',
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: v => '$' + v
                    }
                }
            }
        }
    });
</script>