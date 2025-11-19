<?php
if (!isset($conexion)) {
    require_once "./php/main.php";
    $conexion = conexion();
}

// Consultas de datos
$categorias_stmt = $conexion->query("SELECT categoria_id, categoria_nombre FROM categoria ORDER BY categoria_nombre ASC");
$categorias = $categorias_stmt->fetchAll();

$productos_stmt = $conexion->query("SELECT producto_id, producto_nombre FROM producto ORDER BY producto_nombre ASC");
$productos = $productos_stmt->fetchAll();
?>

<form action="./php/anuncio_guardar.php" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-10 lg:pb-0" method="POST" autocomplete="off">

    <div class="sticky top-16 z-30 bg-white border-b border-slate-200 px-4 py-3 lg:px-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 lg:gap-4">
                <div class="hidden md:block bg-orange-100 text-orange-600 p-2 rounded-lg">
                    <i class="fas fa-bullhorn text-lg"></i>
                </div>
                <div>
                    <div class="opacity-70 scale-90 origin-left -mb-1 hidden sm:block">
                        <?php include "./inc/breadcrumb.php"; ?>
                    </div>
                    <h2 class="text-base lg:text-lg font-bold text-slate-800 leading-tight">Nuevo Anuncio</h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="index.php?vista=ad_list" class="hidden md:inline-block text-sm font-medium text-slate-500 hover:text-slate-800">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 lg:px-6 text-sm font-bold rounded-lg text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i> <span class="hidden sm:inline">Guardar</span><span class="sm:hidden">Guardar</span>
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 p-4 lg:p-6">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 lg:gap-6 items-start">

            <div class="md:col-span-2 lg:col-span-4 bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <i class="fas fa-pen text-slate-400"></i> Contenido
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Mensaje del Anuncio</label>
                        <textarea name="anuncio_mensaje" rows="4" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 resize-none" placeholder="Ej: ¡El desayuno termina a las 11 AM!" required></textarea>
                        <p class="text-[10px] text-slate-400 mt-1 text-right">Texto visible para el cliente</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tipo de Anuncio</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="anuncio_tipo" value="alerta" class="peer sr-only" checked>
                                <div class="p-2 rounded-lg border border-slate-200 text-center peer-checked:bg-orange-50 peer-checked:border-orange-500 peer-checked:text-orange-700 transition-all">
                                    <i class="fas fa-exclamation-triangle mb-1 block"></i>
                                    <span class="text-xs font-bold">Alerta</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="anuncio_tipo" value="info" class="peer sr-only">
                                <div class="p-2 rounded-lg border border-slate-200 text-center peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-700 transition-all">
                                    <i class="fas fa-info-circle mb-1 block"></i>
                                    <span class="text-xs font-bold">Info</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-1 lg:col-span-4 bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-slate-400"></i> Configuración
                </h3>

                <div class="grid grid-cols-2 gap-3 lg:gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Inicio (Hora)</label>
                        <input type="number" name="anuncio_hora_inicio" min="0" max="23" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" placeholder="0-23" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Fin (Hora)</label>
                        <input type="number" name="anuncio_hora_fin" min="0" max="23" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" placeholder="0-23" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Estado</label>
                        <select name="anuncio_estado" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Prioridad</label>
                        <input type="number" name="anuncio_prioridad" value="0" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 mt-2">
                    <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Rango de Fechas (Opcional)</p>
                    <div class="grid grid-cols-2 gap-3 lg:gap-4">
                        <input type="date" name="anuncio_fecha_inicio" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50">
                        <input type="date" name="anuncio_fecha_fin" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50">
                    </div>
                </div>
            </div>

            <div class="md:col-span-1 lg:col-span-4 space-y-4">

                <div class="bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-3 flex items-center gap-2">
                        <i class="fas fa-tags text-slate-400"></i> Categorías
                    </h3>

                    <div class="grid grid-cols-2 gap-2">
                        <?php foreach ($categorias as $cat): ?>
                            <label class="flex items-center p-1.5 border border-slate-100 rounded-lg hover:bg-orange-50 cursor-pointer transition-colors bg-slate-50/50">
                                <input name="categorias_vinculadas[]" type="checkbox" value="<?php echo $cat['categoria_id']; ?>" class="h-3.5 w-3.5 text-orange-600 border-slate-300 rounded focus:ring-orange-500 shrink-0">
                                <span class="ml-2 text-[11px] font-medium text-slate-700 truncate select-none">
                                    <?php echo htmlspecialchars($cat['categoria_nombre']); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-3 flex items-center gap-2">
                        <i class="fas fa-box text-slate-400"></i> Productos
                    </h3>

                    <div class="grid grid-cols-2 gap-2">
                        <?php foreach ($productos as $prod): ?>
                            <label class="flex items-center p-1.5 border border-slate-100 rounded-lg hover:bg-orange-50 cursor-pointer transition-colors bg-slate-50/50">
                                <input name="productos_vinculados[]" type="checkbox" value="<?php echo $prod['producto_id']; ?>" class="h-3.5 w-3.5 text-orange-600 border-slate-300 rounded focus:ring-orange-500 shrink-0">
                                <span class="ml-2 text-[11px] font-medium text-slate-700 truncate select-none">
                                    <?php echo htmlspecialchars($prod['producto_nombre']); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        </div>
    </div>
</form>