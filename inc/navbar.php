<?php
// Obtenemos la vista actual para resaltar el enlace activo
$current_vista = $_GET['vista'] ?? 'home';

// Definimos un array de vistas para agrupar
$vistas_productos = ['product_list', 'promo_list'];
$vistas_admin = ['ad_list', 'user_list'];
?>

<nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between">

            <div class="flex items-center">
                <a href="index.php?vista=home" class="flex-shrink-0 flex items-center">
                    <img src="img/logo.png" alt="Tu Compañía" class="h-8 w-auto" />
                </a>
                <div class="hidden lg:ml-8 lg:block">
                    <div class="flex space-x-4 items-center">
                        <a href="index.php?vista=home" class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo ($current_vista == 'home') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-indigo-600'; ?>">INICIO</a>
                        <a href="index.php?vista=category_list" class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo ($current_vista == 'category_list') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-indigo-600'; ?>">CATEGORIAS</a>

                        <a href="index.php?vista=product_list" class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo ($current_vista == 'product_list') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-indigo-600'; ?>">PRODUCTOS</a>

                        <a href="index.php?vista=promo_list" class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo ($current_vista == 'promo_list') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-indigo-600'; ?>">PROMOCIONES</a>

                        <a href="index.php?vista=ad_list" class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo ($current_vista == 'ad_list') ? 'text-indigo-600 font-semibold' : 'text-gray-500 hover:text-indigo-600'; ?>">ANUNCIOS</a>

                    </div>
                </div>
            </div>

            <div class="flex items-center">
                <div class="lg:hidden">
                    <button id="mobile-menu-button" type="button" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 focus:outline-none">
                        <span class="sr-only">Abrir menú principal</span>
                        <svg id="icon-open" class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                        <svg id="icon-close" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="relative ml-4">
                    <button id="profile-menu-button" type="button" class="relative flex rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <span class="sr-only">Abrir menú de usuario</span>
                        <img class="h-9 w-9 rounded-full object-cover" src="img/user.jpg" alt="Foto de perfil">
                    </button>

                    <div id="profile-menu" class="dropdown-menu hidden absolute right-0 z-10 mt-2 w-64 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-semibold text-gray-900"><?php echo $_SESSION['nombre'] . " " . $_SESSION['apellido']; ?></p>
                            <p class="text-sm text-gray-500 truncate"><?php echo $_SESSION['usuario']; /* Asumiendo que tienes el usuario en la sesión */ ?></p>
                        </div>
                        <a href="index.php?vista=perfil" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">

                            <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A1.75 1.75 0 0117.748 22H6.252a1.75 1.75 0 01-1.75-1.882z" />
                            </svg>

                            <span>Perfil</span>
                        </a>
                        <a href="index.php?vista=logout" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                            <span>Salir</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="mobile-menu-container" class="lg:hidden" role="dialog" aria-modal="true">
        <div id="mobile-menu-backdrop" class="fixed inset-0 bg-black bg-opacity-25 transition-opacity opacity-0 hidden"></div>

        <div id="mobile-menu-panel" class="fixed inset-0 z-40 flex transition-transform transform -translate-x-full">
            <div class="relative flex w-full max-w-xs flex-col overflow-y-auto bg-white pb-12 shadow-xl">
                <div class="flex px-4 pb-2 pt-5">
                    <button id="mobile-menu-close-button" type="button" class="-m-2 inline-flex items-center justify-center rounded-md p-2 text-gray-600">
                        <span class="sr-only">Cerrar menú</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-2 border-t border-gray-200 px-4 py-6">
                    <a href="index.php?vista=home" class="block rounded-md px-3 py-2 text-base font-medium <?php echo ($current_vista == 'home') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-900 hover:bg-gray-50'; ?>">Dashboard</a>
                    <a href="index.php?vista=category_list" class="block rounded-md px-3 py-2 text-base font-medium <?php echo ($current_vista == 'category_list') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-900 hover:bg-gray-50'; ?>">Categorías</a>
                    <a href="index.php?vista=product_list" class="block rounded-md px-3 py-2 text-base font-medium <?php echo ($current_vista == 'product_list') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-900 hover:bg-gray-50'; ?>">Productos</a>

                    <a href="index.php?vista=promo_list" class="block rounded-md px-3 py-2 text-base font-medium <?php echo ($current_vista == 'promo_list') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-900 hover:bg-gray-50'; ?>">Promociones</a>

                    <a href="index.php?vista=ad_list" class="block rounded-md px-3 py-2 text-base font-medium <?php echo ($current_vista == 'ad_list') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-900 hover:bg-gray-50'; ?>">Anuncios</a>

                </div>
            </div>
        </div>
    </div>
</nav>