<?php
require_once "./php/main.php"; // Necesario para usar tiene_permiso()

// Obtener la vista actual
$vista_actual = $_GET['vista'] ?? 'home';

// --- 1. LÓGICA INTELIGENTE: ¿CUÁL ES EL "INICIO" DE ESTE USUARIO? ---

// Por defecto (Cajero/Despacho), su inicio es el Kanban
$vista_raiz = 'orders_kanban';
$link_inicio = 'orders_kanban';

// Si es Gerente/Dueño (tiene permisos de estadísticas), su inicio es el Home
if (tiene_permiso('estadisticas.operativas')) {
    $vista_raiz = 'home';
    $link_inicio = 'home';
}

// --- 2. AUTO-OCULTAR: Si estoy en MI página de inicio, no mostrar breadcrumb ---
if ($vista_actual == $vista_raiz) return;


/* CONFIGURACIÓN DE RUTAS */
$rutas = [
    // --- PRODUCTOS ---
    'product_list'   => ['texto' => 'Lista de Productos', 'padre' => 'Productos', 'padre_link' => 'product_list'],
    'product_new'    => ['texto' => 'Nuevo Producto',     'padre' => 'Productos', 'padre_link' => 'product_list'],
    'product_update' => ['texto' => 'Editar Producto',    'padre' => 'Productos', 'padre_link' => 'product_list'],
    'product_search' => ['texto' => 'Buscar Producto',    'padre' => 'Productos', 'padre_link' => 'product_list'],

    // --- CATEGORÍAS ---
    'category_list'   => ['texto' => 'Lista de Categorías', 'padre' => 'Categorías', 'padre_link' => 'category_list'],
    'category_new'    => ['texto' => 'Nueva Categoría',     'padre' => 'Categorías', 'padre_link' => 'category_list'],
    'category_search' => ['texto' => 'Buscar Categoría',    'padre' => 'Categorías', 'padre_link' => 'category_list'],

    // --- PROMOCIONES ---
    'promo_list'   => ['texto' => 'Lista de Promociones', 'padre' => 'Campaña', 'padre_link' => 'promo_list'],
    'promo_new'    => ['texto' => 'Nueva Promoción',      'padre' => 'Campaña', 'padre_link' => 'promo_list'],

    // --- ANUNCIOS ---
    'ad_list'      => ['texto' => 'Lista de Anuncios',   'padre' => 'Campaña', 'padre_link' => 'ad_list'],
    'ad_new'       => ['texto' => 'Nuevo Anuncio',       'padre' => 'Campaña', 'padre_link' => 'ad_list'],

    // --- USUARIOS ---
    'user_list'    => ['texto' => 'Lista de Usuarios',   'padre' => 'Usuarios', 'padre_link' => 'user_list'],
    'user_new'     => ['texto' => 'Nuevo Usuario',       'padre' => 'Usuarios', 'padre_link' => 'user_list'],
    'user_update'  => ['texto' => 'Editar Usuario',      'padre' => 'Usuarios', 'padre_link' => 'user_list'],
    'perfil'       => ['texto' => 'Mi Perfil',           'padre' => 'Usuario',  'padre_link' => $vista_raiz], // El padre del perfil es el inicio dinámico

    // --- PEDIDOS Y REPORTES ---
    'orders_list'   => ['texto' => 'Historial de Pedidos', 'padre' => 'Pedidos',  'padre_link' => 'orders_kanban'],
    'orders_kanban' => ['texto' => 'Tablero en Vivo',      'padre' => 'Pedidos',  'padre_link' => 'orders_kanban'],
    'order_search'  => ['texto' => 'Buscar Pedido',        'padre' => 'Pedidos',  'padre_link' => 'orders_list'],
    'orders_stats'  => ['texto' => 'Estadísticas',         'padre' => 'Reportes', 'padre_link' => 'orders_stats'],
    'sales_report'  => ['texto' => 'Cierre de Caja',       'padre' => 'Reportes', 'padre_link' => 'sales_report'],
];

// Datos por defecto si la vista no está en el array
$datos = $rutas[$vista_actual] ?? ['texto' => 'Página', 'padre' => 'Sistema', 'padre_link' => $vista_raiz];
?>

<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">

        <!-- ENLACE DE INICIO INTELIGENTE -->
        <li class="inline-flex items-center">
            <a href="<?php echo $link_inicio; ?>" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-orange-600 transition-colors">
                <i class="fa fa-home mr-2"></i> Inicio
            </a>
        </li>

        <?php if ($datos['padre']): ?>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 text-xs mx-1"></i>
                    <!-- El enlace del padre también verificamos que no sea redundante -->
                    <a href="<?php echo $datos['padre_link']; ?>" class="ml-1 text-sm font-medium text-gray-700 hover:text-orange-600 md:ml-2 transition-colors">
                        <?php echo $datos['padre']; ?>
                    </a>
                </div>
            </li>
        <?php endif; ?>

        <li aria-current="page">
            <div class="flex items-center">
                <i class="fas fa-chevron-right text-gray-400 text-xs mx-1"></i>
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                    <?php echo $datos['texto']; ?>
                </span>
            </div>
        </li>
    </ol>
</nav>