<?php
// 1. CALCULO DE PAGINACIÓN
$inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
$tabla = "";

// 2. CONSTRUCCIÓN DE LA CONSULTA
// Usamos alias 'cc' para cierres_caja y 'u' para usuario
$campos = "cc.cierre_id, 
           cc.fecha_cierre, 
           cc.sistema_total_usd, 
           cc.tasa_bcv, 
           cc.diferencia, 
           u.usuario_nombre, 
           u.usuario_apellido";

if (isset($busqueda) && $busqueda != "") {
    // Consulta con búsqueda
    $consulta_datos = "SELECT SQL_CALC_FOUND_ROWS $campos 
                       FROM cierres_caja cc 
                       INNER JOIN usuario u ON cc.usuario_id = u.usuario_id 
                       WHERE (cc.fecha_cierre LIKE '%$busqueda%' 
                           OR u.usuario_nombre LIKE '%$busqueda%' 
                           OR u.usuario_apellido LIKE '%$busqueda%') 
                       ORDER BY cc.fecha_cierre DESC 
                       LIMIT $inicio, $registros";
} else {
    // Consulta normal (Todos los registros)
    $consulta_datos = "SELECT SQL_CALC_FOUND_ROWS $campos 
                       FROM cierres_caja cc 
                       INNER JOIN usuario u ON cc.usuario_id = u.usuario_id 
                       ORDER BY cc.fecha_cierre DESC 
                       LIMIT $inicio, $registros";
}

$conexion = conexion();

$datos = $conexion->query($consulta_datos);
$datos = $datos->fetchAll();

$total = $conexion->query("SELECT FOUND_ROWS()");
$total = (int) $total->fetchColumn();

$Npaginas = ceil($total / $registros);

