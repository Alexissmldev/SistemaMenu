<?php
$inicio = ($pagina > 0) ? (($registros * $pagina) - $registros) : 0;
$tabla = "";

// CAMPOS
$campos = "p.producto_id, p.producto_nombre, p.producto_precio,p.descripcion_producto, p.producto_foto, p.producto_estado, c.categoria_nombre, u.usuario_nombre, u.usuario_apellido";

$conexion = conexion();
if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda = limpiar_cadena($_GET['busqueda']);
    $url = "index.php?vista=product_list&busqueda=" . urlencode($busqueda) . "&page=";
}

// CONSULTAS SQL
if (isset($busqueda) && $busqueda != "") {
    $consulta_datos = $conexion->prepare("SELECT $campos FROM producto p INNER JOIN categoria c ON p.categoria_id=c.categoria_id INNER JOIN usuario u ON p.usuario_id=u.usuario_id WHERE p.producto_nombre LIKE :busqueda ORDER BY p.producto_nombre ASC LIMIT $inicio, $registros");
    $consulta_datos->execute([':busqueda' => "%$busqueda%"]);

    $consulta_total = $conexion->prepare("SELECT COUNT(producto_id) FROM producto WHERE producto_nombre LIKE :busqueda");
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

// INICIO DE LA TABLA HTML
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
                            <img class="w-12 h-12 rounded-lg object-cover" src="' . (is_file("./img/producto/thumb/" . $rows['producto_foto']) ? './img/producto/thumb/' . $rows['producto_foto'] : './img.png') . '" alt="' . htmlspecialchars($rows['producto_nombre']) . '">
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
                        <a href="index.php?vista=product_update&product_id_up=' . $rows['producto_id'] . '" class="text-gray-500 hover:text-blue-600" title="Actualizar Producto">
                            <i class="fa fa-pencil"></i>
                        </a>                        
                        <button onclick="eliminarProducto(' . $rows['producto_id'] . ', \'' . htmlspecialchars($rows['producto_nombre'], ENT_QUOTES) . '\')" type="button" class="text-gray-500 hover:text-red-600" title="Eliminar Producto">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>';
    }
} else {
    $tabla .= '<tr><td colspan="6" class="text-center py-12 text-gray-500">No hay productos en el sistema.</td></tr>';
}

$tabla .= '
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-gray-200">';

// VISTA MOVIL
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
                  
                    <div class="flex items-center space-x-3">
                        <a href="index.php?vista=product_update&product_id_up=' . $rows['producto_id'] . '" class="text-gray-500 hover:text-blue-600" title="Actualizar Producto">
                            <i class="fa fa-pencil fa-lg"></i>
                        </a>
                        <button onclick="eliminarProducto(' . $rows['producto_id'] . ', \'' . htmlspecialchars($rows['producto_nombre'], ENT_QUOTES) . '\')" type="button" class="text-gray-500 hover:text-red-600" title="Eliminar Producto">
                            <i class="fa fa-trash fa-lg"></i>
                        </button>
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
