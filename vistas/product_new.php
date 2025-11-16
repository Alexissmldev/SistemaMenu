<?php
require_once "./php/main.php";
?>

<div class="container mx-auto p-6 lg:p-10 ">

	<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
		<h1 class="text-3xl font-bold text-gray-800 flex items-center">
			<i class="fas fa-box-open mr-3 text-blue-600"></i>
			Crear Nuevo Producto
		</h1>
		<a href="index.php?vista=product_list" class="mt-3 sm:mt-0 py-2 px-4 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium">
			<i class="fas fa-arrow-left mr-1"></i>
			Volver a la lista
		</a>
	</div>

	<form id="formNuevoProducto" action="./php/producto_guarda.php" method="POST" class="FormularioAjax" autocomplete="off" enctype="multipart/form-data">
		<div class="form-rest mb-4"></div>

		<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 relative z-0">

			<div class="lg:col-span-2 space-y-8">

				<div class="bg-white p-6 shadow-lg rounded-xl border border-gray-100">
					<h3 class="text-xl font-semibold mb-5 border-b pb-3 text-gray-700">Detalles del Producto</h3>
					<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
						<div>
							<label for="producto_nombre" class="block text-sm font-medium text-gray-700">Nombre del Producto</label>
							<div class="relative mt-1">
								<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
									<i class="fas fa-tag h-5 w-5 text-gray-400"></i>
								</div>
								<input type="text" name="producto_nombre" id="producto_nombre" class="block w-full rounded-md border-gray-300 bg-gray-50 py-3 pl-10 shadow-sm focus:border-blue-500 focus:ring-blue-500" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}" maxlength="70" required>
							</div>
						</div>
						<div>
							<label for="producto_precio" class="block text-sm font-medium text-gray-700">Precio Base</label>
							<div class="relative mt-1">
								<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
									<i class="fas fa-dollar-sign h-5 w-5 text-gray-400"></i>
								</div>
								<input type="text" name="producto_precio" id="producto_precio" class="block w-full rounded-md border-gray-300 bg-gray-50 py-3 pl-10 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00" pattern="[0-9.]{1,25}" maxlength="25" required>
							</div>
						</div>
						<div class="sm:col-span-2">
							<label for="producto_descripcion" class="block text-sm font-medium text-gray-700">Descripción del Producto</label>
							<div class="relative mt-1">
								<textarea name="producto_descripcion" id="producto_descripcion" rows="4" class="block w-full rounded-md border-gray-300 bg-gray-50 py-3 px-4 shadow-sm focus:border-blue-500 focus:ring-blue-500" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,500}" maxlength="500"></textarea>
							</div>
						</div>
					</div>
				</div>

				<div class="bg-white p-6 shadow-lg rounded-xl border border-gray-100">
					<h3 class="text-xl font-semibold mb-5 border-b pb-3 text-gray-700">Variantes</h3>
					<p class="text-sm text-gray-500 mb-4">Añade tamaños, sabores u otras opciones. El precio de la variante es opcional</p>
					<div class="flex flex-wrap items-center gap-2 mb-4">
						<span class="text-sm font-medium text-gray-700 mr-2">Sugerencias:</span>
						<button type="button" class="preset-variant-btn" data-name="Pequeño" title="Pequeño">P</button>
						<button type="button" class="preset-variant-btn" data-name="Mediano" title="Mediano">M</button>
						<button type="button" class="preset-variant-btn" data-name="Grande" title="Grande">G</button>
						<button type="button" class="preset-variant-btn" data-name="Extra Grande" title="Extra Grande">XG</button>
						<button type="button" class="preset-variant-btn" data-name="Familiar" title="familiar">F</button>
						<button type="button" class="preset-variant-btn" data-name="Especial" title="Especial">ES</button>
					</div>
					<div id="variantes-container" class="space-y-3 mb-4">
					</div>
					<button type="button" id="add-variant-btn" class="w-full py-2 px-4 border-2 border-dashed border-blue-400 text-blue-600 rounded-lg hover:bg-blue-50 hover:border-blue-500 font-medium">
						<i class="fas fa-plus mr-2"></i>Añadir variante personalizada
					</button>
				</div>
			</div>

			<div class="lg:col-span-1 space-y-8">
				<div class="bg-white p-6 shadow-lg rounded-xl border border-gray-100">
					<h3 class="text-xl font-semibold mb-5 border-b pb-3 text-gray-700">Foto del producto</h3>
					<div class="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 bg-gray-50 px-6 pt-5 pb-6 drop-zone">
						<div class="space-y-1 text-center drop-zone-content">
							<i class="fas fa-image mx-auto h-12 w-12 text-gray-400"></i>
							<div class="flex text-sm text-gray-600">
								<label for="producto_foto" class="relative cursor-pointer rounded-md bg-transparent font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
									<input id="producto_foto" name="producto_foto" type="file" class="sr-only" accept=".jpg, .png, .jpeg">
									<span>Sube un archivo</span>
								</label>
								<p class="pl-1">o arrástralo aquí</p>
							</div>
							<p class="text-xs text-gray-500">JPG, JPEG, PNG hasta 3MB</p>
						</div>
					</div>
				</div>
				<div class="bg-white p-6 shadow-lg rounded-xl border border-gray-100">
					<h3 class="text-xl font-semibold mb-5 border-b pb-3 text-gray-700">Categoría</h3>
					<div id="categorySelectorContainer" class="flex items-center gap-x-2 mt-1">
						<select id="producto_categoria" name="producto_categoria" class="block w-full rounded-md border-gray-300 bg-gray-50 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500">
							<option value="" selected>Seleccione una opción</option>
							<?php
							$categorias = conexion();
							$categorias = $categorias->query("SELECT * FROM categoria ORDER BY categoria_nombre ASC");
							if ($categorias->rowCount() > 0) {
								$categorias = $categorias->fetchAll();
								foreach ($categorias as $row) {
									echo '<option value="' . $row['categoria_id'] . '" >' . $row['categoria_nombre'] . '</option>';
								}
							}
							$categorias = null;
							?>
						</select>
						<button type="button" id="addCategoryBtn" class="flex-shrink-0 rounded-md bg-blue-100 p-2.5 text-blue-600 hover:bg-blue-200" title="Crear nueva categoría">
							<i class="fas fa-plus h-5 w-5"></i>
						</button>
					</div>
					<div id="newCategoryForm" class="hidden mt-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
						<p class="text-sm font-medium text-gray-800 mb-2">Crear nueva categoría</p>
						<div id="newCategoryAlerts"></div>
						<div class="grid grid-cols-1 gap-4">
							<div>
								<label for="new_category_name" class="sr-only">Nombre</label>
								<input type="text" id="new_category_name" placeholder="Nombre de la categoría" class="block w-full rounded-md border-gray-300 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500">
							</div>
						</div>
						<div class="flex justify-end items-center gap-x-3 mt-4">
							<button type="button" id="cancelCategoryBtn" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancelar</button>
							<button type="button" id="saveCategoryBtn" class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Guardar</button>
						</div>
					</div>
				</div>
			</div>
		</div>
