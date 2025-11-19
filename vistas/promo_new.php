<?php
if (!isset($conexion)) {
    require_once "./php/main.php";
    $conexion = conexion();
}

// Consultas de datos
$productos_stmt = $conexion->query("SELECT producto_id, producto_nombre FROM producto WHERE producto_estado = 1 ORDER BY producto_nombre ASC");
$productos = $productos_stmt->fetchAll();

$check_limite = $conexion->query("SELECT COUNT(promo_id) FROM promociones WHERE estado = 1");
$total_promos_activas = (int) $check_limite->fetchColumn();
$limite_alcanzado = $total_promos_activas >= 5;
?>

<form action="./php/promo_guardar.php" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-10 lg:pb-0" method="POST" autocomplete="off" enctype="multipart/form-data">

    <div class="sticky top-16 z-30 bg-white border-b border-slate-200 px-4 py-3 lg:px-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 lg:gap-4">
                <div class="hidden md:block bg-orange-100 text-orange-600 p-2 rounded-lg">
                    <i class="fas fa-tags text-lg"></i>
                </div>
                <div>
                    <div class="opacity-70 scale-90 origin-left -mb-1 hidden sm:block">
                        <?php include "./inc/breadcrumb.php"; ?>
                    </div>
                    <h2 class="text-base lg:text-lg font-bold text-slate-800 leading-tight">Nueva Promoción</h2>
                </div>
            </div>

            <?php if (!$limite_alcanzado): ?>
                <div class="flex items-center gap-3">
                    <a href="index.php?vista=promo_list" class="hidden md:inline-block text-sm font-medium text-slate-500 hover:text-slate-800">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 lg:px-6 text-sm font-bold rounded-lg text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-transform hover:-translate-y-0.5">
                        <i class="fas fa-save mr-2"></i> <span class="hidden sm:inline">Guardar</span><span class="sm:hidden">Guardar</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 p-4 lg:p-6">

        <?php if ($limite_alcanzado): ?>
            <div class="flex flex-col items-center justify-center h-full py-10">
                <div class="bg-white p-6 lg:p-8 rounded-2xl shadow-xl text-center max-w-sm lg:max-w-md border border-orange-100 mx-4">
                    <div class="w-14 h-14 lg:w-16 lg:h-16 bg-orange-100 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 text-xl lg:text-2xl">
                        <i class="fas fa-hand-paper"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-bold text-slate-800 mb-2">Límite Alcanzado</h3>
                    <p class="text-sm lg:text-base text-slate-500 mb-6">Has alcanzado el máximo de 5 promociones activas.</p>
                    <a href="index.php?vista=promo_list" class="block w-full px-6 py-3 bg-slate-800 text-white font-medium rounded-xl hover:bg-slate-700 text-sm">
                        Gestionar Promociones
                    </a>
                </div>
            </div>
        <?php else: ?>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 lg:gap-6 items-start">

                <div class="md:col-span-1 lg:col-span-3 bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                        <i class="fas fa-pen text-slate-400"></i> Info Básica
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nombre</label>
                            <input type="text" name="promo_nombre" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" placeholder="Ej: 2x1 Hamburguesas" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Precio ($)</label>
                            <input type="number" name="promo_precio" step="0.01" min="0" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" placeholder="0.00" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Foto</label>
                            <input type="file" name="promo_foto" class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 font-medium" accept="image/*">
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
                            <input type="number" name="hora_inicio" min="0" max="23" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" placeholder="0-23">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Fin (Hora)</label>
                            <input type="number" name="hora_fin" min="0" max="23" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" placeholder="0-23">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 lg:gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Estado</label>
                            <select name="estado" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Prioridad</label>
                            <input type="number" name="prioridad" value="0" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3 mt-2">
                        <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Rango de Fechas (Opcional)</p>
                        <div class="grid grid-cols-2 gap-3 lg:gap-4">
                            <div>
                                <input type="date" name="fecha_inicio" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50">
                            </div>
                            <div>
                                <input type="date" name="fecha_fin" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 lg:col-span-5 bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100 h-full">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                        <i class="fas fa-cubes text-slate-400"></i> Incluir Productos
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 lg:max-h-full overflow-y-auto lg:overflow-visible custom-scrollbar">
                        <?php if (empty($productos)): ?>
                            <p class="col-span-1 sm:col-span-2 text-sm text-slate-400 text-center py-4">No hay productos.</p>
                        <?php else: ?>
                            <?php foreach ($productos as $prod): ?>
                                <label class="flex items-center p-2 border border-slate-100 rounded-lg hover:bg-orange-50 cursor-pointer transition-colors bg-slate-50/50">
                                    <input name="productos_vinculados[]" type="checkbox" value="<?php echo $prod['producto_id']; ?>" class="h-4 w-4 text-orange-600 border-slate-300 rounded focus:ring-orange-500 shrink-0">
                                    <span class="ml-2 text-xs font-medium text-slate-700 truncate select-none">
                                        <?php echo htmlspecialchars($prod['producto_nombre']); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>
</form>