<?php

require_once "./php/main.php";
$conexion = conexion();

if ($conexion === null) {
  die("<h1>Error Crítico: No se pudo conectar a la base de datos.</h1>");
}

include "./php/api_tasa_usd.php";
include "./inc/head_cliente.php";
include "./php/categorias_ordenadas.php";
?>

<body class="bg-gray-50 antialiased">

  <div class="min-h-screen">

    <?php include "./inc/navbar_cliente.php"; ?>
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
          <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
        </div>
      </div>

      <?php
      include "./php/productos_cliente.php";
      ?>
    </main>
  </div>
  <div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 hidden p-4">

    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all flex flex-col max-h-[90vh]">

      <div class="relative flex-shrink-0">
        <img id="modal-image" src="" alt="Producto" class="w-full h-48 sm:h-64 object-cover" />

        <button onclick="closeModal()" class="absolute top-3 right-3 bg-white p-2 rounded-full shadow-lg text-gray-800 hover:bg-gray-100 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
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
            <button id="btn-minus" class="w-10 h-10 flex items-center justify-center border border-red-500 text-red-600 rounded-full hover:bg-red-50 transition text-2xl">-</button>
            <span id="quantity-display" class="text-2xl font-bold px-2">1</span>
            <button id="btn-plus" class="w-10 h-10 flex items-center justify-center bg-red-600 text-white rounded-full hover:bg-red-700 transition text-2xl">+</button>
          </div>

          <button id="btn-add-to-cart" class="w-full sm:w-auto px-6 py-3 bg-red-600 text-white font-semibold rounded-full shadow-md hover:bg-red-700 transition">
            Añadir al Carrito
          </button>
        </div>
      </div>

    </div>
  </div>

  <script src="js/js_cliente.js"></script>

</body>
<?php include "./inc/footer_cliente.php"; ?>