// Archivo: js/dashboard_graficas.js

// --- VARIABLES GLOBALES (Usando window para evitar conflictos de redeclaración) ---
window.chartTrend = window.chartTrend || null;
window.chartHoras = window.chartHoras || null;
window.chartTop   = window.chartTop   || null;
window.chartCat   = window.chartCat   || null;

document.addEventListener('DOMContentLoaded', () => {
    // Busca el botón del mes para simular el click y cargar datos iniciales
    // Usamos ?. para evitar error si el botón no existe
    const btnMes = document.querySelector("button[onclick=\"loadStats('mes', this)\"]");
    loadStats('mes', btnMes);
});

function loadStats(modo, btn = null) {
    // 1. Estilos visuales de botones (Solo si el botón existe y hay botones de filtro)
    if(btn) {
        const botones = document.querySelectorAll('.filter-btn');
        if (botones.length > 0) {
            botones.forEach(b => {
                b.classList.remove('bg-white', 'text-indigo-600', 'shadow-sm');
                b.classList.add('text-slate-500');
            });
            btn.classList.remove('text-slate-500');
            btn.classList.add('bg-white', 'text-indigo-600', 'shadow-sm');
        }
    }

    let fechaInicio, fechaFin;
    const hoy = new Date();
    
    // Función auxiliar segura para obtener valor por ID sin que explote si no existe
    const getVal = (id) => {
        const el = document.getElementById(id);
        return el ? el.value : null;
    };

    // Lógica de fechas
    if (modo === 'hoy') {
        fechaInicio = formatDate(hoy);
        fechaFin = formatDate(hoy);
    }
    else if (modo === 'semana') {
        const day = hoy.getDay() || 7; 
        const lunes = new Date(hoy);
        lunes.setHours(-24 * (day - 1)); 
        fechaInicio = formatDate(lunes);
        const domingo = new Date(lunes);
        domingo.setDate(lunes.getDate() + 6);
        fechaFin = formatDate(domingo);
    } 
    else if (modo === 'mes') {
        // Intenta obtener del select, si no existe usa el mes actual
        const year = getVal('sel_anio') || hoy.getFullYear();
        const month = getVal('sel_mes') || (hoy.getMonth() + 1); 
        
        fechaInicio = `${year}-${String(month).padStart(2,'0')}-01`;
        const ultimoDiaMes = new Date(year, month, 0).getDate();
        fechaFin = `${year}-${String(month).padStart(2,'0')}-${ultimoDiaMes}`;
    } 
    else if (modo === 'anio') {
        const year = getVal('sel_anio') || hoy.getFullYear();
        fechaInicio = `${year}-01-01`;
        fechaFin = `${year}-12-31`;
    }
    else if (modo === 'custom') {
        fechaInicio = getVal('fecha_inicio');
        fechaFin = getVal('fecha_fin');
        
        // Si es custom y el usuario no ha puesto fechas, no hacemos nada
        if(!fechaInicio || !fechaFin) return; 
    }

    // Actualizar inputs visibles solo si existen en el DOM
    const inputInicio = document.getElementById('fecha_inicio');
    const inputFin = document.getElementById('fecha_fin');
    if(inputInicio) inputInicio.value = fechaInicio;
    if(inputFin) inputFin.value = fechaFin;
    
    const labelRango = document.getElementById('kpi_rango');
    if(labelRango) {
        labelRango.innerText = `Rango: ${fechaInicio} al ${fechaFin}`;
    }

    // Llamar al backend
    fetchData(fechaInicio, fechaFin);
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

async function fetchData(inicio, fin) {
    const overlay = document.getElementById('loadingOverlay');
    if(overlay) overlay.classList.remove('hidden');

    try {
        console.log("Enviando petición a PHP:", { inicio, fin });

        const response = await fetch('php/api_estadisticas_dashboard.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ inicio, fin })
        });

        const text = await response.text();
        // console.log("Respuesta servidor:", text); // Descomentar para depurar

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("EL PHP NO DEVOLVIÓ JSON VÁLIDO. Respuesta:", text);
            return;
        }
        
        if(data.error) {
            console.error("Error reportado por PHP:", data.error);
            return;
        }

        renderKPIs(data.kpi);
        renderCharts(data);
        renderTables(data);

    } catch (error) {
        console.error("Error de red o fetch:", error);
    } finally {
        if(overlay) overlay.classList.add('hidden');
    }
}

