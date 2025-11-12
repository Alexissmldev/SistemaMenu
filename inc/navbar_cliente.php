<header class="bg-white p-4 shadow-md sticky top-0 z-10">
    <div class="max-w-7xl mx-auto">

        <div class="flex justify-between items-center w-full lg:grid lg:grid-cols-3 lg:gap-4">

            <div class="flex items-center space-x-2">
                <img src="../img/logo.png" alt="" class="w-24 h-12 object-contain" />
                <span class="hidden lg:block text-xs text-gray-500 bg-gray-100 p-1 rounded-full px-2">
                    Tasa USD: **<?php echo $tasa_usd; ?>**
                </span>
            </div>

            <div class="hidden lg:flex justify-center">
                <form autocomplete="off" class="relative w-full max-w-lg">
                    <input
                        type="text"
                        id="desktop-search-input" name="txt_buscador"
                        placeholder="Buscar comida..."
                        class="w-full py-2 pl-10 pr-4 border border-gray-300 rounded-full focus:ring-red-500 focus:border-red-500" />
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <i class="fa fa-search"></i>
                    </span>
                </form>
            </div>
            <div class="flex items-center space-x-3 justify-end">
                <span class="text-xs text-gray-500 bg-gray-100 p-1 rounded-full px-2 lg:hidden">
                    Tasa USD: **<?php echo $tasa_usd; ?>**
                </span>

                <button id="open-cart-btn-desktop" class="relative text-gray-600 hover:text-red-500 hidden lg:block">
                    <i class="fa fa-shopping-cart text-2xl"></i>
                    <span id="cart-count-badge-desktop" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center hidden">
                        0
                    </span>
                </button>

            </div>
        </div>

        <section
            class="mt-4 pt-4 border-t border-gray-100"
            id="category-nav-section">
            <nav class="flex space-x-6 overflow-x-scroll pb-2 lg:justify-start lg:flex-wrap">
                <?php
                // Clases para "Todos" (activo por defecto)
                $clases_todos = 'text-red-600 border-red-600';

                // El href ahora es "#todos" para que JS lo identifique
                echo '<a href="#todos" class="category-link flex-shrink-0 font-semibold border-b-2 pb-1 hover:text-red-600 hover:border-red-600 transition ' . $clases_todos . '">';
                echo 'Todos';
                echo '</a>';

                // Recorrer el resto de categorías
                foreach ($categorias_ordenadas as $categoria) {
                    $categoria_id = strtolower(str_replace(' ', '', $categoria['categoria_nombre']));

                    // Clases para el resto (inactivas por defecto)
                    $clases_link = 'text-gray-500 border-transparent';

                    echo '<a href="#' . htmlspecialchars($categoria_id) . '" class="category-link flex-shrink-0 font-semibold border-b-2 pb-1 hover:text-red-600 hover:border-red-600 transition ' . $clases_link . '">';
                    echo htmlspecialchars($categoria['categoria_nombre']);
                    echo '</a>';
                }
                ?>
            </nav>
        </section>
    </div>
</header>

<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-3 flex justify-around lg:hidden shadow-xl z-20">

    <button id="mobile-home-trigger" class="text-red-600 flex flex-col items-center">
        <i class="fa fa-home text-2xl"></i>
        <span class="text-xs">Inicio</span>
    </button>

    <button id="mobile-search-trigger" class="text-gray-400 hover:text-red-600 flex flex-col items-center">
        <i class="fa fa-search text-2xl"></i>
        <span class="text-xs">Buscador</span>
    </button>

    <button id="open-cart-btn-mobile" class="relative text-gray-400 hover:text-red-600 flex flex-col items-center">
        <i class="fa fa-shopping-cart text-2xl"></i>
        <span class="text-xs">Carrito</span>
        <span id="cart-count-badge-mobile" class="absolute -top-1 right-2 bg-red-600 text-white text-xs font-bold w-5 h-5 rounded-full flex items-center justify-center hidden">
            0
        </span>
    </button>
</div>