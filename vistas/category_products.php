<?php
require_once "./php/main.php";

// 1. Recibir y limpiar el ID de la categoría
$category_id = limpiar_cadena($_GET['category_id'] ?? 0);

// 2. Obtener el nombre de la categoría para el título del modal
$conexion = conexion();
$categoria_info = $conexion->prepare("SELECT categoria_nombre FROM categoria WHERE categoria_id = :id");
$categoria_info->execute([':id' => $category_id]);
$categoria = $categoria_info->fetch();
$categoria_nombre = $categoria ? $categoria['categoria_nombre'] : 'Desconocida';

?>
<div id="categoryProductsModal" data-role="modal-backdrop" 
    class="fixed inset-0 bg-black bg-opacity-75 h-full w-full flex items-center justify-center z-50 transition-opacity duration-300"
    style="background-color: rgba(0, 0, 0, 0.75)">
    <div id="modalContent" class="relative mx-auto w-full max-w-2xl bg-white rounded-2xl shadow-xl flex flex-col max-h-[90vh]">

        <div class="flex-shrink-0 flex justify-between items-center p-5 border-b border-slate-200">
            <div class="flex items-center gap-x-3">
                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-gray-100 rounded-full">
                    <svg class="w-6 h-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Productos Asociados</h3>
                    <p class="text-sm text-slate-500">Categoría: <?php echo htmlspecialchars($categoria_nombre); ?></p>
                </div>
            </div>
            <button class="modal-close-trigger p-2 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="overflow-y-auto p-6">
            <?php
            // 3. Buscar los productos asociados
            $check_productos = $conexion->prepare("SELECT producto_nombre, producto_precio, producto_stock FROM producto WHERE categoria_id = :id ORDER BY producto_nombre ASC");
            $check_productos->execute([':id' => $category_id]);

            if ($check_productos->rowCount() > 0) {
                $productos = $check_productos->fetchAll();
            ?>
                <div class="overflow-x-auto shadow-md sm:rounded-lg border">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nombre del Producto</th>
                                <th scope="col" class="px-6 py-3">Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto) { ?>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900"><?php echo htmlspecialchars($producto['producto_nombre']); ?></td>
                                    <td class="px-6 py-4 text-green-600 font-bold">$<?php echo htmlspecialchars(number_format($producto['producto_precio'], 2)); ?></td>
                                    <td class="px-6 py-4 text-center"><?php echo htmlspecialchars($producto['producto_stock']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="text-center py-8">
                    <h3 class="text-sm font-medium text-gray-900">Sin Productos</h3>
                    <p class="mt-1 text-sm text-gray-500">No hay productos asociados a esta categoría.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>