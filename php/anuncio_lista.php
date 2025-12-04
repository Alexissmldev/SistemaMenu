<?php
$inicio = ($pagina > 0) ? (($registros * $pagina) - $registros) : 0;
$tabla = "";

$campos = "anuncio_id, anuncio_mensaje, anuncio_hora_inicio, anuncio_hora_fin, anuncio_tipo, anuncio_estado, anuncio_fecha_inicio, anuncio_fecha_fin, anuncio_prioridad";

$conexion = conexion();

// Lógica de Búsqueda
if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
    $busqueda = limpiar_cadena($_GET['busqueda']);
    $url = "index.php?vista=ad_list&busqueda=" . urlencode($busqueda) . "&page=";

    $consulta_datos = $conexion->prepare("SELECT $campos FROM anuncios WHERE anuncio_mensaje LIKE :busqueda ORDER BY anuncio_prioridad DESC, anuncio_hora_inicio ASC LIMIT $inicio, $registros");
    $consulta_datos->execute([':busqueda' => "%$busqueda%"]);

    $consulta_total = $conexion->prepare("SELECT COUNT(anuncio_id) FROM anuncios WHERE anuncio_mensaje LIKE :busqueda");
    $consulta_total->execute([':busqueda' => "%$busqueda%"]);
} else {
    //Lógica por Defecto 
    $consulta_datos = $conexion->prepare("SELECT $campos FROM anuncios ORDER BY anuncio_prioridad DESC, anuncio_hora_inicio ASC LIMIT $inicio, $registros");
    $consulta_datos->execute();

    $consulta_total = $conexion->prepare("SELECT COUNT(anuncio_id) FROM anuncios");
    $consulta_total->execute();
}

$datos = $consulta_datos->fetchAll();
$total = (int) $consulta_total->fetchColumn();
$Npagina = ceil($total / $registros);

if ($total >= 1 && $pagina <= $Npagina) {
    $contador = $inicio + 1;
    $pag_inicio = $inicio + 1;
}

//Construcción del Grid de Anuncios

$tabla .= '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';

if ($total >= 1 && $pagina <= $Npagina) {
    foreach ($datos as $rows) {

        //Lógica para Badges y Textos 

        //Badge de Estado
        $estado_badge = ($rows['anuncio_estado'] == 1)
            ? '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><i class="fa fa-check-circle mr-1"></i>Activo</span>'
            : '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><i class="fa fa-times-circle mr-1"></i>Inactivo</span>';

        // Badge de Tipo
        $tipo_badge = '';
        $tipo_color = 'gray';
        $tipo_icono = 'fa-info-circle';
        $tipo_texto = ucfirst($rows['anuncio_tipo']);

        if ($rows['anuncio_tipo'] == 'alerta') {
            $tipo_color = 'red';
            $tipo_icono = 'fa-exclamation-triangle';
        } elseif ($rows['anuncio_tipo'] == 'oferta') {
            $tipo_color = 'green';
            $tipo_icono = 'fa-star';
        } elseif ($rows['anuncio_tipo'] == 'info') {
            $tipo_color = 'blue';
            $tipo_icono = 'fa-info-circle';
        }

        $tipo_badge = "<span class=\"absolute top-0 right-0 -mt-3 -mr-3 bg-{$tipo_color}-500 text-white text-xs font-bold w-8 h-8 rounded-full flex items-center justify-center shadow-lg\" title=\"Tipo: {$tipo_texto}\"><i class=\"fa {$tipo_icono}\"></i></span>";
        $horario_texto = sprintf("De %02d:00 a %02d:00", $rows['anuncio_hora_inicio'], $rows['anuncio_hora_fin']);
        $fecha_texto = '';
        if (!empty($rows['anuncio_fecha_inicio'])) {
            $fecha_inicio_f = date("d/m/Y", strtotime($rows['anuncio_fecha_inicio']));
            $fecha_fin_f = !empty($rows['anuncio_fecha_fin']) ? date("d/m/Y", strtotime($rows['anuncio_fecha_fin'])) : 'Indefinido';
            $fecha_texto = "<p class=\"text-sm text-gray-500 mt-2 flex items-center gap-2\"><i class=\"fa fa-calendar-alt fa-fw\"></i> {$fecha_inicio_f} - {$fecha_fin_f}</p>";
        }

        //  HTML de la Tarjeta 
        $tabla .= '
            <div class="bg-white rounded-lg shadow-md border border-gray-200 flex flex-col relative overflow-hidden">
                
                ' . $tipo_badge . '

                <div class="p-5 flex-grow">
                    <h5 class="text-lg font-bold tracking-tight text-gray-900 mb-2 pr-8">' . htmlspecialchars($rows['anuncio_mensaje']) . '</h5>
                    
                    <p class="text-base font-semibold text-blue-600 flex items-center gap-2">
                        <i class="fa fa-clock fa-fw"></i> 
                        ' . $horario_texto . '
                    </p>
                    
                    ' . $fecha_texto . '
                </div>
                
                <div class="p-4 bg-gray-50 border-t flex justify-between items-center">
                    <div>
                        ' . $estado_badge . '
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="index.php?vista=ad_update&ad_id_up=' . $rows['anuncio_id'] . '" class="text-gray-500 hover:text-blue-600" title="Actualizar Anuncio">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                        </a>
                        
                        <button onclick="eliminarAnuncio(' . $rows['anuncio_id'] . ', \'' . htmlspecialchars($rows['anuncio_mensaje'], ENT_QUOTES) . '\')" type="button" class="text-gray-500 hover:text-red-600" title="Eliminar Anuncio">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </div>
            </div>';
    }
} else {
    $tabla .= '
        <div class="col-span-full text-center py-12 text-gray-500">
            <i class="fa fa-bullhorn fa-3x text-gray-300"></i>
            <p class="mt-4 text-lg">No hay anuncios registrados.</p>
            <p class="text-sm">¡Comienza creando uno nuevo!</p>
        </div>';
}

$tabla .= '</div>';

$conexion = null;
echo $tabla;

//Paginación
if ($total >= 1 && $pagina <= $Npagina) {
    echo paginador_tablas($pagina, $Npagina, $url, 7);
}
