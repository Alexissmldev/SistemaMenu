<header class="bg-white p-4 shadow-md sticky top-0 z-10">
    <div class="max-w-7xl mx-auto">

        <div class="flex justify-between items-center w-full lg:grid lg:grid-cols-3 lg:gap-4">

            <div class="flex items-center space-x-2">
                <img src="../img/logo.png" alt="" class="w-16 h-12 object-contain" />
                <span class="hidden lg:block text-xs text-gray-500 bg-gray-100 p-1 rounded-full px-2">
                    Tasa USD: **<?php echo $tasa_usd; ?>**
                </span>
            </div>

            <div class="hidden lg:flex justify-center">
                <div class="relative w-full max-w-lg">
                    <input type="text" placeholder="Buscar comida..." class="w-full py-2 pl-10 pr-4 border border-gray-300 rounded-full focus:ring-red-500 focus:border-red-500" />
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            <div class="flex items-center space-x-3 justify-end">
                <span class="text-xs text-gray-500 bg-gray-100 p-1 rounded-full px-2 lg:hidden">
                    Tasa USD: **<?php echo $tasa_usd; ?>**
                </span>
                <button class="text-gray-600 hover:text-red-500 hidden lg:block">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </button>

            </div>
        </div>

        <section class="mt-4 pt-4 border-t border-gray-100">
            <nav class="flex space-x-6 overflow-x-scroll pb-2 lg:justify-start lg:flex-wrap">
                <?php
                // Recorre las categorías ordenadas (PHP)
                $primera_categoria = true;
                foreach ($categorias_ordenadas as $categoria) {
                    $categoria_id = strtolower(str_replace(' ', '', $categoria['categoria_nombre']));
                    // Clases para destacar la primera categoría como activa
                    $clases_link = $primera_categoria ? 'text-red-600 border-red-600' : 'text-gray-500 border-transparent';

                    echo '<a href="#' . htmlspecialchars($categoria_id) . '" class="flex-shrink-0 font-semibold border-b-2 pb-1 hover:text-red-600 hover:border-red-600 transition ' . $clases_link . '">';
                    echo htmlspecialchars($categoria['categoria_nombre']);
                    echo '</a>';

                    $primera_categoria = false;
                }
                ?>
            </nav>
        </section>
    </div>
</header>