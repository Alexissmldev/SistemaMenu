<?php
// 1. LOGICA DE NAVEGACIÓN (Original)
$current_vista = $_GET['vista'] ?? 'home';

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

/* =========================================================
   2. NUEVA LÓGICA: CARGAR DATOS DE LA TIENDA (LOGO)
   ========================================================= */
require_once "./php/main.php";
$conexion = conexion();

// Buscamos la configuración de la tienda (ID 1)
$sql_navbar = "SELECT nombre_tienda, logo_tienda FROM tiendas WHERE id_tienda = 1 LIMIT 1";
$query_navbar = $conexion->query($sql_navbar);
$data_navbar = $query_navbar->fetch();

// Definir Logo y Nombre
$nav_nombre = $data_navbar['nombre_tienda'] ?? "Sistema";
$nav_logo_db = $data_navbar['logo_tienda'] ?? "";

// Validar si existe la imagen física
$nav_logo_src = (file_exists("./img/logo/" . $nav_logo_db) && !empty($nav_logo_db))
    ? "./img/logo/" . $nav_logo_db
    : "./img/logo.png"; // Fallback al logo del sistema por defecto
?>

<nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-[100] font-sans">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between">

            <!-- LOGO DINÁMICO -->
            <div class="flex items-center">
                <a href="index.php?vista=home" class="flex-shrink-0 flex items-center gap-2">
                    <img src="<?php echo $nav_logo_src; ?>"
                        alt="<?php echo htmlspecialchars($nav_nombre); ?>"
                        class="h-15 w-auto object-contain " /> <!-- Ajusté h-8 a h-10 para mejor visibilidad -->

                    
                </a>

                <div class="hidden lg:ml-10 lg:block">
                    <div class="flex space-x-1 items-center">

                        <a href="index.php?vista=home" class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo isActiveText('home', $current_vista); ?>">
                            INICIO
                        </a>

                        <div class="relative group">
                            <a href="index.php?vista=orders_list" class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors group-hover:text-indigo-600 <?php echo isActiveText(['orders_list', 'order_new', 'orders_kanban'], $current_vista); ?>">
                                PEDIDOS
                                <i class="fas fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform duration-200"></i>
                            </a>
                            <!-- Dropdown Pedidos (Opcional si quieres añadir submenús aquí) -->
                        </div>

                        <div class="relative group">
                            <button class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors group-hover:text-indigo-600 <?php echo isActiveText(['product_list', 'product_new', 'product_update'], $current_vista); ?>">
                                PRODUCTOS
                                <i class="fas fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform duration-200"></i>
                            </button>

                            <div class="absolute left-0 mt-0 w-48 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50">
                                <div class="p-1">
                                    <a href="index.php?vista=product_new" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                        <i class="fas fa-plus text-xs"></i> Nuevo Producto
                                    </a>
                                    <a href="index.php?vista=product_list" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                        <i class="fas fa-boxes text-xs"></i> Lista Productos
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="relative group">
                            <button class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors group-hover:text-indigo-600 <?php echo isActiveText(['category_list', 'category_new'], $current_vista); ?>">
                                CATEGORÍAS
                                <i class="fas fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform duration-200"></i>
                            </button>

                            <div class="absolute left-0 mt-0 w-48 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50">
                                <div class="p-1">
                                    <a href="index.php?vista=category_new" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                        <i class="fas fa-plus text-xs"></i> Nueva Categoría
                                    </a>
                                    <a href="index.php?vista=category_list" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                        <i class="fas fa-list text-xs"></i> Lista Categorías
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="relative group">
                            <button class="flex items-center gap-1 px-3 py-2 rounded-md text-sm font-medium transition-colors group-hover:text-indigo-600 <?php echo isActiveText(['promo_list', 'promo_new', 'ad_list', 'ad_new'], $current_vista); ?>">
                                CAMPAÑAS
                                <i class="fas fa-chevron-down text-xs opacity-50 group-hover:rotate-180 transition-transform duration-200"></i>
                            </button>

                            <div class="absolute left-0 mt-0 w-48 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform translate-y-2 group-hover:translate-y-0 z-50">
                                <div class="p-1">
                                    <div class="px-4 py-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Promociones</div>
                                    <a href="index.php?vista=promo_list" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                        <i class="fas fa-tags text-xs"></i> Gestionar Promos
                                    </a>

                                    <div class="border-t border-gray-100 my-1"></div>

                                    <div class="px-4 py-1 text-xs font-bold text-gray-400 uppercase tracking-wider">Anuncios</div>
                                    <a href="index.php?vista=ad_list" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg">
                                        <i class="fas fa-bullhorn text-xs"></i> Gestionar Anuncios
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- PERFIL Y MENU MOVIL -->
            <div class="flex items-center gap-2">
                <div class="lg:hidden">
                    <button id="mobile-menu-button" type="button" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-600 hover:bg-gray-100 focus:outline-none">
                        <i id="icon-open" class="fas fa-bars text-xl block"></i>
                    </button>
                </div>

                <div class="relative ml-2">
                    <button id="profile-menu-button" type="button" class="relative flex items-center gap-2 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 p-1 hover:bg-gray-50 transition-colors">
                        <!-- Foto de usuario estática o dinámica según tu sistema -->
                        <img class="h-8 w-8 rounded-full object-cover border border-gray-300" src="img/user.jpg" alt="Perfil">

                        <!-- NOMBRE DINÁMICO DE SESIÓN -->
                        <span class="hidden md:block text-sm font-medium text-gray-700 mr-2">
                            <?php echo $_SESSION['usuario']; ?>
                        </span>
                        <i class="fas fa-chevron-down text-xs text-gray-400 hidden md:block"></i>
                    </button>

                    <!-- DROPDOWN PERFIL -->
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

    <!-- MENÚ MÓVIL (SIDEBAR) -->
    <div id="mobile-menu-container" class="lg:hidden relative z-[110]">
        <div id="mobile-menu-backdrop" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity opacity-0 hidden"></div>

        <div id="mobile-menu-panel" class="fixed inset-y-0 left-0 z-[120] w-3/4 max-w-xs bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 flex flex-col">

            <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
                <span class="font-bold text-lg text-indigo-600">Menú</span>
                <button id="mobile-menu-close-button" class="text-gray-500 hover:text-gray-800 p-2">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <a href="index.php?vista=home" class="block rounded-lg px-3 py-2.5 text-base font-medium <?php echo isActive('home', $current_vista); ?>">
                    <i class="fas fa-home w-6 text-center"></i> Inicio
                </a>

                <a href="index.php?vista=orders_list" class="block rounded-lg px-3 py-2.5 text-base font-medium <?php echo isActive(['orders_list', 'orders_kanban'], $current_vista); ?>">
                    <i class="fas fa-clipboard-list w-6 text-center"></i> Pedidos
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Inventario</p>
                </div>

                <a href="index.php?vista=category_list" class="block rounded-lg px-3 py-2 text-base font-medium text-gray-500 hover:text-indigo-600 hover:bg-indigo-50">
                    <i class="fas fa-list w-6 text-center"></i> Categorías
                </a>
                <a href="index.php?vista=product_list" class="block rounded-lg px-3 py-2 text-base font-medium text-gray-500 hover:text-indigo-600 hover:bg-indigo-50">
                    <i class="fas fa-box w-6 text-center"></i> Productos
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Marketing</p>
                </div>

                <a href="index.php?vista=promo_list" class="block rounded-lg px-3 py-2 text-base font-medium text-gray-500 hover:text-indigo-600 hover:bg-indigo-50">
                    <i class="fas fa-tags w-6 text-center"></i> Promociones
                </a>
                <a href="index.php?vista=ad_list" class="block rounded-lg px-3 py-2 text-base font-medium text-gray-500 hover:text-indigo-600 hover:bg-indigo-50">
                    <i class="fas fa-bullhorn w-6 text-center"></i> Anuncios
                </a>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50">
                <a href="index.php?vista=logout" class="flex items-center justify-center gap-2 w-full rounded-lg bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </div>
</nav>