function renderKPIs(kpi) {
    if(!kpi) return;
    const formatUSD = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
    
    const elIngresos = document.getElementById('kpi_ingresos');
    const elPedidos = document.getElementById('kpi_pedidos');
    const elTicket = document.getElementById('kpi_ticket');

    if(elIngresos) elIngresos.innerText = formatUSD.format(kpi.ingresos);
    if(elPedidos) elPedidos.innerText = kpi.pedidos;
    if(elTicket) elTicket.innerText = formatUSD.format(kpi.ticket);
}

function renderCharts(data) {
    // Helper para crear/actualizar gráficas de forma segura
    const createChart = (canvasId, globalVarName, config) => {
        const ctx = document.getElementById(canvasId);
        if (ctx) {
            // Destruir instancia anterior si existe en window
            if (window[globalVarName] instanceof Chart) {
                window[globalVarName].destroy();
            }
            // Crear nueva instancia y guardarla en window
            window[globalVarName] = new Chart(ctx.getContext('2d'), config);
        }
    };

    // 1. Trend Chart
    if(data.trend) {
        createChart('chartTrend', 'chartTrend', {
            type: 'line',
            data: {
                labels: data.trend.map(d => d.dia),
                datasets: [{
                    label: 'Ventas (USD)',
                    data: data.trend.map(d => d.venta),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // 2. Horas Pico
    if(data.hours) {
        createChart('chartHoras', 'chartHoras', {
            type: 'bar',
            data: {
                labels: data.hours.map(d => d.hora + ':00'),
                datasets: [{
                    label: 'Pedidos',
                    data: data.hours.map(d => d.cantidad),
                    backgroundColor: '#10b981',
                    borderRadius: 4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // 3. Top Productos
    if(data.products) {
        createChart('chartTop', 'chartTop', {
            type: 'bar',
            indexAxis: 'y',
            data: {
                labels: data.products.map(d => d.producto_nombre),
                datasets: [{
                    label: 'Unidades',
                    data: data.products.map(d => d.cant),
                    backgroundColor: '#f97316',
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // 4. Categorías
    if(data.categories) {
        createChart('chartCat', 'chartCat', {
            type: 'doughnut',
            data: {
                labels: data.categories.map(d => d.categoria),
                datasets: [{
                    data: data.categories.map(d => d.cant),
                    backgroundColor: ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#3b82f6'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }
}

function renderTables(data) {
    const formatUSD = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
    
    // Tabla Clientes
    const tbodyClientes = document.getElementById('table_clientes');
    if(tbodyClientes && data.clients) {
        tbodyClientes.innerHTML = '';
        if(data.clients.length > 0) {
            data.clients.forEach(c => {
                tbodyClientes.innerHTML += `
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-700">${c.cliente_nombre}</td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600">${formatUSD.format(c.gastado)}</td>
                    </tr>`;
            });
        } else {
            tbodyClientes.innerHTML = '<tr><td colspan="2" class="p-4 text-center text-slate-400">Sin datos</td></tr>';
        }
    }

    // Tabla Promos
    const tbodyPromos = document.getElementById('table_promos');
    if(tbodyPromos && data.promos) {
        tbodyPromos.innerHTML = '';
        if(data.promos.length > 0) {
            data.promos.forEach(p => {
                tbodyPromos.innerHTML += `
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-700">${p.producto_nombre}</td>
                        <td class="px-4 py-3 text-right font-bold text-indigo-600">${p.cant}</td>
                    </tr>`;
            });
        } else {
            tbodyPromos.innerHTML = '<tr><td colspan="2" class="p-4 text-center text-slate-400">Sin promos vendidas</td></tr>';
        }
    }
}