<?php
if (!isset($conexion)) {
    require_once "./php/main.php";
    $conexion = conexion();
}
// Obtener todos los productos
$productos_stmt = $conexion->query("SELECT producto_id, producto_nombre FROM producto WHERE producto_estado = 1 ORDER BY producto_nombre ASC");
$productos = $productos_stmt->fetchAll();

// Verificar el límite de 5 promociones activas
$check_limite = $conexion->query("SELECT COUNT(promo_id) FROM promociones WHERE estado = 1");
$total_promos_activas = (int) $check_limite->fetchColumn();
$limite_alcanzado = $total_promos_activas >= 5;
$carpeta_fotos = "anuncios";
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
                        <a href="index.php?vista=promo_list" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Promociones</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fa fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Nueva Promoción</span>
                    </div>
                </li>
            </ol>
        </nav>

        <h1 class="text-3xl font-bold tracking-tight text-gray-900 mt-4">
            Crear Nueva Promoción
        </h1>
        <p class="text-lg text-gray-600 mt-1">Configura una oferta (2x1, combo) para el carrusel de clientes.</p>
    </div>

    <?php if ($limite_alcanzado): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
            <p class="font-bold">Límite Alcanzado</p>
            <p>Has alcanzado el máximo de 5 promociones activas. Debes desactivar o eliminar una promoción existente para poder crear una nueva.</p>
            <a href="index.php?vista=promo_list" class="mt-2 inline-block font-medium text-yellow-800 hover:text-yellow-900">
                Ir a la lista de promociones &rarr;
            </a>
        </div>
    <?php else: ?>

        <form action="./php/promo_guardar.php" class="FormularioAjax" method="POST" autocomplete="off" enctype="multipart/form-data">
            <div class="lg:grid lg:grid-cols-3 lg:gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-4">Detalles de la Promoción</h3>

                            <div>
                                <label for="promo_nombre" class="block text-sm font-medium text-gray-700">Nombre de la Promoción</label>
                                <input type="text" id="promo_nombre" name="promo_nombre" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 2x1 en Hamburguesas" required>
                                <p class="text-xs text-gray-500 mt-1">Este es el título que verá el cliente en el carrusel.</p>
                            </div>

                            <div class="mt-4">
                                <label for="promo_precio" class="block text-sm font-medium text-gray-700">Precio Final (en USD)</label>
                                <input type="number" id="promo_precio" name="promo_precio" step="0.01" min="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 5.00" required>
                                <p class="text-xs text-gray-500 mt-1">El precio total que pagará el cliente por la promo (ej: el precio de 1 producto).</p>
                            </div>

                            <div class="mt-4">
                                <label for="promo_foto" class="block text-sm font-medium text-gray-700">Foto para el Carrusel</label>
                                <input type="file" id="promo_foto" name="promo_foto" class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                                        file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100" accept="image/jpeg, image/png, image/webp">
                                <p class="text-xs text-gray-500 mt-1">Opcional, pero muy recomendado.</p>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="hora_inicio" class="block text-sm font-medium text-gray-700">Hora de Inicio (0-23)</label>
                                    <input type="number" id="hora_inicio" name="hora_inicio" min="0" max="23" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 18 (para 6:00 PM)" required>
                                </div>
                                <div>
                                    <label for="hora_fin" class="block text-sm font-medium text-gray-700">Hora de Fin (0-23)</label>
                                    <input type="number" id="hora_fin" name="hora_fin" min="0" max="23" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ej: 22 (para 10:00 PM)" required>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                                    <select id="estado" name="estado" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="1">Activa (Mostrar)</option>
                                        <option value="0">Inactiva (Ocultar)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="prioridad" class="block text-sm font-medium text-gray-700">Prioridad</label>
                                    <input type="number" id="prioridad" name="prioridad" value="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="mt-6 border-t pt-4">
                                <h4 class="text-md font-semibold text-gray-600">Opcional: Rango de Fechas</h4>
                                <p class="text-sm text-gray-500 mb-2">Dejar en blanco para mostrar siempre (dentro del horario).</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700">Fecha de Fin</label>
                                        <input type="date" id="fecha_fin" name="fecha_fin" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
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
                                <i class="fa fa-link text-gray-400 mr-2"></i>Productos Incluidos
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">Selecciona los productos que forman parte de esta promoción (ej: 2 Hamburguesas, o 1 Hamburguesa + 1 Refresco).</p>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Seleccionar Productos</label>

                                <div class="mt-1 h-96 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
                                    <?php if (empty($productos)): ?>
                                        <span class="text-sm text-gray-500">No hay productos registrados</span>
                                    <?php else: ?>
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
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-3">
                <a href="index.php?vista=promo_list" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-full hover:bg-gray-300 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-full shadow-md hover:bg-blue-700 transition-colors">
                    <i class="fa fa-save mr-2"></i>
                    Guardar Promoción
                </button>
            </div>
        </form>
    <?php endif; 
    ?>
</div>