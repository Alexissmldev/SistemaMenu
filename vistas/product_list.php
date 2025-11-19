<div class="container mx-auto p-6 lg:p-10 max-w-7xl">
    <?php include "./inc/breadcrumb.php"; ?>
    <!-- ENCABEZADO Y BOTONES DE ACCIÓN -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-3">
                <span class="bg-orange-100 p-2 rounded-lg text-orange-600">
                    <i class="fas fa-boxes"></i>
                </span>
                Lista de Productos
            </h1>
            <h2 class="text-sm text-gray-500 mt-2 ml-1">Gestiona los productos y visualiza el menú disponible.</h2>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <!-- Botón Imprimir -->
            <a href="index.php?vista=reportes&descargar=pdf" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-orange-600 transition-all shadow-sm">
                <i class="fas fa-file-pdf mr-2 text-red-500"></i>
                Imprimir Menú
            </a>

            <!-- Botón Nuevo Producto -->
            <a href="index.php?vista=product_new" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i>
                Nuevo Producto
            </a>
        </div>
    </div>

    <!-- BARRA DE BÚSQUEDA -->
    <div class="mb-8">
        <form action="./php/buscador.php" method="POST" autocomplete="off" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <input type="hidden" name="modulo_buscador" value="producto">

            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                <div class="md:col-span-9 lg:col-span-10">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="txt_buscador" class="block w-full p-3 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-orange-500 focus:border-orange-500 transition-colors" placeholder="¿Qué producto estás buscando?" value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                    </div>
                </div>

                <div class="md:col-span-3 lg:col-span-2 flex gap-2">
                    <button type="submit" class="w-full flex items-center justify-center text-white bg-gray-800 hover:bg-gray-900 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-3 transition-colors">
                        Buscar
                    </button>

                    <?php if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])): ?>
                        <a href="index.php?vista=product_list" class="flex items-center justify-center px-4 py-3 text-gray-500 bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-lg transition-colors" title="Limpiar Búsqueda">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- CONTENEDOR DE LA LISTA (PHP) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden min-h-[400px]">
        <?php
        require_once "./php/main.php";

        // Eliminar producto (Lógica existente)
        if (isset($_GET['product_id_del'])) {
            require_once "./php/producto_eliminar.php";
        }

        if (!isset($_GET['page'])) {
            $pagina = 1;
        } else {
            $pagina = (int) $_GET['page'];
            if ($pagina <= 1) {
                $pagina = 1;
            }
        }

        $categoria_id = (isset($_GET['category_id'])) ? $_GET['category_id'] : 0;
        $pagina = limpiar_cadena($pagina);
        $url = "index.php?vista=product_list&page=";
        $registros = 15;
        $busqueda = "";

        // Aquí se cargará la tabla o grid de productos
        require_once "./php/producto_lista.php";
        ?>
    </div>

    <!-- MODAL CONTAINER (Para ediciones rápidas si las implementas a futuro) -->
    <div id="modal-container"></div>
</div>