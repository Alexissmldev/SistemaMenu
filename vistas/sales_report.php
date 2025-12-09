<?php
require_once "./php/api_tasa_usd.php";
if (!isset($conexion) || $conexion === null) $conexion = conexion();

$tasa_calculo = ($tasa_usd_num > 0) ? $tasa_usd_num : 1;

// 1. OBTENER TOTALES DEL SISTEMA
$sql = "SELECT 
            IFNULL(SUM(total_usd), 0) as total_venta_usd,
            IFNULL(SUM(CASE WHEN metodo_pago LIKE '%efectivo%' THEN total_usd ELSE 0 END), 0) as sys_efec_usd,
            IFNULL(SUM(CASE WHEN metodo_pago NOT LIKE '%efectivo%' THEN total_usd ELSE 0 END), 0) as sys_dig_usd
        FROM pedido 
        WHERE estado_pago = 'Entregado' AND cierre_id IS NULL";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$datos = $stmt->fetch(PDO::FETCH_ASSOC);

$sys_total_bs  = $datos['total_venta_usd'] * $tasa_calculo;
$sys_total_usd = $datos['total_venta_usd'];

// ==========================================
//  BLOQUE 1: PANTALLA "CAJA EN CERO"
//  Se muestra si no hay dinero que reportar.
// ==========================================
if ($sys_total_usd <= 0) {
?>
    <div class="fixed inset-0 w-full h-full bg-slate-50 z-50 flex items-center justify-center p-6">

        <div class="text-center max-w-md w-full">
            <div class="relative w-32 h-32 mx-auto mb-8">
                <div class="absolute inset-0 bg-emerald-100 rounded-full animate-pulse"></div>
                <div class="relative w-32 h-32 bg-white rounded-full flex items-center justify-center border-4 border-emerald-50 shadow-xl">
                    <i class="fas fa-check text-5xl text-emerald-500"></i>
                </div>
                <div class="absolute top-0 right-2 text-yellow-400 text-xl animate-bounce"><i class="fas fa-star"></i></div>
            </div>

            <h1 class="text-3xl font-black text-slate-800 mb-3 tracking-tight">¡Todo al día!</h1>
            <p class="text-slate-500 text-lg mb-8 leading-relaxed">
                No hay ventas pendientes por cerrar.<br>
                El sistema no registra movimientos nuevos.
            </p>

            <a href="index.php?vista=orders_kanban" class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-white border border-slate-200 text-slate-700 font-bold rounded-xl shadow-lg shadow-slate-200 hover:shadow-xl hover:-translate-y-1 transition-all w-full sm:w-auto group">
                <i class="fas fa-arrow-left text-slate-400 group-hover:text-indigo-600 transition-colors"></i>
                <span>Volver al Menú</span>
            </a>
        </div>

    </div>
<?php
    exit(); // DETIENE LA EJECUCIÓN AQUÍ PARA NO MOSTRAR EL FORMULARIO
}
// ==========================================
//  FIN BLOQUE 1
// ==========================================
?>

