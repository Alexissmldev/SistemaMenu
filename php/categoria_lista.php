<?php
// --- 1. CONFIGURACIÓN INICIAL (SIN CAMBIOS) ---
require_once "./php/main.php";

$pagina = limpiar_cadena($_GET['page'] ?? 1);
$pagina = ($pagina <= 1) ? 1 : $pagina;

$registros = 15;
$url = "index.php?vista=category_list&page=";
$busqueda = "";

if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda = limpiar_cadena($_GET['busqueda']);
    $url = "index.php?vista=category_list&busqueda=" . urlencode($busqueda) . "&page=";
}

$inicio = ($pagina > 0) ? (($registros * $pagina) - $registros) : 0;
$tabla = "";

$conexion = conexion();

// --- 2. CONSULTAS A LA BASE DE DATOS (SIN CAMBIOS) ---
if (!empty($busqueda)) {
    $consulta_datos = $conexion->prepare("SELECT * FROM categoria WHERE categoria_nombre LIKE :busqueda ORDER BY categoria_nombre ASC LIMIT $inicio, $registros");
    $consulta_datos->execute([':busqueda' => "%$busqueda%"]);

    $consulta_total = $conexion->prepare("SELECT COUNT(categoria_id) FROM categoria WHERE categoria_nombre LIKE :busqueda");
    $consulta_total->execute([':busqueda' => "%$busqueda%"]);
} else {
    $consulta_datos = $conexion->prepare("SELECT * FROM categoria ORDER BY categoria_nombre ASC LIMIT $inicio, $registros");
    $consulta_datos->execute();

    $consulta_total = $conexion->prepare("SELECT COUNT(categoria_id) FROM categoria");
    $consulta_total->execute();
}

$datos = $consulta_datos->fetchAll();
$total = (int)$consulta_total->fetchColumn();
$Npagina = ceil($total / $registros);


// --- INICIO DEL NUEVO DISEÑO DE TABLA ---
$tabla .= '
<div class="bg-white rounded-lg shadow-md border border-gray-200">
    <div class="hidden md:block">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Nombre</th>
                    <th scope="col" class="px-6 py-3">Estado</th>
                    <th scope="col" class="px-6 py-3">Productos</th>
                    <th scope="col" class="px-6 py-3 text-center">Opciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">';

if ($total >= 1 && $pagina <= $Npagina) {
    $pag_inicio = $inicio + 1;
    foreach ($datos as $rows) {
        $estado_badge = ($rows['categoria_estado'] == 1)
            ? '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Vigente</span>'
            : '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">No Vigente</span>';

        $tabla .= '
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 font-semibold text-gray-900">' . htmlspecialchars($rows['categoria_nombre']) . '</td>
            <td class="px-6 py-4">' . $estado_badge . '</td>
            <td class="px-6 py-4">
                <button type="button" 
                    onclick="openModal(\'category_products\', \'' . $rows['categoria_id'] . '\', \'category_id\')"
                    class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full hover:bg-gray-200 transition-colors" 
                    title="Ver Productos Asociados">
                    Ver productos
                </button> 
            </td>
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center space-x-3">
                    <button onclick="openModal(\'category_update\', \'' . $rows['categoria_id'] . '\', \'category_id_up\',\'initCategoryUpdateModal\')" type="button" class="text-gray-500 hover:text-blue-600" title="Actualizar Categoría">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                    </button>
                    <button onclick="eliminarCategoria(' . $rows['categoria_id'] . ', \'' . htmlspecialchars($rows['categoria_nombre'], ENT_QUOTES) . '\')" type="button" class="text-gray-500 hover:text-red-600" title="Eliminar Categoría">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            </td>
        </tr>';
    }
} else {
    $tabla .= '<tr><td colspan="4" class="text-center py-12 text-gray-500">No hay categorías en el sistema.</td></tr>';
}

$tabla .= '
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-gray-200">';

if ($total >= 1 && $pagina <= $Npagina) {
    foreach ($datos as $rows) {
        $estado_badge = ($rows['categoria_estado'] == 1)
            ? '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Vigente</span>'
            : '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">No Vigente</span>';

        $tabla .= '
        <div class="p-4">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <p class="font-semibold text-gray-900">' . htmlspecialchars($rows['categoria_nombre']) . '</p>
                    <div class="mt-1">' . $estado_badge . '</div>
                </div>
                <div class="flex items-center space-x-3 flex-shrink-0 ml-4">
                    <button onclick="openModal(\'category_update\', \'' . $rows['categoria_id'] . '\', \'category_id_up\',\'initCategoryUpdateModal\')" type="button" class="text-gray-500 hover:text-blue-600" title="Actualizar Categoría">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                    </button>
                    <button onclick="eliminarCategoria(' . $rows['categoria_id'] . ', \'' . htmlspecialchars($rows['categoria_nombre'], ENT_QUOTES) . '\')" type="button" class="text-gray-500 hover:text-red-600" title="Eliminar Categoría">
                        <svg class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg>
                    </button>
                </div>
            </div>
            <button type="button" 
                onclick="openModal(\'category_products\', \'' . $rows['categoria_id'] . '\', \'category_id\')"
                class="w-full mt-2 px-3 py-1.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-md hover:bg-gray-200 transition-colors" 
                title="Ver Productos Asociados">
                Ver productos
            </button> 
        </div>';
    }
}
$tabla .= '
    </div>
</div>';


// --- 4. INFORMACIÓN DE PAGINACIÓN ---
if ($total >= 1 && $pagina <= $Npagina) {
    $pag_final = min(($inicio + $registros), $total);
    $tabla .= '
    <div class="py-4 text-sm text-gray-700 text-center sm:text-right">
        Mostrando <strong>' . $pag_inicio . '</strong> a <strong>' . $pag_final . '</strong> de un total de <strong>' . $total . '</strong> registros
    </div>';
}

$conexion = null;
echo $tabla;

// --- 5. RENDERIZADO DEL PAGINADOR ---
if ($total >= 1 && $pagina <= $Npagina) {
    echo paginador_tablas($pagina, $Npagina, $url, 7);
}