// 3. GENERACIÓN DE LA TABLA
if ($total >= 1 && $pagina <= $Npaginas) {
    $contador = $inicio + 1;
    $pag_inicio = $inicio + 1;
    $pag_final = $inicio + count($datos);

    echo '
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                <tr>
                    <th scope="col" class="px-6 py-4 font-bold">#</th>
                    <th scope="col" class="px-6 py-4 font-bold">Fecha / Hora</th>
                    <th scope="col" class="px-6 py-4 font-bold">Responsable</th>
                    <th scope="col" class="px-6 py-4 font-bold text-right">Sistema (Bs)</th>
                    <th scope="col" class="px-6 py-4 font-bold text-right">Ingresado (Bs)</th>
                    <th scope="col" class="px-6 py-4 font-bold text-center">Diferencia</th>
                    <th scope="col" class="px-6 py-4 font-bold text-center">Opciones</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($datos as $rows) {

        // --- CÁLCULOS ---
        // 1. Recuperamos valores de la BD
        $tasa = $rows['tasa_bcv'];
        $sistema_usd = $rows['sistema_total_usd'];
        $diferencia_bs = $rows['diferencia']; // Asumimos que la diferencia se guardó en Bs.

        // 2. Convertimos Sistema a Bs para visualizar
        $sistema_bs = $sistema_usd * $tasa;

        // 3. Calculamos cuánto ingresó el usuario (Sistema + Diferencia)
        // Si la diferencia es -10 (faltan 10), entonces Ingresado = Sistema - 10
        $ingresado_bs = $sistema_bs + $diferencia_bs;

        // --- FORMATO VISUAL ---
        $fecha = date("d/m/Y h:i A", strtotime($rows['fecha_cierre']));

        // Lógica de Colores Semánticos
        $badge_class = "";
        $icon_class = "";
        $texto_estado = "";
        $monto_dif_visual = "";

        if (abs($diferencia_bs) < 1.00) {
            // Cuadre Perfecto (Margen < 1 Bs)
            $badge_class = "bg-emerald-100 text-emerald-700 border-emerald-200";
            $icon_class = "fa-check-circle";
            $texto_estado = "Perfecto";
            $monto_dif_visual = '<span class="text-emerald-600 font-bold">0.00</span>';
        } elseif ($diferencia_bs > 0) {
            // Sobrante (Positivo)
            $badge_class = "bg-blue-100 text-blue-700 border-blue-200";
            $icon_class = "fa-plus-circle";
            $texto_estado = "Sobra";
            $monto_dif_visual = '<span class="text-blue-600 font-bold">+' . number_format($diferencia_bs, 2) . '</span>';
        } else {
            // Faltante (Negativo)
            $badge_class = "bg-red-100 text-red-700 border-red-200";
            $icon_class = "fa-exclamation-circle";
            $texto_estado = "Falta";
            $monto_dif_visual = '<span class="text-red-600 font-bold">' . number_format($diferencia_bs, 2) . '</span>';
        }

        echo '
            <tr class="bg-white border-b hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 font-medium text-gray-900">' . $contador . '</td>
                <td class="px-6 py-4">
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-700">' . date("d/m/Y", strtotime($rows['fecha_cierre'])) . '</span>
                        <span class="text-xs text-gray-400">' . date("h:i A", strtotime($rows['fecha_cierre'])) . '</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold border border-indigo-100">
                            ' . substr($rows['usuario_nombre'], 0, 1) . substr($rows['usuario_apellido'], 0, 1) . '
                        </div>
                        <div class="flex flex-col">
                            <span class="font-medium text-gray-700 text-xs">' . $rows['usuario_nombre'] . ' ' . $rows['usuario_apellido'] . '</span>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    <span class="block font-mono text-gray-600 text-xs">Bs ' . number_format($sistema_bs, 2) . '</span>
                    <span class="block text-[10px] text-gray-400">($' . number_format($sistema_usd, 2) . ')</span>
                </td>
                <td class="px-6 py-4 text-right font-mono font-bold text-gray-800">
                    Bs ' . number_format($ingresado_bs, 2) . '
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex flex-col items-center justify-center gap-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide border ' . $badge_class . '">
                            ' . $texto_estado . '
                        </span>
                        <div class="text-xs font-mono">
                            ' . $monto_dif_visual . '
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        
                        
                        <a href="php/reporte_cierre_pdf.php?id=' . $rows['cierre_id'] . '" target="_blank" class="group relative flex items-center justify-center w-8 h-8 bg-white border border-gray-200 rounded-lg text-gray-500 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition-all shadow-sm">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    </div>
                </td>
            </tr>
        ';
        $contador++;
    }
    $pag_final = $contador - 1;
    echo '</tbody></table></div>';

    // --- BLOQUE DE PAGINACIÓN ---
    if ($total >= 1 && $pagina <= $Npaginas) {
        echo '
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between bg-gray-50">
            <span class="text-xs text-gray-500">
                <span class="font-bold text-gray-900">' . $pag_inicio . '</span> - <span class="font-bold text-gray-900">' . $pag_final . '</span> de <span class="font-bold text-gray-900">' . $total . '</span>
            </span>
            <div class="inline-flex gap-2">';

        if ($pagina == 1) {
            echo '<button class="px-3 py-1.5 text-xs font-medium text-gray-400 bg-white border border-gray-200 rounded-md cursor-not-allowed">Anterior</button>';
        } else {
            echo '<a href="index.php?vista=cash_history&page=' . ($pagina - 1) . '&busqueda=' . $busqueda . '" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100">Anterior</a>';
        }

        if ($pagina == $Npaginas) {
            echo '<button class="px-3 py-1.5 text-xs font-medium text-gray-400 bg-white border border-gray-200 rounded-md cursor-not-allowed">Siguiente</button>';
        } else {
            echo '<a href="index.php?vista=cash_history&page=' . ($pagina + 1) . '&busqueda=' . $busqueda . '" class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100">Siguiente</a>';
        }

        echo '</div></div>';
    }
} else {
    // --- ESTADO SIN RESULTADOS ---
    if ($total >= 1) {
        echo '
        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <div class="bg-gray-100 rounded-full p-4 mb-4">
                <i class="fas fa-search text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900">Fin de la lista</h3>
            <p class="text-gray-500 mt-1 max-w-sm">
                <a href="index.php?vista=cash_history" class="text-indigo-600 hover:underline">Volver al inicio</a>
            </p>
        </div>';
    } else {
        echo '
        <div class="flex flex-col items-center justify-center py-12 px-4 text-center">
            <div class="bg-indigo-50 rounded-full p-6 mb-4">
                <i class="fas fa-clipboard-list text-indigo-300 text-4xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Historial Vacío</h3>
            <p class="text-gray-500 mt-2 max-w-md">No se encontraron registros de cierres de caja.</p>
            ' . ($busqueda != "" ? '<a href="index.php?vista=cash_history" class="mt-4 px-4 py-2 bg-gray-800 text-white rounded-lg text-sm hover:bg-gray-700">Limpiar filtro</a>' : '') . '
        </div>';
    }
}
$conexion = null;
