/* ======================================================================
  ESTADO GLOBAL Y HELPERS
====================================================================== */
let modalBasePrice = 0;
let currentModalProduct = {};
let cart = [];

/**
 * Formatea un número como Bolívares (Bs.)
 */
function formatCurrency(number) {
  const formatted = (number || 0).toLocaleString("es-VE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  return "Bs. " + formatted;
}

/**
 * (NUEVO) Formatea un número como Dólares (USD)
 */
function formatUSD(number) {
  const formatted = (number || 0).toLocaleString("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  return formatted; // Retorna "$1,234.56"
}

/* ======================================================================
  FUNCIONES DEL CARRITO (Modelo)
====================================================================== */

function loadCart() {
  const cartData = localStorage.getItem("miMenuGobernacionCart");
  cart = cartData ? JSON.parse(cartData) : [];
}

function saveCart() {
  localStorage.setItem("miMenuGobernacionCart", JSON.stringify(cart));
}

/**
 * (MODIFICADO) Añade el precio USD al carrito
 */
function addToCart(product) {
  const quantity = parseInt(product.cantidad);
  const existingProductIndex = cart.findIndex((item) => item.id === product.id);

  if (existingProductIndex > -1) {
    cart[existingProductIndex].cantidad += quantity;
  } else {
    cart.push({
      id: product.id,
      nombre: product.nombre,
      foto: product.foto,
      precio: product.precio_raw, // Precio en Bs.
      precio_usd: product.precio_usd, // ¡NUEVO! Precio en USD
      cantidad: quantity,
    });
  }
  saveCart();
  updateCartBadge();
  renderCartItems();
}

function updateCartBadge() {
  const desktopBadge = document.getElementById("cart-count-badge-desktop");
  const mobileBadge = document.getElementById("cart-count-badge-mobile");
  const totalItems = cart.reduce((total, item) => total + item.cantidad, 0);
  const badges = [desktopBadge, mobileBadge];

  badges.forEach((badge) => {
    if (badge) {
      if (totalItems > 0) {
        badge.textContent = totalItems;
        badge.classList.remove("hidden");
      } else {
        badge.classList.add("hidden");
      }
    }
  });
}

/* ======================================================================
  CONTROLADORES DE VISTAS (Modal y Sidebar)
====================================================================== */

function openCart() {
  renderCartItems();
  document.getElementById("cart-backdrop").classList.remove("hidden");
  document.getElementById("cart-sidebar").classList.remove("translate-x-full");
  document.body.style.overflow = "hidden";
}

function closeCart() {
  document.getElementById("cart-backdrop").classList.add("hidden");
  document.getElementById("cart-sidebar").classList.add("translate-x-full");
  if (document.getElementById("product-modal").classList.contains("hidden")) {
    document.body.style.overflow = "";
  }
}

function openModal(productData) {
  const modal = document.getElementById("product-modal");
  if (!modal) return;
  modalBasePrice = productData.precio_raw;
  currentModalProduct = productData;
  document.getElementById("modal-image").src = productData.foto;
  document.getElementById("modal-name").textContent = productData.nombre;
  document.getElementById("modal-description").textContent = productData.descripcion;
  document.getElementById("modal-price").innerHTML = productData.precio_display;
  document.getElementById("quantity-display").textContent = "1";
  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  const modal = document.getElementById("product-modal");
  if (modal) {
    modal.classList.add("hidden");
    if (document.getElementById("cart-sidebar").classList.contains("translate-x-full")) {
      document.body.style.overflow = "";
    }
    modalBasePrice = 0;
    currentModalProduct = {};
  }
}

function updateModalPrice() {
  const quantityDisplay = document.getElementById("quantity-display");
  const priceDisplay = document.getElementById("modal-price");
  if (!quantityDisplay || !priceDisplay) return;
  const quantity = parseInt(quantityDisplay.textContent);
  const totalPrice = modalBasePrice * quantity;
  priceDisplay.innerHTML = formatCurrency(totalPrice);
}

/* ======================================================================
  LÓGICA DE RENDERIZADO Y ACCIONES
====================================================================== */

/**
 * (MODIFICADO) Dibuja los productos con contadores y calcula ambos totales.
 */
function renderCartItems() {
  const container = document.getElementById("cart-items-container");
  const totalDisplayBS = document.getElementById("cart-total-display");
  const totalDisplayUSD = document.getElementById("cart-total-display-usd"); // ¡NUEVO!
  const checkoutForm = document.getElementById("btn-send-order").parentElement;

  container.innerHTML = ""; // Limpia

  if (cart.length === 0) {
    container.innerHTML = `
      <div id="cart-empty-msg" class="text-center text-gray-500 pt-10">
        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <p class="mt-2">Tu carrito está vacío.</p>
      </div>
    `;
    totalDisplayBS.innerHTML = formatCurrency(0);
    totalDisplayUSD.innerHTML = formatUSD(0); // ¡NUEVO!
    if (checkoutForm) checkoutForm.classList.add("hidden");
    return;
  }

  if (checkoutForm) checkoutForm.classList.remove("hidden");

  let subtotal_bs = 0;
  let subtotal_usd = 0;

  cart.forEach((item) => {
    const itemTotal_bs = item.precio * item.cantidad;
    subtotal_bs += itemTotal_bs;

    const itemTotal_usd = item.precio_usd * item.cantidad; // ¡NUEVO!
    subtotal_usd += itemTotal_usd;

    // --- ¡HTML del Item MODIFICADO con contadores! ---
    const itemHTML = `
      <div class="flex space-x-3 p-2 bg-white rounded-lg border">
        <img src="${item.foto}" alt="${item.nombre}" class="w-16 h-16 object-cover rounded-md flex-shrink-0">
        
        <div class="flex-grow min-w-0">
          <p class="font-semibold text-sm text-gray-800 truncate">${item.nombre}</p>
          <p class="text-sm font-bold text-red-600 mt-1">${formatCurrency(itemTotal_bs)}</p>
        </div>

        <div class="flex-shrink-0 flex flex-col items-end justify-between">
            <button class="text-gray-400 hover:text-red-500" onclick="removeItemFromCart(${item.id})">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <div class="flex items-center space-x-2 mt-2">
                <button class="w-6 h-6 flex items-center justify-center border border-gray-300 text-gray-600 rounded-full hover:bg-gray-100 transition" onclick="decrementCartItem(${item.id})">-</button>
                <span class="font-bold text-sm px-1">${item.cantidad}</span>
                <button class="w-6 h-6 flex items-center justify-center border border-gray-300 text-gray-600 rounded-full hover:bg-gray-100 transition" onclick="incrementCartItem(${item.id})">+</button>
            </div>
        </div>
      </div>
    `;
    container.innerHTML += itemHTML;
  });

  totalDisplayBS.innerHTML = formatCurrency(subtotal_bs);
  totalDisplayUSD.innerHTML = formatUSD(subtotal_usd); // ¡NUEVO!
}

/**
 * (Global) Elimina un item del carrito
 */
function removeItemFromCart(productId) {
  cart = cart.filter((item) => item.id !== productId);
  saveCart();
  renderCartItems();
  updateCartBadge();
  if (cart.length === 0) {
    closeCart();
  }
}

/**
 * (¡NUEVO!) Incrementa la cantidad de un item en el carrito
 */
function incrementCartItem(productId) {
  const itemIndex = cart.findIndex((item) => item.id === productId);
  if (itemIndex > -1) {
    cart[itemIndex].cantidad++;
    saveCart();
    renderCartItems();
    updateCartBadge();
  }
}

/**
 * (¡NUEVO!) Decrementa la cantidad de un item en el carrito
 */
function decrementCartItem(productId) {
  const itemIndex = cart.findIndex((item) => item.id === productId);
  if (itemIndex > -1) {
    if (cart[itemIndex].cantidad > 1) {
      cart[itemIndex].cantidad--;
      saveCart();
      renderCartItems();
      updateCartBadge();
    } else {
      // Si la cantidad es 1, decrementar es lo mismo que eliminar
      removeItemFromCart(productId);
    }
  }
}

/**
 * Finaliza el pedido
 */
function sendOrder() {
  const clientName = document.getElementById("client-name").value;
  const clientComments = document.getElementById("client-comments").value;

  if (clientName.trim() === "") {
    alert("Por favor, ingresa tu nombre.");
    return;
  }

  console.log("=== PEDIDO ENVIADO ===");
  console.log("Cliente:", clientName);
  console.log("Comentarios:", clientComments);
  console.log("Carrito:", cart);

  alert(`¡Gracias ${clientName}! Tu pedido ha sido enviado.`);

  closeCart();
  cart = [];
  saveCart();
  renderCartItems();
  updateCartBadge();
}

/* ======================================================================
  LISTENERS (DOMContentLoaded)
====================================================================== */
document.addEventListener("DOMContentLoaded", () => {
  // --- INICIALIZACIÓN ---
  loadCart();
  updateCartBadge();

  // --- Lógica de Menú Hamburguesa ---
  const hamburgerMenu = document.querySelector(".hamburger-menu");
  const navLinks = document.querySelector(".nav-links");
  if (hamburgerMenu && navLinks) {
    hamburgerMenu.addEventListener("click", () => {
      hamburgerMenu.classList.toggle("active");
      navLinks.classList.toggle("active");
    });
    navLinks.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", () => {
        if (navLinks.classList.contains("active")) {
          hamburgerMenu.classList.remove("active");
          navLinks.classList.remove("active");
        }
      });
    });
  }

  // --- Lógica de Smooth Scroll ---
  document.querySelectorAll("nav a").forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");
      try {
        if (targetId && targetId.startsWith("#") && targetId.length > 1) {
          const targetElement = document.querySelector(targetId);
          if (targetElement) {
            targetElement.scrollIntoView({ behavior: "smooth" });
          }
        }
      } catch (error) {
        console.error("Error en smooth scroll:", error);
      }
    });
  });

  // --- Lógica del MODAL de Producto ---
  const modal = document.getElementById("product-modal");
  const btnMinus = document.getElementById("btn-minus");
  const btnPlus = document.getElementById("btn-plus");
  const quantityDisplay = document.getElementById("quantity-display");
  const btnAddToCart = document.getElementById("btn-add-to-cart");

  if (modal && btnMinus && btnPlus && quantityDisplay && btnAddToCart) {
    btnPlus.addEventListener("click", () => {
      let qty = parseInt(quantityDisplay.textContent);
      qty++;
      quantityDisplay.textContent = qty;
      updateModalPrice();
    });
    btnMinus.addEventListener("click", () => {
      let qty = parseInt(quantityDisplay.textContent);
      if (qty > 1) {
        qty--;
        quantityDisplay.textContent = qty;
        updateModalPrice();
      }
    });
    btnAddToCart.addEventListener("click", () => {
      const productToAdd = {
        ...currentModalProduct,
        cantidad: parseInt(quantityDisplay.textContent),
      };
      addToCart(productToAdd);
      closeModal();
      openCart();
    });
  }

  // --- Lógica del SIDEBAR del Carrito ---
  const openCartDesktop = document.getElementById("open-cart-btn-desktop");
  const openCartMobile = document.getElementById("open-cart-btn-mobile");
  const closeCartBtn = document.getElementById("close-cart-btn");
  const cartBackdrop = document.getElementById("cart-backdrop");

  if (openCartDesktop) openCartDesktop.addEventListener("click", openCart);
  if (openCartMobile) openCartMobile.addEventListener("click", openCart);
  if (closeCartBtn) closeCartBtn.addEventListener("click", closeCart);
  if (cartBackdrop) cartBackdrop.addEventListener("click", closeCart);

  // --- Listener para el botón de ENVIAR PEDIDO ---
  const btnSendOrder = document.getElementById("btn-send-order");
  if (btnSendOrder) {
    btnSendOrder.addEventListener("click", sendOrder);
  }

  // --- Listeners Globales de Cierre (ESC) ---
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
      if (!document.getElementById("cart-sidebar").classList.contains("translate-x-full")) {
        closeCart();
      } else if (!document.getElementById("product-modal").classList.contains("hidden")) {
        closeModal();
      }
    }
  });
});
