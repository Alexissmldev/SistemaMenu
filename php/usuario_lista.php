<?php

$inicio = ($pagina > 0) ? (($registros * $pagina) - $registros) : 0;
$tabla = "";
$conexion = conexion();

// --- CONSULTAS SQL (Mantenemos el JOIN con roles) ---
if (isset($busqueda) && $busqueda != "") {
    $consulta_datos = $conexion->prepare("
        SELECT u.*, r.rol_nombre 
        FROM usuario u
        INNER JOIN roles r ON u.rol_id = r.rol_id
        WHERE ((u.usuario_id != :id) 
        AND (u.usuario_nombre LIKE :busqueda OR u.usuario_apellido LIKE :busqueda OR u.usuario_usuario LIKE :busqueda)) 
        ORDER BY u.usuario_nombre ASC LIMIT $inicio, $registros
    ");

    $consulta_datos->execute([':id' => $_SESSION['id'], ':busqueda' => "%$busqueda%"]);

    $consulta_total = $conexion->prepare("
        SELECT COUNT(u.usuario_id) 
        FROM usuario u
        INNER JOIN roles r ON u.rol_id = r.rol_id
        WHERE ((u.usuario_id != :id) 
        AND (u.usuario_nombre LIKE :busqueda OR u.usuario_apellido LIKE :busqueda OR u.usuario_usuario LIKE :busqueda))
    ");
    $consulta_total->execute([':id' => $_SESSION['id'], ':busqueda' => "%$busqueda%"]);
} else {
    $consulta_datos = $conexion->prepare("
        SELECT u.*, r.rol_nombre 
        FROM usuario u
        INNER JOIN roles r ON u.rol_id = r.rol_id
        WHERE u.usuario_id != :id 
        ORDER BY u.usuario_nombre ASC LIMIT $inicio, $registros
    ");
    $consulta_datos->execute([':id' => $_SESSION['id']]);

    $consulta_total = $conexion->prepare("SELECT COUNT(usuario_id) FROM usuario WHERE usuario_id != :id");
    $consulta_total->execute([':id' => $_SESSION['id']]);
}

$datos = $consulta_datos->fetchAll();
$total = (int) $consulta_total->fetchColumn();
$Npagina = ceil($total / $registros);

// --- INICIO DE LA TABLA HTML ---
$tabla .= '
<div class="bg-white rounded-lg shadow-md border border-gray-200">
    <div class="hidden md:block">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="px-6 py-3">Usuario</th>
                    <th scope="col" class="px-6 py-3 text-center">Rol Asignado</th>
                    <th scope="col" class="px-6 py-3 text-center">Opciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">';

if ($total >= 1 && $pagina <= $Npagina) {
    $pag_inicio = $inicio + 1;
    foreach ($datos as $rows) {

        $avatar = '<div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-lg font-bold">
                        <i class="fas fa-user"></i>
                   </div>';

        // Estilos según rol
        if (stripos($rows['rol_nombre'], 'Admin') !== false) {
            $rol_estilo = 'bg-purple-100 text-purple-800 border-purple-200';
        } else {
            $rol_estilo = 'bg-blue-100 text-blue-800 border-blue-200';
        }
        $rol_badge = '<span class="' . $rol_estilo . ' text-xs font-medium px-3 py-1 rounded-full border border-opacity-40">' . htmlspecialchars($rows['rol_nombre']) . '</span>';

        $tabla .= '
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            ' . $avatar . '
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">' . htmlspecialchars($rows['usuario_nombre'] . ' ' . $rows['usuario_apellido']) . '</div>
                            <div class="text-sm text-indigo-500 font-medium">@' . htmlspecialchars($rows['usuario_usuario']) . '</div>
                        </div>
                    </div>
                </td>
                
                <td class="px-6 py-4 text-center">
                    ' . $rol_badge . '
                </td>

                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center">
                        <a href="index.php?vista=user_update&user_id_up=' . $rows['usuario_id'] . '" class="text-gray-500 hover:text-blue-600 transition-colors p-2 rounded-full hover:bg-blue-50" title="Ver detalles">
                            <i class="fas fa-eye fa-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>';
    }
} else {
    $tabla .= '<tr><td colspan="3" class="text-center py-12 text-gray-500">No hay usuarios registrados.</td></tr>';
}

$tabla .= '
            </tbody>
        </table>
    </div>

    <div class="md:hidden divide-y divide-gray-200">';

if ($total >= 1 && $pagina <= $Npagina) {
    foreach ($datos as $rows) {

        $avatar_mobile = '<div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600 text-xl flex-shrink-0">
                            <i class="fas fa-user"></i>
                          </div>';

        if (stripos($rows['rol_nombre'], 'Admin') !== false) {
            $rol_estilo = 'bg-purple-100 text-purple-800 border-purple-200';
        } else {
            $rol_estilo = 'bg-blue-100 text-blue-800 border-blue-200';
        }
        $rol_badge = '<span class="' . $rol_estilo . ' text-xs font-medium px-2.5 py-0.5 rounded-full border">' . htmlspecialchars($rows['rol_nombre']) . '</span>';

        $tabla .= '
        <div class="p-4 bg-white">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                    ' . $avatar_mobile . '
                    <div>
                        <p class="font-semibold text-gray-900">' . htmlspecialchars($rows['usuario_nombre'] . ' ' . $rows['usuario_apellido']) . '</p>
                        <p class="text-sm text-indigo-600 font-medium">@' . htmlspecialchars($rows['usuario_usuario']) . '</p>
                    </div>
                </div>
                <div class="flex-shrink-0 ml-2">
                    ' . $rol_badge . '
                </div>
            </div>
            
            <div class="mt-4 flex justify-end items-center border-t pt-3 border-gray-100">
                <a href="index.php?vista=user_update&user_id_up=' . $rows['usuario_id'] . '" class="text-gray-500 hover:text-blue-600 flex items-center text-sm font-medium transition-colors">
                    <i class="fas fa-eye mr-2"></i> Ver detalles
                </a>
            </div>
        </div>';
    }
} else {
    $tabla .= '<div class="p-6 text-center text-gray-500">No hay usuarios registrados.</div>';
}

$tabla .= '
    </div>
</div>';

// Paginación
if ($total >= 1 && $pagina <= $Npagina) {
    $pag_final = min(($inicio + $registros), $total);
    $tabla .= '
    <p class="text-right text-sm text-gray-600 mt-4 mr-2">
        Mostrando <strong>' . $pag_inicio . '</strong> - <strong>' . $pag_final . '</strong> de <strong>' . $total . '</strong> usuarios
    </p>';
}

$conexion = null;
echo $tabla;

if ($total >= 1 && $pagina <= $Npagina) {
    echo paginador_tablas($pagina, $Npagina, $url, 7);
}
