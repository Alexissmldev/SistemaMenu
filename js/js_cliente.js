/* =========================================
   VARIABLES GLOBALES
   ========================================= */

let currentProductId = null;
let currentProductPrice = 0;
let currentModalProduct = {};
let currentSelection = {};
let cart = [];
let originalMainContent = "";
let debounceTimeout;
let toastTimeout;

/* =========================================
   UTILIDADES Y FORMATO
   ========================================= */

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

function showToast(message, type = "success") {
  const toast = document.getElementById("toast-notification");
  const toastMessage = document.getElementById("toast-message");
  const toastIcon = toast ? toast.querySelector("i") : null;

  if (!toast || !toastMessage) return;

  clearTimeout(toastTimeout);
  toastMessage.textContent = message;

  toast.classList.remove(
    "bg-green-500",
    "bg-red-500",
    "invisible",
    "opacity-0",
    "translate-x-12"
  );
  if (toastIcon) {
    toastIcon.classList.remove("fa-check-circle", "fa-exclamation-triangle");
  }

  if (type === "error") {
    toast.classList.add("bg-red-500");
    if (toastIcon) toastIcon.classList.add("fa-exclamation-triangle");
  } else {
    toast.classList.add("bg-green-500");
    if (toastIcon) toastIcon.classList.add("fa-check-circle");
  }

  toastTimeout = setTimeout(() => {
    toast.classList.add("opacity-0", "translate-x-12");
    setTimeout(() => {
      toast.classList.add("invisible");
    }, 175);
  }, 1150);
}

/* =========================================
   CARACTERÍSTICAS (CARRUSEL Y ANUNCIOS)
   ========================================= */

function inicializarAnunciosClicables() {
  const anuncios = document.querySelectorAll(
    "#banner-dinamico, .anuncio-clicable"
  );
  if (!anuncios || anuncios.length === 0) return;

  anuncios.forEach((anuncio) => {
    anuncio.addEventListener("click", () => {
      const accion = anuncio.dataset.accion;

      if (accion === "quick_add") {
        const productJson = anuncio.dataset.productJson;
        if (!productJson) return;

        try {
          const promoData = JSON.parse(productJson);
          addToCart([
            {
              id: promoData.id,
              producto_id: null, // Es promo, no producto base
              nombre: promoData.nombre,
              foto: promoData.foto,
              precio_raw: promoData.precio_raw,
              precio_usd: promoData.precio_usd,
              cantidad: 1,
              descripcion: "Promoción rápida",
              nota: "",
              type: "promo", // Identificador de promoción
            },
          ]);
          showToast(`¡"${promoData.nombre}" añadido al carrito!`, "success");
        } catch (e) {
          console.error("Error JSON promo:", e);
        }
      } else if (accion === "scroll") {
        const catIDs = anuncio.dataset.categoriasIds;
        const prodIDs = anuncio.dataset.productosIds;
        let targetElement = null;

        if (catIDs) {
          const firstCatID = catIDs.split(",")[0];
          targetElement = document.querySelector(
            `[data-numeric-id="categoria-${firstCatID}"]`
          );
        } else if (prodIDs) {
          const firstProdID = prodIDs.split(",")[0];
          targetElement = document.getElementById(`producto-${firstProdID}`);
        }

        if (targetElement) {
          const headerOffset = 100;
          const elementPosition = targetElement.getBoundingClientRect().top;
          const offsetPosition =
            elementPosition + window.scrollY - headerOffset;
          window.scrollTo({ top: offsetPosition, behavior: "smooth" });
        }
      }
    });
  });
}

function inicializarCarruselAutomatico() {
  const carrusel = document.getElementById("ofertas-carrusel");
  if (!carrusel) return;

  const SCROLL_INTERVAL = 5000;
  let autoScrollTimer = null;

  const startAutoScroll = () => {
    clearInterval(autoScrollTimer);
    autoScrollTimer = setInterval(() => {
      const cardWidth =
        carrusel.querySelector(".anuncio-clicable")?.offsetWidth || 300;
      const scrollAmount = cardWidth + 16;

      if (
        carrusel.scrollLeft + carrusel.clientWidth >=
        carrusel.scrollWidth - 50
      ) {
        carrusel.scrollTo({ left: 0, behavior: "smooth" });
      } else {
        carrusel.scrollBy({ left: scrollAmount, behavior: "smooth" });
      }
    }, SCROLL_INTERVAL);
  };

  startAutoScroll();
  carrusel.addEventListener("mouseenter", () => clearInterval(autoScrollTimer));
  carrusel.addEventListener("mouseleave", () => startAutoScroll());
}

