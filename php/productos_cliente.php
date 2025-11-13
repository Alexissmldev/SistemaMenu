<?php
foreach ($categorias_ordenadas as $categoria) {
    $categoria_id = strtolower(str_replace(' ', '', $categoria['categoria_nombre']));
    $categoria_nombre = $categoria['categoria_nombre'];

    $query = $conexion->prepare("SELECT p.* FROM producto p INNER JOIN categoria c ON p.categoria_id = c.categoria_id WHERE p.producto_estado = 1 AND c.categoria_nombre = :nombre");
    $query->execute([':nombre' => $categoria_nombre]);
    $productos = $query->fetchAll();
?>
    <section id="<?php echo htmlspecialchars($categoria_id); ?>" class="mb-8 product-section">

        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-l-4 border-red-500 pl-4">
            <?php echo htmlspecialchars($categoria_nombre); ?>
        </h2>
        <div class="space-y-4 lg:grid lg:grid-cols-2 lg:gap-6 lg:space-y-0 xl:grid-cols-3">
            <?php if (count($productos) > 0): ?>
                <?php foreach ($productos as $producto):
                    //  Lógica de precios y JSON para el modal
                    $precio_usd_num = (float)$producto['producto_precio'];
                    $precio_display = '';
                    $precio_raw_bs = 0;
                    if ($tasa_usd_num > 0) {
                        $precio_bs = $precio_usd_num * $tasa_usd_num;
                        $precio_raw_bs = $precio_bs;
                        $precio_bs_formateado = number_format($precio_bs, 2, ',', '.');
                        $precio_display = '<span class="text-red-600 font-bold">Bs. ' . $precio_bs_formateado . '</span>';
                    } else {
                        $precio_usd_formateado = number_format($precio_usd_num, 2, ',', '.');
                        $precio_display = '<span class="text-red-600 font-bold">USD ' . $precio_usd_formateado . '</span>';
                    }
                    $producto_json = htmlspecialchars(json_encode([
                        'id' => $producto['producto_id'],
                        'nombre' => $producto['producto_nombre'],
                        'descripcion' => $producto['descripcion_producto'],
                        'precio_display' => $precio_display,
                        'precio_raw' => $precio_raw_bs,
                        'precio_usd' => $precio_usd_num,
                        'foto' => '../img/producto/large/' . $producto['producto_foto']
                    ]), ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="flex bg-white rounded-xl shadow-md overflow-hidden p-3 hover:shadow-lg transition cursor-pointer" onclick="openModal(<?php echo $producto_json; ?>)">
                        <div class="flex-shrink-0 w-24 h-24 bg-gray-100 rounded-lg overflow-hidden mr-4">
                            <img src="../img/producto/large/<?php echo htmlspecialchars($producto['producto_foto']); ?>" alt="<?php echo htmlspecialchars($producto['producto_nombre']); ?>" class="w-full h-full object-cover" />
                        </div>
                        <div class="flex-grow">
                            <h4 class="text-base font-semibold text-gray-800"><?php echo htmlspecialchars($producto['producto_nombre']); ?></h4>
                            <p class="text-sm text-gray-500 line-clamp-2 mt-1"><?php echo htmlspecialchars($producto['descripcion_producto']); ?></p>
                            <div class="flex items-center justify-between mt-2">
                                <?php echo $precio_display; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-gray-500">No hay productos disponibles en esta categoría.</p>
            <?php endif; ?>
        </div>
    </section>
<?php
}
?>