	<?php
	require_once "./php/main.php";
	?>

	<div id="productModal" data-role="modal-backdrop" data-animation="fade-in-scale"
		class="fixed inset-0 bg-black overflow-y-auto h-full w-full flex items-center justify-center z-50 transition-opacity duration-300 ease-in-out " 
		style="background-color: rgba(0, 0, 0, 0.75)">
		<div id="modalContent" class="relative mx-auto p-5 border w-full max-w-3xl shadow-2xl rounded-2xl bg-white transition-all duration-300 ease-in-out scale-95">
			<div class="flex justify-between items-center border-b pb-3">
				<div class="flex items-center">
					<svg class="w-6 h-6 mr-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10.5 11.25h3M12 15V7.5" />
					</svg>
					<h3 class="text-2xl font-bold text-gray-800">Añadir Nuevo Producto</h3>
				</div>
				<button class="modal-close-trigger text-gray-400 hover:text-gray-600">
					<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
					</svg>
				</button>
			</div>
			<div class="mt-5 max-h-[70vh] overflow-y-auto pr-2">
				<form id="formNuevoProducto" action="./php/producto_guarda.php" method="POST" class="FormularioAjax" autocomplete="off" enctype="multipart/form-data">
					<div class="form-rest mb-4"></div>
					<div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-8">
						
						<div>
							<label for="producto_nombre" class="block text-sm font-medium text-gray-700">Nombre del Producto</label>
							<div class="relative mt-1">
								<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
									<svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h13.5m-13.5 7.5h13.5m-1.5-15l-1.5 15m-1.5-15l1.5 15m-6.75-15l1.5 15m-1.5-15l-1.5 15" />
									</svg>
								</div>
								<input type="text" name="producto_nombre" id="producto_nombre" class="block w-full rounded-md border-gray-300 bg-gray-50 py-3 pl-10 shadow-sm focus:border-blue-500 focus:ring-blue-500" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}" maxlength="70" required>
							</div>
						</div>
						<div>
							<label for="producto_precio" class="block text-sm font-medium text-gray-700">Precio</label>
							<div class="relative mt-1">
								<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
									<span class="text-gray-500">$</span>
								</div>
								<input type="text" name="producto_precio" id="producto_precio" class="block w-full rounded-md border-gray-300 bg-gray-50 py-3 pl-7 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00" pattern="[0-9.]{1,25}" maxlength="25" required>
							</div>
						</div>
						<div>
							<label for="producto_descripcion" class="block text-sm font-medium text-gray-700">Descripcion del Producto</label>
							<div class="relative mt-1">
								<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
									<svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h13.5m-13.5 7.5h13.5m-1.5-15l-1.5 15m-1.5-15l1.5 15m-6.75-15l1.5 15m-1.5-15l-1.5 15" />
									</svg>
								</div>
								<input type="text" name="producto_descripcion" id="producto_descripcion" class="block w-full rounded-md border-gray-300 bg-gray-50 py-3 pl-10 shadow-sm focus:border-blue-500 focus:ring-blue-500" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,500}" maxlength="500" required>
							</div>
						</div>
						<!-- <div>
							<label for="producto_stock" class="block text-sm font-medium text-gray-700">Stock / Existencias</label>
							<div class="relative mt-1">
								<div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
									<svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10.5 11.25h3M12 15V7.5" />
									</svg>
								</div>
								<input type="text" name="producto_stock" id="producto_stock" class="block w-full rounded-md border-gray-300 bg-gray-50 py-3 pl-10 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0" pattern="[0-9]{1,25}" maxlength="25" required>
							</div>
						</div> -->
						<div class="sm:col-span-2">
							<label for="producto_categoria" class="block text-sm font-medium text-gray-700">Categoría</label>
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
									<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
										<path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
									</svg>
								</button>
							</div>

							<!-- Este es el div que contiene el formulario de categoría separado -->
							<div id="newCategoryForm" class="hidden mt-2 rounded-lg border border-gray-200 bg-gray-50 p-4">
								<p class="text-sm font-medium text-gray-800 mb-2">Crear nueva categoría</p>
								<div id="newCategoryAlerts"></div>
								<div class="grid grid-cols-1 gap-4">
									<div>
										<label for="new_category_name" class="sr-only">Nombre</label>
										<input type="text" id="new_category_name" placeholder="Nombre de la categoría" class="block w-full rounded-md border-gray-300 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500">
									</div>
									<div class="flex items-center gap-3">
										<span class="text-sm font-medium text-gray-700">Vigente</span>
										<div class="relative inline-block w-12 h-6">
											<div class="absolute inset-0 bg-green-500 rounded-full"></div>
											<div class="absolute left-6 top-1 w-4 h-4 bg-white rounded-full border border-gray-300"></div>
										</div>
									</div>
									<input type="hidden" id="categoria_estado" value="1">
								</div>
								<div class="flex justify-end items-center gap-x-3 mt-4">
									<button type="button" id="cancelCategoryBtn" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancelar</button>
									<button type="button" id="saveCategoryBtn" class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Guardar</button>
								</div>
							</div>
						</div>
						<div class="sm:col-span-2">
							<label class="block text-sm font-medium text-gray-700">Foto o imagen del producto</label>
							<div class="mt-1 flex justify-center rounded-md border-2 border-dashed border-gray-300 bg-gray-50 px-6 pt-5 pb-6 drop-zone">
								<div class="space-y-1 text-center drop-zone-content">
									<svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
										<path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
									</svg>
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
					</div>
					<div class="mt-8 flex justify-end space-x-3 border-t pt-4">
						<button type="button" class=" modal-close-trigger px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 font-medium">Cancelar</button>
						<button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Guardar Producto</button>
					</div>
				</form>
			</div>
		</div>
	</div>