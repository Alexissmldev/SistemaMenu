<?php

/**
 * Este archivo ahora consulta la tabla 'promociones' para mostrar 
 * el carrusel de ofertas (2x1, combos, etc.).
 *
 * Asume que las variables $conexion, $hora_actual_servidor, 
 * y $tasa_usd_num ya están definidas en 'menu.php'.
 */

// 1. Verificar si las variables necesarias existen
if (!isset($conexion) || !isset($hora_actual_servidor) || !isset($tasa_usd_num)) {
    // No hacer 'die()' para no romper la página, solo no mostrar nada.
    return;
}

$fecha_actual_promo = date('Y-m-d');

// 2. Consultar la tabla 'promociones' (no 'anuncios')
//    Buscamos las 5 más importantes que estén activas ahora.
$stmt_promos = $conexion->prepare(
    "SELECT 
        promo_id, promo_nombre, promo_precio, promo_foto
     FROM promociones
     WHERE 
        estado = 1 
        AND :hora_actual >= hora_inicio 
        AND :hora_actual < hora_fin
        AND (
           (fecha_inicio IS NULL AND fecha_fin IS NULL) OR
           (:fecha_actual BETWEEN fecha_inicio AND fecha_fin)
        )
     ORDER BY prioridad DESC
     LIMIT 5" // Respetamos tu límite de 5
);

$stmt_promos->execute([
    ':hora_actual' => $hora_actual_servidor,
    ':fecha_actual' => $fecha_actual_promo
]);

$promociones_activas = $stmt_promos->fetchAll();

// 3. Si se encontraron promociones, construir el carrusel
if (!empty($promociones_activas)) {

    echo '<div class="mb-8">';
    echo '<h2 class="text-2xl font-bold text-gray-800 mb-4">¡Ofertas para ti!</h2>';

    // Contenedor del Carrusel con el ID para el JS
    echo '<div id="ofertas-carrusel" class="flex overflow-x-auto space-x-4 pb-4 scroll-snap-x scroll-smooth">';

    foreach ($promociones_activas as $promo) {

        // --- 4. Preparar el JSON para el Carrito ---
        // Esto es lo que el JS leerá al hacer clic.

        $precio_usd_num = (float)$promo['promo_precio'];
        $precio_raw_bs = ($tasa_usd_num > 0) ? ($precio_usd_num * $tasa_usd_num) : 0;

        // Asumo que la carpeta de fotos es /img/anuncios/
        $imagen_url_promo = './img/anuncios/estandar.jpg'; // Fallback
        if (!empty($promo['promo_foto']) && is_file('./img/anuncios/large/' . $promo['promo_foto'])) {
            $imagen_url_promo = './img/anuncios/large/' . $promo['promo_foto'];
        }

        $promo_json_data = [
            'id' => 'promo_' . $promo['promo_id'], // ID único para el carrito (ej: "promo_3")
            'nombre' => $promo['promo_nombre'],
            'precio_raw' => $precio_raw_bs,
            'precio_usd' => $precio_usd_num,
            'foto' => $imagen_url_promo
        ];

        $data_json_producto = 'data-product-json="' . htmlspecialchars(json_encode($promo_json_data), ENT_QUOTES, 'UTF-8') . '"';

        // --- 5. Imprimir la Tarjeta Visual ---
        echo '
        <div class="anuncio-clicable rounded-xl shadow-lg w-80 md:w-96 flex-shrink-0 snap-start cursor-pointer" 
             data-accion="quick_add"
             ' . $data_json_producto . '>
            
            <div class="relative h-48 w-full">
                <div class="absolute inset-0 bg-cover bg-center rounded-t-xl" 
                     style="background-image: url(\'' . htmlspecialchars($imagen_url_promo) . '\');">
                </div>
                
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent rounded-t-xl"></div>
                
                <div class="absolute bottom-0 left-0 p-4">
                    <h3 class="text-white text-xl font-bold">
                        ' . htmlspecialchars($promo['promo_nombre']) . '
                    </h3>
                </div>
                
                <span class="absolute top-3 right-3 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full">OFERTA</span>
            </div>
            
            <div class="bg-white p-3 rounded-b-xl flex justify-between items-center">
                <span class="text-sm text-green-600 font-semibold">¡Añadir al carrito!</span>
                <i class="fa fa-cart-plus text-green-600"></i>
            </div>
        </div>
        ';
    }

    echo '</div>'; // Fin del carrusel
    echo '</div>'; // Fin del contenedor
}