</div>
<div class="h-32 w-full"></div>
<div class="fixed bottom-0 left-0 right-0 z-50 w-full border-t border-gray-200 bg-white p-4">
	<div class="container mx-auto">
		<div class="grid grid-cols-2 gap-4 lg:flex lg:justify-end">
			<a href="index.php?vista=product_list" class="w-full text-center px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium
                                                lg:w-auto lg:py-2">
				Cancelar
			</a>
			<button type="submit" class="w-full text-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold
                                                lg:w-auto lg:py-2">
				<i class="fas fa-save mr-2"></i>
				Guardar
			</button>
		</div>
	</div>
</div>
</form>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		const variantesContainer = document.getElementById('variantes-container');
		const addVariantBtn = document.getElementById('add-variant-btn');
		const presetButtons = document.querySelectorAll('.preset-variant-btn');

		function addVariantRow(name = '', price = '') {
			const variantId = Date.now();
			const variantRow = document.createElement('div');
			variantRow.classList.add('flex', 'flex-col', 'md:flex-row', 'md:items-center', 'gap-3', 'p-3', 'bg-gray-50', 'rounded-lg', 'border');

			variantRow.innerHTML = `
            <i class="fas fa-grip-vertical text-gray-400 cursor-move hidden md:block" title="Reordenar"></i>
            
            <input type="text" name="variante_nombre[]" value="${name}" placeholder="Nombre (ej. Carne)" 
                   class="block w-full md:w-auto md:flex-1 rounded-md border-gray-300 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            
            <div class="relative w-full md:w-40">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <span class="text-gray-500">$</span>
                </div>
                <input type="text" name="variante_precio[]" value="${price}" placeholder="Precio (Opcional)" 
                       class="block w-full rounded-md border-gray-300 py-2 pl-7 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                       pattern="[0-9.]{1,25}" maxlength="25">
            </div>
            
            <button type="button" class="remove-variant-btn text-red-500 hover:text-red-700 p-2 self-end md:self-center" title="Eliminar variante">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;

			variantesContainer.appendChild(variantRow);
		}

		addVariantBtn.addEventListener('click', () => {
			addVariantRow();
		});

		presetButtons.forEach(button => {
			button.addEventListener('click', () => {
				const name = button.getAttribute('data-name');
				addVariantRow(name, '');
			});
		});

		variantesContainer.addEventListener('click', function(e) {
			const removeButton = e.target.closest('.remove-variant-btn');
			if (removeButton) {
				removeButton.closest('.flex').remove();
			}
		});

		const addCategoryBtn = document.getElementById('addCategoryBtn');
		const newCategoryForm = document.getElementById('newCategoryForm');
		const cancelCategoryBtn = document.getElementById('cancelCategoryBtn');
		const saveCategoryBtn = document.getElementById('saveCategoryBtn');
		const categorySelectorContainer = document.getElementById('categorySelectorContainer');

		if (addCategoryBtn) {
			addCategoryBtn.addEventListener('click', () => {
				categorySelectorContainer.classList.add('hidden');
				newCategoryForm.classList.remove('hidden');
			});
		}

		if (cancelCategoryBtn) {
			cancelCategoryBtn.addEventListener('click', () => {
				newCategoryForm.classList.add('hidden');
				categorySelectorContainer.classList.remove('hidden');
				document.getElementById('new_category_name').value = '';
			});
		}
	});
</script>