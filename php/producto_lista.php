    <?php
    $inicio = ($pagina > 0) ? (($registros * $pagina) - $registros) : 0;
    $tabla = "";

    // Se añade 'producto_estado' a la lista de campos a seleccionar
    $campos = "p.producto_id,p.producto_codigo,p.producto_stock, p.producto_nombre, p.producto_precio,p.descripcion_producto, p.producto_foto, p.producto_estado, c.categoria_nombre, u.usuario_nombre, u.usuario_apellido";

    $conexion = conexion();
    if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
        $busqueda = limpiar_cadena($_GET['busqueda']);
        $url = "index.php?vista=product_list&busqueda=" . urlencode($busqueda) . "&page=";
    }

    // --- CONSULTAS SEGURAS CON PREPARED STATEMENTS ---
    if (isset($busqueda) && $busqueda != "") {
        $consulta_datos = $conexion->prepare("SELECT $campos FROM producto p INNER JOIN categoria c ON p.categoria_id=c.categoria_id INNER JOIN usuario u ON p.usuario_id=u.usuario_id WHERE p.producto_codigo LIKE :busqueda OR p.producto_nombre LIKE :busqueda ORDER BY p.producto_nombre ASC LIMIT $inicio, $registros");
        $consulta_datos->execute([':busqueda' => "%$busqueda%"]);

        $consulta_total = $conexion->prepare("SELECT COUNT(producto_id) FROM producto WHERE producto_codigo LIKE :busqueda OR producto_nombre LIKE :busqueda");
        $consulta_total->execute([':busqueda' => "%$busqueda%"]);
    } elseif ($categoria_id > 0) {
        $consulta_datos = $conexion->prepare("SELECT $campos FROM producto p INNER JOIN categoria c ON p.categoria_id=c.categoria_id INNER JOIN usuario u ON p.usuario_id=u.usuario_id WHERE p.categoria_id = :cat_id ORDER BY p.producto_nombre ASC LIMIT $inicio, $registros");
        $consulta_datos->execute([':cat_id' => $categoria_id]);

        $consulta_total = $conexion->prepare("SELECT COUNT(producto_id) FROM producto WHERE categoria_id = :cat_id");
        $consulta_total->execute([':cat_id' => $categoria_id]);
    } else {
        $consulta_datos = $conexion->prepare("SELECT $campos FROM producto p INNER JOIN categoria c ON p.categoria_id=c.categoria_id INNER JOIN usuario u ON p.usuario_id=u.usuario_id ORDER BY p.producto_nombre ASC LIMIT $inicio, $registros");
        $consulta_datos->execute(); 

        $consulta_total = $conexion->prepare("SELECT COUNT(producto_id) FROM producto");
        $consulta_total->execute();
    }

    $datos = $consulta_datos->fetchAll();
    $total = (int) $consulta_total->fetchColumn();
    $Npagina = ceil($total / $registros);

    if ($total >= 1 && $pagina <= $Npagina) {
        $contador = $inicio + 1;
        $pag_inicio = $inicio + 1;

         }
// Tu código actual es funcional, pero esto lo haría aún más robusto.

// --- INICIO DEL NUEVO DISEÑO DE TABLA ---
$tabla .= '
<div class="bg-white rounded-lg shadow-md border border-gray-200">
    <div class="hidden md:block">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Producto</th>
                    <th scope="col" class="px-6 py-3">Precio</th>
                    <th scope="col" class="px-6 py-3">Categoría</th>
                    <th scope="col" class="px-6 py-3">Descripcion</th>
                    <th scope="col" class="px-6 py-3 text-center">Estado</th>
                    <th scope="col" class="px-6 py-3 text-center">Opciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">';

