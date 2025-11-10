<?php

include "./php/main.php";

// Comprobar autoload de Composer / dompdf
if (!file_exists('./libreria/dompdf/vendor/autoload.php')) {
    echo 'Falta dompdf. Desde la raíz del proyecto ejecuta: composer require dompdf/dompdf';
    exit;
}
require_once './libreria/dompdf/vendor/autoload.php';

use Dompdf\Dompdf;

// Obtener la conexión
$conexion = conexion();

// Consultar productos usando JOIN para obtener el nombre de la categoría
// NOTA: Revisa que los nombres de tus tablas y columnas coincidan (producto, categoria, categoria_id, categoria_nombre)
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
    // Esto es útil si hay errores en el JOIN o nombres de tablas.
    echo 'Error en la consulta: ' . $conexion->errorInfo()[2];
    exit;
}

// 1. ORGANIZAR PRODUCTOS POR CATEGORÍA
$productos_por_categoria = [];
if ($result->rowCount() > 0) {
    $all_rows = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($all_rows as $row) {
        // Usamos 'categoria_nombre' que ahora SÍ viene de la consulta JOIN
        $categoria = htmlspecialchars($row['categoria_nombre'] ?? 'Sin Categoría');
        
        // Agrupa el producto bajo su categoría
        $productos_por_categoria[$categoria][] = [
            'nombre' => htmlspecialchars($row['producto_nombre'] ?? 'Sin nombre'),
            'precio' => (float)($row['producto_precio'] ?? 0),
        ];
    }
}

// ... (resto del código HTML y Dompdf) ...

// 2. GENERAR HTML DINÁMICO
$html = '<!doctype html><html><head><meta charset="utf-8">';

// ESTILOS CSS PARA UN DISEÑO LIMPIO Y ORDENADO CON COLUMNAS
$html .= '<style>
    /* Necesario para caracteres especiales en dompdf */
    body { font-family: DejaVu Sans, sans-serif; font-size:14px; color: #333; margin: 40px; }
    
    /* Diseño del Encabezado */
    h1 { text-align:center; color: #007bff; border-bottom: 3px solid #007bff; padding-bottom: 10px; margin-bottom: 30px; }
    
    /* *** CAMBIO CRÍTICO: USAR COLUMN-COUNT *** */
    .menu-columns {
        /* Divide el contenido en 2 columnas */
        column-count: 2; 
        /* Espacio entre las columnas */
        column-gap: 40px; 
    }
    
    /* Estilo de la Categoría */
    .categoria-header { 
        font-size: 24px; 
        font-weight: bold; 
        color: #28a745; 
        margin-top: 5px; 
        margin-bottom: 15px; 
        border-bottom: 2px solid #28a745; 
        padding-bottom: 5px; 
        
        /* MUY IMPORTANTE: Le dice a Dompdf que NO rompa el título de la categoría al pasar de columna */
        break-before: column;
        page-break-inside: avoid;
    }
    
    /* Lista de Productos */
    .product-list { 
        list-style: none; 
        padding: 0; 
        margin: 0; 
        /* MUY IMPORTANTE: Asegura que la lista se mantenga en una sola columna/página si es posible */
        page-break-inside: avoid; 
        /* Le dice a Dompdf que esta lista es un solo bloque y debe ser tratada como tal */
        display: inline-block;
        width: 100%;
        margin-bottom: 20px;
    }
    
    /* Estilos de Producto (Flexbox para alineación, que funcionó antes) */
    .product-item {
        display: flex; 
        justify-content: space-between; 
        align-items: baseline; 
        padding: 6px 0; 
        border-bottom: 1px dashed #ddd; 
        page-break-inside: avoid; 
    }
    
    .product-name {
        font-weight: 500;
    }
    
    .product-price {
        white-space: nowrap; 
        font-weight: bold;
        color: #007bff; 
        margin-left: 10px; 
    }
    /* El resto de estilos se mantiene igual o con ajustes menores */
</style></head><body>';

$html .= '<h1>📋 Nuestro Menú</h1>';

// ... (Toda la lógica de PHP hasta el inicio del HTML del menú) ...

if (empty($productos_por_categoria)) {
    $html .= '<p style="text-align: center;">No hay productos para mostrar en el menú.</p>';
} else {
    // 3. RECORRER LAS CATEGORÍAS Y PRODUCTOS PARA CREAR EL HTML
    // Abrimos el contenedor que se dividirá en 2 columnas
    $html .= '<div class="menu-columns">'; 

    foreach ($productos_por_categoria as $categoria => $productos) {
        
        // El contenido de la categoría se inserta directamente en el contenedor de columnas
        // Dompdf se encargará de distribuirlo entre las columnas
        
        // Título de la Categoría
        $html .= '<h2 class="categoria-header">' . $categoria . '</h2>';
        $html .= '<ul class="product-list">';

        foreach ($productos as $producto) {
            $nombre = $producto['nombre'];
            $precio_formateado = '$' . number_format($producto['precio'], 2, '.', ',');

            // Fila de Producto
            $html .= '<li class="product-item">';
            $html .= '  <span class="product-name">' . $nombre . '</span>';
            $html .= '  <span class="product-price">' . $precio_formateado . '</span>';
            $html .= '</li>';
        }

        $html .= '</ul>';
        // Aquí no hay que cerrar el div category-section
    }

    $html .= '</div>'; // Cierra menu-columns
}

// ... (El resto del código de Dompdf) ...

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