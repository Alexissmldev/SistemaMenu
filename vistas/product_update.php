c:\Users\alex1\OneDrive\Escritorio\perfil.php<?php
// Ruta: /vistas/product_update.php

require_once "./php/main.php";

$id = limpiar_cadena($_GET['product_id_up'] ?? 0);
$conexion = conexion();

// Consulta mejorada con INNER JOIN para obtener también el nombre de la categoría
$check_producto = $conexion->prepare("
    SELECT p.*, c.categoria_nombre 
    FROM producto p 
    INNER JOIN categoria c ON p.categoria_id = c.categoria_id 
    WHERE p.producto_id = :id
");
$check_producto->execute([':id' => $id]);

if ($check_producto->rowCount() > 0) {
    $datos = $check_producto->fetch();
?>

    <div id="productUpdateModal" data-role="modal-backdrop" data-animation="fade-in-scale"  class="fixed inset-0 bg-black bg-opacity-75 h-full w-full flex items-center justify-center z-50 transition-opacity duration-300" style="background-color: rgba(0, 0, 0, 0.75);">
        <div class="relative mx-auto w-full max-w-2xl bg-white rounded-2xl shadow-xl flex flex-col max-h-[90vh]">

            <div class="flex-shrink-0 flex justify-between items-center p-5 border-b border-slate-200">
                <div class="flex items-center gap-x-3">
                    <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-indigo-100 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z M19.5 7.125L18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Actualizar Producto</h3>
                        <p class="text-sm text-slate-500">
                            <span class="font-medium text-indigo-600"><?php echo htmlspecialchars($datos['categoria_nombre']); ?></span> // <?php echo htmlspecialchars($datos['descripcion_producto']); ?>
                        </p>
                    </div>
                </div>
                <button  class="modal-close-trigger p-2 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="flex-grow overflow-y-auto p-6">
                <form action="./php/producto_actualizar.php" method="POST" class="FormularioAjax" autocomplete="off">
                    <div class="form-rest mb-5"></div>
                    <input type="hidden" name="producto_id" value="<?php echo $datos['producto_id']; ?>" required>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                        <div>
                            <label for="producto_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h13.5m-13.5 7.5h13.5m-1.5-15l-1.5 15m-1.5-15l1.5 15m-6.75-15l1.5 15m-1.5-15l-1.5 15" />
                                    </svg></div>
                                <input id="producto_nombre" type="text" name="producto_nombre" class="block w-full rounded-lg border-slate-300 py-2 px-3 pl-10 shadow-sm" required value="<?php echo htmlspecialchars($datos['producto_nombre']); ?>">
                            </div>
                        </div>

                        <div>
                            <label for="producto_precio" class="block text-sm font-medium text-slate-700 mb-1">Precio</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><span class="text-slate-500">$</span></div>
                                <input id="producto_precio" type="text" name="producto_precio" class="block w-full rounded-lg border-slate-300 py-2 px-3 pl-7 shadow-sm" required value="<?php echo htmlspecialchars($datos['producto_precio']); ?>">
                            </div>
                        </div>

                        <div>
                            <label for="producto_descripcion" class="block text-sm font-medium text-slate-700 mb-1">Descripcion del producto</label>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h13.5m-13.5 7.5h13.5m-1.5-15l-1.5 15m-1.5-15l1.5 15m-6.75-15l1.5 15m-1.5-15l-1.5 15" />
                                    </svg></div>
                                <input id="producto_descripcion" type="text" name="producto_descripcion" class="block w-full rounded-lg border-slate-300 py-2 px-3 pl-10 shadow-sm" required value="<?php echo htmlspecialchars($datos['descripcion_producto']); ?>">
                            </div>
                        </div>
               
                        <div id="custom-select-container" class="md:col-span-2 relative">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Categoría</label>
                            <input type="hidden" id="producto_categoria_hidden" name="producto_categoria" value="<?php echo $datos['categoria_id']; ?>">
                            <button type="button" id="custom-select-button" class="relative w-full cursor-default rounded-lg border border-slate-300 bg-white py-2 pl-10 pr-10 text-left shadow-sm">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"><svg class="h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M3.25 4A2.25 2.25 0 001 6.25v7.5A2.25 2.25 0 003.25 16h7.5A2.25 2.25 0 0013 13.75v-7.5A2.25 2.25 0 0010.75 4h-7.5zM19 6.25a2.25 2.25 0 00-2.25-2.25H15v11.5h1.75A2.25 2.25 0 0019 13.75v-7.5z" />
                                    </svg></span>
                                <span id="custom-select-label" class="block truncate"><?php echo htmlspecialchars($datos['categoria_nombre']); ?></span>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2"><svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 3a.75.75 0 01.53.22l3.5 3.5a.75.75 0 01-1.06 1.06L10 4.81 6.53 8.28a.75.75 0 01-1.06-1.06l3.5-3.5A.75.75 0 0110 3zm-3.72 9.28a.75.75 0 011.06 0L10 15.19l2.67-2.91a.75.75 0 111.06 1.06l-3.5 3.5a.75.75 0 01-1.06 0l-3.5-3.5a.75.75 0 010-1.06z" clip-rule="evenodd" />
                                    </svg></span>
                            </button>
                            <div id="custom-select-panel" class="absolute z-10 top-full mt-2 w-full rounded-md bg-white text-base shadow-lg ring-1 ring-black ring-opacity-5 hidden">
                                <div class="max-h-56 overflow-auto py-1">
                                    <?php
                                    $categorias = $conexion->query("SELECT * FROM categoria ORDER BY categoria_nombre ASC");
                                    if ($categorias->rowCount() > 0) {
                                        foreach ($categorias as $row) {
                                            $isSelected = $datos['categoria_id'] == $row['categoria_id'];
                                            echo '<div class="custom-select-option text-gray-900 relative cursor-pointer select-none py-2 pl-10 pr-4 hover:bg-indigo-600 hover:text-white" data-value="' . $row['categoria_id'] . '" data-label="' . htmlspecialchars($row['categoria_nombre']) . '">
                                                   <span class="font-normal block truncate">' . htmlspecialchars($row['categoria_nombre']) . '</span>
                                                   ' . ($isSelected ? '<span class="selected-tick absolute inset-y-0 left-0 flex items-center pl-3"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" /></svg></span>' : '') . '
                                              </div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Estado</label>
                            <div class="flex items-center">
                                <span class="text-sm text-slate-500 mr-3">No Disponible</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="estado_toggle" class="sr-only peer" <?php echo ($datos['producto_estado'] == 1) ? 'checked' : ''; ?>>
                                    <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                </label>
                                <span class="text-sm font-medium text-slate-700 ml-3">Disponible</span>
                                <input type="hidden" name="producto_estado" id="producto_estado" value="<?php echo $datos['producto_estado']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="flex-shrink-0 flex justify-end items-center gap-x-3 pt-5 mt-6 border-t border-slate-200">
                        <button type="button" class=" modal-close-trigger px-4 py-2 bg-slate-100 text-slate-800 rounded-lg font-medium hover:bg-slate-200 transition-colors">Cancelar</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">Actualizar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
} else {
    echo '<div role="alert" class="rounded border-s-4 border-red-500 bg-red-50 p-4"><strong class="block font-medium text-red-800">Error</strong><p class="mt-2 text-sm text-red-700">No se encontró el producto solicitado.</p></div>';
}
$check_producto = null;
$conexion = null;
?>