<?php
//OBTENER DATOS PARA EL FORMULARIO 

if (!isset($conexion)) {
    require_once "./php/main.php";
}
$conexion = conexion();

// Obtenemos el ID de la promo a editar
$promo_id = (isset($_GET['promo_id_up'])) ? (int)$_GET['promo_id_up'] : 0;

// Consulta 1: Obtener los datos principales de la promo 
$stmt_promo = $conexion->prepare("SELECT * FROM promociones WHERE promo_id = :id");
$stmt_promo->execute([':id' => $promo_id]);
$promo = $stmt_promo->fetch();

if (!$promo) {
    echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"><div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert"><p class="font-bold">Error</p><p>No se encontró la promoción solicitada.</p></div></div>';
    exit();
}

//Obtener TODOS los productos 
$productos_stmt = $conexion->query("SELECT producto_id, producto_nombre FROM producto WHERE producto_estado = 1 ORDER BY producto_nombre ASC");
$todos_productos = $productos_stmt->fetchAll();

//Obtener IDs de productos YA VINCULADOS 
$stmt_prods_vinculados = $conexion->prepare("SELECT producto_id FROM promocion_productos WHERE promo_id = :id");
$stmt_prods_vinculados->execute([':id' => $promo_id]);
$ids_prods_vinculados = $stmt_prods_vinculados->fetchAll(PDO::FETCH_COLUMN);

$check_limite = $conexion->query("SELECT COUNT(promo_id) FROM promociones WHERE estado = 1");
$total_promos_activas = (int) $check_limite->fetchColumn();
$limite_alcanzado = ($total_promos_activas >= 5 && $promo['estado'] == 0);

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="index.php?vista=home" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fa fa-home mr-2"></i> Inicio
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fa fa-chevron-right text-gray-400"></i>
                        <a href="index.php?vista=promo_list" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Promociones</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fa fa-chevron-right text-gray-400"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Actualizar</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 mt-4">Actualizar Promoción</h1>
        <p class="text-lg text-gray-600 mt-1">Modifica los datos de la promoción seleccionada.</p>
    </div>

    <?php if ($limite_alcanzado): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
            <p class="font-bold">Límite de Promociones Activas</p>
            <p>Ya tienes 5 promociones activas. Si activas esta, superarás el límite. Desactiva otra primero.</p>
        </div>
    <?php endif; ?>

    <form action="./php/promo_actualizar.php" class="FormularioAjax" method="POST" autocomplete="off" enctype="multipart/form-data">
        <input type="hidden" name="promo_id" value="<?php echo $promo['promo_id']; ?>">
        <div class="lg:grid lg:grid-cols-3 lg:gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Detalles de la Promoción</h3>
                        <div>
                            <label for="promo_nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                            <input type="text" id="promo_nombre" name="promo_nombre" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($promo['promo_nombre']); ?>" required>
                        </div>

                        <div class="mt-4">
                            <label for="promo_precio" class="block text-sm font-medium text-gray-700">Precio Final (en USD)</label>
                            <input type="number" id="promo_precio" name="promo_precio" step="0.01" min="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($promo['promo_precio']); ?>" required>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Foto Actual</label>
                            <?php
                            $img_url = './img/anuncios/estandar.jpg';
                            if (!empty($promo['promo_foto']) && is_file('./img/anuncios/large/' . $promo['promo_foto'])) {
                                $img_url = './img/anuncios/large/' . $promo['promo_foto'];
                            }
                            ?>
                            <img src="<?php echo $img_url; ?>" alt="Foto Actual" class="mt-1 w-48 h-24 object-cover rounded-md border bg-gray-100">

                            <label for="promo_foto" class="block text-sm font-medium text-gray-700 mt-4">Cambiar Foto (Opcional)</label>
                            <input type="file" id="promo_foto" name="promo_foto" class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100" accept="image/jpeg, image/png, image/webp">
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="hora_inicio" class="block text-sm font-medium text-gray-700">Hora de Inicio (0-23)</label>
                                <input type="number" id="hora_inicio" name="hora_inicio" min="0" max="23" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $promo['hora_inicio']; ?>" required>
                            </div>
                            <div>
                                <label for="hora_fin" class="block text-sm font-medium text-gray-700">Hora de Fin (0-23)</label>
                                <input type="number" id="hora_fin" name="hora_fin" min="0" max="23" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" value="<?php echo $promo['hora_fin']; ?>" required>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700">Estado</label>
                                <select id="estado" name="estado" class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" <?php echo $limite_alcanzado ? 'disabled' : ''; ?>>
                                    <option value="1" <?php echo ($promo['estado'] == 1) ? 'selected' : ''; ?>>Activa</option>
                                    <option value="0" <?php echo ($promo['estado'] == 0) ? 'selected' : ''; ?>>Inactiva</option>
                                </select>
                            </div>
                            <div>
                                <label for="prioridad" class="block text-sm font-medium text-gray-700">Prioridad</label>
                                <input type="number" id="prioridad" name="prioridad" value="<?php echo $promo['prioridad']; ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white shadow-lg rounded-lg border border-gray-200">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">
                            <i class="fa fa-link text-gray-400 mr-2"></i>Productos Incluidos
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">Selecciona los productos que forman parte de esta promo.</p>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Seleccionar Productos</label>

                            <div class="mt-1 h-96 overflow-y-auto border border-gray-300 rounded-md p-3 space-y-2">
                                <?php if (empty($todos_productos)): ?>
                                    <span class="text-sm text-gray-500">No hay productos activos para vincular.</span>
                                <?php else: ?>
                                    <?php foreach ($todos_productos as $prod): ?>
                                        <?php
                                        $checked = in_array($prod['producto_id'], $ids_prods_vinculados) ? 'checked' : '';
                                        ?>
                                        <div class="flex items-center">
                                            <input id="prod-<?php echo $prod['producto_id']; ?>"
                                                name="productos_vinculados[]"
                                                type="checkbox"
                                                value="<?php echo $prod['producto_id']; ?>"
                                                class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                <?php echo $checked; ?>>
                                            <label for="prod-<?php echo $prod['producto_id']; ?>" class="ml-3 block text-sm text-gray-700">
                                                <?php echo htmlspecialchars($prod['producto_nombre']); ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end gap-3">
            <a href="index.php?vista=promo_list" class="px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-full hover:bg-gray-300 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white font-semibold rounded-full shadow-md hover:bg-blue-700 transition-colors disabled:opacity-50" <?php echo $limite_alcanzado ? 'disabled' : ''; ?>>
                <i class="fa fa-save mr-2"></i>
                Actualizar Promoción
            </button>
        </div>

    </form>
</div>