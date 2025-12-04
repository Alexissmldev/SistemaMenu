<?php
require_once "./php/main.php";
$conexion = conexion();

// Consultas de conteo
$total_productos  = (int) $conexion->query("SELECT COUNT(producto_id) FROM producto")->fetchColumn();
$total_categorias = (int) $conexion->query("SELECT COUNT(categoria_id) FROM categoria")->fetchColumn();
$total_promos     = (int) $conexion->query("SELECT COUNT(promo_id) FROM promociones")->fetchColumn();
$total_anuncios   = (int) $conexion->query("SELECT COUNT(anuncio_id) FROM anuncios")->fetchColumn();

$conexion = null;
?>

<div class="h-screen w-full bg-gray-50 flex flex-col font-sans overflow-y-auto md:overflow-hidden">

    <header class="bg-white border-b border-gray-200 h-20 px-4 md:px-8 flex items-center justify-between shadow-sm shrink-0 z-20 sticky top-0 md:static">
        <div class="flex items-center gap-3 md:gap-4">
            <div class="bg-gray-900 text-white p-2 md:p-3 rounded-xl shadow-lg">
                <i class="fas fa-user-astronaut text-xl md:text-2xl"></i>
            </div>
            <div>
                <h1 class="text-lg md:text-2xl font-bold text-gray-800">Hola, <?php echo $_SESSION['nombre']; ?></h1>
                <p class="text-[10px] md:text-xs text-gray-500 font-medium uppercase tracking-wider"><?php echo $_SESSION['apellido']; ?></p>
            </div>
        </div>
        <div class="bg-gray-100 px-3 py-1 md:px-4 md:py-2 rounded-lg border border-gray-200 hidden sm:block">
            <p class="text-xs md:text-sm font-bold text-gray-600 flex items-center gap-2">
                <i class="far fa-calendar-alt"></i> <?php echo date("d/m/Y"); ?>
            </p>
        </div>
    </header>

    <main class="flex-1 p-4 md:p-5 flex flex-col max-w-[1600px] mx-auto w-full justify-start md:justify-center gap-6 md:gap-6">

        <div class="flex flex-col shrink-0">
            <h2 class="text-gray-400 font-bold uppercase tracking-widest text-xs md:text-sm mb-4 flex items-center gap-2">
                <span class="w-6 md:w-8 h-0.5 bg-gray-300 rounded-full"></span> Resumen de Datos
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">

                <a href="index.php?vista=product_list" class="relative bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:-translate-y-1 transition-transform duration-300 flex flex-col justify-between group overflow-hidden min-h-[160px] md:min-h-[200px]">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-box-open text-6xl md:text-7xl text-orange-600"></i>
                    </div>
                    <div>
                        <p class="text-orange-600 font-bold uppercase tracking-wider mb-1 text-xs md:text-sm">Productos</p>
                        <p class="text-4xl md:text-5xl font-black text-gray-800"><?php echo $total_productos; ?></p>
                    </div>
                    <div class="text-gray-400 text-xs md:text-sm font-medium group-hover:text-orange-600 transition-colors flex items-center gap-2 mt-4">
                        Gestionar inventario <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

                <a href="index.php?vista=promo_list" class="relative bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:-translate-y-1 transition-transform duration-300 flex flex-col justify-between group overflow-hidden min-h-[160px] md:min-h-[200px]">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-tags text-6xl md:text-7xl text-purple-600"></i>
                    </div>
                    <div>
                        <p class="text-purple-600 font-bold uppercase tracking-wider mb-1 text-xs md:text-sm">Promociones</p>
                        <p class="text-4xl md:text-5xl font-black text-gray-800"><?php echo $total_promos; ?></p>
                    </div>
                    <div class="text-gray-400 text-xs md:text-sm font-medium group-hover:text-purple-600 transition-colors flex items-center gap-2 mt-4">
                        Ofertas activas <i class="fas fa-arrow-right"></i>
                    </div>
                </a>

                <a href="index.php?vista=ad_list" class="relative bg-white rounded-3xl p-6 shadow-lg border border-gray-100 hover:-translate-y-1 transition-transform duration-300 flex flex-col justify-between group overflow-hidden min-h-[160px] md:min-h-[200px]">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-bullhorn text-6xl md:text-7xl text-indigo-600"></i>
                    </div>
                    <div>
                        <p class="text-indigo-600 font-bold uppercase tracking-wider mb-1 text-xs md:text-sm">Anuncios</p>
                        <p class="text-4xl md:text-5xl font-black text-gray-800"><?php echo $total_anuncios; ?></p>
                    </div>
                    <div class="text-gray-400 text-xs md:text-sm font-medium group-hover:text-indigo-600 transition-colors flex items-center gap-2 mt-4">
                        Ver avisos <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </div>
        </div>

        <div class="flex-shrink-0 pb-4 md:pb-0">
            <h2 class="text-gray-400 font-bold uppercase tracking-widest text-xs md:text-sm mb-4 flex items-center gap-2">
                <span class="w-6 md:w-8 h-0.5 bg-gray-300 rounded-full"></span> Crear Nuevo
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">

                <a href="index.php?vista=product_new" class="group bg-orange-50 hover:bg-orange-500 rounded-2xl p-4 flex items-center gap-4 transition-all duration-300 border border-orange-100 hover:shadow-xl hover:shadow-orange-200 cursor-pointer">
                    <div class="bg-white text-orange-500 w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center text-lg md:text-xl shadow-sm group-hover:scale-110 transition-transform shrink-0">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div>
                        <span class="block text-sm md:text-base font-bold text-gray-800 group-hover:text-white">Producto</span>
                        <span class="text-xs text-orange-600/70 group-hover:text-orange-100">Agregar al menú</span>
                    </div>
                </a>

                <a href="index.php?vista=category_new" class="group bg-blue-50 hover:bg-blue-500 rounded-2xl p-4 flex items-center gap-4 transition-all duration-300 border border-blue-100 hover:shadow-xl hover:shadow-blue-200 cursor-pointer w-full text-left">
                    <div class="bg-white text-blue-500 w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center text-lg md:text-xl shadow-sm group-hover:scale-110 transition-transform shrink-0">
                        <i class="fas fa-folder-plus"></i>
                    </div>
                    <div>
                        <span class="block text-sm md:text-base font-bold text-gray-800 group-hover:text-white">Categoría</span>
                        <span class="text-xs text-blue-600/70 group-hover:text-blue-100">Nueva sección</span>
                    </div>
                </a>

                <a href="index.php?vista=promo_new" class="group bg-purple-50 hover:bg-purple-500 rounded-2xl p-4 flex items-center gap-4 transition-all duration-300 border border-purple-100 hover:shadow-xl hover:shadow-purple-200 cursor-pointer">
                    <div class="bg-white text-purple-500 w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center text-lg md:text-xl shadow-sm group-hover:scale-110 transition-transform shrink-0">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div>
                        <span class="block text-sm md:text-base font-bold text-gray-800 group-hover:text-white">Promoción</span>
                        <span class="text-xs text-purple-600/70 group-hover:text-purple-100">Crear oferta</span>
                    </div>
                </a>

                <a href="index.php?vista=ad_new" class="group bg-indigo-50 hover:bg-indigo-500 rounded-2xl p-4 flex items-center gap-4 transition-all duration-300 border border-indigo-100 hover:shadow-xl hover:shadow-indigo-200 cursor-pointer">
                    <div class="bg-white text-indigo-500 w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center text-lg md:text-xl shadow-sm group-hover:scale-110 transition-transform shrink-0">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div>
                        <span class="block text-sm md:text-base font-bold text-gray-800 group-hover:text-white">Anuncio</span>
                        <span class="text-xs text-indigo-600/70 group-hover:text-indigo-100">Publicar aviso</span>
                    </div>
                </a>

            </div>
        </div>

    </main>

    <div id="modal-container"></div>
</div>