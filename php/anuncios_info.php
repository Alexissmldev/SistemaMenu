<?php
// Este archivo MUESTRA 'INFO' como una tarjeta NO CLICABLE
// Asume que $conexion y $hora_actual_servidor ya están definidos.

$fecha_actual_info = date('Y-m-d');

// ¡CAMBIO 1: Consulta simplificada! Ya no busca vínculos (LEFT JOINs, GROUP_CONCAT).
$stmt_info = $conexion->prepare(
    "SELECT 
        a.anuncio_id, a.anuncio_mensaje
     FROM anuncios a
     WHERE 
        a.anuncio_estado = 1 
        AND a.anuncio_tipo = 'info'
        AND :hora_actual >= a.anuncio_hora_inicio 
        AND :hora_actual < a.anuncio_hora_fin
        AND (
           (a.anuncio_fecha_inicio IS NULL AND a.anuncio_fecha_fin IS NULL) OR
           (:fecha_actual BETWEEN a.anuncio_fecha_inicio AND a.anuncio_fecha_fin)
        )
     ORDER BY a.anuncio_prioridad DESC
     LIMIT 1"
);

$stmt_info->execute([
    ':hora_actual' => $hora_actual_servidor,
    ':fecha_actual' => $fecha_actual_info
]);

$anuncio_info = $stmt_info->fetch();

if ($anuncio_info) {

    // ¡CAMBIO 2: Se eliminó toda la "Lógica de Vínculos"!
    // (No $data_productos, no $data_categorias, no $estilo_cursor)

    // ¡CAMBIO 3: Se eliminaron las clases y atributos de clic del <div>!
    // (Se quitó 'anuncio-clicable', 'cursor-pointer', 'role="button"' y los data-attributes)
    echo '
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6"
         role="alert">
        
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fa fa-info-circle text-2xl text-blue-500"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-semibold text-gray-800">' . htmlspecialchars($anuncio_info['anuncio_mensaje']) . '</p>
                </div>
            </div>
            
            <div class="ml-4 flex-shrink-0">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Info
                </span>
            </div>
        </div>
    </div>
    ';
}
?>