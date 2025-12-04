<header id="main-header" class="bg-white py-2 px-4 shadow-md sticky top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto">

        <div class="flex justify-between items-center w-full lg:grid lg:grid-cols-3 lg:gap-4">

            <div class="flex items-center space-x-2">
                <a href="menu.php" class="block">
                    <?php
                    // Verificamos si la variable existe y si el archivo físico existe
                    $ruta_logo = './img/logo/' . ($tienda['logo_tienda'] ?? 'logo_default.png');

                    if (!file_exists($ruta_logo) || empty($tienda['logo_tienda'])) {
                        $ruta_logo = './img/logo_default.png';
                    }
                    ?>
                    <img src="<?php echo $ruta_logo; ?>"
                        alt="<?php echo htmlspecialchars($nombre_tienda ?? 'Tienda'); ?>"
                        class="w-auto h-12 md:h-16 object-contain"
                        onerror="this.src='./img/logo_default.png';">
                </a>
            </div>

            <div class="hidden lg:flex justify-center">
                <form autocomplete="off" class="relative w-full max-w-lg">
                    <input type="text" id="desktop-search-input" name="txt_buscador"
                        placeholder="Buscar comida..."
                        class="w-full py-2.5 pl-10 pr-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition-all shadow-sm z-10 bg-white" />

                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 z-20 pointer-events-none">
                        <i class="fa fa-search text-lg"></i>
                    </span>
                </form>
            </div>

            <div class="flex items-center space-x-4 justify-end">
                <span class="text-sm font-bold text-green-700 bg-green-50 border border-green-200 px-3 py-1 rounded-lg shadow-sm">
                    Tasa: <?php echo $tasa_usd; ?>
                </span>

                <button id="open-cart-btn-desktop" class="relative group p-2 rounded-full hover:bg-gray-100 transition-colors hidden lg:block">
                    <i class="fa fa-shopping-cart text-2xl text-gray-600 group-hover:text-red-600 transition-colors"></i>

                    <span id="cart-count-badge-desktop" class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center shadow-sm hidden transform scale-100 transition-transform">
                        0
                    </span>
                </button>
            </div>
        </div>

        <section class="mt-3 pt-2 border-t border-gray-100" id="category-nav-section">
            <nav class="flex space-x-6 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0 lg:justify-start lg:flex-wrap no-scrollbar">
                <a href="#todos" class="category-link flex-shrink-0 font-bold border-b-2 border-red-600 text-red-600 pb-1 hover:opacity-80 transition">Todos</a>
                <?php
                foreach ($categorias_ordenadas as $categoria) {
                    $categoria_id = strtolower(str_replace(' ', '', $categoria['categoria_nombre']));
                    echo '<a href="#' . htmlspecialchars($categoria_id) . '" class="category-link flex-shrink-0 font-medium text-gray-500 border-b-2 border-transparent hover:text-red-600 hover:border-red-600 pb-1 transition-all">';
                    echo htmlspecialchars($categoria['categoria_nombre']);
                    echo '</a>';
                }
                ?>
            </nav>
        </section>
    </div>
</header>

<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-6 py-3 flex justify-between lg:hidden shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-40 safe-area-pb">
    <a href="menu.php" id="mobile-home-trigger" class="text-red-600 flex flex-col items-center gap-1 active:scale-90 transition-transform">
        <i class="fa fa-home text-2xl"></i>
        <span class="text-[10px] font-medium">Inicio</span>
    </a>

    <button id="mobile-search-trigger" class="text-gray-400 hover:text-red-600 flex flex-col items-center gap-1 active:scale-90 transition-transform">
        <i class="fa fa-search text-2xl"></i>
        <span class="text-[10px] font-medium">Buscar</span>
    </button>

    <button id="open-cart-btn-mobile" class="relative text-gray-400 hover:text-red-600 flex flex-col items-center gap-1 active:scale-90 transition-transform">
        <i class="fa fa-shopping-cart text-2xl"></i>
        <span class="text-[10px] font-medium">Carrito</span>

        <span id="cart-count-badge-mobile" class="absolute -top-2 -right-1 bg-red-600 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center hidden shadow-sm border border-white">
            0
        </span>
    </button>
</div>

<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-3 flex justify-around lg:hidden shadow-xl z-20">
    <a href="home" id="mobile-home-trigger" class="text-red-600 flex flex-col items-center">
        <i class="fa fa-home text-2xl"></i>
        <span class="text-xs">Inicio</span>
    </a>

    <button id="mobile-search-trigger" class="text-gray-400 hover:text-red-600 flex flex-col items-center">
        <i class="fa fa-search text-2xl"></i>
        <span class="text-xs">Buscador</span>
    </button>

    <button id="open-cart-btn-mobile" class="relative text-gray-400 hover:text-red-600 flex flex-col items-center">
        <i class="fa fa-shopping-cart text-2xl"></i>
        <span class="text-xs">Carrito</span>
        <span id="cart-count-badge-mobile" class="absolute -top-1 right-2 bg-red-600 text-white text-xs font-bold w-4 h-4 rounded-full flex items-center justify-center hidden">
            0
        </span>
    </button>
</div>