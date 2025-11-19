<?php
require_once "./php/main.php";

$id = limpiar_cadena($_GET['category_id_up'] ?? 0);

$check_categoria = conexion()->prepare("SELECT * FROM categoria WHERE categoria_id = :id");
$check_categoria->execute([':id' => $id]);
?>

<div id="categoryUpdateModal" data-role="modal-backdrop"
    class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm h-full w-full flex items-center justify-center z-[150] p-4 animate-fade-in-down">

    <div id="modalContent" class="relative mx-auto w-full max-w-lg bg-white rounded-2xl shadow-2xl flex flex-col max-h-[90vh]">

        <?php if ($check_categoria->rowCount() > 0) {
            $datos = $check_categoria->fetch();
        ?>
            <div class="flex-shrink-0 flex justify-between items-center p-5 border-b border-slate-100 bg-white rounded-t-2xl">
                <div class="flex items-center gap-x-3">
                    <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-orange-100 rounded-xl">
                        <i class="fas fa-edit text-orange-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 leading-none">Editar Categoría</h3>
                        <p class="text-xs text-slate-500 mt-1">ID: <?php echo $datos['categoria_id']; ?></p>
                    </div>
                </div>
                <button class="modal-close-trigger p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="./php/categoria_actualizar.php" method="POST" class="FormularioAjax flex-grow flex flex-col overflow-hidden" autocomplete="off">

                <div class="overflow-y-auto p-6 space-y-6 custom-scrollbar">
                    <div class="form-rest mb-2"></div>
                    <input type="hidden" name="categoria_id" value="<?php echo $datos['categoria_id']; ?>" required>

                    <div>
                        <label for="categoria_nombre" class="block text-xs font-bold text-slate-600 uppercase mb-1">Nombre</label>
                        <input id="categoria_nombre" type="text" name="categoria_nombre" class="block w-full rounded-lg border-slate-200 py-2.5 px-3 text-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500 bg-slate-50" value="<?php echo htmlspecialchars($datos['categoria_nombre']); ?>" required>
                    </div>

                    <div class="border-t border-slate-100 pt-4">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Horario de Disponibilidad (0-23h)</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] text-slate-500 mb-1">Hora Inicio</label>
                                <div class="relative">
                                    <input type="number" name="categoria_hora_inicio" min="0" max="23"
                                        value="<?php echo $datos['categoria_hora_inicio']; ?>"
                                        class="block w-full rounded-lg border-slate-200 py-2 px-3 text-sm text-center focus:border-orange-500 focus:ring-2 focus:ring-orange-500 bg-slate-50">
                                    <span class="absolute right-3 top-2 text-slate-400 text-xs">h</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-500 mb-1">Hora Fin</label>
                                <div class="relative">
                                    <input type="number" name="categoria_hora_fin" min="0" max="23"
                                        value="<?php echo $datos['categoria_hora_fin']; ?>"
                                        class="block w-full rounded-lg border-slate-200 py-2 px-3 text-sm text-center focus:border-orange-500 focus:ring-2 focus:ring-orange-500 bg-slate-50">
                                    <span class="absolute right-3 top-2 text-slate-400 text-xs">h</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-2 italic">Si ambos son 0, se muestra todo el día.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-2">Estado</label>
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg border border-slate-100">
                            <span class="text-xs text-slate-500 mr-3 font-medium">Oculta</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="categoria_estado_toggle" class="sr-only peer"
                                    <?php echo ($datos['categoria_estado'] == 1) ? 'checked' : ''; ?>
                                    onchange="document.getElementById('categoria_estado').value = this.checked ? 1 : 0">
                                <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                            </label>
                            <span class="text-xs text-slate-700 ml-3 font-bold">Visible</span>

                            <input type="hidden" name="categoria_estado" id="categoria_estado" value="<?php echo $datos['categoria_estado']; ?>">
                        </div>
                    </div>
                </div>

                <div class="flex-shrink-0 flex justify-end items-center gap-x-3 p-5 mt-auto border-t border-slate-100 bg-white rounded-b-2xl">
                    <button type="button" class="modal-close-trigger px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 transition-colors">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-orange-600 text-white rounded-lg text-sm font-bold hover:bg-orange-700 shadow-sm hover:shadow transition-colors">Actualizar Datos</button>
                </div>
            </form>

        <?php } else { ?>

            <div class="p-10 text-center flex flex-col items-center justify-center h-full">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4 text-red-500">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Categoría no encontrada</h3>
                <p class="text-sm text-slate-500 mt-2 mb-6">La categoría que intentas editar no existe o fue eliminada.</p>
                <button class="modal-close-trigger px-6 py-2 bg-slate-800 text-white rounded-lg font-medium hover:bg-slate-700 transition-colors">Cerrar</button>
            </div>

        <?php }
        $check_categoria = null;
        ?>
    </div>
</div>