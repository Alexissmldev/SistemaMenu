<?php

require_once "./php/main.php";
$conexion = conexion();

if ($conexion === null) {
  die("<h1>Error Crítico: No se pudo conectar a la base de datos.</h1>");
}
$telefono_whatsapp = "";
$stmt = $conexion->prepare("SELECT usuario_telefono FROM usuario WHERE usuario_id = 30 LIMIT 1");
$stmt->execute();
$admin_user = $stmt->fetch();

if ($admin_user && $admin_user['usuario_telefono']) {
  $telefono_whatsapp = $admin_user['usuario_telefono'];
}


include "./php/api_tasa_usd.php";
include "./inc/head_cliente.php";
include "./php/categorias_ordenadas.php";
?>

<body class="bg-gray-50 antialiased" data-whatsapp-number="<?php echo htmlspecialchars($telefono_whatsapp); ?>">

  <div class="min-h-screen">

    <?php include "./inc/navbar_cliente.php"; ?>

    <main
      class="max-w-7xl mx-auto p-4 pt-6 pb-20 lg:pb-4"
      id="main-content">

      <div class="mb-8 lg:hidden" id="mobile-search-form">
        <form autocomplete="off" class="relative w-full">
          <input
            type="text"
            id="mobile-search-input"
            name="txt_buscador"
            placeholder="Buscar comida..."
            class="w-full py-3 pl-10 pr-4 border border-gray-300 rounded-full focus:ring-red-500 focus:border-red-500" />
          <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
            <i class="fa fa-search"></i>
          </span>
        </form>
      </div>

      <div id="product-content-wrapper">
        <?php
        include "./php/productos_cliente.php";
        ?>
      </div>

    </main>

  </div>

  <div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-end sm:items-center sm:justify-center z-50 p-0 sm:p-4 invisible opacity-0 transition-opacity duration-300 ease-in-out">

    <div id="product-modal-content" class="bg-white sm:rounded-xl shadow-2xl w-full sm:max-w-md overflow-hidden transform flex flex-col transition-transform duration-300 ease-in-out translate-y-full h-screen sm:h-auto sm:max-h-[90vh]">
      <div class="relative flex-shrink-0">
        <img id="modal-image" src="" alt="Producto" class="w-full h-56 sm:h-64 object-cover" />
        <button onclick="closeModal()" class="absolute top-3 right-3 bg-white p-2 rounded-full shadow-lg text-gray-800 hover:bg-gray-100 transition">
          <i class="fa fa-times text-xl"></i>
        </button>
      </div>

      <div class="p-4 sm:p-6 overflow-y-auto flex-grow">
        <div class="flex justify-between items-start gap-3 mb-4">
          <h2 id="modal-name" class="text-2xl sm:text-3xl font-bold text-gray-800 leading-tight">Nombre del Producto</h2>
          <p id="modal-price" class="text-xl sm:text-2xl font-extrabold text-red-600 flex-shrink-0">Precio</p>
        </div>
        <p id="modal-description" class="text-sm sm:text-base text-gray-600">Descripción detallada del producto...</p>
      </div>

      <div class="flex-shrink-0 border-t border-gray-200 p-4 sm:p-5 bg-gray-50">
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center border border-gray-300 rounded-full">
            <button id="btn-minus" class="w-10 h-10 flex items-center justify-center text-red-600 rounded-l-full hover:bg-red-50 transition text-2xl">-</button>
            <span id="quantity-display" class="text-xl font-bold px-4 text-gray-800">1</span>
            <button id="btn-plus" class="w-10 h-10 flex items-center justify-center text-red-600 rounded-r-full hover:bg-red-50 transition text-2xl">+</button>
          </div>
          <button id="btn-add-to-cart" class="flex-grow flex items-center justify-center gap-2 px-5 py-3 bg-red-600 text-white font-semibold rounded-full shadow-md hover:bg-red-700 transition">
            <i class="fa fa-shopping-cart"></i>
            <span>Añadir</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div id="cart-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden"></div>

  <div id="cart-sidebar" class="fixed top-0 right-0 h-full w-full max-w-sm bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex-shrink-0 flex items-center justify-between p-4 border-b">
      <h2 class="text-xl font-bold text-gray-800">Tu Carrito</h2>
      <button id="close-cart-btn" class="text-gray-500 hover:text-red-600">
        <i class="fa fa-times text-2xl"></i>
      </button>
    </div>
    <div id="cart-items-container" class="flex-grow p-4 overflow-y-auto space-y-4">
      <div id="cart-empty-msg" class="text-center text-gray-500 pt-10">
        <i class="fa fa-shopping-cart text-6xl mx-auto text-gray-300"></i>
        <p class="mt-2">Tu carrito está vacío.</p>
      </div>
    </div>
    <div class="flex-shrink-0 p-4 border-t bg-gray-50">
      <div class="flex justify-between font-bold text-lg text-gray-800">
        <span>Subtotal (Bs.):</span>
        <span id="cart-total-display">Bs. 0,00</span>
      </div>
      <div class="flex justify-between text-sm text-gray-600 mt-1">
        <span>Subtotal (USD):</span>
        <span id="cart-total-display-usd">$0.00</span>
      </div>
      <hr class="my-4">
      <div class="space-y-4">
        <div>
          <label for="client-name" class="block text-sm font-medium text-gray-700">Tu Nombre</label>
          <input type="text" id="client-name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-red-500 focus:border-red-500" placeholder="Escribe tu nombre">
        </div>
        <div>
          <label for="client-comments" class="block text-sm font-medium text-gray-700">Especificaciones (Opcional)</label>
          <textarea id="client-comments" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-red-500 focus:border-red-500" placeholder="Ej: La empanada de carne. La malta sin pitillo..."></textarea>
        </div>
        <button id="btn-send-order" class="w-full bg-red-600 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-red-700 transition">
          Confirmar y Enviar Pedido
        </button>
      </div>
    </div>
  </div>


  <div
    id="toast-notification"
    class="fixed top-20 right-4 z-[60] p-4 rounded-lg shadow-lg bg-green-500 text-white transition-all duration-300 ease-in-out opacity-0 translate-x-12 invisible">
    <i class="fa fa-check-circle"></i>
    <span id="toast-message" class="ml-2">Producto añadido</span>
  </div>
  <script src="js/js_cliente.js"></script>
</body>
<?php include "./inc/footer_cliente.php"; ?>