/* =========================================
   LÓGICA DEL CARRITO (CORE)
   ========================================= */

function loadCart() {
  const cartData = localStorage.getItem("miMenuGobernacionCart");
  cart = cartData ? JSON.parse(cartData) : [];
}

function saveCart() {
  localStorage.setItem("miMenuGobernacionCart", JSON.stringify(cart));
}

// Modificado para aceptar arrays y notas
function addToCart(itemOrArray) {
  const items = Array.isArray(itemOrArray) ? itemOrArray : [itemOrArray];
  let addedCount = 0;

  items.forEach((product) => {
    const quantity = parseInt(product.cantidad);
    if (quantity <= 0) return;

    // Buscamos si existe un item con el mismo ID Y la misma nota
    // Esto agrupa productos iguales para que la BD reciba la cantidad correcta
    const existingIndex = cart.findIndex(
      (item) =>
        item.id === product.id && (item.nota || "") === (product.nota || "")
    );

    if (existingIndex > -1) {
      cart[existingIndex].cantidad += quantity;
    } else {
      cart.push({
        id: product.id,
        producto_id: product.producto_id || product.id,
        nombre: product.nombre,
        foto: product.foto,
        precio: product.precio_raw,
        precio_usd: product.precio_usd,
        cantidad: quantity,
        descripcion: product.descripcion || "",
        nota: product.nota || "",
        type: product.type || "producto", // Guardamos el tipo (promo/producto)
      });
    }
    addedCount++;
  });

  if (addedCount > 0) {
    saveCart();
    updateCartBadge();
    renderCartItems();
  }
}

function updateCartBadge() {
  const desktopBadge = document.getElementById("cart-count-badge-desktop");
  const mobileBadge = document.getElementById("cart-count-badge-mobile");
  const totalItems = cart.reduce((total, item) => total + item.cantidad, 0);
  const badges = [desktopBadge, mobileBadge];

  badges.forEach((badge) => {
    if (badge) {
      if (totalItems > 0) {
        badge.textContent = totalItems > 99 ? "99+" : totalItems;
        badge.classList.remove("hidden");
      } else {
        badge.classList.add("hidden");
      }
    }
  });
}

