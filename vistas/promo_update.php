<?php
// OBTENER DATOS PARA EL FORMULARIO
if (!isset($conexion)) {
    require_once "./php/main.php";
    $conexion = conexion();
}

// 1. Obtener ID de la promo
$promo_id = (isset($_GET['promo_id_up'])) ? (int)$_GET['promo_id_up'] : 0;

// 2. Obtener datos de la promo
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

// 3. Obtener TODOS los productos disponibles
$productos_stmt = $conexion->query("SELECT producto_id, producto_nombre FROM producto WHERE producto_estado = 1 ORDER BY producto_nombre ASC");
$todos_productos = $productos_stmt->fetchAll();

// 4. Obtener productos YA VINCULADOS y sus CANTIDADES
// Nota: Asumo que tu tabla 'promocion_productos' tiene una columna 'cantidad'. 
// Si no la tiene y solo guarda IDs, el código usará 1 por defecto.
$stmt_vinculados = $conexion->prepare("SELECT producto_id, cantidad FROM promocion_productos WHERE promo_id = :id");
$stmt_vinculados->execute([':id' => $promo_id]);
$raw_vinculados = $stmt_vinculados->fetchAll();

// Convertimos a un array asociativo para búsqueda rápida: [id_producto => cantidad]
$vinculados_map = [];
foreach ($raw_vinculados as $v) {
    $vinculados_map[$v['producto_id']] = $v['cantidad'];
}

// 5. Verificar límite de promos activas (Lógica original conservada)
$check_limite = $conexion->query("SELECT COUNT(promo_id) FROM promociones WHERE estado = 1");
$total_promos_activas = (int) $check_limite->fetchColumn();
// El límite afecta si ya hay 5 y esta promo NO es una de las activas (está en 0)
$limite_alcanzado = ($total_promos_activas >= 5 && $promo['estado'] == 0);
?>

