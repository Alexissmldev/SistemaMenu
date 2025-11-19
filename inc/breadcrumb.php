<?php
// Obtener la vista actual de la URL, por defecto 'home'
$vista_actual = $_GET['vista'] ?? 'home';

// Si estamos en el home, no mostramos el breadcrumb (opcional)
if ($vista_actual == 'home') return;

/* CONFIGURACIÓN DE RUTAS
   - 'texto': El nombre que se ve en la pantalla.
   - 'padre': El nombre de la categoría superior.
   - 'padre_link': A dónde lleva el click de la categoría superior.
*/
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
    'perfil'       => ['texto' => 'Mi Perfil',           'padre' => 'Usuario',  'padre_link' => 'home'],
];

// Datos por defecto si la vista no está en el array
$datos = $rutas[$vista_actual] ?? ['texto' => 'Página', 'padre' => 'Sistema', 'padre_link' => 'home'];
?>

<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        
        <li class="inline-flex items-center">
            <a href="index.php?vista=home" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-orange-600 transition-colors">
                <i class="fa fa-home mr-2"></i> Inicio
            </a>
        </li>

        <?php if($datos['padre']): ?>
        <li>
            <div class="flex items-center">
                <i class="fa fa-chevron-right text-gray-400 text-xs mx-1"></i>
                <a href="index.php?vista=<?php echo $datos['padre_link']; ?>" class="ml-1 text-sm font-medium text-gray-700 hover:text-orange-600 md:ml-2 transition-colors">
                    <?php echo $datos['padre']; ?>
                </a>
            </div>
        </li>
        <?php endif; ?>

        <li aria-current="page">
            <div class="flex items-center">
                <i class="fa fa-chevron-right text-gray-400 text-xs mx-1"></i>
                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">
                    <?php echo $datos['texto']; ?>
                </span>
            </div>
        </li>
    </ol>
</nav>