<?php
if (!isset($conexion)) {
    require_once "./php/main.php";
    $conexion = conexion();
}

$ad_id = (isset($_GET['ad_id_up'])) ? (int)$_GET['ad_id_up'] : 0;

// 1. Obtener datos del anuncio
$stmt_anuncio = $conexion->prepare("SELECT * FROM anuncios WHERE anuncio_id = :id");
$stmt_anuncio->execute([':id' => $ad_id]);
$anuncio = $stmt_anuncio->fetch();

// VALIDACIÓN DE ERROR (Diseño Centrado)
if (!$anuncio) {
    echo '
    <div class="flex items-center justify-center h-screen bg-slate-50">
        <div class="bg-white p-8 rounded-xl shadow-lg text-center border border-red-100 max-w-md">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fas fa-times"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Anuncio no encontrado</h3>
            <p class="text-slate-500 mb-6">El anuncio que intentas editar no existe o ha sido eliminado.</p>
            <a href="index.php?vista=ad_list" class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700">Volver al listado</a>
        </div>
    </div>';
    exit();
}

// 2. Obtener datos auxiliares
$categorias_stmt = $conexion->query("SELECT categoria_id, categoria_nombre FROM categoria ORDER BY categoria_nombre ASC");
$todas_categorias = $categorias_stmt->fetchAll();

$productos_stmt = $conexion->query("SELECT producto_id, producto_nombre FROM producto ORDER BY producto_nombre ASC");
$todos_productos = $productos_stmt->fetchAll();

// 3. Obtener Vínculos existentes
$stmt_cats_vinculadas = $conexion->prepare("SELECT categoria_id FROM anuncio_categorias WHERE anuncio_id = :id");
$stmt_cats_vinculadas->execute([':id' => $ad_id]);
$ids_cats_vinculadas = $stmt_cats_vinculadas->fetchAll(PDO::FETCH_COLUMN);

$stmt_prods_vinculados = $conexion->prepare("SELECT producto_id FROM anuncio_productos WHERE anuncio_id = :id");
$stmt_prods_vinculados->execute([':id' => $ad_id]);
$ids_prods_vinculados = $stmt_prods_vinculados->fetchAll(PDO::FETCH_COLUMN);
?>

