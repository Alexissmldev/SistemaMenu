<?php
require_once "./php/main.php";
$conexion = conexion();

if ($conexion === null) {
  die("<h1>Error Crítico: No se pudo conectar a la base de datos.</h1>");
}

date_default_timezone_set('America/Caracas');

$hora_actual_servidor = (int)date('H');
$telefono_whatsapp = "";
// Buscamos el teléfono del admin (ID 30 según tu código)
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

<body class="bg-gray-50 antialiased"
  data-whatsapp-number="<?php echo htmlspecialchars($telefono_whatsapp); ?>"
  data-server-hour="<?php echo $hora_actual_servidor; ?>">

  <?php include "./php/anuncio_dinamico.php"; ?>

  <div class="min-h-screen">
    <?php include "./inc/navbar_cliente.php"; ?>

    <main class="max-w-7xl mx-auto p-4 pt-6 pb-20 lg:pb-4" id="main-content">

      <div class="mb-8 lg:hidden" id="mobile-search-form">
        <form autocomplete="off" class="relative w-full">
          <input type="text" id="mobile-search-input" name="txt_buscador"
            placeholder="Buscar comida..."
            class="w-full py-3 pl-10 pr-4 border border-gray-300 rounded-full focus:ring-red-500 focus:border-red-500 shadow-sm" />
          <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
            <i class="fa fa-search"></i>
          </span>
        </form>
      </div>

      <?php include "./php/anuncios_info.php"; ?>
      <?php include "./php/anuncio_ofertas.php"; ?>

      <div id="product-content-wrapper">
        <?php include "./php/productos_cliente.php"; ?>
      </div>
    </main>
  </div>

  <div id="product-modal" class="fixed inset-0 z-50 invisible">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm transition-opacity flex items-end md:items-center md:justify-center" onclick="closeModal()">

      <div id="product-modal-content" onclick="event.stopPropagation()"
        class="bg-white w-full h-full md:w-[900px] md:h-[600px] md:max-h-[90vh] md:rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row transform translate-y-full md:translate-y-10 opacity-0 transition-all duration-300 ease-out relative">

        <div id="mobile-sticky-header" class="absolute top-0 left-0 w-full bg-white/95 backdrop-blur shadow-sm z-30 flex items-center justify-end p-3 transform -translate-y-full transition-transform duration-300 md:hidden">
          <button onclick="closeModal()" class="text-gray-500 hover:text-red-600 bg-gray-100 rounded-full p-2 w-10 h-10 flex items-center justify-center">
            <i class="fa fa-times"></i>
          </button>
        </div>

        <div class="hidden md:block relative w-5/12 h-full bg-gray-100 flex-shrink-0">
          <img id="modal-image-desktop" src="" alt="Producto" class="w-full h-full object-cover absolute inset-0" />
        </div>

        <div id="modal-scroll-container" class="flex flex-col w-full md:w-7/12 h-full bg-white relative overflow-y-auto">

          <div id="mobile-image-container" class="md:hidden relative w-full h-64 flex-shrink-0 transition-all duration-500 ease-in-out overflow-hidden">
            <img id="modal-image-mobile" src="" alt="Producto" class="w-full h-full object-cover" />
            <button onclick="closeModal()" class="absolute top-3 right-3 bg-white/90 p-2 rounded-full shadow-lg text-gray-800 z-20">
              <i class="fa fa-times text-lg"></i>
            </button>
          </div>

          <div class="hidden md:block absolute top-4 right-4 z-10">
            <button onclick="closeModal()" class="bg-white/80 hover:bg-red-100 text-gray-500 hover:text-red-600 backdrop-blur shadow-sm rounded-full w-10 h-10 flex items-center justify-center transition">
              <i class="fa fa-times text-xl"></i>
            </button>
          </div>

          <div class="p-6 md:p-8 flex-grow">
            <h2 id="modal-name" class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight pr-10">Nombre</h2>
            <p id="modal-description" class="text-gray-600 text-sm md:text-base mt-2 mb-6">Descripción...</p>

            <div class="border-t border-gray-100 my-4"></div>

            <h3 class="font-bold text-gray-900 mb-3 text-xs uppercase tracking-wider">Opciones</h3>
            <div id="modal-variants-list" class="space-y-3 pb-4"></div>

            <div class="mt-6">
              <label for="modal-note" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fa fa-pen text-gray-400 mr-1"></i> Notas de cocina (Opcional)
              </label>
              <textarea id="modal-note" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none resize-none placeholder-gray-400" placeholder="Ej: Sin cebolla, salsas aparte..."></textarea>
            </div>
          </div>

          <div class="p-4 md:p-6 border-t border-gray-100 bg-gray-50 mt-auto sticky bottom-0 z-20">
            <div class="flex justify-between items-center mb-3">
              <span class="text-gray-600 font-medium">Total:</span>
              <span id="modal-total-price" class="text-2xl font-bold text-gray-900">Bs. 0,00</span>
            </div>

            <button id="btn-add-modal-selection" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-xl shadow-lg transform active:scale-[0.98] transition-all flex justify-center items-center gap-2">
              <span>Agregar al Pedido</span>
              <span id="modal-total-qty-badge" class="bg-white text-red-600 text-xs px-2 py-0.5 rounded-full font-bold hidden">0</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="cart-backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] hidden transition-opacity" onclick="closeCart()"></div>

  <div id="cart-sidebar" class="fixed top-0 right-0 h-full w-full max-w-[400px] bg-white shadow-2xl z-[70] transform translate-x-full flex flex-col font-sans ">
    <div class="flex-shrink-0 px-5 py-4 border-b border-gray-100 bg-white z-30 flex justify-between items-center shadow-sm h-16">
      <h2 class="text-lg font-extrabold text-gray-900 flex items-center gap-2">
        <span id="cart-title-step">Tu Pedido</span>
      </h2>
      <button onclick="closeCart()" class="bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 w-9 h-9 rounded-full flex items-center justify-center transition-all">
        <i class="fa fa-times text-lg"></i>
      </button>
    </div>

    <div id="cart-step-1" class="flex flex-col flex-grow overflow-hidden h-full">

      <div id="cart-items-container" class="flex-grow overflow-y-auto p-5 bg-gray-50/50 space-y-3">
      </div>

      <div class="flex-shrink-0 bg-white border-t border-gray-200 p-5 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] z-20">

        <div class="mb-3">
          <label class="flex items-center gap-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
            <i class="fa fa-comment-dots"></i> Nota General
          </label>
          <textarea id="cart-general-note" rows="1" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm focus:ring-2 focus:ring-red-500 outline-none resize-none" placeholder="Ej: Vuelto de 50$"></textarea>
        </div>

        <div class="flex justify-between items-end mb-3">
          <div class="text-xs text-gray-500">Ref: <span id="step1-usd" class="font-semibold">$0.00</span></div>
          <div class="text-2xl font-black text-red-600 leading-none" id="step1-bs">Bs. 0,00</div>
        </div>

        <button onclick="goToCheckout()" class="w-full bg-gray-900 hover:bg-black text-white font-bold py-3.5 rounded-xl shadow-lg flex items-center justify-center gap-2 transform active:scale-[0.98] transition-all">
          <span>Continuar</span> <i class="fa fa-arrow-right"></i>
        </button>
      </div>
    </div>

    <div id="cart-step-2" class="hidden flex-col flex-grow overflow-hidden h-full bg-white">

      <div class="flex-grow overflow-y-auto p-5 space-y-5">

        <button onclick="backToCart()" class="text-gray-500 hover:text-gray-800 text-xs font-bold flex items-center gap-1 mb-2">
          <i class="fa fa-arrow-left"></i> Volver
        </button>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Tu Nombre</label>
          <input type="text" id="client-name" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 outline-none font-medium" placeholder="Nombre y Apellido">
        </div>

        <div>
          <label class="block text-xs font-bold text-gray-700 mb-2 uppercase">Método de Pago</label>
          <div class="grid grid-cols-2 gap-3">
            <label class="relative cursor-pointer group">
              <input type="radio" name="payment_method" value="pago_movil" class="peer hidden" onchange="togglePaymentDetails()">
              <div class="h-full flex flex-col items-center justify-center p-3 border-2 border-gray-100 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50/30 hover:border-gray-300 transition-all">
                <i class="fa fa-mobile-alt text-xl mb-1 text-gray-400 peer-checked:text-green-600"></i>
                <span class="text-xs font-bold text-gray-600 peer-checked:text-gray-900">Pago Móvil</span>
              </div>
              <div class="absolute top-2 right-2 text-green-500 opacity-0 peer-checked:opacity-100 transition-opacity"><i class="fa fa-check-circle"></i></div>
            </label>

            <label class="relative cursor-pointer group">
              <input type="radio" name="payment_method" value="en_caja" class="peer hidden" onchange="togglePaymentDetails()">
              <div class="h-full flex flex-col items-center justify-center p-3 border-2 border-gray-100 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50/30 hover:border-gray-300 transition-all">
                <i class="fa fa-cash-register text-xl mb-1 text-gray-400 peer-checked:text-blue-600"></i>
                <span class="text-xs font-bold text-gray-600 peer-checked:text-gray-900">En Caja</span>
              </div>
              <div class="absolute top-2 right-2 text-blue-500 opacity-0 peer-checked:opacity-100 transition-opacity"><i class="fa fa-check-circle"></i></div>
            </label>
          </div>
        </div>

        <div id="pago-movil-details" class="hidden animate-fade-in-down">
          <div class="bg-gray-50 rounded-xl p-4 border border-gray-200 space-y-2">
            <div class="flex justify-between items-center mb-1">
              <p class="text-[10px] font-bold text-gray-400 uppercase">Datos Transferencia</p>
              <button onclick="copyAllPagoMovil()" class="text-[10px] bg-white border border-gray-200 px-2 py-1 rounded hover:text-green-600 font-bold flex items-center gap-1 transition-colors">
                <i class="fa fa-copy"></i> Copiar Todo
              </button>
            </div>

            <div class="flex justify-between items-center bg-white px-3 py-2 rounded border border-gray-100">
              <div>
                <p class="text-[10px] text-gray-400">Banco</p>
                <p class="text-sm font-bold text-gray-800" id="pm-bank">Banesco</p>
              </div>
              <button onclick="copyToClipboard('Banesco')" class="text-gray-400 hover:text-green-600"><i class="fa fa-clone"></i></button>
            </div>
            <div class="flex justify-between items-center bg-white px-3 py-2 rounded border border-gray-100">
              <div>
                <p class="text-[10px] text-gray-400">Teléfono</p>
                <p class="text-sm font-bold text-gray-800" id="pm-phone">0414-1234567</p>
              </div>
              <button onclick="copyToClipboard('04141234567')" class="text-gray-400 hover:text-green-600"><i class="fa fa-clone"></i></button>
            </div>
            <div class="flex justify-between items-center bg-white px-3 py-2 rounded border border-gray-100">
              <div>
                <p class="text-[10px] text-gray-400">Cédula/RIF</p>
                <p class="text-sm font-bold text-gray-800" id="pm-id">V-12345678</p>
              </div>
              <button onclick="copyToClipboard('12345678')" class="text-gray-400 hover:text-green-600"><i class="fa fa-clone"></i></button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-shrink-0 bg-white border-t border-gray-200 p-5 shadow-[0_-5px_20px_rgba(0,0,0,0.05)] z-20 pb-8 md:pb-5">
        <div class="flex justify-between items-end mb-3">
          <span class="text-sm text-gray-500 font-medium">Total a pagar:</span>
          <span id="step2-bs" class="text-2xl font-black text-gray-900">Bs. 0,00</span>
        </div>
        <button onclick="sendOrder()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-200 flex items-center justify-center gap-2 transform active:scale-[0.98] transition-all">
          <i class="fa fa-whatsapp text-xl"></i>
          <span>Confirmar y Enviar</span>
        </button>
      </div>
    </div>
  </div>


  <div id="toast-notification" class="fixed top-4 right-4 z-[80] max-w-xs w-full shadow-2xl rounded-xl p-4 flex items-center space-x-4 text-white invisible opacity-0 translate-x-12 transition-all duration-500 ease-out transform">
    <i class="fa fa-check-circle text-2xl"></i>
    <span id="toast-message" class="font-medium ml-2">Notificación</span>
  </div>

  <script src="js/js_cliente.js"></script>

  <?php include "./inc/footer_cliente.php"; ?>
</body>