function renderCartItems() {
  const container = document.getElementById("cart-items-container");
  const step1Bs = document.getElementById("step1-bs");
  const step1Usd = document.getElementById("step1-usd");
  const step2Bs = document.getElementById("step2-bs");

  if (cart.length === 0) {
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-64 text-gray-400">
            <i class="fa fa-shopping-basket text-5xl mb-4 opacity-20"></i>
            <p class="font-medium">Tu carrito está vacío</p>
        </div>`;
    if (step1Bs) step1Bs.textContent = formatCurrency(0);
    if (step1Usd) step1Usd.textContent = formatUSD(0);
    return;
  }

  let subtotal_bs = 0;
  let subtotal_usd = 0;
  let itemsHTML = "";

  cart.forEach((item) => {
    const itemTotal_bs = item.precio * item.cantidad;
    subtotal_bs += itemTotal_bs;
    subtotal_usd += item.precio_usd * item.cantidad;

    const notaHTML = item.nota
      ? `<div class="mt-1 flex items-start gap-1 text-[10px] text-gray-500 bg-gray-50 p-1 rounded">
             <i class="fa fa-pen mt-0.5 opacity-50"></i> <span class="italic">"${item.nota}"</span>
           </div>`
      : "";

    itemsHTML += `
        <div class="flex gap-3 py-2 border-b border-gray-100 last:border-0 relative group">
            <button class="absolute top-2 right-0 text-gray-300 hover:text-red-500" onclick="removeFromCart('${item.id}')">
                <i class="fa fa-times"></i>
            </button>
            <div class="w-14 h-14 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                <img src="${item.foto}" class="w-full h-full object-cover">
            </div>
            <div class="flex-grow pr-6">
                <p class="font-bold text-gray-800 text-sm line-clamp-2">${item.nombre}</p>
                <div class="flex justify-between items-end mt-1">
                    <p class="text-red-600 font-bold text-xs">${formatCurrency(itemTotal_bs)}</p>
                    <div class="flex items-center bg-white border border-gray-200 rounded h-6 shadow-sm">
                        <button class="w-6 text-gray-400 hover:text-red-600" onclick="updateItemQty('${item.id}', -1)">−</button>
                        <span class="w-6 text-center text-xs font-bold">${item.cantidad}</span>
                        <button class="w-6 text-gray-400 hover:text-green-600" onclick="updateItemQty('${item.id}', 1)">+</button>
                    </div>
                </div>
                ${notaHTML}
            </div>
        </div>
    `;
  });

  container.innerHTML = itemsHTML;

  const totalTexto = formatCurrency(subtotal_bs);
  if (step1Bs) step1Bs.textContent = totalTexto;
  if (step1Usd) step1Usd.textContent = formatUSD(subtotal_usd);
  if (step2Bs) step2Bs.textContent = totalTexto;
}

function updateItemQty(id, change) {
  const itemIndex = cart.findIndex((i) => i.id === id);
  if (itemIndex > -1) {
    if (change < 0 && cart[itemIndex].cantidad === 1) {
      cart.splice(itemIndex, 1);
    } else {
      cart[itemIndex].cantidad += change;
    }
    saveCart();
    renderCartItems();
    updateCartBadge();
  }
}
function removeFromCart(id) {
  cart = cart.filter((i) => i.id !== id);
  saveCart();
  renderCartItems();
  updateCartBadge();
}

// ==========================================
// 2. LÓGICA DE NAVEGACIÓN ENTRE PASOS
// ==========================================

function goToCheckout() {
  if (cart.length === 0) {
    showToast("El carrito está vacío", "error");
    return;
  }
  document.getElementById("cart-step-1").classList.add("hidden");
  document.getElementById("cart-step-2").classList.remove("hidden");
  document.getElementById("cart-step-2").classList.add("flex");
  document.getElementById("cart-title-step").textContent = "Finalizar Compra";
}

function backToCart() {
  document.getElementById("cart-step-2").classList.add("hidden");
  document.getElementById("cart-step-2").classList.remove("flex");
  document.getElementById("cart-step-1").classList.remove("hidden");
  document.getElementById("cart-title-step").textContent = "Tu Pedido";
}

function togglePaymentDetails() {
  const pmRadio = document.querySelector(
    'input[name="payment_method"][value="pago_movil"]'
  );
  const cajaRadio = document.querySelector(
    'input[name="payment_method"][value="en_caja"]'
  );

  const pmDetails = document.getElementById("pago-movil-details");
  const cajaDetails = document.getElementById("en-caja-details");

  if (pmDetails) pmDetails.classList.add("hidden");
  if (cajaDetails) cajaDetails.classList.add("hidden");

  if (pmRadio && pmRadio.checked && pmDetails) {
    pmDetails.classList.remove("hidden");
  }

  if (cajaRadio && cajaRadio.checked && cajaDetails) {
    cajaDetails.classList.remove("hidden");
  }
}

function copyToClipboard(text) {
  navigator.clipboard
    .writeText(text)
    .then(() => {
      showToast("¡Copiado! " + text, "success");
    })
    .catch((err) => {
      showToast("Error al copiar", "error");
    });
}

function setupCartListener() {
  const container = document.getElementById("cart-items-container");
  if (!container) return;

  container.addEventListener("click", (event) => {
    const button = event.target.closest("button");
    if (!button || !button.dataset.action) return;

    const action = button.dataset.action;
    const itemId = button.dataset.id;

    const itemIndex = cart.findIndex((i) => i.id === itemId);
    if (itemIndex === -1) return;

    if (action === "increment") {
      cart[itemIndex].cantidad++;
    } else if (action === "decrement") {
      if (cart[itemIndex].cantidad > 1) {
        cart[itemIndex].cantidad--;
      } else {
        cart.splice(itemIndex, 1);
      }
    } else if (action === "remove") {
      cart.splice(itemIndex, 1);
    }

    saveCart();
    renderCartItems();
    updateCartBadge();
  });
}

function openCart() {
  renderCartItems();
  // NUEVO: Intentar cargar datos del usuario si existen
  cargarDatosLocales();

  const backdrop = document.getElementById("cart-backdrop");
  const sidebar = document.getElementById("cart-sidebar");

  if (!backdrop || !sidebar) return;

  backdrop.classList.remove("hidden");
  setTimeout(() => {
    backdrop.classList.remove("opacity-0");
    backdrop.classList.add("opacity-100", "duration-500", "ease-out-expo");
  }, 10);

  sidebar.classList.remove("translate-x-full");
  sidebar.classList.add("translate-x-0", "duration-500", "ease-out-expo");

  document.body.style.overflow = "hidden";
}

function closeCart() {
  const backdrop = document.getElementById("cart-backdrop");
  const sidebar = document.getElementById("cart-sidebar");
  const modal = document.getElementById("product-modal");

  if (!backdrop || !sidebar) return;

  sidebar.classList.remove("translate-x-0", "duration-500");
  sidebar.classList.add("translate-x-full", "duration-300", "ease-out-expo");

  backdrop.classList.remove("opacity-100");
  backdrop.classList.add("opacity-0", "duration-300");

  setTimeout(() => {
    backdrop.classList.add("hidden");
    backdrop.classList.remove("duration-300", "ease-out-expo");
    sidebar.classList.remove("duration-300", "ease-out-expo");
  }, 300);

  if (!modal || modal.classList.contains("invisible")) {
    document.body.style.overflow = "";
  }
}

/* =========================================
   LÓGICA DEL MODAL
   ========================================= */

function openModal(productData) {
  const modal = document.getElementById("product-modal");
  const modalContent = document.getElementById("product-modal-content");
  const variantsContainer = document.getElementById("modal-variants-list");
  const stickyHeader = document.getElementById("mobile-sticky-header");

  if (!modal || !modalContent) return;

  currentModalProduct = productData;
  currentProductId = productData.id;
  currentSelection = {};

  const scrollContainer = document.getElementById("modal-scroll-container");
  if (scrollContainer) scrollContainer.scrollTop = 0;

  if (stickyHeader) stickyHeader.classList.add("-translate-y-full");

  const noteInput = document.getElementById("modal-note");
  if (noteInput) noteInput.value = "";

  const imgDesktop = document.getElementById("modal-image-desktop");
  const imgMobile = document.getElementById("modal-image-mobile");
  if (imgDesktop) imgDesktop.src = productData.foto;
  if (imgMobile) imgMobile.src = productData.foto;

  const nameEl = document.getElementById("modal-name");
  const descEl = document.getElementById("modal-description");
  if (nameEl) nameEl.textContent = productData.nombre;
  if (descEl) descEl.textContent = productData.descripcion;

  if (variantsContainer) {
    variantsContainer.innerHTML = "";

    const variantes =
      productData.variantes && productData.variantes.length > 0
        ? productData.variantes
        : [
            {
              id: (productData.id_padre || productData.id) + "-def",
              nombre: "Estándar",
              precio_bs: productData.precio_raw || 0,
              precio_usd: productData.precio_usd || 0,
            },
          ];

    variantes.forEach((variant) => {
      const variantRow = document.createElement("div");
      variantRow.className =
        "flex items-center justify-between p-4 border border-gray-100 rounded-xl hover:border-red-200 transition-all bg-white shadow-sm";
      variantRow.id = `row-${variant.id}`;

      const priceText = formatCurrency(variant.precio_bs);

      variantRow.innerHTML = `
            <div class="flex flex-col">
                <span class="font-bold text-gray-800 text-base">${variant.nombre}</span>
                <span class="text-red-600 font-bold text-sm mt-0.5">${priceText}</span>
            </div>
            <div class="flex items-center bg-gray-50 rounded-full h-10 border border-gray-200">
                <button type="button" class="w-10 h-full flex items-center justify-center text-gray-500 hover:text-red-600 font-bold rounded-l-full transition active:scale-90 select-none" 
                        onclick="updateModalVariant('${variant.id}', -1)">−</button>
                <span id="qty-${variant.id}" class="w-8 text-center font-bold text-gray-900 text-base select-none">0</span>
                <button type="button" class="w-10 h-full flex items-center justify-center text-red-600 hover:text-red-700 font-bold rounded-r-full transition active:scale-90 select-none" 
                        onclick="updateModalVariant('${variant.id}', 1)">+</button>
            </div>
        `;
      variantsContainer.appendChild(variantRow);
    });
  }

  calculateModalTotal();
  if (typeof setupModalScroll === "function") setupModalScroll();

  modal.classList.remove("invisible");
  void modal.offsetWidth;

  if (modal.firstElementChild) {
    modal.firstElementChild.classList.remove("opacity-0", "backdrop-blur-none");
    modal.firstElementChild.classList.add(
      "opacity-100",
      "backdrop-blur-sm",
      "duration-500",
      "ease-out-expo"
    );
  }

  modalContent.classList.remove(
    "translate-y-full",
    "md:translate-y-10",
    "opacity-0"
  );

  modalContent.classList.add(
    "translate-y-0",
    "opacity-100",
    "duration-500",
    "ease-out-expo"
  );

  document.body.style.overflow = "hidden";
}

window.updateModalVariant = function (variantId, change) {
  const variantes =
    currentModalProduct.variantes && currentModalProduct.variantes.length > 0
      ? currentModalProduct.variantes
      : [
          {
            id:
              (currentModalProduct.id_padre || currentModalProduct.id) + "-def",
            nombre: "Estándar",
            precio_bs: currentModalProduct.precio_raw,
            precio_usd: currentModalProduct.precio_usd,
          },
        ];

  const variantData = variantes.find((v) => v.id == variantId);
  if (!variantData) return;

  let currentQty = currentSelection[variantId]?.cantidad || 0;
  let newQty = currentQty + change;
  if (newQty < 0) newQty = 0;

  const qtyDisplay = document.getElementById(`qty-${variantId}`);
  const row = document.getElementById(`row-${variantId}`);
  if (qtyDisplay) qtyDisplay.textContent = newQty;

  if (row) {
    if (newQty > 0) {
      row.classList.add("border-red-500", "bg-red-50");
      row.classList.remove("border-gray-100", "bg-white");
    } else {
      row.classList.remove("border-red-500", "bg-red-50");
      row.classList.add("border-gray-100", "bg-white");
    }
  }

  if (newQty > 0) {
    currentSelection[variantId] = {
      id: variantId,
      producto_id: currentModalProduct.id,
      nombre:
        variantData.nombre === "Estándar"
          ? currentModalProduct.nombre
          : `${currentModalProduct.nombre} (${variantData.nombre})`,
      precio_raw: variantData.precio_bs,
      precio_usd: variantData.precio_usd,
      foto: currentModalProduct.foto,
      cantidad: newQty,
      descripcion: currentModalProduct.descripcion,
    };
  } else {
    delete currentSelection[variantId];
  }

  calculateModalTotal();
};

function calculateModalTotal() {
  let totalBs = 0;
  let totalCount = 0;

  Object.values(currentSelection).forEach((item) => {
    totalBs += item.precio_raw * item.cantidad;
    totalCount += item.cantidad;
  });

  const priceEl = document.getElementById("modal-total-price");
  const badgeEl = document.getElementById("modal-total-qty-badge");
  const btn = document.getElementById("btn-add-modal-selection");

  if (priceEl) priceEl.textContent = formatCurrency(totalBs);

  if (badgeEl) {
    badgeEl.textContent = totalCount;
    if (totalCount > 0) badgeEl.classList.remove("hidden");
    else badgeEl.classList.add("hidden");
  }

  if (btn) {
    if (totalCount === 0) {
      btn.disabled = true;
      btn.classList.add("opacity-50", "cursor-not-allowed");
    } else {
      btn.disabled = false;
      btn.classList.remove("opacity-50", "cursor-not-allowed");
    }
  }
}

function addModalSelectionToCart() {
  const itemsToAdd = Object.values(currentSelection);
  const noteInput = document.getElementById("modal-note");
  const userNote = noteInput ? noteInput.value.trim() : "";

  if (itemsToAdd.length === 0) {
    showToast("Selecciona al menos una opción", "error");
    return;
  }

  const itemsWithNote = itemsToAdd.map((item) => {
    return { ...item, nota: userNote };
  });

  addToCart(itemsWithNote);
  closeModal();
  showToast("Agregado al pedido", "success");
}

function closeModal() {
  const modal = document.getElementById("product-modal");
  const modalContent = document.getElementById("product-modal-content");
  const sidebar = document.getElementById("cart-sidebar");

  if (!modal || !modalContent) return;

  if (modal.firstElementChild) {
    modal.firstElementChild.classList.remove("opacity-100", "backdrop-blur-sm");
    modal.firstElementChild.classList.add(
      "opacity-0",
      "duration-300",
      "ease-out-expo"
    );
  }

  modalContent.classList.remove("translate-y-0", "opacity-100");
  modalContent.classList.add(
    "translate-y-full",
    "md:translate-y-10",
    "opacity-0",
    "duration-300",
    "ease-out-expo"
  );

  setTimeout(() => {
    modal.classList.add("invisible");
    modalContent.classList.remove("duration-300", "ease-out-expo");
    if (modal.firstElementChild) {
      modal.firstElementChild.classList.remove("duration-300", "ease-out-expo");
    }
    const imgDesktop = document.getElementById("modal-image-desktop");
    const imgMobile = document.getElementById("modal-image-mobile");
    if (imgDesktop)
      imgDesktop.src =
        "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";
    if (imgMobile)
      imgMobile.src =
        "data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=";
  }, 300);

  if (!sidebar || sidebar.classList.contains("translate-x-full")) {
    document.body.style.overflow = "";
  }
  currentSelection = {};
}

function setupModalScroll() {
  const scrollContainer = document.getElementById("modal-scroll-container");
  const stickyHeader = document.getElementById("mobile-sticky-header");

  if (!scrollContainer || !stickyHeader) return;

  scrollContainer.onscroll = null;
  scrollContainer.onscroll = () => {
    if (window.innerWidth < 768) {
      if (scrollContainer.scrollTop > 200) {
        stickyHeader.classList.remove("-translate-y-full");
      } else {
        stickyHeader.classList.add("-translate-y-full");
      }
    }
  };
}

/* =========================================
   PEDIDOS (WHATSAPP + BD)
   ========================================= */

async function sendOrder() {
  const nameInput = document.getElementById("client-name");
  const phoneInput = document.getElementById("client-phone");
  const idInput = document.getElementById("client-id");
  const noteInput = document.getElementById("cart-general-note");

  const paymentRadio = document.querySelector(
    'input[name="payment_method"]:checked'
  );
  const orderTypeRadio = document.querySelector(
    'input[name="order_type"]:checked'
  );
  const cashTypeRadio = document.querySelector(
    'input[name="cash_type"]:checked'
  );
  const pmReference = document.getElementById("pm-reference");

  if (!nameInput.value.trim()) {
    showToast("Por favor, escribe tu nombre", "error");
    nameInput.focus();
    nameInput.classList.add("ring-2", "ring-red-500");
    setTimeout(
      () => nameInput.classList.remove("ring-2", "ring-red-500"),
      1500
    );
    return;
  }

  if (!phoneInput.value.trim()) {
    showToast("Por favor, escribe tu teléfono", "error");
    phoneInput.focus();
    phoneInput.classList.add("ring-2", "ring-red-500");
    setTimeout(
      () => phoneInput.classList.remove("ring-2", "ring-red-500"),
      1500
    );
    return;
  }

  if (!idInput.value.trim()) {
    showToast("Por favor, escribe tu Cédula/RIF", "error");
    idInput.focus();
    idInput.classList.add("ring-2", "ring-red-500");
    setTimeout(() => idInput.classList.remove("ring-2", "ring-red-500"), 1500);
    return;
  }

  if (!paymentRadio) {
    showToast("Selecciona un método de pago", "error");
    return;
  }

  if (paymentRadio.value === "pago_movil") {
    if (!pmReference.value || pmReference.value.length < 4) {
      showToast("Ingresa los últimos 4 dígitos de la referencia", "error");
      pmReference.focus();
      pmReference.classList.add("ring-2", "ring-red-500");
      setTimeout(
        () => pmReference.classList.remove("ring-2", "ring-red-500"),
        1500
      );
      return;
    }
  }

  const btnSend = document.querySelector('button[onclick="sendOrder()"]');
  const btnText = btnSend.innerHTML;
  btnSend.disabled = true;
  btnSend.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

  let whatsAppNumber = document.body.dataset.whatsappNumber || "584120000000";
  if (whatsAppNumber.startsWith("0"))
    whatsAppNumber = "58" + whatsAppNumber.substring(1);
  whatsAppNumber = whatsAppNumber.replace(/[^0-9]/g, "");

  const tipoOrden =
    orderTypeRadio && orderTypeRadio.value === "llevar"
      ? "Para Llevar 🛍️"
      : "Comer Aquí 🍽️";
  const notaGeneral = noteInput ? noteInput.value.trim() : "";

  let metodoPagoTexto = "";
  if (paymentRadio.value === "pago_movil") {
    metodoPagoTexto = `Pago Móvil (Ref: ${pmReference.value})`;
  } else {
    const tipoCaja = cashTypeRadio
      ? cashTypeRadio.value === "efectivo"
        ? "Efectivo"
        : "Tarjeta"
      : "Caja";
    metodoPagoTexto = `En Caja (${tipoCaja})`;
  }

  let totalBs = 0;
  let totalUsd = 0;

  let mensaje = `*¡Hola! Nuevo Pedido*\n\n`;
  mensaje += ` *Cliente:* ${nameInput.value.trim()}\n`;
  mensaje += ` *Tel:* ${phoneInput.value.trim()}\n`;
  mensaje += ` *Cédula:* ${idInput.value.trim()}\n`;
  mensaje += ` *Tipo:* ${tipoOrden}\n`;
  mensaje += ` *Pago:* ${metodoPagoTexto}\n`;
  mensaje += ` *Fecha:* ${new Date().toLocaleDateString()}\n`;
  if (notaGeneral) mensaje += ` *Nota Gral:* ${notaGeneral}\n`;
  mensaje += `--------------------------------\n`;

  cart.forEach((item) => {
    mensaje += `▪️ *${item.cantidad}x* ${item.nombre}\n`;
    if (item.nota) mensaje += `   _(${item.nota})_\n`;
    totalBs += item.precio * item.cantidad;
    totalUsd += item.precio_usd * item.cantidad;
  });

  mensaje += `--------------------------------\n`;
  mensaje += `*TOTAL: ${formatCurrency(totalBs).replace("Bs. ", "")} Bs*\n`;
  mensaje += `_(Ref: ${formatUSD(totalUsd)})_`;

  try {
    const ordenData = {
      nombre: nameInput.value.trim(),
      telefono: phoneInput.value.trim(),
      cedula: idInput.value.trim(),
      tipo_orden: orderTypeRadio ? orderTypeRadio.value : "comer",
      metodo_pago: paymentRadio.value,
      referencia:
        paymentRadio.value === "pago_movil" ? pmReference.value : null,
      tipo_caja:
        paymentRadio.value === "en_caja" && cashTypeRadio
          ? cashTypeRadio.value
          : null,
      items: cart,
      total_bs: totalBs,
      total_usd: totalUsd,
      nota: notaGeneral,
    };

    const respuestaBackend = await fetch("./php/pedido_guardar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(ordenData),
    });

    const resultado = await respuestaBackend.json();

    if (resultado.status === "success") {
      // --- NUEVO: GUARDAR DATOS EN LOCALSTORAGE PARA FUTURAS COMPRAS ---
      guardarDatosLocales(
        ordenData.nombre,
        ordenData.telefono,
        ordenData.cedula
      );

      showToast("¡Registrado! Abriendo WhatsApp...", "success");
      const url = `https://wa.me/${whatsAppNumber}?text=${encodeURIComponent(mensaje)}`;

      setTimeout(() => {
        window.open(url, "_blank");

        cart = [];
        saveCart();

        // No borramos datos del cliente (UX)
        if (noteInput) noteInput.value = "";
        if (pmReference) pmReference.value = "";

        const radios = document.querySelectorAll(
          'input[name="payment_method"]'
        );
        radios.forEach((r) => (r.checked = false));

        togglePaymentDetails();
        renderCartItems();
        updateCartBadge();
        closeCart();

        setTimeout(() => {
          backToCart();
        }, 500);
      }, 1000);
    } else {
      console.error(resultado);
      showToast(resultado.message, "error");
    }
  } catch (error) {
    console.error(error);
    showToast("Error de conexión con el servidor", "error");
  } finally {
    btnSend.disabled = false;
    btnSend.innerHTML = btnText;
  }
}

