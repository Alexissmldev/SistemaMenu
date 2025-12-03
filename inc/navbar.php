<?php

require_once "./php/main.php";

// Asegurarnos de que existe la vista actual
$current_vista = $_GET['vista'] ?? 'home';

//  LÓGICA INTELIGENTE PARA EL LOGO 
$url_logo = "orders_kanban";

// Si tiene permiso de ver estadísticas operativas (Gerente o Admin), el logo lleva al Dashboard
if (tiene_permiso('estadisticas.operativas')) {
    $url_logo = "home";
}

// Funciones de ayuda para clases CSS activas
function isActive($vistas, $current)
{
    if (is_array($vistas)) {
        return in_array($current, $vistas) ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-gray-500 hover:text-indigo-600 hover:bg-indigo-50';
    }
    return ($current == $vistas) ? 'text-indigo-600 bg-indigo-50 font-bold' : 'text-gray-500 hover:text-indigo-600 hover:bg-indigo-50';
}

function isActiveText($vistas, $current)
{
    if (is_array($vistas)) {
        return in_array($current, $vistas) ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-600';
    }
    return ($current == $vistas) ? 'text-indigo-600 font-bold' : 'text-gray-500 hover:text-indigo-600';
}
?>

<nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-[100] font-sans">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between">

            <!-- LOGO CON REDIRECCIÓN DINÁMICA -->
            <div class="flex items-center">
                <a href="index.php?vista=<?php echo $url_logo; ?>" class="flex-shrink-0 flex items-center gap-2 group">
                    <img src="img/logo.png" alt="Logo" class="h-8 w-auto group-hover:scale-105 transition-transform" />
                    <span class="font-bold text-gray-700 hidden md:block">ALAS</span>
                </a>

                <!-- MENÚ DE ESCRITORIO -->
                <div class="hidden lg:ml-10 lg:block">
                    <div class="flex space-x-1 items-center">

                        <!-- 1. INICIO (Solo visible si tiene permiso de estadísticas operativas - Gerente/Dueño) -->
                        <?php if (tiene_permiso('estadisticas.operativas')): ?>
                            <a href="index.php?vista=home" class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo isActiveText('home', $current_vista); ?>">
                                INICIO
                            </a>
                        <?php endif; ?>

                        <!-- 2. PEDIDOS (Visible para Despacho, Gerente, Admin) -->
                        <?php if (tiene_permiso('pedidos.ver')): ?>
                            <div class="relative group">
                                <button class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors group-hover:text-indigo-600 <?php echo isActiveText(['orders_kanban', 'orders_list'], $current_vista); ?>">
                                    PEDIDOS
                                    <i class="fas fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform duration-200"></i>
                                </button>
                                <!-- Dropdown -->
                                <div class="absolute left-0 mt-0 w-48 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50">
                                    <div class="p-1">
                                        <a href="index.php?vista=orders_kanban" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                            <i class="fas fa-columns text-xs"></i> Tablero Kanban
                                        </a>
                                        <a href="index.php?vista=orders_list" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                            <i class="fas fa-list text-xs"></i> Lista Histórica
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 3. GESTIÓN MENÚ (Productos + Categorías) - (Visible para Gerente, Admin) -->
                        <?php if (tiene_permiso('inventario.ver')): ?>
                            <div class="relative group">
                                <button class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors group-hover:text-indigo-600 <?php echo isActiveText(['product_list', 'product_new', 'product_update', 'category_list', 'category_new'], $current_vista); ?>">
                                    GESTIÓN MENÚ
                                    <i class="fas fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform duration-200"></i>
                                </button>

                                <div class="absolute left-0 mt-0 w-56 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50">
                                    <div class="p-1">
                                        <!-- Sección Productos -->
                                        <div class="px-4 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Productos</div>
                                        <a href="index.php?vista=product_new" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                            <i class="fas fa-plus text-xs"></i> Nuevo Platillo
                                        </a>
                                        <a href="index.php?vista=product_list" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                            <i class="fas fa-hamburger text-xs"></i> Catálogo Actual
                                        </a>

                                        <div class="border-t border-gray-100 my-1"></div>

                                        <!-- Sección Categorías -->
                                        <div class="px-4 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Categorías</div>
                                        <a href="index.php?vista=category_list" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                            <i class="fas fa-tags text-xs"></i> Ver Categorías
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 4. MARKETING (Campañas) - (Visible para Gerente, Admin) -->
                        <?php if (tiene_permiso('campanas.gestionar')): ?>
                            <div class="relative group">
                                <button class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors group-hover:text-indigo-600 <?php echo isActiveText(['promo_list', 'promo_new', 'ad_list', 'ad_new'], $current_vista); ?>">
                                    MARKETING
                                    <i class="fas fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform duration-200"></i>
                                </button>
                                <div class="absolute left-0 mt-0 w-48 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50">
                                    <div class="p-1">
                                        <a href="index.php?vista=promo_list" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                            <i class="fas fa-percent text-xs"></i> Promociones
                                        </a>
                                        <a href="index.php?vista=ad_list" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                            <i class="fas fa-bullhorn text-xs"></i> Anuncios / Banners
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 5. REPORTES (Estadísticas separadas) - (Visible para Gerente, Admin y Caja ve solo lo suyo) -->
                        <?php if (tiene_permiso('estadisticas.operativas') || tiene_permiso('estadisticas.financieras')): ?>
                            <div class="relative group">
                                <button class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors group-hover:text-indigo-600 <?php echo isActiveText(['orders_stats', 'sales_report'], $current_vista); ?>">
                                    REPORTES
                                    <i class="fas fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform duration-200"></i>
                                </button>
                                <div class="absolute left-0 mt-0 w-48 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50">
                                    <div class="p-1">
                                        <?php if (tiene_permiso('estadisticas.operativas')): ?>
                                            <a href="index.php?vista=orders_stats" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                                <i class="fas fa-chart-pie text-xs"></i> Estadísticas Generales
                                            </a>
                                        <?php endif; ?>

                                        <?php if (tiene_permiso('estadisticas.financieras')): ?>
                                            <a href="index.php?vista=sales_report" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                                <i class="fas fa-file-invoice-dollar text-xs"></i> Cierre de Caja
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- 6. ADMIN (Usuarios) - (Solo Super Admin) -->
                        <?php if (tiene_permiso('usuarios.gestionar')): ?>
                            <a href="index.php?vista=user_list" class="px-3 py-2 rounded-md text-sm font-medium text-red-500 hover:bg-red-50 transition-colors <?php echo isActiveText(['user_list', 'user_new'], $current_vista); ?>">
                                ADMIN
                            </a>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <!-- PERFIL Y MENU MOVIL -->
            <div class="flex items-center gap-2">
                <!-- Botón Menu Movil -->
                <div class="lg:hidden">
                    <button id="mobile-menu-button" type="button" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 focus:outline-none">
                        <i id="icon-open" class="fas fa-bars text-xl block"></i>
                    </button>
                </div>

                <!-- Dropdown Perfil -->
                <div class="relative ml-2">
                    <button id="profile-menu-button" type="button" class="relative flex items-center gap-2 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 p-1 hover:bg-gray-50 transition-colors">
                        <img class="h-8 w-8 rounded-full object-cover border border-gray-300" src="img/user.jpg" alt="Perfil">
                        <span class="hidden md:block text-sm font-medium text-gray-700 mr-2"><?php echo $_SESSION['usuario']; ?></span>
                        <i class="fas fa-chevron-down text-xs text-gray-400 hidden md:block"></i>
                    </button>

                    <div id="profile-menu" class="hidden absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-xl bg-white py-1 shadow-xl ring-1 ring-black ring-opacity-5 animate-fade-in-down">
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                            <p class="text-sm font-bold text-gray-900"><?php echo $_SESSION['nombre'] . " " . $_SESSION['apellido']; ?></p>
                            <p class="text-xs text-gray-500 truncate">@<?php echo $_SESSION['usuario']; ?></p>
                        </div>
                        <div class="p-1">
                            <a href="index.php?vista=configuration" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 rounded-lg">
                                <i class="fas fa-user-circle"></i> Mi Cuenta
                            </a>
                        </div>
                        <div class="border-t border-gray-100 p-1">
                            <a href="index.php?vista=logout" class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MENÚ MOVIL (Mobile) -->
    <div id="mobile-menu-container" class="lg:hidden relative z-[110]">
        <div id="mobile-menu-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity opacity-0 hidden"></div>

        <div id="mobile-menu-panel" class="fixed inset-y-0 left-0 z-[120] w-3/4 max-w-xs bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 flex flex-col">

            <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
                <span class="font-bold text-lg text-indigo-600">Alas Menu</span>
                <button id="mobile-menu-close-button" class="text-gray-500 hover:text-gray-800 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

                <!-- 1. INICIO (Solo visible si tiene permiso de estadísticas operativas) -->
                <?php if (tiene_permiso('estadisticas.operativas')): ?>
                    <a href="index.php?vista=home" class="block rounded-lg px-3 py-2.5 text-base font-medium <?php echo isActive('home', $current_vista); ?>">
                        <i class="fas fa-home w-6 text-center"></i> Inicio
                    </a>
                <?php endif; ?>

                <?php if (tiene_permiso('pedidos.ver')): ?>
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Pedidos</p>
                    </div>
                    <a href="index.php?vista=orders_kanban" class="block rounded-lg px-3 py-2 text-base font-medium <?php echo isActive('orders_kanban', $current_vista); ?>">
                        <i class="fas fa-columns w-6 text-center"></i> Tablero Kanban
                    </a>
                    <a href="index.php?vista=orders_list" class="block rounded-lg px-3 py-2 text-base font-medium <?php echo isActive('orders_list', $current_vista); ?>">
                        <i class="fas fa-list w-6 text-center"></i> Lista Histórica
                    </a>
                <?php endif; ?>

                <?php if (tiene_permiso('inventario.ver')): ?>
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Catálogo</p>
                    </div>
                    <a href="index.php?vista=product_new" class="block rounded-lg px-3 py-2 text-base font-medium <?php echo isActive('product_new', $current_vista); ?>">
                        <i class="fas fa-plus w-6 text-center"></i> Nuevo Platillo
                    </a>
                    <a href="index.php?vista=product_list" class="block rounded-lg px-3 py-2 text-base font-medium <?php echo isActive('product_list', $current_vista); ?>">
                        <i class="fas fa-hamburger w-6 text-center"></i> Lista Platillos
                    </a>
                    <a href="index.php?vista=category_list" class="block rounded-lg px-3 py-2 text-base font-medium <?php echo isActive('category_list', $current_vista); ?>">
                        <i class="fas fa-tags w-6 text-center"></i> Categorías
                    </a>
                <?php endif; ?>

                <?php if (tiene_permiso('campanas.gestionar')): ?>
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Marketing</p>
                    </div>
                    <a href="index.php?vista=promo_list" class="block rounded-lg px-3 py-2 text-base font-medium <?php echo isActive('promo_list', $current_vista); ?>">
                        <i class="fas fa-percent w-6 text-center"></i> Promociones
                    </a>
                <?php endif; ?>

                <?php if (tiene_permiso('estadisticas.operativas')): ?>
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Reportes</p>
                    </div>
                    <a href="index.php?vista=orders_stats" class="block rounded-lg px-3 py-2 text-base font-medium <?php echo isActive('orders_stats', $current_vista); ?>">
                        <i class="fas fa-chart-pie w-6 text-center"></i> Estadísticas
                    </a>
                <?php endif; ?>

                <?php if (tiene_permiso('usuarios.gestionar')): ?>
                    <div class="pt-4 pb-2">
                        <p class="px-3 text-xs font-bold text-red-400 uppercase tracking-wider">Admin</p>
                    </div>
                    <a href="index.php?vista=user_list" class="block rounded-lg px-3 py-2 text-base font-medium text-red-600 bg-red-50">
                        <i class="fas fa-users w-6 text-center"></i> Usuarios
                    </a>
                <?php endif; ?>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50">
                <a href="index.php?vista=logout" class="flex items-center justify-center gap-2 w-full rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</nav>