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
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.51 0 .962-.328 1.09-.824l1.455-5.324A1.125 1.125 0 0017.25 6H5.25m4.5-3V1.5M12.75 3V1.5M15 12.75V15m-4.5-2.25V15M7.5 15h7.5" /></svg>
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
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
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
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:justify-start gap-3">
                     <button onclick="openModal('product_new', '' , '' , 'initProductModalScripts')" class="w-full sm:w-auto text-center px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors">Añadir Producto</button>
                     <button onclick="openModal('category_new', '' , '' , '')" class="w-full sm:w-auto text-center px-4 py-2 bg-indigo-50 text-indigo-700 font-semibold rounded-lg hover:bg-indigo-100 transition-colors">Añadir Categoría</button>
                </div>
            </div>
            
        </div>
    </div>
    <div id="modal-container"></div>
</div>