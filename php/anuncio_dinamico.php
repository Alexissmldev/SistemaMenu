<?php

$fecha_actual = date('Y-m-d');

$stmt_anuncio = $conexion->prepare(
    "SELECT 
        a.anuncio_id, a.anuncio_mensaje, a.anuncio_tipo,
        GROUP_CONCAT(DISTINCT ap.producto_id) AS productos_vinculados,
        GROUP_CONCAT(DISTINCT ac.categoria_id) AS categorias_vinculadas
      FROM anuncios a
      LEFT JOIN anuncio_productos ap ON a.anuncio_id = ap.anuncio_id
      LEFT JOIN anuncio_categorias ac ON a.anuncio_id = ac.anuncio_id
      WHERE 
        a.anuncio_estado = 1 
        AND a.anuncio_tipo = 'alerta'
        AND :hora_actual >= a.anuncio_hora_inicio 
        AND :hora_actual < a.anuncio_hora_fin
        AND (
          (a.anuncio_fecha_inicio IS NULL AND a.anuncio_fecha_fin IS NULL) OR
          (:fecha_actual BETWEEN a.anuncio_fecha_inicio AND a.anuncio_fecha_fin)
        )
      GROUP BY a.anuncio_id
      ORDER BY a.anuncio_prioridad DESC
      LIMIT 1"
);

$stmt_anuncio->execute([
    ':hora_actual' => $hora_actual_servidor,
    ':fecha_actual' => $fecha_actual
]);

$anuncio_alerta = $stmt_anuncio->fetch();

if ($anuncio_alerta) {

    $data_productos = !empty($anuncio_alerta['productos_vinculados']) ? 'data-productos-ids="' . htmlspecialchars($anuncio_alerta['productos_vinculados']) . '"' : '';
    $data_categorias = !empty($anuncio_alerta['categorias_vinculadas']) ? 'data-categorias-ids="' . htmlspecialchars($anuncio_alerta['categorias_vinculadas']) . '"' : '';
    $estilo_cursor = ($data_productos || $data_categorias) ? 'style="cursor: pointer;"' : '';

 
    echo '
    <div id="banner-dinamico"
         class="bg-red-600 text-white font-bold p-3 marquee-container shadow-lg"
         ' . $data_productos . ' 
         ' . $data_categorias . '
         ' . $estilo_cursor . '>
        <span class="marquee-content">
            <span class="mx-4"> 
                <i class="fa fa-clock banner-shake-icon text-yellow-300 mr-1"></i>
                ' . htmlspecialchars($anuncio_alerta['anuncio_mensaje']) . '
            </span>
        </span>
    </div>';
}
