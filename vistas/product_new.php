<?php
require_once "./php/main.php";

$conexion = conexion();

// 1. Variantes (Lista simple para el autocompletado)
$variantes_db = $conexion->query("SELECT DISTINCT nombre_variante FROM variante ORDER BY nombre_variante ASC");
$variantes_disponibles = $variantes_db->fetchAll(PDO::FETCH_COLUMN, 0);

// 2. Categorías (Lista completa con ID y Nombre)
$categorias_db = $conexion->query("SELECT * FROM categoria ORDER BY categoria_nombre ASC");
$categorias = $categorias_db->fetchAll();

$conexion = null;
?>

<div id="toast-container" class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

<form id="productForm" action="./php/producto_guarda.php" method="POST" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-10 lg:pb-0" autocomplete="off" enctype="multipart/form-data">

	<div class="sticky top-16 z-30  bg-white border-b border-slate-200 px-4 py-3 lg:px-6 shadow-sm">
		<div class="flex items-center justify-between">
			<div class="flex items-center gap-3 lg:gap-4">
				<div class="hidden md:block bg-orange-100 text-orange-600 p-2 rounded-lg">
					<i class="fas fa-hamburger text-lg"></i>
				</div>
				<div>
					<div class="opacity-70 scale-90 origin-left -mb-1 hidden sm:block">
						<?php include "./inc/breadcrumb.php"; ?>
					</div>
					<h2 class="text-base lg:text-lg font-bold text-slate-800 leading-tight">Nuevo Producto</h2>
				</div>
			</div>

			<div class="flex items-center gap-3">
				<a href="index.php?vista=product_list" class="hidden md:inline-block text-sm font-medium text-slate-500 hover:text-slate-800">
					Cancelar
				</a>
				<button type="submit" class="inline-flex items-center px-4 py-2 lg:px-6 text-sm font-bold rounded-lg text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-transform hover:-translate-y-0.5">
					<i class="fas fa-save mr-2"></i> <span class="hidden sm:inline">Guardar Producto</span><span class="sm:hidden">Guardar</span>
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
							<input type="text" name="producto_nombre" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-slate-50" placeholder="Ej. Hamburguesa Clásica" required>
						</div>

						<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
							<div>
								<label class="block text-xs font-bold text-slate-600 uppercase mb-1">Precio Base ($)</label>
								<div class="relative">
									<span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold">$</span>
									<input type="text" name="producto_precio" id="producto_precio" class="block w-full pl-8 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50" placeholder="0.00" required>
								</div>
							</div>

							<div>
								<label class="block text-xs font-bold text-slate-600 uppercase mb-1">Categoría</label>
								<div id="categorySelectorContainer" class="flex gap-2">
									<select name="producto_categoria" id="producto_categoria" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50">
										<option value="" selected>Seleccionar...</option>
										<?php foreach ($categorias as $cat): ?>
											<option value="<?= $cat['categoria_id'] ?>"><?= $cat['categoria_nombre'] ?></option>
										<?php endforeach; ?>
									</select>
									<button type="button" id="addCategoryBtn" class="px-3 text-orange-600 bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded-lg transition-colors" title="Nueva Categoría">
										<i class="fas fa-plus"></i>
									</button>
								</div>

								<div id="newCategoryForm" class="hidden mt-3 rounded-lg border border-orange-200 bg-orange-50 p-3 animate-fade-in-down relative">
									<div class="absolute -top-2 right-8 w-4 h-4 bg-orange-50 border-t border-l border-orange-200 transform rotate-45"></div>
									<p class="text-[10px] font-bold text-orange-800 mb-2 uppercase">Crear Categoría Rápida</p>

									<div id="newCategoryAlerts" class="mb-2"></div>

									<div class="flex flex-col gap-2">
										<input type="text" id="new_category_name" placeholder="Nombre nueva categoría..." class="block w-full rounded-md border-orange-200 focus:border-orange-500 focus:ring-orange-500 text-xs py-2">
										<div class="flex gap-2 justify-end">
											<button type="button" id="cancelCategoryBtn" class="px-3 py-1 text-xs font-medium text-slate-600 hover:text-slate-800 transition-colors">Cancelar</button>
											<button type="button" id="saveCategoryBtn" class="px-3 py-1 text-xs font-bold text-white bg-orange-600 rounded shadow-sm hover:bg-orange-700">Guardar</button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div>
							<label class="block text-xs font-bold text-slate-600 uppercase mb-1">Descripción (Ingredientes)</label>
							<textarea name="producto_descripcion" rows="2" class="block w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 bg-slate-50 resize-none" placeholder="Carne, queso, lechuga..."></textarea>
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
								<input type="text" id="input_variante_nombre" class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500" placeholder="Escribe o selecciona..." autocomplete="off">
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
								<tr id="row-empty-state">
									<td colspan="3" class="px-6 py-8 text-center text-slate-400">
										<p class="text-xs italic">No hay variantes agregadas aún.</p>
									</td>
								</tr>
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

					<label class="drop-zone group relative flex flex-col items-center justify-center w-full h-64 border-2 border-slate-300 border-dashed rounded-xl cursor-pointer bg-slate-50 hover:bg-orange-50 hover:border-orange-300 transition-all">

						<div class="drop-zone-content flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center z-10">
							<div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
								<i class="fas fa-cloud-upload-alt text-orange-500 text-xl"></i>
							</div>
							<p class="text-sm text-slate-500 mb-1"><span class="font-bold text-slate-700">Click para subir</span> o arrastra aquí</p>
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