<div class="h-screen flex flex-col bg-slate-100 overflow-hidden font-sans">

    <div class="bg-white border-b border-gray-200 px-4 md:px-6 py-3 flex justify-between items-center shrink-0 z-20 shadow-sm h-16 md:h-20">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-600 text-white w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-xl shadow-lg transition-all">
                <i class="fas fa-columns text-lg md:text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800 leading-none">Despacho KDS</h1>
                <p id="live-indicator" class="text-xs md:text-sm text-gray-500 mt-0.5 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> En vivo
                </p>
            </div>
        </div>

        <div class="flex gap-2 md:gap-3">
          

            <button onclick="toggleHistorial()" id="btnHistorial" class="flex items-center justify-center gap-2 px-3 md:px-4 py-2 text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition-all shadow-sm">
                <i class="fas fa-history text-lg"></i>
                <span class="hidden md:inline font-bold text-sm">Historial</span>
            </button>

            <a href="index.php?vista=orders_list" class="flex items-center justify-center w-10 h-10 md:w-auto md:h-auto md:px-5 md:py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                <i class="fas fa-list md:mr-2"></i>
                <span class="hidden md:inline font-bold text-sm">Lista</span>
            </a>
        </div>
    </div>

    <div id="kanban-container" class="flex-1 p-2 md:p-6 overflow-x-auto overflow-y-hidden bg-slate-100 relative">
        <div class="flex items-center justify-center h-full text-gray-400">
            <i class="fas fa-circle-notch fa-spin text-4xl"></i>
        </div>
    </div>

</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
    }

    @media (max-width: 768px) {
        .custom-scrollbar::-webkit-scrollbar {
            width: 0px;
        }
    }
</style>

<script>
    // 1. CARGA AUTOMÁTICA (Polling cada 3 segundos)
    document.addEventListener("DOMContentLoaded", () => {
        cargarKanban();
        setInterval(cargarKanban, 3000); // 3 segundos para que sea casi tiempo real
    });

    // 2. FUNCIÓN AJAX PARA OBTENER EL TABLERO
    function cargarKanban() {
        const indicador = document.getElementById('live-indicator');

        fetch('php/kanban_content.php')
            .then(response => {
                if (!response.ok) return; // Si falla silenciosamente, no hacemos nada
                return response.text();
            })
            .then(html => {
                if (html) {
                    document.getElementById('kanban-container').innerHTML = html;
                    if (indicador) indicador.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> En vivo';
                }
            })
            .catch(error => {
                console.error('Conexión perdida momentáneamente');
                if (indicador) indicador.innerHTML = '<span class="w-2 h-2 rounded-full bg-red-500"></span> Reconectando...';
            });
    }

    // 3. MOSTRAR/OCULTAR HISTORIAL (Sin cambios, solo visual)
    function toggleHistorial() {
        let col = document.getElementById('colHistorial');
        let btn = document.getElementById('btnHistorial');
        if (!col) return;

        col.classList.toggle('hidden');
        col.classList.toggle('flex');

        if (!col.classList.contains('hidden')) {
            btn.classList.replace('bg-gray-100', 'bg-slate-800');
            btn.classList.replace('text-gray-700', 'text-white');
            setTimeout(() => {
                col.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            }, 100);
        } else {
            btn.classList.replace('bg-slate-800', 'bg-gray-100');
            btn.classList.replace('text-white', 'text-gray-700');
        }
    }

    // 4. CAMBIAR ESTADO (DIRECTO - SIN ALERTAS)
    function actEstado(id, estado) {
        // Creamos los datos
        let fd = new FormData();
        fd.append('id_pedido', id);
        fd.append('estado', estado);

        // Enviamos la petición sin preguntar
        fetch('php/pedido_actualizar_pago.php', {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(d => {
                if (d.status === 'success') {
                    // Si fue exitoso, recargamos el tablero inmediatamente
                    cargarKanban();
                } else {
                    // Solo si hay un error grave lo mostramos en consola para no molestar
                    console.error("Error al actualizar:", d.message);
                }
            })
            .catch(e => console.error("Error de red al actualizar pedido"));
    }
</script>