<?php
require_once "./php/main.php";

$id = limpiar_cadena($_GET['category_id_up'] ?? 0);

$check_categoria = conexion()->prepare("SELECT * FROM categoria WHERE categoria_id = :id");
$check_categoria->execute([':id' => $id]);
?>
<div id="categoryUpdateModal" data-role="modal-backdrop" data-animation="fade-in-scale" class="fixed inset-0 bg-black bg-opacity-75 h-full w-full flex items-center justify-center z-50 transition-opacity duration-300" style="background-color: rgba(0, 0, 0, 0.75);">
    <div id="modalContent" class="relative mx-auto w-full max-w-lg bg-white rounded-2xl shadow-xl flex flex-col max-h-[90vh]">

        <?php if ($check_categoria->rowCount() > 0) {
            $datos = $check_categoria->fetch();
        ?>
            <div class="flex-shrink-0 flex justify-between items-center p-5 border-b border-slate-200">
                <div class="flex items-center gap-x-3">
                    <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Actualizar Categoría</h3>
                        <p class="text-sm text-slate-500">Editando: <?php echo htmlspecialchars($datos['categoria_nombre']); ?></p>
                    </div>
                </div>
                <button class="modal-close-trigger p-2 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="./php/categoria_actualizar.php" method="POST" class="FormularioAjax flex-grow flex flex-col" autocomplete="off">
                <div class="overflow-y-auto p-6 space-y-6">
                    <div class="form-rest mb-5"></div>
                    <input type="hidden" name="categoria_id" value="<?php echo $datos['categoria_id']; ?>" required>

                    <div>
                        <label for="categoria_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                        <input id="categoria_nombre" type="text" name="categoria_nombre" class="block w-full rounded-lg border-slate-300 py-2 px-3 shadow-sm focus:border-blue-500 focus:ring-blue-500" value="<?php echo htmlspecialchars($datos['categoria_nombre']); ?>" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Estado</label>
                        <div class="flex items-center">
                            <span class="text-sm text-slate-500 mr-3">No Vigente</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="categoria_estado_toggle" class="sr-only peer" <?php echo ($datos['categoria_estado'] == 1) ? 'checked' : ''; ?>>
                                <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                            <span class="text-sm font-medium text-slate-700 ml-3">Vigente</span>
                            <input type="hidden" name="categoria_estado" id="categoria_estado" value="<?php echo $datos['categoria_estado']; ?>">
                        </div>
                    </div>
                </div>

                <div class="flex-shrink-0 flex justify-end items-center gap-x-3 p-5 mt-auto border-t border-slate-200">
                    <button type="button" class="modal-close-trigger px-4 py-2 bg-slate-100 text-slate-800 rounded-lg font-medium hover:bg-slate-200 transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">Actualizar</button>
                </div>
            </form>

        <?php } else { ?>
            <div class="p-8 text-center">
                <h3 class="text-lg font-bold text-red-700">Error</h3>
                <p class="text-sm text-slate-500 mt-2">No se encontró la categoría seleccionada.</p>
                <button class="modal-close-trigger mt-4 px-4 py-2 bg-slate-100 text-slate-800 rounded-lg font-medium hover:bg-slate-200 transition-colors">Cerrar</button>
            </div>
        <?php }
        $check_categoria = null;
        ?>
    </div>
</div>