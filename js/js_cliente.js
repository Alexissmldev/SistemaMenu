let modalBasePrice = 0;
let currentModalProduct = {};
let cart = [];

let originalMainContent = "";
let debounceTimeout;
let toastTimeout;

function formatCurrency(number) {
  const formatted = (number || 0).toLocaleString("es-VE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  return "Bs. " + formatted;
}

function formatUSD(number) {
  const formatted = (number || 0).toLocaleString("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  return formatted;
}

/**
 * Muestra una alerta toast en la esquina.
 * @param {string} message - El mensaje a mostrar.
 * @param {string} type - 'success' (verde) o 'error' (rojo).
 */
function showToast(message, type = "success") {
  const toast = document.getElementById("toast-notification");
  const toastMessage = document.getElementById("toast-message");
  const toastIcon = toast.querySelector("i");

  if (!toast || !toastMessage || !toastIcon) return;

  clearTimeout(toastTimeout);
  toastMessage.textContent = message;

  toast.classList.remove("bg-green-500", "bg-red-500");
  toastIcon.classList.remove("fa-check-circle", "fa-exclamation-triangle");

  if (type === "error") {
    toast.classList.add("bg-red-500");
    toastIcon.classList.add("fa-exclamation-triangle");
  } else {
    // 'success' por defecto
    toast.classList.add("bg-green-500");
    toastIcon.classList.add("fa-check-circle");
  }

  toast.classList.remove("invisible", "opacity-0", "translate-x-12");

  toastTimeout = setTimeout(() => {
    toast.classList.add("opacity-0", "translate-x-12");
    setTimeout(() => {
      toast.classList.add("invisible");
    }, 300);
  }, 3000);
}

function loadCart() {
  const cartData = localStorage.getItem("miMenuGobernacionCart");
  cart = cartData ? JSON.parse(cartData) : [];
}

function saveCart() {
  localStorage.setItem("miMenuGobernacionCart", JSON.stringify(cart));
}

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
      precio: product.precio_raw,
      precio_usd: product.precio_usd,
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

function openCart() {
  renderCartItems();
  document.getElementById("cart-backdrop").classList.remove("hidden");
  document.getElementById("cart-sidebar").classList.remove("translate-x-full");
  document.body.style.overflow = "hidden";
}

function closeCart() {
  document.getElementById("cart-backdrop").classList.add("hidden");
  document.getElementById("cart-sidebar").classList.add("translate-x-full");
  if (document.getElementById("product-modal").classList.contains("invisible")) {
    document.body.style.overflow = "";
  }
}

function openModal(productData) {
  const modal = document.getElementById("product-modal");
  const modalContent = document.getElementById("product-modal-content");
  if (!modal || !modalContent) return;

  modalBasePrice = productData.precio_raw;
  currentModalProduct = productData;
  document.getElementById("modal-image").src = productData.foto;
  document.getElementById("modal-name").textContent = productData.nombre;
  document.getElementById("modal-description").textContent = productData.descripcion;
  document.getElementById("modal-price").innerHTML = productData.precio_display;
  document.getElementById("quantity-display").textContent = "1";

  modal.classList.remove("invisible");
  modal.classList.remove("opacity-0");
  modalContent.classList.remove("translate-y-full");
  document.body.style.overflow = "hidden";
}

function closeModal() {
  const modal = document.getElementById("product-modal");
  const modalContent = document.getElementById("product-modal-content");
  if (!modal || !modalContent) return;

  modal.classList.add("opacity-0");
  modalContent.classList.add("translate-y-full");

  setTimeout(() => {
    modal.classList.add("invisible");
  }, 300);

  if (document.getElementById("cart-sidebar").classList.contains("translate-x-full")) {
    document.body.style.overflow = "";
  }

  modalBasePrice = 0;
  currentModalProduct = {};
}

function updateModalPrice() {
  const quantityDisplay = document.getElementById("quantity-display");
  const priceDisplay = document.getElementById("modal-price");
  if (!quantityDisplay || !priceDisplay) return;
  const quantity = parseInt(quantityDisplay.textContent);
  const totalPrice = modalBasePrice * quantity;
  priceDisplay.innerHTML = formatCurrency(totalPrice);
}

function renderCartItems() {
  const container = document.getElementById("cart-items-container");
  const totalDisplayBS = document.getElementById("cart-total-display");
  const totalDisplayUSD = document.getElementById("cart-total-display-usd");
  const checkoutForm = document.getElementById("btn-send-order").parentElement;

  container.innerHTML = "";

  if (cart.length === 0) {
    container.innerHTML = `
      <div id="cart-empty-msg" class="text-center text-gray-500 pt-10">
        <i class="fa fa-shopping-cart text-6xl mx-auto text-gray-300"></i>
        <p class="mt-2">Tu carrito está vacío.</p>
      </div>
    `;
    totalDisplayBS.innerHTML = formatCurrency(0);
    totalDisplayUSD.innerHTML = formatUSD(0);
    if (checkoutForm) checkoutForm.classList.add("hidden");
    return;
  }

  if (checkoutForm) checkoutForm.classList.remove("hidden");

  let subtotal_bs = 0;
  let subtotal_usd = 0;

  cart.forEach((item) => {
    const itemTotal_bs = item.precio * item.cantidad;
    subtotal_bs += itemTotal_bs;
    const itemTotal_usd = item.precio_usd * item.cantidad;
    subtotal_usd += itemTotal_usd;

    const itemHTML = `
      <div class="flex space-x-3 p-2 bg-white rounded-lg border">
        <img src="${item.foto}" alt="${item.nombre}" class="w-16 h-16 object-cover rounded-md flex-shrink-0">
        <div class="flex-grow min-w-0">
          <p class="font-semibold text-sm text-gray-800 truncate">${item.nombre}</p>
          <p class="text-sm font-bold text-red-600 mt-1">${formatCurrency(itemTotal_bs)}</p>
        </div>
        <div class="flex-shrink-0 flex flex-col items-end justify-between">
            <button class="text-gray-400 hover:text-red-500" onclick="removeItemFromCart(${item.id})">
              <i class="fa fa-times text-xl"></i>
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
  totalDisplayUSD.innerHTML = formatUSD(subtotal_usd);
}

function removeItemFromCart(productId) {
  cart = cart.filter((item) => item.id !== productId);
  saveCart();
  renderCartItems();
  updateCartBadge();
  if (cart.length === 0) {
    closeCart();
  }
}

function incrementCartItem(productId) {
  const itemIndex = cart.findIndex((item) => item.id === productId);
  if (itemIndex > -1) {
    cart[itemIndex].cantidad++;
    saveCart();
    renderCartItems();
    updateCartBadge();
  }
}

function decrementCartItem(productId) {
  const itemIndex = cart.findIndex((item) => item.id === productId);
  if (itemIndex > -1) {
    if (cart[itemIndex].cantidad > 1) {
      cart[itemIndex].cantidad--;
      saveCart();
      renderCartItems();
      updateCartBadge();
    } else {
      removeItemFromCart(productId);
    }
  }
}

function sendOrder() {
  const clientNameInput = document.getElementById("client-name");
  const clientCommentsInput = document.getElementById("client-comments");

  const clientName = clientNameInput.value;
  const clientComments = clientCommentsInput.value;

  if (clientName.trim() === "") {
    showToast("Por favor, ingresa tu nombre.", "error");
    return;
  }

  let whatsAppNumber = document.body.dataset.whatsappNumber;
  if (!whatsAppNumber || whatsAppNumber.trim() === "") {
    showToast("Error: No hay número de WhatsApp.", "error");
    return;
  }
  if (whatsAppNumber.startsWith("0")) {
    whatsAppNumber = "58" + whatsAppNumber.substring(1);
  }
  whatsAppNumber = whatsAppNumber.replace(/[^0-9]/g, "");

  let mensaje = `*¡Hola!  Nuevo Pedido del Menú Digital*\n\n`;
  mensaje += `*Cliente:* ${clientName}\n\n`;
  mensaje += `*--- MI PEDIDO ---*\n`;

  let subtotal_bs = 0;
  let subtotal_usd = 0;

  cart.forEach((item) => {
    mensaje += `*${item.cantidad}x* - ${item.nombre}\n`;
    subtotal_bs += item.precio * item.cantidad;
    subtotal_usd += item.precio_usd * item.cantidad;
  });

  mensaje += `\n*--- TOTALES ---*\n`;
  mensaje += `*Total (Bs):* ${formatCurrency(subtotal_bs)}\n`;
  mensaje += `*Total (USD):* ${formatUSD(subtotal_usd)}\n\n`;

  if (clientComments.trim() !== "") {
    mensaje += `*Comentarios:*\n${clientComments}\n`;
  }
  mensaje += `_Pedido generado automáticamente._`;

  const encodedMessage = encodeURIComponent(mensaje);
  const whatsAppUrl = `https://wa.me/${whatsAppNumber}?text=${encodedMessage}`;

  showToast(`¡Gracias ${clientName}! Redirigiendo...`, "success");
  window.open(whatsAppUrl, "_blank");

  closeCart();
  cart = [];
  saveCart();
  renderCartItems();
  updateCartBadge();
  clientNameInput.value = "";
  clientCommentsInput.value = "";
}

function registerAppListeners() {
  const categoryLinks = document.querySelectorAll("nav a.category-link");
  const productSections = document.querySelectorAll("#product-content-wrapper section.product-section");
  const header = document.querySelector("header");
  const activeClasses = ["text-red-600", "border-red-600"];
  const inactiveClasses = ["text-gray-500", "border-transparent"];
  const isSearchPage = new URLSearchParams(window.location.search).has("busqueda");

  if (isSearchPage) {
    categoryLinks.forEach((link) => {
      link.classList.remove(...activeClasses);
      link.classList.add(...inactiveClasses);
      if (link.getAttribute("href") === "#todos") {
        link.classList.add(...activeClasses);
        link.classList.remove(...inactiveClasses);
      }
    });
  }

  const currentHash = window.location.hash;
  if (!isSearchPage && currentHash && currentHash.length > 1) {
    const activeLink = document.querySelector(`nav a.category-link[href="${currentHash}"]`);
    if (activeLink) {
      activeLink.click();
    }
  }

  categoryLinks.forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");

      if (isSearchPage) {
        window.location.href = "menu.php" + (targetId === "#todos" ? "" : targetId);
        return;
      }

      categoryLinks.forEach((link) => {
        link.classList.remove(...activeClasses);
        link.classList.add(...inactiveClasses);
      });
      this.classList.add(...activeClasses);
      this.classList.remove(...inactiveClasses);

      let sectionToScrollTo = null;
      if (targetId === "#todos") {
        productSections.forEach((section) => {
          if (section.id !== "search-results") {
            section.style.display = "block";
          }
        });
        sectionToScrollTo =
          document.getElementById("desayunos") || (productSections.length > 0 ? productSections[0] : null);
      } else {
        productSections.forEach((section) => {
          section.style.display = "none";
        });
        try {
          const targetElement = document.getElementById(targetId.substring(1));
          if (targetElement) {
            targetElement.style.display = "block";
            sectionToScrollTo = targetElement;
          }
        } catch (error) {}
      }

      if (sectionToScrollTo) {
        const headerHeight = header ? header.offsetHeight : 0;
        const elementPosition = sectionToScrollTo.getBoundingClientRect().top;
        const offsetPosition = elementPosition + window.scrollY - headerHeight - 16;
        window.scrollTo({ top: offsetPosition, behavior: "smooth" });
      } else {
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    });
  });

  const homeTrigger = document.getElementById("mobile-home-trigger");
  const searchTrigger = document.getElementById("mobile-search-trigger");
  const searchInput = document.getElementById("mobile-search-input");
  const searchContainer = document.getElementById("mobile-search-form");

  if (homeTrigger) {
    homeTrigger.addEventListener("click", (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }

  if (searchTrigger && searchInput && searchContainer) {
    searchTrigger.addEventListener("click", (e) => {
      e.preventDefault();
      const header = document.querySelector("header");
      const headerHeight = header ? header.offsetHeight : 0;
      const elementPosition = searchContainer.getBoundingClientRect().top;
      const offsetPosition = elementPosition + window.scrollY - headerHeight - 16;
      window.scrollTo({ top: offsetPosition, behavior: "smooth" });
      searchInput.focus();
    });
  }

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
      const productName = document.getElementById("modal-name").textContent;

      addToCart(productToAdd);
      closeModal();
      showToast(`¡"${productName}" añadido al carrito!`, "success");
    });

    modal.addEventListener("click", (e) => {
      if (e.target.id === "product-modal") {
        closeModal();
      }
    });
  }

  const openCartDesktop = document.getElementById("open-cart-btn-desktop");
  const openCartMobile = document.getElementById("open-cart-btn-mobile");
  const closeCartBtn = document.getElementById("close-cart-btn");
  const cartBackdrop = document.getElementById("cart-backdrop");

  if (openCartDesktop) openCartDesktop.addEventListener("click", openCart);
  if (openCartMobile) openCartMobile.addEventListener("click", openCart);
  if (closeCartBtn) closeCartBtn.addEventListener("click", closeCart);
  if (cartBackdrop) cartBackdrop.addEventListener("click", closeCart);

  const btnSendOrder = document.getElementById("btn-send-order");
  if (btnSendOrder) {
    btnSendOrder.addEventListener("click", sendOrder);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  loadCart();
  updateCartBadge();

  const productContentWrapper = document.getElementById("product-content-wrapper");
  if (productContentWrapper) {
    originalMainContent = productContentWrapper.innerHTML;
  }

  const hamburgerMenu = document.querySelector(".hamburger-menu");
  const navLinks = document.querySelector(".nav-links");
  if (hamburgerMenu && navLinks) {
    // ... (Tu código de hamburguesa)
  }

  const desktopInput = document.getElementById("desktop-search-input");
  const mobileInput = document.getElementById("mobile-search-input");
  const categoryNav = document.getElementById("category-nav-section");

  const handleSearch = (query) => {
    if (query.trim() === "") {
      if (productContentWrapper) productContentWrapper.innerHTML = originalMainContent;
      if (categoryNav) categoryNav.style.display = "block";
      registerAppListeners();
      return;
    }

    if (categoryNav) categoryNav.style.display = "none";
    if (productContentWrapper)
      productContentWrapper.innerHTML = '<p class="text-center text-gray-500">Buscando...</p>';

    const formData = new FormData();
    formData.append("query", query);

    fetch("./php/buscador_cliente.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.text())
      .then((html) => {
        if (productContentWrapper) productContentWrapper.innerHTML = html;
      })
      .catch((error) => {
        console.error("Error en fetch:", error);
        if (productContentWrapper)
          productContentWrapper.innerHTML =
            '<p class="text-center text-red-500">Error al cargar resultados.</p>';
      });
  };

  const onInput = (event) => {
    clearTimeout(debounceTimeout);
    const query = event.target.value;
    debounceTimeout = setTimeout(() => {
      handleSearch(query);
    }, 300);
  };

  if (desktopInput) desktopInput.addEventListener("input", onInput);
  if (mobileInput) mobileInput.addEventListener("input", onInput);

  registerAppListeners();

  document.addEventListener("keydown", (e) => {
    const modal = document.getElementById("product-modal");
    const cartSidebar = document.getElementById("cart-sidebar");

    if (e.key === "Escape") {
      if (!cartSidebar.classList.contains("translate-x-full")) {
        closeCart();
      } else if (!modal.classList.contains("invisible")) {
        closeModal();
      }
    }
  });
});