<div class="w-full min-h-screen bg-slate-50 font-sans flex flex-col relative">

    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 px-4 lg:px-6 py-3 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex bg-slate-800 text-white w-10 h-10 rounded-lg items-center justify-center shadow-lg">
                    <i class="fas fa-cash-register text-lg"></i>
                </div>
                <div>
                    <h1 class="text-base lg:text-lg font-bold text-slate-800 leading-tight">Cierre de Caja</h1>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-500 hidden sm:inline">Tasa BCV:</span>
                        <span class="bg-indigo-600 text-white px-2 py-0.5 rounded font-bold shadow-sm">
                            <?php echo number_format($tasa_calculo, 2); ?> Bs
                        </span>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider hidden sm:block">Sistema Espera</p>
                <div class="text-xl lg:text-2xl font-black text-slate-800">
                    Bs <?php echo number_format($sys_total_bs, 2); ?>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-1 w-full max-w-7xl mx-auto p-4 lg:p-6 pb-24">

        <form id="formCierre" class="flex flex-col lg:flex-row gap-6 justify-center items-start" onsubmit="event.preventDefault(); cerrarCaja();">

            <div class="w-full lg:flex-1 bg-white rounded-xl shadow-[0_10px_40px_-15px_rgba(0,0,0,0.1)] border border-slate-300 border-t-8 border-t-blue-600 overflow-hidden flex flex-col">

                <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 shadow-sm">
                            <i class="fas fa-coins text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 text-lg leading-none">Bolívares</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Moneda Nacional</p>
                        </div>
                    </div>
                </div>

                <div class="p-5 lg:p-8 flex flex-col gap-6 bg-white">

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Pago Móvil / Punto</label>
                        <div class="relative group">
                            <div class="absolute left-0 top-0 bottom-0 w-14 flex items-center justify-center bg-slate-100 border-r border-slate-200 rounded-l-xl text-slate-400 font-bold group-focus-within:bg-blue-50 group-focus-within:text-blue-600 group-focus-within:border-blue-100 transition-colors">
                                Bs
                            </div>
                            <input type="number" step="0.01" id="bs_digital" placeholder="0.00" oninput="calcular()"
                                class="w-full pl-16 pr-4 py-4 bg-white border-2 border-slate-200 rounded-xl text-2xl font-bold text-slate-800 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-right shadow-sm placeholder:text-slate-300">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Efectivo Físico</label>
                        <div class="relative group">
                            <div class="absolute left-0 top-0 bottom-0 w-14 flex items-center justify-center bg-slate-100 border-r border-slate-200 rounded-l-xl text-slate-400 font-bold group-focus-within:bg-blue-50 group-focus-within:text-blue-600 group-focus-within:border-blue-100 transition-colors">
                                Bs
                            </div>
                            <input type="number" step="0.01" id="bs_efectivo" placeholder="0.00" oninput="calcular()"
                                class="w-full pl-16 pr-4 py-4 bg-white border-2 border-slate-200 rounded-xl text-2xl font-bold text-slate-800 focus:ring-4 focus:ring-blue-50 focus:border-blue-500 transition-all text-right shadow-sm placeholder:text-slate-300">
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:flex-1 bg-white rounded-xl shadow-[0_10px_40px_-15px_rgba(0,0,0,0.1)] border border-slate-300 border-t-8 border-t-emerald-500 overflow-hidden flex flex-col">

                <div class="px-5 py-4 border-b border-slate-200 flex justify-between items-center bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-100 shadow-sm">
                            <i class="fas fa-dollar-sign text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-slate-800 text-lg leading-none">Divisas USD</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Se convierte a Bs</p>
                        </div>
                    </div>
                </div>

                <div class="p-5 lg:p-8 flex flex-col gap-6 justify-center h-full bg-white">

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Efectivo Físico USD</label>
                        <div class="relative group">
                            <div class="absolute left-0 top-0 bottom-0 w-14 flex items-center justify-center bg-slate-100 border-r border-slate-200 rounded-l-xl text-slate-400 font-bold group-focus-within:bg-emerald-50 group-focus-within:text-emerald-600 group-focus-within:border-emerald-100 transition-colors">
                                $
                            </div>
                            <input type="number" step="0.01" id="usd_efectivo" placeholder="0.00" oninput="calcular()"
                                class="w-full pl-16 pr-4 py-4 bg-white border-2 border-slate-200 rounded-xl text-3xl font-bold text-slate-800 focus:ring-4 focus:ring-emerald-50 focus:border-emerald-500 transition-all text-right shadow-sm placeholder:text-slate-300">
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </main>

    <footer class="sticky bottom-0 z-50 bg-white border-t border-slate-200 px-4 lg:px-6 py-3 lg:py-4 shadow-[0_-5px_20px_rgba(0,0,0,0.1)]">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">

            <div class="flex w-full sm:w-auto justify-between sm:justify-start sm:gap-8 items-center">
                <div>
                    <p class="text-[9px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Ingresado</p>
                    <p class="text-base lg:text-xl font-bold text-slate-700" id="lblTotalContadoBs">Bs 0.00</p>
                </div>

                <div class="text-right sm:text-left sm:border-l sm:pl-8 border-slate-200">
                    <p class="text-[9px] lg:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Diferencia Final</p>
                    <div class="flex flex-col sm:flex-row sm:items-baseline sm:gap-2">
                        <p class="text-xl lg:text-3xl font-black text-slate-300 transition-colors duration-300 leading-none" id="lblDiferenciaBs">Bs 0.00</p>
                        <span class="text-xs font-medium text-slate-400" id="lblDiferenciaUsd">$0.00</span>
                    </div>
                </div>
            </div>

            <button type="button" onclick="cerrarCaja()" id="btnCerrar"
                class="w-full sm:w-auto px-6 py-3 lg:px-8 lg:py-4 bg-slate-900 hover:bg-indigo-600 active:bg-slate-950 text-white rounded-xl font-bold shadow-lg transition-all flex items-center justify-center gap-2 text-sm lg:text-base transform active:scale-95">
                <i class="fas fa-lock"></i>
                <span>CERRAR TURNO</span>
            </button>
        </div>
    </footer>