function copyAllPagoMovil() {
  const banco = document.getElementById("pm-bank").innerText;
  const telefono = document.getElementById("pm-phone").innerText;
  const cedula = document.getElementById("pm-id").innerText;
  const textoCompleto = `Banco: ${banco}\nTel: ${telefono}\nCI: ${cedula}`;

  navigator.clipboard
    .writeText(textoCompleto)
    .then(() => {
      showToast("¡Datos copiados al portapapeles!", "success");
    })
    .catch((err) => {
      showToast("Error al copiar", "error");
    });
}

/* =========================================
   LOCAL STORAGE (FUNCIONALIDAD NUEVA)
   ========================================= */

// 1. Guardar en el navegador
function guardarDatosLocales(nombre, telefono, cedula) {
  const datosUsuario = { nombre, telefono, cedula };
  localStorage.setItem("datos_usuario_menu", JSON.stringify(datosUsuario));
}

// 2. Cargar automáticamente al entrar
function cargarDatosLocales() {
  const datosGuardados = localStorage.getItem("datos_usuario_menu");
  if (datosGuardados) {
    const datos = JSON.parse(datosGuardados);

    const inName = document.getElementById("client-name");
    const inPhone = document.getElementById("client-phone");
    const inCedula = document.getElementById("client-id");

    // Solo rellenar si los inputs existen en la página
    if (inName) inName.value = datos.nombre || "";
    if (inPhone) inPhone.value = datos.telefono || "";
    if (inCedula) inCedula.value = datos.cedula || "";
  }
}

