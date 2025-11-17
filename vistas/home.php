<?php
// Conexión y consultas para obtener los datos reales
require_once "./php/main.php";
$conexion = conexion();

// Ejemplo: Total de productos
$total_productos = $conexion->query("SELECT COUNT(producto_id) FROM producto");
$total_productos = (int)$total_productos->fetchColumn();

// Ejemplo: Total de categorías
$total_categorias = $conexion->query("SELECT COUNT(categoria_id) FROM categoria");
$total_categorias = (int)$total_categorias->fetchColumn();

$conexion = null;
?>

<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">¡Bienvenido, <?php echo $_SESSION['nombre']; ?>!</h1>
            <p class="text-gray-600 mt-1">Aquí tienes un resumen de la actividad de tu sistema.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <div class="bg-white shadow-lg p-6 rounded-2xl border border-gray-200 flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Productos</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_productos; ?></p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-xl">
                        <i class="fa fa-shopping-cart h-6 w-6 text-green-600" aria-hidden="true"></i>
                    </div>
                </div>
                <a href="index.php?vista=product_list" class="text-sm font-medium text-green-600 hover:text-green-800 mt-4 inline-block">Gestionar productos →</a>
            </div>

            <div class="bg-white shadow-lg p-6 rounded-2xl border border-gray-200 flex flex-col justify-between hover:-translate-y-1 transition-transform duration-300">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Categorías</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_categorias; ?></p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-xl">
                        <i class="fa fa-list h-6 w-6 text-yellow-600" aria-hidden="true"></i>
                    </div>
                </div>
                <a href="index.php?vista=category_list" class="text-sm font-medium text-yellow-600 hover:text-yellow-800 mt-4 inline-block">Gestionar categorías →</a>
            </div>

            <div class="bg-white shadow-lg p-6 rounded-2xl border border-gray-200 md:col-span-2 lg:col-span-3">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-2xl font-bold text-gray-800">Acciones Rápidas</p>
                        <p class="text-sm text-gray-500 mt-1">Añade nuevos elementos a tu inventario o sistema.</p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-xl">
                        <i class="fa fa-bolt h-6 w-6 text-indigo-600" aria-hidden="true"></i>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-start gap-3">
                    <a href="index.php?vista=product_new" class="w-full sm:w-auto text-center px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors">Añadir Producto</a>
                    <a onclick="openModal('category_new', '' , '' , '')" class="w-full sm:w-auto text-center px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors">Añadir Categoría</a>
                    <a href="index.php?vista=promo_new" class="w-full sm:w-auto text-center px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors">Añadir Anuncio</a>
                    <a href="index.php?vista=ad_new" class="w-full sm:w-auto text-center px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors">Añadir Promocion</a>
                </div>
            </div>

        </div>
    </div>
    <div id="modal-container"></div>
</div>