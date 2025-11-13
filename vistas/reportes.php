<?php

include "./php/main.php";

// NO SE NECESITA 'api_tasa_usd.php' para este reporte
// include "./php/api_tasa_usd.php"; 

// Comprobar autoload de Composer / dompdf
if (!file_exists('./libreria/dompdf/vendor/autoload.php')) {
    echo 'Falta dompdf. Desde la raíz del proyecto ejecuta: composer require dompdf/dompdf';
    exit;
}
require_once './libreria/dompdf/vendor/autoload.php';

use Dompdf\Dompdf;

// Obtener la conexión
$conexion = conexion();

// Consultar productos usando JOIN
$sql = "
    SELECT 
        p.producto_nombre, 
        p.producto_precio, 
        c.categoria_nombre 
    FROM 
        producto p 
    INNER JOIN 
        categoria c ON p.categoria_id = c.categoria_id 
    ORDER BY 
        c.categoria_nombre ASC, p.producto_nombre ASC
";

$result = $conexion->query($sql);
if ($result === false) {
    echo 'Error en la consulta: ' . $conexion->errorInfo()[2];
    exit;
}

// 1. ORGANIZAR PRODUCTOS POR CATEGORÍA
$productos_por_categoria = [];
if ($result->rowCount() > 0) {
    $all_rows = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($all_rows as $row) {
        $categoria = htmlspecialchars($row['categoria_nombre'] ?? 'Sin Categoría');
        
        $productos_por_categoria[$categoria][] = [
            'nombre' => htmlspecialchars($row['producto_nombre'] ?? 'Sin nombre'),
            'precio' => (float)($row['producto_precio'] ?? 0),
        ];
    }
}

// 2. GENERAR HTML DINÁMICO
$html = '<!doctype html><html><head><meta charset="utf-8">';

// ======================================================
// ¡DISEÑO CSS RESTAURADO A SOLO USD!
// ======================================================
$html .= '<style>
    body { 
        font-family: \'Helvetica\', \'Arial\', sans-serif, \'DejaVu Sans\'; 
        font-size: 11pt; 
        color: #333;
        margin: 40px; 
        line-height: 1.4;
    }
    
    h1 { 
        text-align: center; 
        color: #c53030; 
        border-bottom: 2px solid #c53030; 
        padding-bottom: 10px; 
        margin-bottom: 25px; 
        font-size: 28pt;
        font-weight: bold;
        letter-spacing: 1px;
    }
    
    .menu-columns {
        column-count: 2; 
        column-gap: 30px; 
    }
    
    .categoria-header { 
        font-size: 18pt; 
        font-weight: bold; 
        color: #111; 
        margin-top: 25px; 
        margin-bottom: 15px; 
        border-bottom: 2px solid #555; 
        padding-bottom: 5px; 
        break-before: column;
        page-break-inside: avoid;
    }
    
    .categoria-header.first {
       margin-top: 0;
    }
    
    .product-list { 
        list-style: none; 
        padding: 0; 
        margin: 0 0 20px 0; 
        page-break-inside: avoid; 
        display: inline-block;
        width: 100%;
    }
    
    .product-item {
        display: flex; 
        justify-content: space-between; 
        align-items: baseline;
        padding: 8px 2px; 
        border-bottom: 1px dotted #aaa; 
        page-break-inside: avoid; 
    }
    
    .product-item:last-child {
       border-bottom: none;
    }
    
    .product-name {
        font-weight: 600; 
        font-size: 11pt;
        padding-right: 15px; 
    }
    
    /* --- ¡PRECIO ÚNICO RESTAURADO! --- */
    .product-price {
        white-space: nowrap; 
        font-weight: bold; /* Más grueso que el nombre */
        color: #c53030; /* Rojo, como tu marca */
        margin-left: 10px; 
        font-size: 11pt;
    }
</style></head><body>';
// ======================================================
// FIN DEL DISEÑO CSS
// ======================================================

$html .= '<h1>📋 Nuestro Menú</h1>';


if (empty($productos_por_categoria)) {
    $html .= '<p style="text-align: center;">No hay productos para mostrar en el menú.</p>';
} else {
    // 3. RECORRER LAS CATEGORÍAS Y PRODUCTOS
    $html .= '<div class="menu-columns">'; 

    $is_first_category = true;

    foreach ($productos_por_categoria as $categoria => $productos) {
        
        $clase_categoria = $is_first_category ? 'categoria-header first' : 'categoria-header';
        $html .= '<h2 class="' . $clase_categoria . '">' . $categoria . '</h2>';
        $is_first_category = false;
        
        $html .= '<ul class="product-list">';

        foreach ($productos as $producto) {
            $nombre = $producto['nombre'];
            
            // --- LÓGICA DE PRECIO SIMPLIFICADA A USD ---
            $precio_formateado = '$' . number_format($producto['precio'], 2, '.', ',');

            // Fila de Producto
            $html .= '<li class="product-item">';
            $html .= '  <span class="product-name">' . $nombre . '</span>';
            // Se usa el estilo .product-price simple
            $html .= '  <span class="product-price">' . $precio_formateado . '</span>';
            $html .= '</li>';
            // --- FIN DEL BLOQUE ---
        }

        $html .= '</ul>';
    }

    $html .= '</div>'; // Cierra menu-columns
}

$html .= '</body></html>';

// 4. Generar PDF con Dompdf
$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->set('isRemoteEnabled', true);
$dompdf->setOptions($options);

$dompdf->setPaper('A4', 'portrait');
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->render();

// Enviar al navegador para previsualizar
$dompdf->stream("menu_productos.pdf", ["Attachment" => false]);
exit;