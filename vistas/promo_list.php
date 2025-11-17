<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Promociones</h1>
            <h2 class="text-lg text-gray-600 mt-1">Gestiona las ofertas (2x1, combos) del carrusel.</h2>
        </div>
        <div>
            <a 
                href="index.php?vista=promo_new" 
                class="w-full sm:w-auto inline-flex items-center justify-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">
                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Nueva Promoción</span>
            </a>
        </div>
    </div>

    <div class="py-4">
        <form action="./php/buscador.php" method="POST" autocomplete="off" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
            <input type="hidden" name="modulo_buscador" value="promocion">
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <input type="text" name="txt_buscador" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Buscar por nombre de la promoción..." value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                </div>
                <div class="flex space-x-2">
                    <button type="submit" class="w-full flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md">
                        <i class="fa fa-search w-5 h-5 mr-2"></i>
                        Buscar
                    </button>
                    
                    <?php if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])): ?>
                        <a href="index.php?vista=promo_list" class="w-full flex items-center justify-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-md" title="Limpiar Búsqueda">
                            <i class="fa fa-times w-5 h-5"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="mt-6">
        <?php
        require_once "./php/main.php";

        // Lógica para eliminar una promoción
        if (isset($_GET['promo_id_del'])) {
            require_once "./php/promo_eliminar.php"; 
        }

        if (!isset($_GET['page'])) {
            $pagina = 1;
        } else {
            $pagina = (int) $_GET['page'];
            if ($pagina <= 1) {
                $pagina = 1;
            }
        }

        $pagina = limpiar_cadena($pagina);
        $url = "index.php?vista=promo_list&page="; // URL para paginación
        $registros = 9; // 9 tarjetas por página
        $busqueda = "";

        // Aquí se carga el archivo de lógica
        require_once "./php/promo_lista.php";
        ?>
    </div>
</div>