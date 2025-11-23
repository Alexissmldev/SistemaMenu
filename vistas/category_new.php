<?php
require_once "./php/main.php";
?>

<form action="./php/categoria_guardar.php" class="FormularioAjax w-full min-h-screen bg-slate-50 flex flex-col pb-10 lg:pb-0" method="POST" autocomplete="off">

	<div class="sticky top-16 z-30 bg-white border-b border-slate-200 px-4 py-4 lg:px-8 shadow-sm transition-all">
		<div class="flex items-center justify-between">
			<div class="flex items-center gap-4 lg:gap-5">
				<div class="hidden md:block bg-orange-100 text-orange-600 p-3 rounded-xl">
					<i class="fas fa-layer-group text-xl"></i>
				</div>
				<div>
					<div class="opacity-70 hidden sm:block mb-1">
						<?php include "./inc/breadcrumb.php"; ?>
					</div>
					<h2 class="text-lg lg:text-xl font-bold text-slate-800 leading-tight">Nueva Categoría</h2>
				</div>
			</div>

			<div class="flex items-center gap-4">
				<a href="index.php?vista=category_list" class="hidden md:inline-block text-base font-medium text-slate-500 hover:text-slate-800">
					Cancelar
				</a>
				<button type="submit" class="inline-flex items-center px-6 py-3 text-base font-bold rounded-xl text-white bg-orange-600 hover:bg-orange-700 shadow-md transition-transform hover:-translate-y-0.5">
					<i class="fas fa-save mr-2"></i> <span class="hidden sm:inline">Guardar Categoría</span><span class="sm:hidden">Guardar</span>
				</button>
			</div>
		</div>
	</div>

	<div class="flex-1 p-4 lg:p-8">

		<div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-12 gap-8 items-start">

			<div class="md:col-span-7 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
				<h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-6 flex items-center gap-2">
					<i class="fas fa-pen text-slate-400"></i> Información General
				</h3>

				<div class="space-y-6">
					<div>
						<label class="block text-sm font-bold text-slate-600 uppercase mb-2">Nombre de la Categoría</label>
						<div class="relative">
							<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 text-lg">
								<i class="fas fa-tag"></i>
							</div>
							<input type="text" name="categoria_nombre" id="categoria_nombre" class="block w-full pl-12 pr-4 py-3.5 border border-slate-200 rounded-xl text-base focus:ring-2 focus:ring-orange-500 focus:border-orange-500 bg-slate-50" placeholder="Ej: Bebidas Calientes" required>
						</div>
						<p class="text-xs text-slate-400 mt-2">El nombre que aparecerá en el menú principal.</p>
					</div>

					<div class="p-5 bg-blue-50 rounded-xl border border-blue-100">
						<div class="flex items-start gap-3">
							<i class="fas fa-info-circle text-blue-500 mt-1 text-lg"></i>
							<p class="text-sm text-blue-700 leading-relaxed">
								<strong>Nota:</strong> Las categorías nuevas aparecerán al final de la lista por defecto. Puedes reordenarlas más tarde.
							</p>
						</div>
					</div>
				</div>
			</div>

			<div class="md:col-span-5 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
				<h3 class="text-base font-bold text-slate-800 border-b border-slate-100 pb-3 mb-6 flex items-center gap-2">
					<i class="fas fa-clock text-slate-400"></i> Disponibilidad
				</h3>

				<div class="space-y-6">

					<div>
						<label class="block text-sm font-bold text-slate-600 uppercase mb-2">Estado Actual</label>
						<select name="categoria_estado" class="block w-full px-4 py-3.5 border border-slate-200 rounded-xl text-base focus:ring-2 focus:ring-orange-500 bg-slate-50 cursor-pointer">
							<option value="1">Activa (Visible)</option>
							<option value="0">Inactiva (Oculta)</option>
						</select>
					</div>

					<div class="border-t border-slate-100 pt-5">
						<p class="text-sm font-bold text-slate-800 mb-2">Horario de Visualización</p>
						<p class="text-xs text-slate-500 mb-4 leading-snug">
							Define en qué horario esta categoría es visible.<br>
							<span class="text-orange-600">Ejemplo: Desayunos de 7 a 11.</span>
						</p>

						<div class="grid grid-cols-2 gap-5">
							<div>
								<label class="block text-xs font-bold text-slate-500 uppercase mb-2">Hora Inicio (0-23)</label>
								<div class="relative">
									<input type="number" name="categoria_hora_inicio" min="0" max="23" placeholder="0" class="block w-full px-4 py-3 border border-slate-200 rounded-xl text-base focus:ring-2 focus:ring-orange-500 bg-slate-50 text-center">
									<span class="absolute right-4 top-3.5 text-slate-400 text-sm">h</span>
								</div>
							</div>
							<div>
								<label class="block text-xs font-bold text-slate-500 uppercase mb-2">Hora Fin (0-23)</label>
								<div class="relative">
									<input type="number" name="categoria_hora_fin" min="0" max="23" placeholder="23" class="block w-full px-4 py-3 border border-slate-200 rounded-xl text-base focus:ring-2 focus:ring-orange-500 bg-slate-50 text-center">
									<span class="absolute right-4 top-3.5 text-slate-400 text-sm">h</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</form>