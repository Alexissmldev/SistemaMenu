<?php
// Ruta: /vistas/product_img_update.php

require_once "./php/main.php";

$id = limpiar_cadena($_GET['product_id_up'] ?? 0);
$conexion = conexion();

$check_producto = $conexion->prepare("SELECT * FROM producto WHERE producto_id = :id");
$check_producto->execute([':id' => $id]);

if ($check_producto->rowCount() > 0) {
    $datos = $check_producto->fetch();
?>

    <div id="productImageModal" data-role="modal-backdrop" data-animation="fade-in-scale" class="fixed inset-0 z-50 p-4 sm:p-6 transition-opacity duration-300   transition-opacity flex items-center justify-center"
    style="background-color: rgba(0, 0, 0, 0.75)">
        
        <div id="modalContent" class="relative w-full max-w-xl bg-white rounded-xl shadow-2xl flex flex-col max-h-[90vh] mx-auto overflow-hidden">

            <div class="flex-shrink-0 flex items-center justify-between p-4 sm:p-5 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-indigo-500 rounded-full text-white">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.158 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-800">Actualizar Imagen</h3>
                        <p class="text-sm text-gray-500 truncate max-w-[200px] sm:max-w-xs leading-tight">Producto: <span class="font-semibold"><?php echo htmlspecialchars($datos['producto_nombre']); ?></span></p>
                    </div>
                </div>
                <button class="modal-close-trigger p-2 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors duration-200" aria-label="Cerrar modal">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="formActualizarImagen" class="FormularioAjax flex-grow overflow-y-auto" action="./php/producto_img_actualizar.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="img_up_id" value="<?php echo $datos['producto_id']; ?>">
                
                <div class="p-4 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
                    
                    <div class="flex flex-col items-center">
                        <h4 class="font-semibold text-gray-700 mb-3 text-center text-base sm:text-lg">Imagen Actual</h4>
                        <div class="relative group aspect-square w-full max-w-[280px] bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center p-3 overflow-hidden">
                            <?php if (is_file("./img/producto/large/" . $datos['producto_foto'])) { ?>
                                <img src="./img/producto/large/<?php echo $datos['producto_foto']; ?>" class="max-w-full max-h-full object-contain rounded-md" alt="Imagen actual del producto">
                                <button type="button" onclick="eliminarImagen('<?php echo $datos['producto_id']; ?>')" class="absolute top-2 right-2 p-2 bg-red-600 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 shadow-md hover:bg-red-700" aria-label="Eliminar imagen actual">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            <?php } else { ?>
                                <div class="text-center text-gray-400">
                                    <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.158 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    <p class="mt-2 text-sm">Sin imagen</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="flex flex-col items-center">
                        <h4 class="font-semibold text-gray-700 mb-3 text-center text-base sm:text-lg">Subir Nueva Imagen</h4>
                        
                        <div class="drop-zone mt-1 flex flex-col justify-center items-center rounded-lg border-2 border-dashed border-indigo-300 bg-indigo-50 px-4 py-8 sm:px-6 sm:py-10 text-center transition-all duration-200 hover:border-indigo-500 hover:bg-indigo-100 w-full max-w-[280px]">
                            
                            <div class="drop-zone-content">
                                <svg class="mx-auto h-12 w-12 text-indigo-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                <div class="flex text-sm text-indigo-700 mt-4 justify-center">
                                    <label for="producto_foto" class="relative cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2">
                                        <span>Seleccionar un archivo</span>
                                        <input id="producto_foto" name="producto_foto" type="file" class="sr-only" accept=".jpg, .png, .jpeg">
                                    </label>
                                </div>
                                <p class="text-xs text-indigo-600 mt-2 font-medium">JPG, JPEG, PNG hasta 3MB</p>
                            </div>
                            </div>
                    </div>
                </div>

                <div class="flex-shrink-0 flex flex-col sm:flex-row sm:justify-end items-center gap-3 p-4 sm:p-5 mt-auto border-t border-gray-200 bg-gray-50">
                    <button type="button" class="modal-close-trigger w-full sm:w-auto px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300 transition-colors duration-200 shadow-sm">Cancelar</button>
                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors duration-200 shadow-md">Actualizar Imagen</button>
                </div>
            </form>
            
        </div>
    </div>

<?php
} 
$check_producto = null;
$conexion = null;
?>