/* =========================================
   NAVEGACIÓN Y BÚSQUEDA
   ========================================= */

function registerAppListeners() {
  const categoryLinks = document.querySelectorAll("nav a.category-link");
  const productSections = document.querySelectorAll(
    "#product-content-wrapper section.product-section"
  );
  const header = document.querySelector("header");
  const activeClasses = ["text-red-600", "border-red-600"];
  const inactiveClasses = ["text-gray-500", "border-transparent"];

  const isSearchPage = new URLSearchParams(window.location.search).has(
    "busqueda"
  );

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
    const activeLink = document.querySelector(
      `nav a.category-link[href="${currentHash}"]`
    );
    if (activeLink) activeLink.click();
  }

  categoryLinks.forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const targetId = this.getAttribute("href");

      if (isSearchPage) {
        window.location.href =
          "menu.php" + (targetId === "#todos" ? "" : targetId);
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
          if (section.id !== "search-results") section.style.display = "block";
        });
        sectionToScrollTo =
          document.getElementById("desayunos") ||
          (productSections.length > 0 ? productSections[0] : null);
      } else {
        productSections.forEach((section) => (section.style.display = "none"));
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
        const banner = document.getElementById("banner-dinamico");
        const bannerHeight = banner ? banner.offsetHeight : 0;
        const totalOffset = headerHeight + bannerHeight;

        const elementPosition = sectionToScrollTo.getBoundingClientRect().top;
        const offsetPosition =
          elementPosition + window.scrollY - totalOffset - 16;
        window.scrollTo({ top: offsetPosition, behavior: "smooth" });
      } else {
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    });
  });

  const searchTrigger = document.getElementById("mobile-search-trigger");
  const searchInput = document.getElementById("mobile-search-input");
  const searchContainer = document.getElementById("mobile-search-form");

  if (searchTrigger && searchInput && searchContainer) {
    searchTrigger.addEventListener("click", (e) => {
      e.preventDefault();
      const header = document.querySelector("header");
      const headerHeight = header ? header.offsetHeight : 0;
      const banner = document.getElementById("banner-dinamico");
      const bannerHeight =
        banner && !banner.classList.contains("hidden")
          ? banner.offsetHeight
          : 0;

      const totalOffset = headerHeight + bannerHeight;
      const elementPosition = searchContainer.getBoundingClientRect().top;
      const offsetPosition =
        elementPosition + window.scrollY - totalOffset - 16;

      window.scrollTo({ top: offsetPosition, behavior: "smooth" });
      searchInput.focus();
    });
  }

  const modalBtn = document.getElementById("btn-add-modal-selection");
  if (modalBtn) {
    const newBtn = modalBtn.cloneNode(true);
    modalBtn.parentNode.replaceChild(newBtn, modalBtn);
    newBtn.addEventListener("click", addModalSelectionToCart);
  }

  const openCartDesktop = document.getElementById("open-cart-btn-desktop");
  const openCartMobile = document.getElementById("open-cart-btn-mobile");
  const closeCartBtn = document.getElementById("close-cart-btn");
  const cartBackdrop = document.getElementById("cart-backdrop");
  const btnSendOrder = document.getElementById("btn-send-order");

  if (openCartDesktop) openCartDesktop.addEventListener("click", openCart);
  if (openCartMobile) openCartMobile.addEventListener("click", openCart);
  if (closeCartBtn) closeCartBtn.addEventListener("click", closeCart);
  if (cartBackdrop) cartBackdrop.addEventListener("click", closeCart);
  if (btnSendOrder) btnSendOrder.addEventListener("click", sendOrder);
}

