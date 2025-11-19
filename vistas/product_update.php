<?php
require_once "./php/main.php";

$id = limpiar_cadena($_GET['product_id_up'] ?? 0);
$conexion = conexion();

/* ==================================================================
   1. CONSULTA ROBUSTA DEL PRODUCTO (SIN JOINS)
   Esto garantiza que el producto cargue incluso si su categoría tiene problemas.
   ================================================================== */
$check_producto = $conexion->prepare("SELECT * FROM producto WHERE producto_id = :id");
$check_producto->execute([':id' => $id]);

if ($check_producto->rowCount() > 0) {
    $datos = $check_producto->fetch(PDO::FETCH_ASSOC);

    /* ==================================================================
       2. CONSULTA DE VARIANTES
       Traemos las variantes asociadas a este producto
       ================================================================== */
    $check_variantes = $conexion->prepare("
        SELECT vp.id_variante_producto, vp.producto_id, vp.id_variante, vp.precio_variante, v.nombre_variante 
        FROM variante_producto vp
        INNER JOIN variante v ON vp.id_variante = v.id_variante
        WHERE vp.producto_id = :id
    ");
    $check_variantes->execute([':id' => $id]);
    $variantes_existentes = $check_variantes->fetchAll(PDO::FETCH_ASSOC);

    /* ==================================================================
       3. CONSULTAS PARA LAS LISTAS DESPLEGABLES
       ================================================================== */
    // Variantes disponibles para el autocompletado
    $variantes_db = $conexion->query("SELECT DISTINCT nombre_variante FROM variante ORDER BY nombre_variante ASC");
    $variantes_disponibles = $variantes_db->fetchAll(PDO::FETCH_COLUMN, 0);

    // Lista de todas las categorías
    $categorias_db = $conexion->query("SELECT * FROM categoria ORDER BY categoria_nombre ASC");
    $categorias = $categorias_db->fetchAll(PDO::FETCH_ASSOC);
?>

    <div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <form id="productForm" action="./php/producto_actualizar.php" method="POST" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-10 lg:pb-0" autocomplete="off" enctype="multipart/form-data">

        <input type="hidden" name="producto_id" value="<?php echo $datos['producto_id']; ?>">

        <div class="sticky top-16 z-30 bg-white border-b border-slate-200 px-4 py-3 lg:px-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 lg:gap-4">
                    <div class="hidden md:block bg-orange-100 text-orange-600 p-2 rounded-lg">
                        <i class="fas fa-edit text-lg"></i>
                    </div>
                    <div>
                        <div class="opacity-70 scale-90 origin-left -mb-1 hidden sm:block">
                            <?php include "./inc/breadcrumb.php"; ?>
                        </div>
                        <h2 class="text-base lg:text-lg font-bold text-slate-800 leading-tight">
                            Editar: <?= htmlspecialchars($datos['producto_nombre']) ?>
                        </h2>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="index.php?vista=product_list" class="hidden md:inline-block text-sm font-medium text-slate-500 hover:text-slate-800">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 lg:px-6 text-sm font-bold rounded-lg text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-transform hover:-translate-y-0.5">
                        <i class="fas fa-sync-alt mr-2"></i> <span class="hidden sm:inline">Actualizar Producto</span><span class="sm:hidden">Actualizar</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex-1 p-4 lg:p-6">
            <div class="form-rest mb-4"></div>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <div class="lg:col-span-8 space-y-6">

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                        <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                            <i class="fas fa-pen text-slate-400"></i> Información Básica
                        </h3>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nombre del Producto</label>
                                <input type="text" name="producto_nombre" value="<?php echo htmlspecialchars($datos['producto_nombre']); ?>" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-slate-50" required>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Precio Base ($)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold">$</span>
                                        <input type="text" name="producto_precio" id="producto_precio" value="<?php echo htmlspecialchars($datos['producto_precio']); ?>" class="block w-full pl-8 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" required>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Categoría</label>
                                    <div id="categorySelectorContainer" class="flex gap-2">
                                        <select name="producto_categoria" id="producto_categoria" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
                                            <?php
                                            foreach ($categorias as $cat):
                                                // Detección segura del ID (generalmente es categoria_id)
                                                $cat_id = $cat['categoria_id'];
                                                $selected = ($cat_id == $datos['categoria_id']) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $cat_id ?>" <?= $selected ?>>
                                                    <?= $cat['categoria_nombre'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Descripción (Ingredientes)</label>
                                <textarea name="producto_descripcion" rows="2" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 resize-none"><?php echo htmlspecialchars($datos['descripcion_producto']); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Estado del Producto</label>
                                <div class="flex items-center">
                                    <span class="text-sm text-slate-500 mr-3">No Disponible</span>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="producto_estado_check" class="sr-only peer" <?php echo ($datos['producto_estado'] == 1) ? 'checked' : ''; ?>>
                                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600"></div>
                                    </label>
                                    <span class="text-sm font-bold text-slate-700 ml-3">Disponible</span>
                                    <input type="hidden" name="producto_estado" value="<?php echo $datos['producto_estado']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 relative">
                        <div class="flex justify-between items-end border-b border-slate-100 pb-3 mb-4">
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <i class="fas fa-layer-group text-slate-400"></i> Variantes y Sabores
                            </h3>
                            <div class="hidden sm:flex gap-2">
                                <button type="button" class="quick-fill text-[10px] bg-slate-50 border border-slate-200 text-slate-600 hover:border-orange-400 hover:text-orange-600 px-2 py-1 rounded transition-all" data-val="Pequeño">Pequeño</button>
                                <button type="button" class="quick-fill text-[10px] bg-slate-50 border border-slate-200 text-slate-600 hover:border-orange-400 hover:text-orange-600 px-2 py-1 rounded transition-all" data-val="Grande">Grande</button>
                            </div>
                        </div>

                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 mb-4">
                            <div class="grid grid-cols-12 gap-3 items-end">
                                <div class="col-span-7 sm:col-span-6 relative">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Nombre Variante</label>
                                    <input type="text" id="input_variante_nombre" class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500" placeholder="Ej. Grande, Con Queso..." autocomplete="off">
                                    <ul id="custom-dropdown-list" class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-lg shadow-xl max-h-48 overflow-y-auto mt-1">
                                        <?php foreach ($variantes_disponibles as $v): ?>
                                            <li class="dropdown-item px-3 py-2 hover:bg-orange-50 hover:text-orange-700 cursor-pointer text-xs text-slate-700 border-b border-slate-50 last:border-0"><?= $v ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <div class="col-span-5 sm:col-span-4">
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Precio ($)</label>
                                    <input type="number" id="input_variante_precio" step="0.01" class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500" placeholder="0.00">
                                </div>

                                <div class="col-span-12 sm:col-span-2">
                                    <button type="button" id="btn-add-variant" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-medium rounded-lg text-sm py-2 transition-colors shadow-sm">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-lg border border-slate-200">
                            <table class="w-full text-sm text-left text-slate-500">
                                <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                                    <tr>
                                        <th class="px-4 py-3">Variante</th>
                                        <th class="px-4 py-3">Precio</th>
                                        <th class="px-4 py-3 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-variantes-body" class="bg-white divide-y divide-slate-100">
                                    <?php if (count($variantes_existentes) > 0): ?>
                                        <?php foreach ($variantes_existentes as $ve): ?>
                                            <tr class="variant-row group hover:bg-orange-50 transition-colors">
                                                <td class="px-4 py-3 font-medium text-slate-800">
                                                    <?= htmlspecialchars($ve['nombre_variante']) ?>

                                                    <input type="hidden" name="variante_nombre[]" value="<?= htmlspecialchars($ve['nombre_variante']) ?>">
                                                </td>
                                                <td class="px-4 py-3 text-slate-600">
                                                    $<?= number_format($ve['precio_variante'], 2) ?>
                                                    <input type="hidden" name="variante_precio[]" value="<?= $ve['precio_variante'] ?>">
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <button type="button" class="btn-remove-variant text-red-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition-all" title="Eliminar variante">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr id="row-empty-state">
                                            <td colspan="3" class="px-6 py-8 text-center text-slate-400">
                                                <p class="text-xs italic">No hay variantes agregadas aún.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="hidden-inputs-container"></div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 h-full">
                        <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 flex items-center gap-2">
                            <i class="fas fa-image text-slate-400"></i> Imagen del Producto
                        </h3>

                        <?php if (is_file("./img/producto/" . $datos['producto_foto'])): ?>
                            <div class="mb-4 text-center">
                                <p class="text-xs font-bold text-slate-500 mb-2 uppercase">Imagen Actual</p>
                                <img src="./img/producto/<?php echo $datos['producto_foto']; ?>" alt="Imagen actual" class="w-full h-48 object-cover rounded-lg border border-slate-200">
                            </div>
                        <?php endif; ?>

                        <label class="drop-zone group relative flex flex-col items-center justify-center w-full h-64 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-orange-50 hover:border-orange-300 transition-all">
                            <div class="drop-zone-content flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center z-10">
                                <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-cloud-upload-alt text-orange-500 text-xl"></i>
                                </div>
                                <p class="text-sm text-slate-500 mb-1"><span class="font-bold text-slate-700">Cambiar imagen</span></p>
                                <p class="text-[10px] text-slate-400">JPG, PNG, JPEG (Max 5MB)</p>
                                <p id="file-name-display" class="mt-4 text-xs font-bold text-orange-600 bg-orange-100 px-2 py-1 rounded hidden"></p>
                            </div>
                            <input id="producto_foto" name="producto_foto" type="file" class="hidden" accept=".jpg, .png, .jpeg" />
                        </label>
                    </div>
                </div>

            </div>
        </div>

    </form>
    <script src="./js/product_new.js"></script>

<?php
} else {
    echo '<div class="w-full h-screen flex items-center justify-center"><div role="alert" class="rounded border-s-4 border-red-500 bg-red-50 p-4"><strong class="block font-medium text-red-800">Error</strong><p class="mt-2 text-sm text-red-700">No se encontró el producto solicitado.</p></div></div>';
}
$check_producto = null;
$conexion = null;
?>