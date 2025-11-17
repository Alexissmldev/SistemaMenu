<?php
$inicio = ($pagina > 0) ? (($registros * $pagina) - $registros) : 0;
$tabla = "";

//  Definir los campos
$campos = "promo_id, promo_nombre, promo_precio, promo_foto, hora_inicio, hora_fin, estado, prioridad";

$conexion = conexion();

//  Lógica de Búsqueda
if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda = limpiar_cadena($_GET['busqueda']);
    $url = "index.php?vista=promo_list&busqueda=" . urlencode($busqueda) . "&page=";

    $consulta_datos = $conexion->prepare("SELECT $campos FROM promociones WHERE promo_nombre LIKE :busqueda ORDER BY prioridad DESC, hora_inicio ASC LIMIT $inicio, $registros");
    $consulta_datos->execute([':busqueda' => "%$busqueda%"]);

    $consulta_total = $conexion->prepare("SELECT COUNT(promo_id) FROM promociones WHERE promo_nombre LIKE :busqueda");
    $consulta_total->execute([':busqueda' => "%$busqueda%"]);
} else {
    // Lógica por Defecto
    $consulta_datos = $conexion->prepare("SELECT $campos FROM promociones ORDER BY prioridad DESC, hora_inicio ASC LIMIT $inicio, $registros");
    $consulta_datos->execute();

    $consulta_total = $conexion->prepare("SELECT COUNT(promo_id) FROM promociones");
    $consulta_total->execute();
}

$datos = $consulta_datos->fetchAll();
$total = (int) $consulta_total->fetchColumn();
$Npagina = ceil($total / $registros);

if ($total >= 1 && $pagina <= $Npagina) {
    $contador = $inicio + 1;
    $pag_inicio = $inicio + 1;
}

//  Diseño de Tarjetas para Promociones 

$tabla .= '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';

if ($total >= 1 && $pagina <= $Npagina) {
    foreach ($datos as $rows) {

        // Badge de Estado
        $estado_badge = ($rows['estado'] == 1)
            ? '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><i class="fa fa-check-circle mr-1"></i>Activa</span>'
            : '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><i class="fa fa-times-circle mr-1"></i>Inactiva</span>';

        $horario_texto = sprintf("De %02d:00 a %02d:00", $rows['hora_inicio'], $rows['hora_fin']);

        $imagen_url = './img/anuncios/estandar.jpg';
        if (!empty($rows['promo_foto']) && is_file('./img/anuncios/large/' . $rows['promo_foto'])) {
            $imagen_url = './img/anuncios/large/' . $rows['promo_foto'];
        }

        $tabla .= '
            <div class="bg-white rounded-lg shadow-md border border-gray-200 flex flex-col">
                
                <div class="h-40 w-full bg-cover bg-center rounded-t-lg" style="background-image: url(\'' . htmlspecialchars($imagen_url) . '\');"></div>

                <div class="p-5 flex-grow">
                    <h5 class="text-lg font-bold tracking-tight text-gray-900 mb-2">' . htmlspecialchars($rows['promo_nombre']) . '</h5>
                    
                    <p class="text-xl font-extrabold text-green-600 mb-2">
                        $' . htmlspecialchars(number_format($rows['promo_precio'], 2)) . '
                    </p>

                    <p class="text-sm font-semibold text-blue-600 flex items-center gap-2">
                        <i class="fa fa-clock fa-fw"></i> 
                        ' . $horario_texto . '
                    </p>
                </div>
                
                <div class="p-4 bg-gray-50 border-t flex justify-between items-center rounded-b-lg">
                    <div>
                        ' . $estado_badge . '
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="index.php?vista=promo_update&promo_id_up=' . $rows['promo_id'] . '" class="text-gray-500 hover:text-blue-600" title="Actualizar Promoción">
                            <i class="fa fa-pencil h-5 w-5"></i>
                        </a>
                        
                        <button onclick="eliminarPromocion(' . $rows['promo_id'] . ', \'' . htmlspecialchars($rows['promo_nombre'], ENT_QUOTES) . '\')" type="button" class="text-gray-500 hover:text-red-600" title="Eliminar Promoción">
                            <i class="fa fa-trash h-5 w-5"></i>
                        </button>
                    </div>
                </div>
            </div>';
    }
} else {
    $tabla .= '
        <div class="col-span-full text-center py-12 text-gray-500">
            <i class="fa fa-star fa-3x text-gray-300"></i>
            <p class="mt-4 text-lg">No hay promociones registradas.</p>
            <p class="text-sm">¡Comienza creando una nueva!</p>
        </div>';
}

$tabla .= '</div>';

$conexion = null;
echo $tabla;


if ($total >= 1 && $pagina <= $Npagina) {
    echo paginador_tablas($pagina, $Npagina, $url, 7);
}