/* =========================================
   INICIALIZACIÓN (MAIN)
   ========================================= */

document.addEventListener("DOMContentLoaded", () => {
  inicializarAnunciosClicables();
  inicializarCarruselAutomatico();
  loadCart();
  updateCartBadge();
  setupCartListener();

  // NUEVO: Intentar cargar datos del usuario al iniciar la página
  cargarDatosLocales();

  const productContentWrapper = document.getElementById(
    "product-content-wrapper"
  );
  if (productContentWrapper) {
    originalMainContent = productContentWrapper.innerHTML;
  }

  const desktopInput = document.getElementById("desktop-search-input");
  const mobileInput = document.getElementById("mobile-search-input");
  const categoryNav = document.getElementById("category-nav-section");

  const handleSearch = (query) => {
    if (query.trim() === "") {
      if (productContentWrapper)
        productContentWrapper.innerHTML = originalMainContent;
      if (categoryNav) categoryNav.style.display = "block";
      registerAppListeners();
      return;
    }

    if (categoryNav) categoryNav.style.display = "none";
    if (productContentWrapper) {
      productContentWrapper.innerHTML =
        '<div class="text-center py-10"><i class="fa fa-spinner fa-spin text-3xl text-red-500"></i><p class="mt-2 text-gray-500">Buscando...</p></div>';
    }

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
            '<p class="text-center text-red-500 py-10">Error al cargar resultados.</p>';
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
      if (cartSidebar && !cartSidebar.classList.contains("translate-x-full")) {
        closeCart();
      } else if (modal && !modal.classList.contains("invisible")) {
        closeModal();
      }
    }
  });
});
