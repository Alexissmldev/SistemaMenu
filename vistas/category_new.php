
<div id="categoryModal" data-role="modal-backdrop" data-animation="fade-in-scale" class="fixed inset-0 bg-black overflow-y-auto h-full w-full flex items-center justify-center z-50 transition-opacity duration-300 ease-in-out " style="background-color: rgba(0, 0, 0, 0.75);">
	<div id="modalContent" class="relative mx-auto p-5 border w-full max-w-3xl shadow-2xl rounded-2xl bg-white transition-all duration-300 ease-in-out scale-95">
		<div class="flex justify-between items-center border-b pb-3">
			<div class="flex items-center"> <svg class="w-6 h-6 mr-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10.5 11.25h3M12 15V7.5" />
				</svg>
				<h3 class="text-2xl font-bold text-gray-800">Añadir Nueva categoria</h3>
			</div> <button id="closeModalBtn" class="modal-close-trigger text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
				</svg></button>
		</div>
		<div class="mt-5 max-h-[70vh] overflow-y-auto pr-2">
			<form action="./php/categoria_guardar.php" method="POST" class="FormularioAjax" autocomplete="off">
				<p class="text-sm font-medium text-gray-800 mb-2">Crear nueva categoría</p>
				<div id="newCategoryAlerts"></div>
				<div class="grid grid-cols-1 gap-4">
					<div>
						<label for="categoria_nombre" class="sr-only">Nombre</label>
						<input type="text" id="categoria_nombre" name="categoria_nombre"  placeholder="Nombre de la categoría" class="block w-full rounded-md border-gray-300 py-2 shadow-sm focus:border-blue-500 focus:ring-blue-500">
					</div>
					<div class="flex items-center gap-3"> <span class="text-sm font-medium text-gray-700">Vigente</span>
						<div class="relative inline-block w-12 h-6">
							<div class="absolute inset-0 bg-green-500 rounded-full"></div> <!-- Bolita blanca -->
							<div class="absolute left-6 top-1 w-4 h-4 bg-white rounded-full border border-gray-300"></div>
						</div>
					</div> 
					<input class="input" type="hidden" name="categoria_estado" value="1">
				</div>
				<div class="flex justify-end items-center gap-x-3 mt-4">
					<button type="button" class=" modal-close-trigger text-sm font-medium text-gray-600 hover:text-gray-900">Cancelar</button>
					<button type="summit" id="saveCategoryBtn" class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Guardar</button>
				</div>
			</form>
		</div>
	</div>
</div>