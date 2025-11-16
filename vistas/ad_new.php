<?php
// Necesitamos cargar los datos para los selectores antes de que el HTML comience
// Asumimos que la conexión ya está disponible o la incluimos
if (!isset($conexion)) {
    require_once "./php/main.php";
    $conexion = conexion();
}

// 1. Obtener todas las categorías
$categorias_stmt = $conexion->query("SELECT categoria_id, categoria_nombre FROM categoria ORDER BY categoria_nombre ASC");
$categorias = $categorias_stmt->fetchAll();

// 2. Obtener todos los productos
$productos_stmt = $conexion->query("SELECT producto_id, producto_nombre FROM producto ORDER BY producto_nombre ASC");
$productos = $productos_stmt->fetchAll();

// No cerramos la conexión aquí, puede que se use más abajo
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="index.php?vista=home" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fa fa-home mr-2"></i> Inicio
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fa fa-chevron-right text-gray-400"></i>
                        <a href="index.php?vista=ad_list" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Anuncios</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fa fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Nuevo Anuncio</span>
                    </div>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl font-bold tracking-tight text-gray-900 mt-4">
            Crear Nuevo Anuncio
        </h1>
        <p class="text-lg text-gray-600 mt-1">Configura el mensaje, horario y los vínculos del anuncio.</p>
    </div>

    <form action="./php/anuncio_guardar.php" class="FormularioAjax"  method="POST" autocomplete="off">

        <div class="lg:grid lg:grid-cols-3 lg:gap-8">

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Detalles del Anuncio</h3>

                        <div>
                            <label for="anuncio_mensaje" class="block text-sm font-medium text-gray-700">Mensaje del Anuncio</label>
                            <textarea id="anuncio_mensaje" name="anuncio_mensaje" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: ¡Hoy 2x1 en hamburguesas!" required></textarea>
                            <p class="text-xs text-gray-500 mt-1">Este es el texto que verán los clientes.</p>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="anuncio_hora_inicio" class="block text-sm font-medium text-gray-700">Hora de Inicio (0-23)</label>
                                <input type="number" id="anuncio_hora_inicio" name="anuncio_hora_inicio" min="0" max="23" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 8 (para 8:00 AM)" required>
                            </div>
                            <div>
                                <label for="anuncio_hora_fin" class="block text-sm font-medium text-gray-700">Hora de Fin (0-23)</label>
                                <input type="number" id="anuncio_hora_fin" name="anuncio_hora_fin" min="0" max="23" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 11 (para 11:00 AM)" required>
                                <p class="text-xs text-gray-500 mt-1">El anuncio se oculta a esta hora. (Ej: 11)</p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="anuncio_tipo" class="block text-sm font-medium text-gray-700">Tipo de Anuncio</slabel>
                                    <select id="anuncio_tipo" name="anuncio_tipo" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="info">Info </option>
                                        <option value="alerta">Alerta </option>
                                        <option value="oferta">Oferta </option>
                                    </select>
                            </div>
                            <div>
                                <label for="anuncio_estado" class="block text-sm font-medium text-gray-700">Estado</label>
                                <select id="anuncio_estado" name="anuncio_estado" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1">Activo (Mostrar)</option>
                                    <option value="0">Inactivo (Ocultar)</option>
                                </select>
                            </div>
                            <div>
                                <label for="anuncio_prioridad" class="block text-sm font-medium text-gray-700">Prioridad</label>
                                <input type="number" id="anuncio_prioridad" name="anuncio_prioridad" value="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">Número más alto se muestra primero.</p>
                            </div>
                        </div>

                        <div class="mt-6 border-t pt-4">
                            <h4 class="text-md font-semibold text-gray-600">Opcional: Rango de Fechas</h4>
                            <p class="text-sm text-gray-500 mb-2">Dejar en blanco para mostrar siempre (dentro del horario).</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="anuncio_fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                                    <input type="date" id="anuncio_fecha_inicio" name="anuncio_fecha_inicio" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label for="anuncio_fecha_fin" class="block text-sm font-medium text-gray-700">Fecha de Fin</label>
                                    <input type="date" id="anuncio_fecha_fin" name="anuncio_fecha_fin" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">
                            <i class="fa fa-link text-gray-400 mr-2"></i>Vínculos
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">Opcional: Haz clic en el anuncio para filtrar por categorías o productos. Puedes seleccionar varios.</p>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Vincular a Categorías</label>
                            <div class="mt-1 h-48 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
                                <?php foreach ($categorias as $cat): ?>
                                    <div class="flex items-center">
                                        <input id="cat-<?php echo $cat['categoria_id']; ?>"
                                            name="categorias_vinculadas[]"
                                            type="checkbox"
                                            value="<?php echo $cat['categoria_id']; ?>"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="cat-<?php echo $cat['categoria_id']; ?>" class="ml-3 block text-sm text-gray-700">
                                            <?php echo htmlspecialchars($cat['categoria_nombre']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Vincular a Productos</label>
                            <div class="mt-1 h-48 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
                                <?php foreach ($productos as $prod): ?>
                                    <div class="flex items-center">
                                        <input id="prod-<?php echo $prod['producto_id']; ?>"
                                            name="productos_vinculados[]"
                                            type="checkbox"
                                            value="<?php echo $prod['producto_id']; ?>"
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="prod-<?php echo $prod['producto_id']; ?>" class="ml-3 block text-sm text-gray-700">
                                            <?php echo htmlspecialchars($prod['producto_nombre']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-3">
            <a href="index.php?vista=ad_list" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-full hover:bg-gray-300 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-full shadow-md hover:bg-blue-700 transition-colors">
                <i class="fa fa-save mr-2"></i>
                Guardar Anuncio
            </button>
        </div>

    </form>
</div>