<?php
// OBTENER DATOS PARA EL FORMULARIO 
if (!isset($conexion)) {
    require_once "./php/main.php";
    $conexion = conexion();
}

// Obtenemos el ID de la promo a editar
$promo_id = (isset($_GET['promo_id_up'])) ? (int)$_GET['promo_id_up'] : 0;

// Consulta 1: Obtener los datos principales de la promo 
$stmt_promo = $conexion->prepare("SELECT * FROM promociones WHERE promo_id = :id");
$stmt_promo->execute([':id' => $promo_id]);
$promo = $stmt_promo->fetch();

if (!$promo) {
    echo '
    <div class="flex items-center justify-center h-screen bg-slate-50">
        <div class="bg-white p-8 rounded-xl shadow-lg text-center border border-red-100 max-w-md">
            <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fas fa-times"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Error</h3>
            <p class="text-slate-500 mb-6">No se encontró la promoción solicitada.</p>
            <a href="index.php?vista=promo_list" class="px-6 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700">Volver</a>
        </div>
    </div>';
    exit();
}

// Obtener TODOS los productos 
$productos_stmt = $conexion->query("SELECT producto_id, producto_nombre FROM producto WHERE producto_estado = 1 ORDER BY producto_nombre ASC");
$todos_productos = $productos_stmt->fetchAll();

// Obtener IDs de productos YA VINCULADOS 
$stmt_prods_vinculados = $conexion->prepare("SELECT producto_id FROM promocion_productos WHERE promo_id = :id");
$stmt_prods_vinculados->execute([':id' => $promo_id]);
$ids_prods_vinculados = $stmt_prods_vinculados->fetchAll(PDO::FETCH_COLUMN);

// Verificar límite solo si la promo actual está inactiva y se quiere activar
$check_limite = $conexion->query("SELECT COUNT(promo_id) FROM promociones WHERE estado = 1");
$total_promos_activas = (int) $check_limite->fetchColumn();
// El límite afecta si ya hay 5 y esta promo NO es una de las activas (está en 0)
$limite_alcanzado = ($total_promos_activas >= 5 && $promo['estado'] == 0);
?>

