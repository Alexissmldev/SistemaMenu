<?php
require_once "./php/main.php";
$conexion = conexion();

if ($conexion === null) {
  die("<h1>Error Crítico: No se pudo conectar a la base de datos.</h1>");
}

date_default_timezone_set('America/Caracas');
$hora_actual_servidor = (int)date('H');

/* =========================================================
   1. CARGAR CONFIGURACIÓN DE LA TIENDA (NUEVO CEREBRO)
   ========================================================= */
// Buscamos la tienda con ID 1 (Tu negocio principal)
$sql_tienda = "SELECT * FROM tiendas WHERE id_tienda = 1 LIMIT 1";
$query_tienda = $conexion->query($sql_tienda);
$tienda = $query_tienda->fetch(PDO::FETCH_ASSOC);

// Si no hay datos, usamos valores por defecto para que no falle
$nombre_tienda = $tienda['nombre_tienda'] ?? "Mi Restaurante";
$telefono_whatsapp = $tienda['telefono_tienda'] ?? "5800000000"; // Este es el que usa el carrito
$moneda = $tienda['moneda_simbolo'] ?? "$";

// Logo: Si hay en BD lo usamos, si no, el default
$logo_db = $tienda['logo_tienda'] ?? "";
$logo_src = (file_exists("./img/logo/" . $logo_db) && !empty($logo_db))
  ? "./img/logo/" . $logo_db
  : "./img/logo_default.png";

// Colores: Si no hay en BD, usamos el rojo por defecto
$color_principal = $tienda['color_principal'] ?? "#E11D48";

/* =========================================================
   2. INCLUDES DE LÓGICA DE PRODUCTOS
   ========================================================= */
include "./php/api_tasa_usd.php";
include "./inc/head_cliente.php";
include "./php/categorias_ordenadas.php";
?>

<style>
  /* Inyectamos el color de la base de datos en las clases CSS */
  .text-brand {
    color: <?php echo $color_principal; ?> !important;
  }

  .bg-brand {
    background-color: <?php echo $color_principal; ?> !important;
  }

  .border-brand {
    border-color: <?php echo $color_principal; ?> !important;
  }

  /* Hover effects dinámicos */
  .hover\:text-brand:hover {
    color: <?php echo $color_principal; ?> !important;
  }

  .hover\:bg-brand:hover {
    background-color: <?php echo $color_principal; ?> !important;
    filter: brightness(90%);
  }

  /* Botones y Badges específicos que usaban 'red-600' ahora usarán tu color */
  .focus\:ring-red-500:focus {
    --tw-ring-color: <?php echo $color_principal; ?> !important;
  }

  /* Sobreescribimos clases de Tailwind usadas en el diseño original para que cambien solas */
  .text-red-600 {
    color: <?php echo $color_principal; ?> !important;
  }

  .bg-red-600 {
    background-color: <?php echo $color_principal; ?> !important;
  }

  .border-red-600 {
    border-color: <?php echo $color_principal; ?> !important;
  }

  .hover\:text-red-600:hover {
    color: <?php echo $color_principal; ?> !important;
  }

  .hover\:bg-red-700:hover {
    background-color: <?php echo $color_principal; ?> !important;
    filter: brightness(85%);
  }
</style>

<body class="bg-gray-50 antialiased"
  data-whatsapp-number="<?php echo htmlspecialchars($telefono_whatsapp); ?>"
  data-server-hour="<?php echo $hora_actual_servidor; ?>"
  data-pago-banco="<?php echo htmlspecialchars($tienda['pm_banco'] ?? ''); ?>"
  data-pago-tel="<?php echo htmlspecialchars($tienda['pm_telefono'] ?? ''); ?>"
  data-pago-ced="<?php echo htmlspecialchars($tienda['pm_cedula'] ?? ''); ?>">

  <?php include "./php/anuncio_dinamico.php"; ?>

  <div class="min-h-screen">
    <?php include "./inc/navbar_cliente.php"; ?>

    <main class="max-w-7xl mx-auto p-4 pt-6 pb-20 lg:pb-4" id="main-content">

      <div class="mb-8 lg:hidden" id="mobile-search-form">
        <form autocomplete="off" class="relative w-full">
          <input type="text" id="mobile-search-input" name="txt_buscador"
            placeholder="Buscar comida..."
            class="w-full py-3 pl-10 pr-4 border border-gray-300 rounded-full focus:ring-2 focus:ring-red-500 focus:border-red-500 shadow-sm" />
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

  <?php include "./inc/carrito_sidebar.php"; // O pega aquí el código del <div id="cart-sidebar">...</div> 
  ?>


  <div id="toast-notification" class="fixed top-4 right-4 z-[80] max-w-xs w-full shadow-2xl rounded-xl p-4 flex items-center space-x-4 text-white invisible opacity-0 translate-x-12 transition-all duration-500 ease-out transform">
    <i class="fa fa-check-circle text-2xl"></i>
    <span id="toast-message" class="font-medium ml-2">Notificación</span>
  </div>

  <script src="js/js_cliente.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // Pasamos los datos del PHP al JS si la función existe
      if (typeof cargarDatosPagoMovil === "function") {
        cargarDatosPagoMovil();
      } else {
        // Si la función no existe, hacemos la inyección manual por si acaso
        const banco = document.body.dataset.pagoBanco;
        const tel = document.body.dataset.pagoTel;
        const ced = document.body.dataset.pagoCed;

        const elBanco = document.getElementById("pm-bank");
        const elPhone = document.getElementById("pm-phone");
        const elCedula = document.getElementById("pm-id");

        if (elBanco && banco) elBanco.innerText = banco;
        if (elPhone && tel) elPhone.innerText = tel;
        if (elCedula && ced) elCedula.innerText = ced;
      }
    });
  </script>

  <?php include "./inc/footer_cliente.php"; ?>
</body>