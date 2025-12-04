<?php
require_once "./php/main.php";
$conexion = conexion();

// 1. OBTENER PEDIDOS ACTIVOS
$consulta = "SELECT * FROM pedido WHERE pedido_estado IN ('Pendiente', 'Preparacion', 'Despacho') ORDER BY pedido_fecha ASC";
$pedidos = $conexion->query($consulta)->fetchAll(PDO::FETCH_ASSOC);

// 2. SEPARAR POR COLUMNAS
$pendientes = [];   
$preparacion = [];
$despacho = [];

foreach ($pedidos as $p) {
    if ($p['pedido_estado'] == 'Pendiente') $pendientes[] = $p;
    elseif ($p['pedido_estado'] == 'Preparacion') $preparacion[] = $p;
    elseif ($p['pedido_estado'] == 'Despacho') $despacho[] = $p;
}

$conexion = null;
?>

<div class="w-full h-screen bg-slate-50 font-sans flex flex-col overflow-hidden">

    <div class="bg-white border-b border-slate-200 px-6 py-3 shrink-0 z-30 shadow-sm flex justify-between items-center h-16">
        <div class="flex items-center gap-3">
            <div class="bg-orange-100 text-orange-600 p-2 rounded-lg">
                <i class="fas fa-columns text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-800 leading-tight">Gestión de Pedidos</h1>
                <div class="flex items-center gap-2">
                    <p class="text-xs text-slate-500">Monitor de Cocina y Despacho</p>
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <button onclick="window.location.reload()" class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Actualizar Tablero">
                <i class="fas fa-sync-alt text-lg"></i>
            </button>
        </div>
    </div>

    <div class="flex-1 p-6 overflow-x-auto overflow-y-hidden bg-slate-50">
        <div class="flex gap-6 h-full min-w-[1000px] lg:min-w-full">

            <div class="flex-1 flex flex-col h-full bg-slate-100/50 border border-slate-200 rounded-2xl">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-white/50 rounded-t-2xl backdrop-blur-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500 shadow-sm shadow-red-300"></div>
                        <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wide">Pendientes</h2>
                    </div>
                    <span class="bg-white border border-slate-200 text-slate-600 px-2.5 py-0.5 rounded-md text-xs font-bold shadow-sm">
                        <?php echo count($pendientes); ?>
                    </span>
                </div>

                <div class="flex-1 p-3 space-y-3 overflow-y-auto custom-scrollbar">
                    <?php if (empty($pendientes)): ?>
                        <div class="h-full flex flex-col items-center justify-center text-slate-300">
                            <i class="fas fa-check-circle text-4xl mb-2 opacity-50"></i>
                            <p class="text-sm font-medium">Todo al día</p>
                        </div>
                    <?php endif; ?>

                    <?php foreach ($pendientes as $orden): ?>
                        <div class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md border border-slate-100 transition-all group relative">
                            <div class="absolute left-0 top-4 bottom-4 w-1 bg-red-500 rounded-r-full"></div>

                            <div class="pl-3">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-black text-slate-800 text-lg">#<?php echo $orden['id_pedido']; ?></span> <span class="text-[10px] font-bold bg-slate-100 text-slate-500 px-2 py-1 rounded uppercase border border-slate-200">
                                        <?php echo $orden['tipo_orden']; ?>
                                    </span>
                                </div>
                                <p class="text-sm font-bold text-slate-700 mb-1 truncate"><?php echo $orden['id_cliente']; ?></p>
                                <p class="text-xs text-slate-400 mb-3 flex items-center gap-1">
                                    <i class="far fa-clock"></i> <?php echo date("h:i A", strtotime($orden['fecha'])); ?>
                                </p>

                                <button onclick="cambiarEstado(<?php echo $orden['id_pedido']; ?>, 'Preparacion')" class="w-full py-2 bg-red-50 text-red-600 hover:bg-red-500 hover:text-white rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 transform active:scale-95">
                                    <i class="fas fa-fire"></i> COCINAR
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex-1 flex flex-col h-full bg-orange-50/30 border border-orange-100 rounded-2xl">
                <div class="p-4 border-b border-orange-100 flex justify-between items-center bg-white/50 rounded-t-2xl backdrop-blur-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-orange-500 shadow-sm shadow-orange-300 animate-pulse"></div>
                        <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wide">En Cocina</h2>
                    </div>
                    <span class="bg-white border border-orange-100 text-orange-600 px-2.5 py-0.5 rounded-md text-xs font-bold shadow-sm">
                        <?php echo count($preparacion); ?>
                    </span>
                </div>

                <div class="flex-1 p-3 space-y-3 overflow-y-auto custom-scrollbar">
                    <?php foreach ($preparacion as $orden): ?>
                        <div class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md border border-orange-100 transition-all relative">
                            <div class="absolute left-0 top-4 bottom-4 w-1 bg-orange-500 rounded-r-full"></div>

                            <div class="pl-3">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-black text-slate-800 text-lg">#<?php echo $orden['id_pedido']; ?></span>
                                    <i class="fas fa-utensils text-orange-200"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-700 mb-3"><?php echo $orden['id_cliente']; ?></p>

                                <button onclick="cambiarEstado(<?php echo $orden['id_pedido']; ?>, 'Despacho')" class="w-full py-2 bg-orange-500 text-white hover:bg-orange-600 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 shadow-sm shadow-orange-200 transform active:scale-95">
                                    <i class="fas fa-check"></i> TERMINAR
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="flex-1 flex flex-col h-full bg-slate-100/50 border border-slate-200 rounded-2xl">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center bg-white/50 rounded-t-2xl backdrop-blur-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-green-500 shadow-sm shadow-green-300"></div>
                        <h2 class="font-bold text-slate-700 text-sm uppercase tracking-wide">Despacho</h2>
                    </div>
                    <span class="bg-white border border-slate-200 text-slate-600 px-2.5 py-0.5 rounded-md text-xs font-bold shadow-sm">
                        <?php echo count($despacho); ?>
                    </span>
                </div>

                <div class="flex-1 p-3 space-y-3 overflow-y-auto custom-scrollbar">
                    <?php foreach ($despacho as $orden): ?>
                        <div class="bg-white p-4 rounded-xl shadow-sm hover:shadow-md border border-slate-100 transition-all opacity-90 hover:opacity-100 relative">
                            <div class="absolute left-0 top-4 bottom-4 w-1 bg-green-500 rounded-r-full"></div>

                            <div class="pl-3">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-black text-slate-800 text-lg">#<?php echo $orden['id_pedido']; ?></span>
                                    <div class="text-right">
                                        <span class="block text-[10px] font-bold text-slate-400 uppercase">Total</span>
                                        <span class="font-bold text-green-600 text-sm">$<?php echo number_format($orden['total_usd'], 2); ?></span>
                                    </div>
                                </div>
                                <p class="text-sm font-bold text-slate-700 mb-1"><?php echo $orden['id_cliente']; ?></p>
                                <p class="text-[10px] text-slate-400 mb-3 bg-slate-50 px-2 py-1 rounded inline-block">
                                    <?php echo str_replace('_', ' ', ucfirst($orden['metodo_pago'])); ?>
                                </p>

                                <button onclick="cambiarEstado(<?php echo $orden['id_pedido']; ?>, 'Finalizado')" class="w-full py-2 bg-green-600 text-white hover:bg-green-700 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-2 shadow-sm shadow-green-200 transform active:scale-95">
                                    ENTREGAR
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // --- LÓGICA PARA MOVER TARJETAS ---
    function cambiarEstado(id, nuevoEstado) {

        let colorBtn = "#1e293b";
        let textoAccion = "mover esta orden";

        if (nuevoEstado === 'Preparacion') {
            textoAccion = "enviar a cocina";
            colorBtn = "#ef4444";
        }
        if (nuevoEstado === 'Despacho') {
            textoAccion = "marcar como listo";
            colorBtn = "#f97316";
        }
        if (nuevoEstado === 'Finalizado') {
            textoAccion = "finalizar entrega";
            colorBtn = "#22c55e";
        }

        Swal.fire({
            title: '¿Confirmar?',
            text: `Vas a ${textoAccion}.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: colorBtn,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {

                // Petición AJAX al servidor
                let formData = new FormData();
                formData.append('id_pedido', id);
                formData.append('estado', nuevoEstado);

                fetch('php/pedido_actualizar_pago.php', { // Usamos el archivo que ya tienes
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Recarga suave sin alerta intrusiva
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 1500,
                                background: '#1e293b',
                                color: '#fff'
                            });
                            Toast.fire({
                                icon: 'success',
                                title: 'Estado actualizado'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', 'No se pudo actualizar', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Fallo de conexión', 'error');
                    });
            }
        });
    }

    // Auto-refresco cada 30 segundos
    setInterval(() => {
        window.location.reload();
    }, 30000);
</script>