</div>

<script>
    // Variables desde PHP
    const sysTotalBs = <?php echo $sys_total_bs; ?>;
    const tasa = <?php echo $tasa_calculo; ?>;

    // Función de cálculo en tiempo real
    function calcular() {
        // Obtener valores (o 0 si está vacío)
        let bs_dig = parseFloat(document.getElementById('bs_digital').value) || 0;
        let bs_efe = parseFloat(document.getElementById('bs_efectivo').value) || 0;
        let usd_efe = parseFloat(document.getElementById('usd_efectivo').value) || 0;

        // Matemáticas: Convertir USD a BS y sumar todo
        let usd_convertido_a_bs = usd_efe * tasa;
        let total_manual_bs = bs_dig + bs_efe + usd_convertido_a_bs;

        // Calcular diferencias
        let diferencia_bs = total_manual_bs - sysTotalBs;
        let diferencia_usd = diferencia_bs / tasa;

        // Formateadores de moneda
        const fmtBs = new Intl.NumberFormat('es-VE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        const fmtUsd = new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        // Actualizar etiqueta de Total Ingresado
        document.getElementById('lblTotalContadoBs').innerText = 'Bs ' + fmtBs.format(total_manual_bs);

        // Actualizar etiquetas de Diferencia
        const lblDifBs = document.getElementById('lblDiferenciaBs');
        const lblDifUsd = document.getElementById('lblDiferenciaUsd');
        const signo = diferencia_bs >= 0 ? '+' : '';

        lblDifBs.innerText = signo + 'Bs ' + fmtBs.format(diferencia_bs);
        lblDifUsd.innerText = signo + '$' + fmtUsd.format(diferencia_usd);

        // Lógica de colores semánticos (Verde=Cuadra, Azul=Sobra, Rojo=Falta)
        if (Math.abs(diferencia_bs) < 1.00) {
            lblDifBs.className = "text-xl lg:text-3xl font-black text-emerald-600 transition-colors duration-300 leading-none";
            lblDifUsd.className = "text-xs font-medium text-emerald-700";
        } else if (diferencia_bs > 0) {
            lblDifBs.className = "text-xl lg:text-3xl font-black text-blue-600 transition-colors duration-300 leading-none";
            lblDifUsd.className = "text-xs font-medium text-blue-700";
        } else {
            lblDifBs.className = "text-xl lg:text-3xl font-black text-rose-600 transition-colors duration-300 leading-none";
            lblDifUsd.className = "text-xs font-medium text-rose-700";
        }
    }

    // Función de envío de datos
    function cerrarCaja() {
        Swal.fire({
            title: '¿Confirmar Cierre?',
            html: `
                <div class="text-left text-sm text-slate-600">
                    <p>Se registrará el cuadre actual.</p>
                    <p class="mt-2 font-bold text-slate-800">Verifica que los montos físicos coincidan.</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, Cerrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#94a3b8',
            reverseButtons: true
        }).then((res) => {
            if (res.isConfirmed) {
                // Preparar datos
                let data = new FormData();
                data.append('bs_digital', document.getElementById('bs_digital').value || 0);
                data.append('bs_efectivo', document.getElementById('bs_efectivo').value || 0);
                data.append('usd_efectivo', document.getElementById('usd_efectivo').value || 0);
                data.append('usd_digital', 0); // Enviamos 0 explícito ya que eliminamos el input

                // Loading
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                // Enviar al Backend
                fetch('./php/cierre_guardar.php', {
                        method: 'POST',
                        body: data
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.status === 'success') {
                            Swal.fire({
                                title: '¡Turno Cerrado!',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => window.location.reload());
                        } else {
                            Swal.fire('Error', d.message, 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Fallo de conexión', 'error'));
            }
        });
    }
</script>