if ($total >= 1 && $pagina <= $Npagina) {
    foreach ($datos as $rows) {
        $estado_badge = ($rows['producto_estado'] == 1)
            ? '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Disponible</span>'
            : '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">No Disponible</span>';

        $tabla .= '
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <img class="w-12 h-12 rounded-lg object-cover" src="' . (is_file("./img/producto/thumb/" . $rows['producto_foto']) ? './img/producto/thumb/' . $rows['producto_foto'] : './img/producto.png') . '" alt="' . htmlspecialchars($rows['producto_nombre']) . '">
                    </div>
                    <div class="font-medium text-gray-900">' . htmlspecialchars($rows['producto_nombre']) . '</div>
                </div>
            </td>
            <td class="px-6 py-4 font-semibold text-green-600">$' . htmlspecialchars(number_format($rows['producto_precio'], 2)) . '</td>
            
            <td class="px-6 py-4">' . htmlspecialchars($rows['categoria_nombre']) . '</td>
             <td class="px-6 py-4">' . htmlspecialchars($rows['descripcion_producto']) . '</td>

            <td class="px-6 py-4 text-center">' . $estado_badge . '</td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center space-x-3">
                    <button onclick="openModal(\'product_img\', \'' . $rows['producto_id'] . '\', \'product_id_up\', \'initProductImageModalScripts\')" type="button" class="text-gray-500 hover:text-indigo-600" title="Gestionar Imagen"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M5.25 12.862a.75.75 0 10-1.06 1.06l3.25 3.25a.75.75 0 001.06 0l3.25-3.25a.75.75 0 10-1.06-1.06L10.5 14.22V8.25a.75.75 0 00-1.5 0v5.97l-1.22-1.22zM3.5 4.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 10h10.5A2.75 2.75 0 0018 7.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" /></svg></button>
                    <button onclick="openModal(\'product_update\', \'' . $rows['producto_id'] . '\', \'product_id_up\', \'initProductUpdateModal\')" type="button" class="text-gray-500 hover:text-blue-600" title="Actualizar Producto"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg></button>
                    <button onclick="eliminarProducto(' . $rows['producto_id'] . ', \'' . htmlspecialchars($rows['producto_nombre'], ENT_QUOTES) . '\')" type="button" class="text-gray-500 hover:text-red-600" title="Eliminar Producto"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg></button>
                </div>
            </td>
        </tr>';
    }
} else {
    // Si no hay registros, muestra una fila con un mensaje.
    $tabla .= '<tr><td colspan="6" class="text-center py-12 text-gray-500">No hay productos en el sistema.</td></tr>';
}

$tabla .= '
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-gray-200">';

if ($total >= 1 && $pagina <= $Npagina) {
    foreach ($datos as $rows) {
        $estado_badge = ($rows['producto_estado'] == 1)
            ? '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Disponible</span>'
            : '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">No Disponible</span>';
        
        $tabla .= '
        <div class="p-4">
            <div class="flex items-start space-x-4">
                <img class="w-16 h-16 rounded-lg object-cover flex-shrink-0" src="' . (is_file("./img/producto/thumb/" . $rows['producto_foto']) ? './img/producto/thumb/' . $rows['producto_foto'] : './img/producto.png') . '" alt="' . htmlspecialchars($rows['producto_nombre']) . '">
                <div class="flex-grow">
                    <p class="font-semibold text-gray-900">' . htmlspecialchars($rows['producto_nombre']) . '</p>
                    <p class="text-sm text-green-600 font-bold mt-1">$' . htmlspecialchars(number_format($rows['producto_precio'], 2)) . '</p>
                    <p class="text-xs text-gray-500 mt-1">Categoría: ' . htmlspecialchars($rows['categoria_nombre']) . '</p>
                </div>
                <div class="flex-shrink-0">' . $estado_badge . '</div>
            </div>
            <div class="mt-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <strong>Stock:</strong> ' . htmlspecialchars($rows['producto_stock']) . '
                </div>
                <div class="flex items-center space-x-3">
                    <button onclick="openModal(\'product_img\', \'' . $rows['producto_id'] . '\', \'product_id_up\', \'initProductImageModalScripts\')" type="button" class="text-gray-500 hover:text-indigo-600" title="Gestionar Imagen"><svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M5.25 12.862a.75.75 0 10-1.06 1.06l3.25 3.25a.75.75 0 001.06 0l3.25-3.25a.75.75 0 10-1.06-1.06L10.5 14.22V8.25a.75.75 0 00-1.5 0v5.97l-1.22-1.22zM3.5 4.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 10h10.5A2.75 2.75 0 0018 7.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" /></svg></button>
                    <button onclick="openModal(\'product_update\', \'' . $rows['producto_id'] . '\', \'product_id_up\', \'initProductUpdateModal\')" type="button" class="text-gray-500 hover:text-blue-600" title="Actualizar Producto"><svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg></button>
                    <button onclick="eliminarProducto(' . $rows['producto_id'] . ', \'' . htmlspecialchars($rows['producto_nombre'], ENT_QUOTES) . '\')" type="button" class="text-gray-500 hover:text-red-600" title="Eliminar Producto"><svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg></button>
                </div>
            </div>
        </div>';
    }
}
$tabla .= '
    </div>
</div>';

    $conexion = null;
    echo $tabla;

    if ($total >= 1 && $pagina <= $Npagina) {
        echo paginador_tablas($pagina, $Npagina, $url, 6);
    }

