<?php
if (!isset($conexion)) {
    require_once "./php/main.php";
    $conexion = conexion();
}

// Consultas
$productos_stmt = $conexion->query("SELECT producto_id, producto_nombre FROM producto WHERE producto_estado = 1 ORDER BY producto_nombre ASC");
$productos = $productos_stmt->fetchAll();

$check_limite = $conexion->query("SELECT COUNT(promo_id) FROM promociones WHERE estado = 1");
$total_promos_activas = (int) $check_limite->fetchColumn();
$limite_alcanzado = $total_promos_activas >= 5;
?>

<form action="./php/promo_guardar.php" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-20 lg:pb-0 font-sans" method="POST" autocomplete="off" enctype="multipart/form-data">

    <input type="hidden" name="prioridad" value="1">

    <div class="sticky top-16 z-30 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 py-3 lg:px-6 shadow-sm">
        <div class="flex items-center justify-between max-w-7xl mx-auto w-full">
            <div class="flex items-center gap-4">
                <div class="hidden md:flex bg-orange-100 text-orange-600 w-10 h-10 items-center justify-center rounded-xl shadow-sm">
                    <i class="fas fa-tags text-lg"></i>
                </div>
                <div>
                    <div class="opacity-60 scale-90 origin-left hidden sm:block">
                        <?php include "./inc/breadcrumb.php"; ?>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800 leading-tight">Crear Promoción</h2>
                </div>
            </div>

            <?php if (!$limite_alcanzado): ?>
                <div class="flex items-center gap-3">
                    <a href="index.php?vista=promo_list" class="hidden md:inline-block text-sm font-bold text-slate-500 hover:text-slate-800 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-2.5 text-sm font-bold rounded-xl text-white bg-orange-600 hover:bg-orange-700 shadow-lg shadow-orange-200 transition-all hover:-translate-y-0.5 active:scale-95">
                        <i class="fas fa-save mr-2"></i> Guardar Promo
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">

        <?php if ($limite_alcanzado): ?>
            <div class="flex flex-col items-center justify-center h-[60vh] text-center">
                <div class="bg-white p-8 rounded-3xl shadow-xl border border-orange-100 max-w-md w-full relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-orange-400 to-red-500"></div>
                    <div class="w-20 h-20 bg-orange-50 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-6 text-3xl shadow-inner">
                        <i class="fas fa-hand-paper"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800 mb-2">Límite Alcanzado</h3>
                    <p class="text-slate-500 mb-8 leading-relaxed">Has alcanzado el máximo de <b class="text-slate-800">5 promociones activas</b>. Debes desactivar alguna para crear una nueva.</p>
                    <a href="index.php?vista=promo_list" class="flex items-center justify-center w-full px-6 py-3.5 bg-slate-900 text-white font-bold rounded-xl hover:bg-black transition-colors">
                        Ir a mis Promociones
                    </a>
                </div>
            </div>
        <?php else: ?>

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
                                <input type="text" name="promo_nombre" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-slate-50 font-medium placeholder-slate-400 outline-none transition-all" placeholder="Ej: Súper Pack Desayuno" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Precio de Venta ($)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-slate-400 font-bold">$</span>
                                    </div>
                                    <input type="number" name="promo_precio" step="0.01" min="0" class="w-full pl-8 pr-4 py-3 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-slate-50 font-bold text-slate-700 outline-none transition-all" placeholder="0.00" required>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1 ml-1">Imagen Promocional</label>
                                <div class="relative w-full h-40 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors group cursor-pointer overflow-hidden">
                                    <input type="file" name="promo_foto" id="inputFoto" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" accept="image/*" onchange="previewImage(this)">

                                    <div id="placeholderFoto" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 group-hover:text-orange-500 transition-colors">
                                        <i class="fas fa-cloud-upload-alt text-3xl mb-2"></i>
                                        <span class="text-xs font-bold">Toca para subir foto</span>
                                    </div>

                                    <img id="imgPreview" class="absolute inset-0 w-full h-full object-cover hidden">
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
                                <label class="cursor-pointer">
                                    <input type="radio" name="estado" value="1" class="peer sr-only" checked>
                                    <div class="text-center py-2 rounded-lg text-xs font-bold text-slate-500 peer-checked:bg-white peer-checked:text-green-600 peer-checked:shadow-sm border border-transparent peer-checked:border-slate-200 transition-all flex items-center justify-center gap-1">
                                        <i class="fas fa-check-circle"></i> Activa
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="estado" value="0" class="peer sr-only">
                                    <div class="text-center py-2 rounded-lg text-xs font-bold text-slate-500 peer-checked:bg-white peer-checked:text-slate-800 peer-checked:shadow-sm border border-transparent peer-checked:border-slate-200 transition-all flex items-center justify-center gap-1">
                                        <i class="fas fa-pause-circle"></i> Pausada
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="bg-indigo-50/50 p-3 rounded-xl border border-indigo-100">
                            <p class="text-[10px] uppercase font-bold text-indigo-400 mb-2 flex items-center gap-1">
                                <i class="far fa-clock"></i> Disponibilidad Diaria
                            </p>
                            <div class="flex items-center gap-2">
                                <div class="relative w-full">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-indigo-300 font-bold">DE</span>
                                    <input type="number" name="hora_inicio" value="0" min="0" max="23" class="w-full text-center pl-6 pr-2 py-2 border border-indigo-100 rounded-lg text-sm font-bold text-indigo-900 bg-white focus:ring-1 focus:ring-indigo-500 outline-none placeholder-indigo-200" placeholder="00">
                                </div>
                                <span class="text-indigo-300 font-bold">a</span>
                                <div class="relative w-full">
                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-indigo-300 font-bold">A</span>
                                    <input type="number" name="hora_fin" value="23" min="0" max="23" class="w-full text-center pl-6 pr-2 py-2 border border-indigo-100 rounded-lg text-sm font-bold text-indigo-900 bg-white focus:ring-1 focus:ring-indigo-500 outline-none placeholder-indigo-200" placeholder="23">
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
                                    <input type="date" name="fecha_inicio" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors outline-none">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-400 mb-1">Hasta</label>
                                    <input type="date" name="fecha_fin" class="w-full px-2 py-2 border border-slate-200 rounded-lg text-xs text-slate-600 bg-slate-50 focus:bg-white transition-colors outline-none">
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
                                <?php if (empty($productos)): ?>
                                    <div class="col-span-full flex flex-col items-center justify-center py-10 text-slate-400">
                                        <i class="fas fa-box-open text-3xl mb-2 opacity-50"></i>
                                        <p class="text-sm">No hay productos registrados.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($productos as $prod): ?>
                                        <?php $idProd = $prod['producto_id']; ?>

                                        <div class="prod-item group relative flex items-center p-3 border border-slate-200 rounded-xl hover:border-orange-300 hover:bg-orange-50/30 hover:shadow-md transition-all duration-200 bg-white">

                                            <div class="flex-1 flex items-center gap-3 min-w-0 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    id="chk_<?php echo $idProd; ?>"
                                                    name="productos_vinculados[]"
                                                    value="<?php echo $idProd; ?>"
                                                    class="peer h-5 w-5 text-orange-600 border-slate-300 rounded focus:ring-orange-500 cursor-pointer accent-orange-600"
                                                    onchange="toggleQtyInput('<?php echo $idProd; ?>')">
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
                                                    value="1"
                                                    min="1"
                                                    class="w-14 py-1 px-1 text-center text-sm font-bold border border-slate-200 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none bg-slate-50 text-slate-800 disabled:opacity-50 disabled:bg-slate-100 disabled:text-slate-400 transition-all"
                                                    disabled>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
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
            parent.classList.add('border-orange-400', 'bg-orange-50', 'shadow-sm');
            input.focus();
            input.select();
        } else {
            input.disabled = true;
            input.value = 1;
            input.classList.remove('bg-white', 'border-orange-300');
            input.classList.add('bg-slate-50');
            parent.classList.remove('border-orange-400', 'bg-orange-50', 'shadow-sm');
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
            preview.src = "";
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }
</script>