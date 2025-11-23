<?php
// Asumimos que $conexion y $tasa_usd_num vienen del include anterior (index.php o menu.php)

foreach ($categorias_ordenadas as $categoria) {
    $categoria_id = strtolower(str_replace(' ', '', $categoria['categoria_nombre']));
    $categoria_nombre = $categoria['categoria_nombre'];

    // 1. Buscamos productos de esta categoría
    $query = $conexion->prepare("
        SELECT p.* FROM producto p 
        INNER JOIN categoria c ON p.categoria_id = c.categoria_id 
        WHERE p.producto_estado = 1 
        AND c.categoria_nombre = :nombre
    ");
    $query->execute([':nombre' => $categoria_nombre]);
    $productos = $query->fetchAll();

    if (count($productos) == 0) continue;

    $categoria_id_texto = strtolower(str_replace(' ', '', $categoria_nombre));
    $id_de_esta_categoria = $productos[0]['categoria_id'];
?>

    <section id="<?php echo htmlspecialchars($categoria_id_texto); ?>"
        data-numeric-id="categoria-<?php echo $id_de_esta_categoria; ?>"
        class="mb-8 product-section pt-10 mt-2">

        <h2 class="text-2xl font-bold text-gray-800 mb-6 border-l-4 border-red-500 pl-4">
            <?php echo htmlspecialchars($categoria_nombre); ?>
        </h2>

        <div class="space-y-4 lg:grid lg:grid-cols-2 lg:gap-6 lg:space-y-0 xl:grid-cols-3">

            <?php foreach ($productos as $producto):

                // --- DATOS BÁSICOS DEL PRODUCTO ---
                $producto_id = $producto['producto_id'];
                $precio_base_usd = (float)$producto['producto_precio'];
                $nombre_producto = ucwords(strtolower($producto['producto_nombre']));
                $descripcion = ucfirst(strtolower($producto['descripcion_producto']));
                $foto_url = '../img/producto/large/' . $producto['producto_foto'];

                // Calculamos precio base en Bs para mostrar en la tarjeta (Card)
                if ($tasa_usd_num > 0) {
                    $precio_base_bs = $precio_base_usd * $tasa_usd_num;
                    $precio_display = '<span class="text-red-600 font-bold">Bs. ' . number_format($precio_base_bs, 2, ',', '.') . '</span>';
                } else {
                    $precio_base_bs = 0;
                    $precio_display = '<span class="text-red-600 font-bold">USD ' . number_format($precio_base_usd, 2, ',', '.') . '</span>';
                }

                // --- LÓGICA DE VARIANTES (BASE DE DATOS) ---

                // Consultamos si este producto tiene variantes asignadas
                $stmt_var = $conexion->prepare("
                    SELECT vp.id_variante_producto, v.nombre_variante, vp.precio_variante
                    FROM variante_producto vp
                    INNER JOIN variante v ON vp.id_variante = v.id_variante
                    WHERE vp.producto_id = :pid
                ");
                $stmt_var->execute([':pid' => $producto_id]);
                $variantes_db = $stmt_var->fetchAll();

                $lista_variantes = [];

                if (count($variantes_db) > 0) {
                    // CASO A: El producto TIENE variantes en BD
                    foreach ($variantes_db as $v) {
                        $precio_var_usd = (float)$v['precio_variante'];
                        $precio_var_bs = ($tasa_usd_num > 0) ? $precio_var_usd * $tasa_usd_num : 0;

                        $lista_variantes[] = [
                            'id' => 'v' . $v['id_variante_producto'], // ID único para el carrito (ej: v10)
                            'nombre' => $v['nombre_variante'],
                            'precio_bs' => $precio_var_bs,
                            'precio_usd' => $precio_var_usd
                        ];
                    }
                } else {
                    // CASO B: El producto NO tiene variantes (Es un producto simple)
                    // Creamos una variante "falsa" llamada Estándar con el precio base del producto
                    $lista_variantes[] = [
                        'id' => 'p' . $producto_id, // ID basado en el producto (ej: p50)
                        'nombre' => 'Estándar',
                        'precio_bs' => $precio_base_bs,
                        'precio_usd' => $precio_base_usd
                    ];
                }

                // Preparamos el JSON completo para el modal
                $producto_data_json = [
                    'id_padre' => $producto_id,
                    'nombre' => $nombre_producto,
                    'descripcion' => $descripcion,
                    'foto' => $foto_url,
                    'variantes' => $lista_variantes // Array de variantes
                ];

                // Codificamos a JSON seguro para HTML
                $json_attr = htmlspecialchars(json_encode($producto_data_json), ENT_QUOTES, 'UTF-8');
            ?>

                <div id="producto-<?php echo $producto_id; ?>"
                    class="flex bg-white rounded-xl shadow-md overflow-hidden p-3 hover:shadow-lg transition cursor-pointer transform active:scale-[0.98] duration-200 border border-transparent hover:border-red-100"
                    onclick="openModal(<?php echo $json_attr; ?>)">

                    <div class="flex-shrink-0 w-24 h-24 bg-gray-100 rounded-lg overflow-hidden mr-4 relative">
                        <img src="<?php echo htmlspecialchars($foto_url); ?>"
                            alt="<?php echo htmlspecialchars($nombre_producto); ?>"
                            loading="lazy"
                            class="w-full h-full object-cover" />

                        <div class="absolute bottom-0 right-0 bg-red-600 text-white w-7 h-7 flex items-center justify-center rounded-tl-lg rounded-br-lg shadow-sm">
                            <i class="fa fa-plus text-xs"></i>
                        </div>
                    </div>

                    <div class="flex-grow flex flex-col justify-between py-1">
                        <div>
                            <h4 class="text-base font-bold text-gray-800 leading-snug mb-1"><?php echo htmlspecialchars($nombre_producto); ?></h4>
                            <p class="text-xs text-gray-500 line-clamp-2"><?php echo htmlspecialchars($descripcion); ?></p>
                        </div>

                        <div class="flex items-end justify-between mt-2">
                            <div class="text-sm">
                                <?php echo $precio_display; ?>
                            </div>

                            <?php if (count($variantes_db) > 0): ?>
                                <span class="text-[10px] uppercase font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                                    Opciones
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </div>
    </section>
<?php
}
?>