<form action="./php/promo_actualizar.php" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-20 lg:pb-0 font-sans" method="POST" autocomplete="off" enctype="multipart/form-data">

    <input type="hidden" name="promo_id" value="<?php echo $promo['promo_id']; ?>">
    <input type="hidden" name="prioridad" value="<?php echo $promo['prioridad']; ?>">

    <div class="sticky top-16 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 py-3 lg:px-6 shadow-sm">
        <div class="flex items-center justify-between max-w-7xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <div class="hidden md:flex bg-orange-100 text-orange-600 w-10 h-10 items-center justify-center rounded-xl shadow-sm">
                    <i class="fas fa-edit text-lg"></i>
                </div>
                <div>
                    <div class="opacity-60 scale-90 origin-left hidden sm:block">
                        <?php include "./inc/breadcrumb.php"; ?>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800 leading-tight">Editar Promoción</h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="index.php?vista=promo_list" class="hidden md:inline-block text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                    Cancelar
                </a>

                <?php if ($limite_alcanzado): ?>
                    <span class="text-xs font-bold text-orange-600 bg-orange-100 px-3 py-2 rounded-lg hidden sm:inline-block">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Límite alcanzado (No podrás activarla)
                    </span>
                <?php endif; ?>

                <button type="submit" class="inline-flex items-center px-6 py-2.5 text-sm font-bold rounded-xl text-white bg-orange-600 hover:bg-orange-700 shadow-lg shadow-orange-200 transition-all hover:-translate-y-0.5 active:scale-95">
                    <i class="fas fa-sync-alt mr-2"></i> Actualizar
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

            <div class="lg:col-span-4 space-y-6">

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs"><i class="fas fa-info"></i></span>
                        Información
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Nombre de la Promo</label>
                            <input type="text" name="promo_nombre" value="<?php echo htmlspecialchars($promo['promo_nombre']); ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-slate-50 font-medium placeholder-slate-400 outline-none transition-all" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Precio de Venta ($)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-slate-400 font-bold">$</span>
                                </div>
                                <input type="number" name="promo_precio" step="0.01" min="0" value="<?php echo htmlspecialchars($promo['promo_precio']); ?>" class="w-full pl-8 pr-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-slate-50 font-bold text-slate-700 outline-none transition-all" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Imagen Promocional</label>
                            <div class="relative w-full h-40 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors group cursor-pointer overflow-hidden">
                                <input type="file" name="promo_foto" id="inputFoto" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" onchange="previewImage(this)">

                                <?php
                                $img_src = "";
                                $has_img = false;
                                if (!empty($promo['promo_foto']) && is_file('./img/anuncios/large/' . $promo['promo_foto'])) {
                                    $img_src = './img/anuncios/large/' . $promo['promo_foto'];
                                    $has_img = true;
                                }
                                ?>

                                <div id="placeholderFoto" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 group-hover:text-orange-500 transition-colors <?php echo $has_img ? 'hidden' : ''; ?>">
                                    <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
                                    <span class="text-xs font-bold">Cambiar foto</span>
                                </div>

                                <img id="imgPreview" src="<?php echo $img_src; ?>" class="absolute inset-0 w-full h-full object-cover <?php echo $has_img ? '' : 'hidden'; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200">
                    <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center text-xs"><i class="fas fa-cog"></i></span>
                        Ajustes
                    </h3>

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Estado</label>
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 p-1 rounded-xl border border-slate-200">
                            <label class="cursor-pointer <?php echo $limite_alcanzado ? 'opacity-50 cursor-not-allowed' : ''; ?>">
                                <input type="radio" name="estado" value="1" class="peer sr-only" <?php echo ($promo['estado'] == 1) ? 'checked' : ''; ?> <?php echo $limite_alcanzado ? 'disabled' : ''; ?>>
                                <div class="text-center py-2 rounded-lg text-xs font-bold text-slate-500 peer-checked:bg-white peer-checked:text-green-600 peer-checked:shadow-sm border border-transparent peer-checked:border-slate-200 transition-all flex items-center justify-center gap-1">
                                    <i class="fas fa-check-circle"></i> Activa
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="estado" value="0" class="peer sr-only" <?php echo ($promo['estado'] == 0) ? 'checked' : ''; ?>>
                                <div class="text-center py-2 rounded-lg text-xs font-bold text-slate-500 peer-checked:bg-white peer-checked:text-slate-800 peer-checked:shadow-sm border border-transparent peer-checked:border-slate-200 transition-all flex items-center justify-center gap-1">
                                    <i class="fas fa-pause-circle"></i> Pausada
                                </div>
                            </label>
                        </div>
                        <?php if ($limite_alcanzado): ?>
                            <?php if ($promo['estado'] == 0): ?>
                                <input type="hidden" name="estado" value="0">
                            <?php endif; ?>
                            <p class="text-[10px] text-orange-500 mt-1 ml-1">Límite de activas alcanzado.</p>
                        <?php endif; ?>
                    </div>

                    <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100">
                        <p class="text-[10px] uppercase font-bold text-indigo-400 mb-2 flex items-center gap-1">
                            <i class="far fa-clock"></i> Disponibilidad Diaria
                        </p>
                        <div class="flex items-center gap-2">
                            <div class="relative w-full">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-indigo-300 font-bold">DE</span>
                                <input type="number" name="hora_inicio" value="<?php echo $promo['hora_inicio']; ?>" min="0" max="23" class="w-full text-center pl-6 pr-2 py-2 border border-indigo-100 rounded-lg text-sm font-bold text-indigo-900 bg-white focus:ring-1 focus:ring-indigo-500 outline-none placeholder-indigo-200">
                            </div>
                            <span class="text-indigo-300 font-bold">a</span>
                            <div class="relative w-full">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-indigo-300 font-bold">A</span>
                                <input type="number" name="hora_fin" value="<?php echo $promo['hora_fin']; ?>" min="0" max="23" class="w-full text-center pl-6 pr-2 py-2 border border-indigo-100 rounded-lg text-sm font-bold text-indigo-900 bg-white focus:ring-1 focus:ring-indigo-500 outline-none placeholder-indigo-200">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-[10px] uppercase font-bold text-slate-400">Vigencia (Opcional)</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-1">Desde</label>
                                <input type="date" name="fecha_inicio" value="<?php echo $promo['fecha_inicio']; ?>" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors outline-none">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-1">Hasta</label>
                                <input type="date" name="fecha_fin" value="<?php echo $promo['fecha_fin']; ?>" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 h-full flex flex-col">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 h-full flex flex-col">

                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-3 border-b border-slate-100 pb-4">
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center text-xs"><i class="fas fa-cubes"></i></span>
                            Composición de la Promo
                        </h3>

                        <div class="relative w-full sm:w-64">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" id="searchProd" onkeyup="filterProducts()" class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-orange-500 outline-none transition-all" placeholder="Buscar producto...">
                        </div>
                    </div>

                    <div class="mb-2 flex justify-between items-center text-xs px-1">
                        <span class="text-slate-400">Selecciona los productos y su cantidad</span>
                        <span class="font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded-md" id="countSelected">0 productos</span>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar pr-1 max-h-[500px] lg:max-h-none">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="productsGrid">
                            <?php if (empty($todos_productos)): ?>
                                <div class="col-span-full flex flex-col items-center justify-center py-10 text-slate-400">
                                    <i class="fas fa-box-open text-3xl mb-2 opacity-50"></i>
                                    <p class="text-sm">No hay productos registrados.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($todos_productos as $prod): ?>
                                    <?php
                                    $idProd = $prod['producto_id'];
                                    // Verificamos si este producto ya estaba guardado en la promo
                                    $is_linked = array_key_exists($idProd, $vinculados_map);
                                    $qty_val = $is_linked ? $vinculados_map[$idProd] : 1;

                                    // Estilos condicionales
                                    $container_class = $is_linked ? 'border-orange-400 bg-orange-50 shadow-sm' : 'border-slate-200 bg-white hover:border-orange-300 hover:bg-orange-50/30';
                                    $input_class = $is_linked ? 'bg-white border-orange-300' : 'bg-slate-50';
                                    $checked_attr = $is_linked ? 'checked' : '';
                                    $disabled_attr = $is_linked ? '' : 'disabled';
                                    ?>

                                    <div class="prod-item group relative flex items-center p-3 border rounded-xl transition-all duration-200 <?php echo $container_class; ?>">

                                        <div class="flex-1 flex items-center gap-3 min-w-0 cursor-pointer">
                                            <input
                                                type="checkbox"
                                                id="chk_<?php echo $idProd; ?>"
                                                name="productos_vinculados[]"
                                                value="<?php echo $idProd; ?>"
                                                class="peer h-5 w-5 text-orange-600 border-slate-300 rounded focus:ring-orange-500 cursor-pointer accent-orange-600"
                                                onchange="toggleQtyInput('<?php echo $idProd; ?>')"
                                                <?php echo $checked_attr; ?>>

                                            <label for="chk_<?php echo $idProd; ?>" class="text-sm font-medium text-slate-700 truncate peer-checked:text-orange-700 peer-checked:font-bold cursor-pointer select-none flex-1">
                                                <?php echo htmlspecialchars($prod['producto_nombre']); ?>
                                            </label>
                                        </div>

                                        <div class="flex items-center gap-1 pl-3 border-l border-slate-100 ml-2">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">Cant:</span>
                                            <input
                                                type="number"
                                                name="cantidades[<?php echo $idProd; ?>]"
                                                id="qty_<?php echo $idProd; ?>"
                                                value="<?php echo $qty_val; ?>"
                                                min="1"
                                                class="w-14 py-1 px-1 text-center text-sm font-bold border border-slate-200 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none text-slate-800 disabled:opacity-50 disabled:text-slate-400 transition-all <?php echo $input_class; ?>"
                                                <?php echo $disabled_attr; ?>>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<script>
    // 1. Lógica para habilitar/deshabilitar cantidad y contar seleccionados
    function toggleQtyInput(id) {
        const checkbox = document.getElementById('chk_' + id);
        const input = document.getElementById('qty_' + id);
        const parent = checkbox.closest('.prod-item');

        if (checkbox.checked) {
            input.disabled = false;
            input.classList.add('bg-white', 'border-orange-300');
            input.classList.remove('bg-slate-50');
            // Agregar clases de selección al contenedor
            parent.classList.add('border-orange-400', 'bg-orange-50', 'shadow-sm');
            parent.classList.remove('border-slate-200', 'bg-white');

            input.focus();
            input.select();
        } else {
            input.disabled = true;
            input.value = 1; // Resetear valor visualmente
            input.classList.remove('bg-white', 'border-orange-300');
            input.classList.add('bg-slate-50');
            // Quitar clases de selección al contenedor
            parent.classList.remove('border-orange-400', 'bg-orange-50', 'shadow-sm');
            parent.classList.add('border-slate-200', 'bg-white');
        }
        updateCounter();
    }

    function updateCounter() {
        const checked = document.querySelectorAll('input[name="productos_vinculados[]"]:checked').length;
        const label = document.getElementById('countSelected');
        label.textContent = checked + (checked === 1 ? ' producto' : ' productos');

        if (checked > 0) {
            label.classList.remove('bg-slate-100', 'text-slate-500');
            label.classList.add('bg-orange-100', 'text-orange-700');
        } else {
            label.classList.add('bg-slate-100', 'text-slate-500');
            label.classList.remove('bg-orange-100', 'text-orange-700');
        }
    }

    // 2. Buscador de Productos en tiempo real
    function filterProducts() {
        const input = document.getElementById('searchProd');
        const filter = input.value.toLowerCase();
        const grid = document.getElementById('productsGrid');
        const items = grid.getElementsByClassName('prod-item');

        for (let i = 0; i < items.length; i++) {
            const label = items[i].getElementsByTagName("label")[0];
            const txtValue = label.textContent || label.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                items[i].style.display = "";
            } else {
                items[i].style.display = "none";
            }
        }
    }

    // 3. Previsualización de Imagen
    function previewImage(input) {
        const preview = document.getElementById('imgPreview');
        const placeholder = document.getElementById('placeholderFoto');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }

            reader.readAsDataURL(input.files[0]);
        } else {
            // Si cancela la subida, volver a estado anterior o limpiar
            // En edición, idealmente si cancela no pasa nada visualmente si ya había img,
            // pero si limpiamos el input, volvemos al estado inicial.
        }
    }

    // Inicializar contador al cargar la página para reflejar los productos pre-seleccionados
    document.addEventListener('DOMContentLoaded', function() {
        updateCounter();
    });
</script>