<form action="./php/anuncio_actualizar.php" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-10 lg:pb-0" method="POST" autocomplete="off">

    <input type="hidden" name="anuncio_id" value="<?php echo $anuncio['anuncio_id']; ?>">

    <div class="sticky top-0 z-40 bg-white border-b border-slate-200 px-4 py-3 lg:px-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 lg:gap-4">
                <div class="hidden md:block bg-orange-100 text-orange-600 p-2 rounded-lg">
                    <i class="fas fa-edit text-lg"></i>
                </div>
                <div>
                    <div class="opacity-70 scale-90 origin-left -mb-1 hidden sm:block">
                        <?php include "./inc/breadcrumb.php"; ?>
                    </div>
                    <h2 class="text-base lg:text-lg font-bold text-slate-800 leading-tight">Editar Anuncio</h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="index.php?vista=ad_list" class="hidden md:inline-block text-sm font-medium text-slate-500 hover:text-slate-800">
                    Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 lg:px-6 text-sm font-bold rounded-lg text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-transform hover:-translate-y-0.5">
                    <i class="fas fa-sync-alt mr-2"></i> <span class="hidden sm:inline">Actualizar</span><span class="sm:hidden">Guardar</span>
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 p-4 lg:p-6">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 lg:gap-6 items-start">

            <div class="md:col-span-2 lg:col-span-4 bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <i class="fas fa-bullhorn text-slate-400"></i> Contenido
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Mensaje</label>
                        <textarea name="anuncio_mensaje" rows="4" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 resize-none" placeholder="Escribe el mensaje..." required><?php echo htmlspecialchars($anuncio['anuncio_mensaje']); ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Tipo de Anuncio</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer relative">
                                <input type="radio" name="anuncio_tipo" value="alerta" class="peer sr-only" <?php echo ($anuncio['anuncio_tipo'] == 'alerta') ? 'checked' : ''; ?>>
                                <div class="p-3 rounded-lg border border-slate-200 text-center peer-checked:bg-orange-50 peer-checked:border-orange-500 peer-checked:text-orange-700 transition-all hover:bg-slate-50">
                                    <i class="fas fa-exclamation-triangle mb-1 block text-lg"></i>
                                    <span class="text-xs font-bold">Alerta</span>
                                </div>
                            </label>

                            <label class="cursor-pointer relative">
                                <input type="radio" name="anuncio_tipo" value="info" class="peer sr-only" <?php echo ($anuncio['anuncio_tipo'] == 'info') ? 'checked' : ''; ?>>
                                <div class="p-3 rounded-lg border border-slate-200 text-center peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-700 transition-all hover:bg-slate-50">
                                    <i class="fas fa-info-circle mb-1 block text-lg"></i>
                                    <span class="text-xs font-bold">Info</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-1 lg:col-span-4 bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <i class="fas fa-cog text-slate-400"></i> Configuración
                </h3>

                <div class="grid grid-cols-2 gap-3 lg:gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Inicio (Hora)</label>
                        <input type="number" name="anuncio_hora_inicio" min="0" max="23" value="<?php echo $anuncio['anuncio_hora_inicio']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Fin (Hora)</label>
                        <input type="number" name="anuncio_hora_fin" min="0" max="23" value="<?php echo $anuncio['anuncio_hora_fin']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Estado</label>
                        <select name="anuncio_estado" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                            <option value="1" <?php echo ($anuncio['anuncio_estado'] == 1) ? 'selected' : ''; ?>>Activo</option>
                            <option value="0" <?php echo ($anuncio['anuncio_estado'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Prioridad</label>
                        <input type="number" name="anuncio_prioridad" value="<?php echo $anuncio['anuncio_prioridad']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 mt-2">
                    <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Rango de Fechas (Opcional)</p>
                    <div class="grid grid-cols-2 gap-3 lg:gap-4">
                        <div>
                            <label class="block text-[10px] text-slate-500 mb-1">Fecha Inicio</label>
                            <input type="date" name="anuncio_fecha_inicio" value="<?php echo $anuncio['anuncio_fecha_inicio']; ?>" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-500 mb-1">Fecha Fin</label>
                            <input type="date" name="anuncio_fecha_fin" value="<?php echo $anuncio['anuncio_fecha_fin']; ?>" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50">
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-1 lg:col-span-4 space-y-4">

                <div class="bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-3 flex items-center gap-2">
                        <i class="fas fa-tags text-slate-400"></i> Categorías
                    </h3>

                    <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto custom-scrollbar">
                        <?php foreach ($todas_categorias as $cat): ?>
                            <?php
                            $checked = in_array($cat['categoria_id'], $ids_cats_vinculadas) ? 'checked' : '';
                            $bg_active = $checked ? 'bg-orange-50 border-orange-200' : 'bg-slate-50/50 border-slate-100';
                            ?>
                            <label class="flex items-center p-1.5 border rounded-lg hover:bg-orange-50 cursor-pointer transition-colors <?php echo $bg_active; ?>">
                                <input name="categorias_vinculadas[]" type="checkbox" value="<?php echo $cat['categoria_id']; ?>" class="h-3.5 w-3.5 text-orange-600 border-slate-300 rounded focus:ring-orange-500 shrink-0" <?php echo $checked; ?>>
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

                    <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto custom-scrollbar">
                        <?php foreach ($todos_productos as $prod): ?>
                            <?php
                            $checked = in_array($prod['producto_id'], $ids_prods_vinculados) ? 'checked' : '';
                            $bg_active = $checked ? 'bg-orange-50 border-orange-200' : 'bg-slate-50/50 border-slate-100';
                            ?>
                            <label class="flex items-center p-1.5 border rounded-lg hover:bg-orange-50 cursor-pointer transition-colors <?php echo $bg_active; ?>">
                                <input name="productos_vinculados[]" type="checkbox" value="<?php echo $prod['producto_id']; ?>" class="h-3.5 w-3.5 text-orange-600 border-slate-300 rounded focus:ring-orange-500 shrink-0" <?php echo $checked; ?>>
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