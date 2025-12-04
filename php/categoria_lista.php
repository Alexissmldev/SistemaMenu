<?php
// --- 1. CONFIGURACIÓN INICIAL ---
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

// --- 2. CONSULTAS A LA BASE DE DATOS ---
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


// --- 3. INICIO DE LA TABLA HTML ---
$tabla .= '
<div class="bg-white rounded-lg shadow-md border border-gray-200">
    
    <div class="hidden md:block">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Nombre</th>
                    <th scope="col" class="px-6 py-3">Horario Disp.</th>
                    <th scope="col" class="px-6 py-3 text-center">Estado</th>
                    <th scope="col" class="px-6 py-3 text-center">Productos</th>
                    <th scope="col" class="px-6 py-3 text-center">Opciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">';

if ($total >= 1 && $pagina <= $Npagina) {
    $pag_inicio = $inicio + 1;
    foreach ($datos as $rows) {

        // --- BADGE DE ESTADO ---
        $estado_badge = ($rows['categoria_estado'] == 1)
            ? '<span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Vigente</span>'
            : '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">No Vigente</span>';

        // --- LÓGICA DE HORARIO (FORMATO AM/PM) ---
        $horario_html = '<span class="text-gray-400 italic">Todo el día</span>';
        if (!empty($rows['categoria_hora_inicio']) && !empty($rows['categoria_hora_fin'])) {
            // Verificamos si no son 00:00:00 para mostrar horas reales
            if ($rows['categoria_hora_inicio'] != '00:00:00' || $rows['categoria_hora_fin'] != '00:00:00') {
                $hora_inicio = date("g:i A", strtotime($rows['categoria_hora_inicio']));
                $hora_fin = date("g:i A", strtotime($rows['categoria_hora_fin']));
                $horario_html = '<span class="text-gray-700 font-medium">' . $hora_inicio . ' - ' . $hora_fin . '</span>';
            }
        }

        $tabla .= '
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 font-semibold text-gray-900">' . htmlspecialchars($rows['categoria_nombre']) . '</td>
            
            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-clock text-gray-400"></i>
                    ' . $horario_html . '
                </div>
            </td>

            <td class="px-6 py-4 text-center">' . $estado_badge . '</td>
            
            <td class="px-6 py-4 text-center">
                <button type="button" 
                    onclick="openModal(\'category_products\', \'' . $rows['categoria_id'] . '\', \'category_id\')"
                    class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-full hover:bg-gray-200 transition-colors" 
                    title="Ver Productos Asociados">
                    Ver productos
                </button> 
            </td>
            
            <td class="px-6 py-4 text-center">
                <div class="flex items-center justify-center space-x-3">
                    
                    <button type="button" onclick="openModal(\'category_update\', \'' . $rows['categoria_id'] . '\', \'category_id_up\')" class="text-gray-500 hover:text-blue-600 transition-colors" title="Actualizar Categoría">
                        <i class="fas fa-pencil-alt text-lg"></i>
                    </button>
                    
                    <button type="button" onclick="eliminarCategoria(' . $rows['categoria_id'] . ', \'' . htmlspecialchars($rows['categoria_nombre'], ENT_QUOTES) . '\')" class="text-gray-500 hover:text-red-600 transition-colors" title="Eliminar Categoría">
                        <i class="fas fa-trash text-lg"></i>
                    </button>
                </div>
            </td>
        </tr>';
    }
} else {
    $tabla .= '<tr><td colspan="5" class="text-center py-12 text-gray-500">No hay categorías en el sistema.</td></tr>';
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

        // Lógica horario móvil
        $horario_texto = 'Todo el día';
        if (!empty($rows['categoria_hora_inicio']) && !empty($rows['categoria_hora_fin'])) {
            if ($rows['categoria_hora_inicio'] != '00:00:00' || $rows['categoria_hora_fin'] != '00:00:00') {
                $hora_inicio = date("g:i A", strtotime($rows['categoria_hora_inicio']));
                $hora_fin = date("g:i A", strtotime($rows['categoria_hora_fin']));
                $horario_texto = $hora_inicio . ' - ' . $hora_fin;
            }
        }

        $tabla .= '
        <div class="p-4 bg-white">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="font-semibold text-gray-900 text-lg">' . htmlspecialchars($rows['categoria_nombre']) . '</p>
                    
                    <div class="flex items-center gap-2 text-xs text-gray-500 mt-1">
                        <i class="fas fa-clock text-orange-500"></i>
                        <span>' . $horario_texto . '</span>
                    </div>

                    <div class="mt-2">' . $estado_badge . '</div>
                </div>
                
                <div class="flex items-center space-x-4 flex-shrink-0 ml-4">
                    
                    <button type="button" onclick="openModal(\'category_update\', \'' . $rows['categoria_id'] . '\', \'category_id_up\')" class="text-gray-500 hover:text-blue-600 p-1">
                        <i class="fas fa-pencil-alt text-xl"></i>
                    </button>
                    
                    <button type="button" onclick="eliminarCategoria(' . $rows['categoria_id'] . ', \'' . htmlspecialchars($rows['categoria_nombre'], ENT_QUOTES) . '\')" class="text-gray-500 hover:text-red-600 p-1">
                        <i class="fas fa-trash text-xl"></i>
                    </button>
                </div>
            </div>
            
            <button type="button" 
                onclick="openModal(\'category_products\', \'' . $rows['categoria_id'] . '\', \'category_id\')"
                class="w-full mt-3 px-3 py-2 bg-gray-50 text-gray-600 text-sm font-medium rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors flex justify-center items-center gap-2">
                <i class="fas fa-boxes"></i> Ver productos asociados
            </button> 
        </div>';
    }
} else {
    $tabla .= '<div class="p-6 text-center text-gray-500">No hay categorías registradas.</div>';
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