<form action="./php/promo_actualizar.php" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-10 lg:pb-0" method="POST" autocomplete="off" enctype="multipart/form-data">

    <input type="hidden" name="promo_id" value="<?php echo $promo['promo_id']; ?>">

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
                    <h2 class="text-base lg:text-lg font-bold text-slate-800 leading-tight">Editar Promoción</h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="index.php?vista=promo_list" class="hidden md:inline-block text-sm font-medium text-slate-500 hover:text-slate-800">
                    Cancelar
                </a>

                <?php if ($limite_alcanzado): ?>
                    <span class="text-xs font-bold text-orange-600 bg-orange-100 px-3 py-2 rounded-lg mr-2 hidden sm:inline-block">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Límite alcanzado
                    </span>
                <?php endif; ?>

                <button type="submit" class="inline-flex items-center px-4 py-2 lg:px-6 text-sm font-bold rounded-lg text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-transform hover:-translate-y-0.5">
                    <i class="fas fa-sync-alt mr-2"></i> <span class="hidden sm:inline">Actualizar</span><span class="sm:hidden">Guardar</span>
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 p-4 lg:p-6">

        <?php if ($limite_alcanzado): ?>
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-yellow-600 mt-1"></i>
                <div>
                    <h4 class="text-sm font-bold text-yellow-800">Límite de Promociones Activas</h4>
                    <p class="text-xs text-yellow-700 mt-1">Ya tienes 5 promociones activas. Puedes editar el contenido de esta promoción, pero no podrás cambiar su estado a "Activa" hasta que desactives otra.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-4 lg:gap-6 items-start">

            <div class="md:col-span-1 lg:col-span-3 bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <i class="fas fa-pen text-slate-400"></i> Detalles
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nombre</label>
                        <input type="text" name="promo_nombre" value="<?php echo htmlspecialchars($promo['promo_nombre']); ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Precio Final ($)</label>
                        <input type="number" name="promo_precio" step="0.01" min="0" value="<?php echo htmlspecialchars($promo['promo_precio']); ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Imagen Actual</label>
                        <div class="relative group">
                            <?php
                            $img_url = './img/anuncios/estandar.jpg';
                            if (!empty($promo['promo_foto']) && is_file('./img/anuncios/large/' . $promo['promo_foto'])) {
                                $img_url = './img/anuncios/large/' . $promo['promo_foto'];
                            }
                            ?>
                            <div class="h-32 w-full rounded-lg overflow-hidden border border-slate-200 bg-slate-100 relative">
                                <img src="<?php echo $img_url; ?>" alt="Promo" class="w-full h-full object-cover">
                            </div>

                            <label class="block mt-2">
                                <span class="sr-only">Cambiar foto</span>
                                <input type="file" name="promo_foto" class="block w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 font-medium transition-colors" accept="image/jpeg, image/png, image/webp">
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
                        <input type="number" name="hora_inicio" min="0" max="23" value="<?php echo $promo['hora_inicio']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Fin (Hora)</label>
                        <input type="number" name="hora_fin" min="0" max="23" value="<?php echo $promo['hora_fin']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Estado</label>
                        <select name="estado" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 disabled:bg-slate-100 disabled:text-slate-400" <?php echo $limite_alcanzado ? 'disabled' : ''; ?>>
                            <option value="1" <?php echo ($promo['estado'] == 1) ? 'selected' : ''; ?>>Activa</option>
                            <option value="0" <?php echo ($promo['estado'] == 0) ? 'selected' : ''; ?>>Inactiva</option>
                        </select>
                        <?php if ($limite_alcanzado): ?>
                            <input type="hidden" name="estado" value="0">
                        <?php endif; ?>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Prioridad</label>
                        <input type="number" name="prioridad" value="<?php echo $promo['prioridad']; ?>" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 mt-2">
                    <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Rango de Fechas (Opcional)</p>
                    <div class="grid grid-cols-2 gap-3 lg:gap-4">
                        <div>
                            <label class="block text-[10px] text-slate-500 mb-1">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" value="<?php echo $promo['fecha_inicio']; ?>" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-500 mb-1">Fecha Fin</label>
                            <input type="date" name="fecha_fin" value="<?php echo $promo['fecha_fin']; ?>" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50">
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 lg:col-span-5 bg-white p-4 lg:p-5 rounded-xl shadow-sm border border-slate-100 h-full">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                    <i class="fas fa-cubes text-slate-400"></i> Productos Incluidos
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[400px] lg:max-h-full overflow-y-auto lg:overflow-visible custom-scrollbar">
                    <?php if (empty($todos_productos)): ?>
                        <p class="col-span-2 text-sm text-slate-400 text-center py-4">No hay productos activos.</p>
                    <?php else: ?>
                        <?php foreach ($todos_productos as $prod): ?>
                            <?php
                            $checked = in_array($prod['producto_id'], $ids_prods_vinculados) ? 'checked' : '';
                            // Estilo visual para los seleccionados (opcional)
                            $bg_class = ($checked) ? 'bg-orange-50 border-orange-200' : 'bg-slate-50/50 border-slate-100';
                            ?>
                            <label class="flex items-center p-2 border rounded-lg hover:bg-orange-50 cursor-pointer transition-colors <?php echo $bg_class; ?>">
                                <input name="productos_vinculados[]" type="checkbox" value="<?php echo $prod['producto_id']; ?>" class="h-4 w-4 text-orange-600 border-slate-300 rounded focus:ring-orange-500 shrink-0" <?php echo $checked; ?>>
                                <span class="ml-2 text-xs font-medium text-slate-700 truncate select-none">
                                    <?php echo htmlspecialchars($prod['producto_nombre']); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</form>