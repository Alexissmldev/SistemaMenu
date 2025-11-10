<?php

require_once "../php/main.php"; 
$conexion = conexion();

if ($conexion === null) {
    die("<h1>Error Crítico: No se pudo conectar a la base de datos.</h1>");
}

// ====================================================================
// BLOQUE PARA OBTENER LA TASA USD DE LA API 
// ====================================================================
$tasa_usd = 'FALTA INTERNET'; 
$tasa_usd_num = 0; 

$api_url = "https://api.dolarvzla.com/public/exchange-rate"; 

if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code == 200 && $response !== false) {
        $data = json_decode($response, true);

        if (isset($data['current']) && isset($data['current']['usd'])) {
            $tasa_usd_num = (float)$data['current']['usd']; 
            $tasa_usd = number_format($tasa_usd_num, 2); 
        }
    }
} else if (ini_get('allow_url_fopen')) {
    $response = @file_get_contents($api_url);
    if ($response !== false) {
        $data = json_decode($response, true);

        if (isset($data['current']) && isset($data['current']['usd'])) {
            $tasa_usd_num = (float)$data['current']['usd']; 
            $tasa_usd = number_format($tasa_usd_num, 2);
        }
    }
}


// Consulta para obtener categorías activas
$query_categorias = $conexion->prepare("
  SELECT DISTINCT c.categoria_nombre 
  FROM categoria c 
  INNER JOIN producto p ON c.categoria_id = p.categoria_id 
  WHERE p.producto_estado = 1 
  ORDER BY c.categoria_nombre ASC
");
$query_categorias->execute();
$categorias = $query_categorias->fetchAll(PDO::FETCH_ASSOC);

// Lógica de Reordenamiento de Categorías
$desayunos = array_filter($categorias, fn($c) => $c['categoria_nombre'] === 'Desayunos');
$almuerzos = array_filter($categorias, fn($c) => $c['categoria_nombre'] === 'Almuerzos');
$especiales = array_filter($categorias, fn($c) => $c['categoria_nombre'] === 'Especiales');
$bebidas = array_filter($categorias, fn($c) => $c['categoria_nombre'] === 'Bebidas');
$otras = array_filter($categorias, fn($c) => !in_array($c['categoria_nombre'], ['Desayunos', 'Almuerzos', 'Especiales', 'Bebidas']));

// Concatenar en el orden deseado
$categorias_ordenadas = array_merge($desayunos, $almuerzos, $otras, $especiales, $bebidas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menú - Alas Restaurante - Cliente</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  
</head>
<body class="bg-gray-50 antialiased">

  <div class="min-h-screen">
  
  <header class="bg-white p-4 shadow-md sticky top-0 z-10">
  <div class="max-w-7xl mx-auto">
    
    <div class="flex justify-between items-center w-full lg:grid lg:grid-cols-3 lg:gap-4">
    
    <div class="flex items-center space-x-2">
<img src="../img/logo.png" alt="" class="w-16 h-12 object-contain"/>
 <span class="hidden lg:block text-xs text-gray-500 bg-gray-100 p-1 rounded-full px-2">
 Tasa USD: **<?php echo $tasa_usd; ?>**
      </span>
    </div>

    <div class="hidden lg:flex justify-center">
 <div class="relative w-full max-w-lg">
 <input type="text" placeholder="Buscar comida..." class="w-full py-2 pl-10 pr-4 border border-gray-300 rounded-full focus:ring-red-500 focus:border-red-500" />
 <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
 </div>
     </div>

    <div class="flex items-center space-x-3 justify-end">
 <span class="text-xs text-gray-500 bg-gray-100 p-1 rounded-full px-2 lg:hidden">
 Tasa USD: **<?php echo $tasa_usd; ?>**
 </span>
 <button class="text-gray-600 hover:text-red-500 hidden lg:block">
 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
 </button>
 
    </div>
    </div>

    <section class="mt-4 pt-4 border-t border-gray-100"> 
    <nav class="flex space-x-6 overflow-x-scroll pb-2 lg:justify-start lg:flex-wrap">
 <?php
 // Recorre las categorías ordenadas (PHP)
 $primera_categoria = true;
 foreach ($categorias_ordenadas as $categoria) {
 $categoria_id = strtolower(str_replace(' ', '', $categoria['categoria_nombre']));
 // Clases para destacar la primera categoría como activa
 $clases_link = $primera_categoria ? 'text-red-600 border-red-600' : 'text-gray-500 border-transparent';
 
 echo '<a href="#' . htmlspecialchars($categoria_id) . '" class="flex-shrink-0 font-semibold border-b-2 pb-1 hover:text-red-600 hover:border-red-600 transition ' . $clases_link . '">';
 echo htmlspecialchars($categoria['categoria_nombre']);
 echo '</a>';

 $primera_categoria = false;
 }
 ?>
    </nav>
    </section>
    </div>
  </header>
  <main class="max-w-7xl mx-auto p-4 pt-6 pb-20 lg:pb-4">

  <div class="bg-red-600 rounded-xl p-4 mb-8 lg:p-6 lg:mb-10 shadow-lg">
    <h2 class="text-white text-lg font-bold">¡Pide y Disfruta!</h2>
    <p class="text-red-100 text-sm mt-1">MENÚ DEL RESTAURANTE GOBERNACIÓN</p>
    <div class="mt-3 text-white font-semibold">
    Tasa USD del día: **<?php echo $tasa_usd; ?>**
    </div>
  </div>

  <div class="mb-8 lg:hidden">
    <div class="relative w-full">
    <input type="text" placeholder="Buscar comida..." class="w-full py-3 pl-10 pr-4 border border-gray-300 rounded-full focus:ring-red-500 focus:border-red-500" />
    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </div>
  </div>
  
  <?php
  // Genera las secciones y productos dinámicamente
  foreach ($categorias_ordenadas as $categoria) {
    $categoria_id = strtolower(str_replace(' ', '', $categoria['categoria_nombre']));
    $categoria_nombre = $categoria['categoria_nombre'];

    // Consulta productos para esta categoría
    $query = $conexion->prepare("SELECT p.* FROM producto p INNER JOIN categoria c ON p.categoria_id = c.categoria_id WHERE p.producto_estado = 1 AND c.categoria_nombre = :nombre");
    $query->execute([':nombre' => $categoria_nombre]);
    $productos = $query->fetchAll();
  ?>
    <section id="<?php echo htmlspecialchars($categoria_id); ?>" class="mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6"><?php echo htmlspecialchars($categoria_nombre); ?></h2>

    <div class="space-y-4 lg:grid lg:grid-cols-2 lg:gap-6 lg:space-y-0 xl:grid-cols-3">
 
 <?php if (count($productos) > 0): ?>
 <?php foreach ($productos as $producto): 
   $precio_usd_num = (float)$producto['producto_precio'];
   $precio_display = '';

   if ($tasa_usd_num > 0) {
   $precio_bs = $precio_usd_num * $tasa_usd_num;
   $precio_bs_formateado = number_format($precio_bs, 2, ',', '.'); 
   $precio_display = '<span class="text-red-600 font-bold">Bs. ' . $precio_bs_formateado . '</span>';
   } else {
   // Si la API falla o la tasa es cero, mostramos el precio en USD
   $precio_usd_formateado = number_format($precio_usd_num, 2, ',', '.');
   $precio_display = '<span class="text-red-600 font-bold">USD ' . $precio_usd_formateado . '</span>';
   }
   
   // Preparamos los datos para el JS 
   $producto_json = htmlspecialchars(json_encode([
   'nombre' => $producto['producto_nombre'],
   'descripcion' => $producto['descripcion_producto'],
   'precio' => $precio_display, 
   'foto' => '../img/producto/large/' . $producto['producto_foto'] 
   ]), ENT_QUOTES, 'UTF-8');
 ?>
   <div class="flex bg-white rounded-xl shadow-md overflow-hidden p-3 hover:shadow-lg transition cursor-pointer" onclick="openModal(<?php echo $producto_json; ?>)">
   <div class="flex-shrink-0 w-24 h-24 bg-gray-100 rounded-lg overflow-hidden mr-4">
   <img src="../img/producto/large/<?php echo htmlspecialchars($producto['producto_foto']); ?>" alt="<?php echo htmlspecialchars($producto['producto_nombre']); ?>" class="w-full h-full object-cover" />
   </div>
   <div class="flex-grow">
   <h4 class="text-base font-semibold text-gray-800"><?php echo htmlspecialchars($producto['producto_nombre']); ?></h4>
   <p class="text-sm text-gray-500 line-clamp-2 mt-1"><?php echo htmlspecialchars($producto['descripcion_producto']); ?></p>
   <div class="flex items-center justify-between mt-2">
<?php echo $precio_display; ?>
   </div>
   </div>
   </div>
 <?php endforeach; ?>
 <?php else: ?>
 <p class="text-gray-500">No hay productos disponibles en esta categoría.</p>
 <?php endif; ?>

    </div>
    </section>
 <?php } ?>
  </main>
  
  <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-3 flex justify-around lg:hidden shadow-xl z-20">
  <button class="text-red-600 flex flex-col items-center">
    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
    <span class="text-xs">Inicio</span>
  </button>
 
  
  <button class="text-gray-400 hover:text-red-600 flex flex-col items-center">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
    <span class="text-xs">Carrito</span>
  </button>
   
  </div>
  
  </div>

    <div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 hidden p-4">
        
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all flex flex-col max-h-[90vh]">
            
            <div class="relative flex-shrink-0">
                <img id="modal-image" src="" alt="Producto" class="w-full h-48 sm:h-64 object-cover" />
                
                <button onclick="closeModal()" class="absolute top-3 right-3 bg-white p-2 rounded-full shadow-lg text-gray-800 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto">
                <h2 id="modal-name" class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Nombre del Producto</h2>
                <p id="modal-price" class="text-xl sm:text-2xl font-extrabold text-red-600 mb-4">Precio</p>
                
                <h3 class="text-base sm:text-lg font-semibold text-gray-700 mb-1">Descripción:</h3>
                <p id="modal-description" class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">Descripción detallada del producto...</p>
            </div>
            
            <div class="flex-shrink-0 border-t border-gray-200 p-4 sm:p-6 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center justify-center space-x-3">
                        <button class="w-10 h-10 flex items-center justify-center border border-red-500 text-red-600 rounded-full hover:bg-red-50 transition text-2xl">-</button>
                        <span class="text-2xl font-bold px-2">1</span>
                        <button class="w-10 h-10 flex items-center justify-center bg-red-600 text-white rounded-full hover:bg-red-700 transition text-2xl">+</button>
                    </div>
                    
                    <button class="w-full sm:w-auto px-6 py-3 bg-red-600 text-white font-semibold rounded-full shadow-md hover:bg-red-700 transition">
                        Añadir al Carrito
                    </button>
                </div>
            </div>

        </div>
    </div>
    
    <script>
  // Función para abrir el modal y poblarlo con datos
  function openModal(productData) {
  const modal = document.getElementById('product-modal');
  
  // Llenar el contenido del modal
  document.getElementById('modal-image').src = productData.foto; 
  document.getElementById('modal-name').textContent = productData.nombre;
  document.getElementById('modal-price').innerHTML = productData.precio;
  document.getElementById('modal-description').textContent = productData.descripcion;

  // Mostrar el modal
  modal.classList.remove('hidden');
  document.body.style.overflow = 'hidden'; 
  }

  // Función para cerrar el modal
  function closeModal() {
  const modal = document.getElementById('product-modal');
  modal.classList.add('hidden');
  document.body.style.overflow = ''; 
  }

  // Cierra el modal si se pulsa fuera de él
  document.getElementById('product-modal').addEventListener('click', (e) => {
  if (e.target.id === 'product-modal') {
    closeModal();
  }
  });
  
  // Cierra el modal si se pulsa ESC
  document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape' && !document.getElementById('product-modal').classList.contains('hidden')) {
    closeModal();
  }
  });
  </script>
</body>
</html>

<?php
$conexion = null;
?>
