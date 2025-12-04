// Archivo: js/dashboard_graficas.js

let chartTrend = null;
let chartHoras = null;
let chartTop = null;
let chartCat = null;

document.addEventListener('DOMContentLoaded', () => {
    // Al cargar, pide estadísticas del MES por defecto
    loadStats('mes', document.querySelector("button[onclick=\"loadStats('mes', this)\"]"));
});

function loadStats(modo, btn = null) {
    // Estilos visuales de botones
    if(btn) {
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('bg-white', 'text-indigo-600', 'shadow-sm');
            b.classList.add('text-slate-500');
        });
        btn.classList.remove('text-slate-500');
        btn.classList.add('bg-white', 'text-indigo-600', 'shadow-sm');
    }

    let fechaInicio, fechaFin;
    const hoy = new Date();

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
        const year = document.getElementById('sel_anio').value;
        const month = document.getElementById('sel_mes').value;
        fechaInicio = `${year}-${month}-01`;
        const ultimoDiaMes = new Date(year, month, 0).getDate();
        fechaFin = `${year}-${month}-${ultimoDiaMes}`;
    } 
    else if (modo === 'anio') {
        const year = document.getElementById('sel_anio').value;
        fechaInicio = `${year}-01-01`;
        fechaFin = `${year}-12-31`;
    }
    else if (modo === 'custom') {
        fechaInicio = document.getElementById('fecha_inicio').value;
        fechaFin = document.getElementById('fecha_fin').value;
    }

    document.getElementById('fecha_inicio').value = fechaInicio;
    document.getElementById('fecha_fin').value = fechaFin;
    
    if(document.getElementById('kpi_rango')) {
        document.getElementById('kpi_rango').innerText = `Rango: ${fechaInicio} al ${fechaFin}`;
    }

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
        console.log("Respuesta cruda del servidor:", text); // MIRA LA CONSOLA SI FALLA

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("EL PHP NO DEVOLVIÓ JSON VÁLIDO. Respuesta:", text);
            // alert("Error grave: El servidor devolvió texto en lugar de datos. Revisa la consola.");
            return;
        }
        
        if(data.error) {
            console.error("Error reportado por PHP:", data.error);
            // alert("Error de base de datos: " + data.error);
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
    const formatUSD = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });
    if(document.getElementById('kpi_ingresos')) document.getElementById('kpi_ingresos').innerText = formatUSD.format(kpi.ingresos);
    if(document.getElementById('kpi_pedidos')) document.getElementById('kpi_pedidos').innerText = kpi.pedidos;
    if(document.getElementById('kpi_ticket')) document.getElementById('kpi_ticket').innerText = formatUSD.format(kpi.ticket);
}

function renderCharts(data) {
    // 1. Trend Chart
    const ctxTrend = document.getElementById('chartTrend');
    if (ctxTrend) {
        if (chartTrend) chartTrend.destroy();
        chartTrend = new Chart(ctxTrend.getContext('2d'), {
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
    const ctxHoras = document.getElementById('chartHoras');
    if (ctxHoras) {
        if (chartHoras) chartHoras.destroy();
        chartHoras = new Chart(ctxHoras.getContext('2d'), {
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
    const ctxTop = document.getElementById('chartTop');
    if (ctxTop) {
        if (chartTop) chartTop.destroy();
        chartTop = new Chart(ctxTop.getContext('2d'), {
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
    const ctxCat = document.getElementById('chartCat');
    if (ctxCat) {
        if (chartCat) chartCat.destroy();
        chartCat = new Chart(ctxCat.getContext('2d'), {
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
    
    // Clientes
    const tbodyClientes = document.getElementById('table_clientes');
    if(tbodyClientes) {
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

    // Promos
    const tbodyPromos = document.getElementById('table_promos');
    if(tbodyPromos) {
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