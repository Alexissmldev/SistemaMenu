    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans bg-slate-50 min-h-screen">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 mb-8">
            <div>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Tablero de Control</h2>
                <p class="text-sm text-slate-500 font-medium">Resumen ejecutivo del rendimiento.</p>
            </div>

            <div class="bg-white p-1.5 rounded-xl shadow-sm border border-slate-200 flex flex-wrap gap-2">
                <a href="index.php?vista=orders_list" class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                    <i class="fas fa-print mr-2"></i> PDF
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 mb-8">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-4">

                <div class="flex bg-slate-100 p-1 rounded-xl w-full lg:w-auto" id="btn-group-periodo">
                    <button onclick="loadStats('semana', this)" class="filter-btn flex-1 lg:flex-none px-6 py-2 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-700">Semana</button>
                    <button onclick="loadStats('mes', this)" class="filter-btn flex-1 lg:flex-none px-6 py-2 text-xs font-bold rounded-lg transition-all bg-white text-indigo-600 shadow-sm">Mes</button>
                    <button onclick="loadStats('anio', this)" class="filter-btn flex-1 lg:flex-none px-6 py-2 text-xs font-bold rounded-lg transition-all text-slate-500 hover:text-slate-700">Año</button>
                </div>

                <div class="flex items-center gap-2 w-full lg:w-auto">
                    <select id="sel_mes" onchange="loadStats('mes')" class="flex-1 rounded-xl border-slate-200 bg-slate-50 text-slate-700 text-xs py-2.5 font-bold focus:ring-indigo-500">
                        <?php $m_act = date('m');
                        foreach (["01" => "Ene", "02" => "Feb", "03" => "Mar", "04" => "Abr", "05" => "May", "06" => "Jun", "07" => "Jul", "08" => "Ago", "09" => "Sep", "10" => "Oct", "11" => "Nov", "12" => "Dic"] as $k => $v) echo "<option value='$k' " . ($k == $m_act ? 'selected' : '') . ">$v</option>"; ?>
                    </select>
                    <select id="sel_anio" onchange="loadStats('anio')" class="rounded-xl border-slate-200 bg-slate-50 text-slate-700 text-xs py-2.5 font-bold focus:ring-indigo-500">
                        <?php $y_act = date('Y');
                        for ($y = 2024; $y <= $y_act; $y++) echo "<option value='$y' " . ($y == $y_act ? 'selected' : '') . ">$y</option>"; ?>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full lg:w-auto border-t lg:border-t-0 lg:border-l border-slate-100 pt-4 lg:pt-0 lg:pl-4">
                    <input type="date" id="fecha_inicio" class="w-full rounded-xl border-slate-200 text-xs py-2">
                    <span class="text-slate-300">/</span>
                    <input type="date" id="fecha_fin" class="w-full rounded-xl border-slate-200 text-xs py-2">
                    <button onclick="loadStats('custom')" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-3 py-2 shadow-sm"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </div>

        <div id="loadingOverlay" class="hidden fixed inset-0 bg-white/50 z-50 flex items-center justify-center backdrop-blur-sm">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-indigo-500"></div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Ingresos</p>
                <h3 class="text-2xl font-black text-slate-800" id="kpi_ingresos">...</h3>
                <p class="text-[10px] text-indigo-500 font-medium mt-2 bg-indigo-50 inline-block px-2 py-1 rounded-md" id="kpi_rango">Cargando...</p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-emerald-500"></div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Pedidos</p>
                <h3 class="text-2xl font-black text-slate-800" id="kpi_pedidos">...</h3>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-orange-500"></div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Ticket Promedio</p>
                <h3 class="text-2xl font-black text-slate-800" id="kpi_ticket">...</h3>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Pedidos Hoy</p>
                <h3 class="text-2xl font-black text-slate-800">
                    <?php
                    require_once "./php/main.php";
                    $conexion = conexion();
                    $hoy = date('Y-m-d');
                    $sql_hoy = "SELECT COUNT(*) FROM pedido WHERE date(fecha) = '$hoy' AND estado_pago != 'Rechazado'";
                    echo $conexion->query($sql_hoy)->fetchColumn();
                    ?>
                </h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8">
            <h3 class="font-bold text-slate-700 mb-6">Evolución de Ventas</h3>
            <div class="relative h-72 w-full"><canvas id="chartTrend"></canvas></div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-8">
            <h3 class="font-bold text-slate-700 mb-6">Horas Pico</h3>
            <div class="relative h-64 w-full"><canvas id="chartHoras"></canvas></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="font-bold text-slate-700 mb-6">Top 5 Productos</h3>
                <div class="relative h-64"><canvas id="chartTop"></canvas></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <h3 class="font-bold text-slate-700 mb-4">Top Categorías</h3>
                <div class="relative h-64"><canvas id="chartCat"></canvas></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-xs font-bold text-slate-500 uppercase">Mejores Clientes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-400 uppercase bg-white border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">Cliente</th>
                                <th class="px-4 py-3 text-right">Total ($)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="table_clientes"></tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-xs font-bold text-slate-500 uppercase">Top Promos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-slate-400 uppercase bg-white border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-3">Promo</th>
                                <th class="px-4 py-3 text-right">Vendidas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="table_promos"></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <script>
        // --- VARIABLES GLOBALES DE CHARTS ---
        let chartTrend, chartHoras, chartTop, chartCat;

        // --- FUNCIÓN PRINCIPAL DE CARGA AJAX ---
        async function loadStats(tipo, btn = null) {
            // UI Loading
            document.getElementById('loadingOverlay').classList.remove('hidden');

            // Cambiar estilo botones
            if (btn) {
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('bg-white', 'text-indigo-600', 'shadow-sm');
                    b.classList.add('text-slate-500', 'hover:text-slate-700');
                });
                btn.classList.remove('text-slate-500', 'hover:text-slate-700');
                btn.classList.add('bg-white', 'text-indigo-600', 'shadow-sm');
            }

            // Preparar datos POST
            const formData = new FormData();
            formData.append('tipo', tipo);
            formData.append('anio', document.getElementById('sel_anio').value);
            formData.append('mes', document.getElementById('sel_mes').value);
            formData.append('inicio', document.getElementById('fecha_inicio').value);
            formData.append('fin', document.getElementById('fecha_fin').value);

            try {
                const res = await fetch('./php/api_stats.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                // 1. ACTUALIZAR KPI
                document.getElementById('kpi_ingresos').innerText = '$' + data.kpi.ingresos;
                document.getElementById('kpi_pedidos').innerText = data.kpi.pedidos;
                document.getElementById('kpi_ticket').innerText = '$' + data.kpi.ticket;
                document.getElementById('kpi_rango').innerText = data.rango;

                // 2. ACTUALIZAR GRÁFICAS
                updateChart(chartTrend, data.chartTrend.labels, data.chartTrend.data);
                updateChart(chartHoras, data.chartHoras.labels, data.chartHoras.data);
                updateChart(chartTop, data.chartTop.labels, data.chartTop.data);
                updateChart(chartCat, data.chartCat.labels, data.chartCat.data);

                // 3. ACTUALIZAR TABLAS
                renderTable('table_clientes', data.listas.clientes, 'cliente');
                renderTable('table_promos', data.listas.promos, 'promo');

            } catch (error) {
                console.error("Error cargando estadísticas:", error);
            } finally {
                document.getElementById('loadingOverlay').classList.add('hidden');
            }
        }

        function updateChart(chart, labels, data) {
            chart.data.labels = labels;
            chart.data.datasets[0].data = data;
            chart.update();
        }

        function renderTable(id, data, tipo) {
            const tbody = document.getElementById(id);
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="2" class="p-4 text-center text-xs text-slate-400">No hay datos</td></tr>`;
                return;
            }
            data.forEach(row => {
                let html = `<tr class="hover:bg-slate-50">`;
                if (tipo === 'cliente') {
                    html += `<td class="px-4 py-3 font-medium text-slate-700">${row.nombre_cliente} ${row.apellido_cliente}</td>
                            <td class="px-4 py-3 text-right font-bold text-green-600">$${parseFloat(row.gastado).toFixed(0)}</td>`;
                } else {
                    html += `<td class="px-4 py-3 font-medium text-slate-700">${row.promo_nombre}</td>
                            <td class="px-4 py-3 text-right font-bold text-slate-700">${row.vendidas}</td>`;
                }
                html += `</tr>`;
                tbody.innerHTML += html;
            });
        }

        // --- INICIALIZACIÓN DE CHARTS AL CARGAR ---
        document.addEventListener("DOMContentLoaded", function() {
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#64748b';

            // 1. TREND
            const ctxTrend = document.getElementById('chartTrend').getContext('2d');
            const gradTrend = ctxTrend.createLinearGradient(0, 0, 0, 300);
            gradTrend.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
            gradTrend.addColorStop(1, 'rgba(79, 70, 229, 0.0)');
            chartTrend = new Chart(ctxTrend, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Ventas ($)',
                        data: [],
                        borderColor: '#4f46e5',
                        backgroundColor: gradTrend,
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [5, 5]
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // 2. HORAS
            chartHoras = new Chart(document.getElementById('chartHoras'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Pedidos',
                        data: [],
                        backgroundColor: '#6366f1',
                        borderRadius: 4,
                        barThickness: 'flex',
                        maxBarThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                autoSkip: false,
                                maxRotation: 90,
                                minRotation: 0,
                                font: {
                                    size: 10
                                }
                            }
                        }
                    }
                }
            });

            // 3. TOP
            chartTop = new Chart(document.getElementById('chartTop'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Cant.',
                        data: [],
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                        barThickness: 15
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
                            grid: {
                                display: false
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // 4. CAT
            chartCat = new Chart(document.getElementById('chartCat'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Ventas',
                        data: [],
                        backgroundColor: ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#3b82f6'],
                        borderRadius: 4,
                        barThickness: 15
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
                            grid: {
                                display: false
                            },
                            ticks: {
                                precision: 0
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // Cargar datos iniciales (Mes actual)
            loadStats('mes');
        });
    </script>