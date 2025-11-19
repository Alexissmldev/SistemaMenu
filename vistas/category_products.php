<?php
require_once "./php/main.php";

// 1. Recibir y limpiar el ID
$category_id = limpiar_cadena($_GET['category_id'] ?? 0);

// 2. Obtener información de la categoría
$conexion = conexion();
$categoria_info = $conexion->prepare("SELECT categoria_nombre FROM categoria WHERE categoria_id = :id");
$categoria_info->execute([':id' => $category_id]);
$categoria = $categoria_info->fetch();
$categoria_nombre = $categoria ? $categoria['categoria_nombre'] : 'Desconocida';
?>

<div id="categoryProductsModal" data-role="modal-backdrop"
    class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm h-full w-full flex items-center justify-center z-[150] p-4">

    <div id="modalContent" class="relative w-full max-w-3xl bg-white rounded-2xl shadow-2xl flex flex-col max-h-[85vh] animate-fade-in-down">

        <div class="flex-shrink-0 flex justify-between items-center px-6 py-4 border-b border-slate-100 bg-white rounded-t-2xl sticky top-0 z-10">
            <div class="flex items-center gap-4">
                <div class="bg-orange-100 text-orange-600 p-2.5 rounded-xl">
                    <i class="fas fa-boxes text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 leading-none">Productos Asociados</h3>
                    <p class="text-xs text-slate-500 mt-1">Categoría: <span class="font-semibold text-orange-600"><?php echo htmlspecialchars($categoria_nombre); ?></span></p>
                </div>
            </div>

            <button class="modal-close-trigger text-slate-400 hover:text-slate-600 hover:bg-slate-100 p-2 rounded-lg transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="overflow-y-auto p-6 bg-slate-50/50 custom-scrollbar">
            <?php
            $check_productos = $conexion->prepare("SELECT producto_nombre, producto_precio, producto_foto FROM producto WHERE categoria_id = :id ORDER BY producto_nombre ASC");
            $check_productos->execute([':id' => $category_id]);

            if ($check_productos->rowCount() > 0) {
                $productos = $check_productos->fetchAll();
            ?>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <table class="w-full text-sm text-left text-slate-500">
                        <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-bold">Producto</th>
                                <th scope="col" class="px-6 py-3 font-bold text-right">Precio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($productos as $producto) { ?>
                                <tr class="bg-white hover:bg-orange-50/50 transition-colors">
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 overflow-hidden flex-shrink-0">
                                                <?php if (is_file("./img/producto/" . $producto['producto_foto'])): ?>
                                                    <img src="./img/producto/<?php echo $producto['producto_foto']; ?>" class="w-full h-full object-cover">
                                                <?php else: ?>
                                                    <i class="fas fa-box"></i>
                                                <?php endif; ?>
                                            </div>
                                            <span class="font-medium text-slate-700">
                                                <?php echo htmlspecialchars($producto['producto_nombre']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <span class="font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded-md text-xs">
                                            $<?php echo number_format($producto['producto_precio'], 2); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 text-right text-xs text-slate-400">
                    Total: <?php echo count($productos); ?> productos
                </div>

            <?php } else { ?>

                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-300">
                        <i class="fas fa-box-open text-3xl"></i>
                    </div>
                    <h3 class="text-sm font-bold text-slate-700">Sin Productos</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-xs">
                        Esta categoría no tiene productos asignados.
                    </p>
                </div>

            <?php } ?>
        </div>

        <div class="flex-shrink-0 border-t border-slate-100 p-4 bg-white rounded-b-2xl flex justify-end">
            <button class="modal-close-trigger px-5 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>