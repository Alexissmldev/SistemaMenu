/* ======================================================================
  Variables Globales y Helpers
====================================================================== */

/** * Guardará el precio base (ej: 150.50) del producto que está en el modal.
 */
let modalBasePrice = 0;

/**
 * Formatea un número como moneda local (Bolívares).
 * Ej: 1500.5 -> "Bs. 1.500,50"
 * @param {number} number - El número a formatear
 */
function formatCurrency(number) {
  // 'es-VE' usa '.' para miles y ',' para decimales.
  const formatted = number.toLocaleString("es-VE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  return "Bs. " + formatted;
}

/**
 * Actualiza el texto del precio en el modal basado en la cantidad actual.
 */
function updateModalPrice() {
  const quantityDisplay = document.getElementById("quantity-display");
  const priceDisplay = document.getElementById("modal-price");

  if (!quantityDisplay || !priceDisplay) return;

  const quantity = parseInt(quantityDisplay.textContent);
  const totalPrice = modalBasePrice * quantity;

  priceDisplay.innerHTML = formatCurrency(totalPrice);
}

/* ======================================================================
  Funciones Globales para el Modal (Llamadas desde HTML)
====================================================================== */

/**
 * Función para cerrar el modal
 */
function closeModal() {
  const modal = document.getElementById("product-modal");
  if (modal) {
    modal.classList.add("hidden");
    document.body.style.overflow = ""; // Devuelve el scroll al body
    modalBasePrice = 0; // Resetea el precio base
  }
}

/**
 * Función para abrir el modal y poblarlo con datos
 * @param {object} productData - Datos del producto
 */
function openModal(productData) {
  const modal = document.getElementById("product-modal");
  if (!modal) return;

  // --- ¡CAMBIO IMPORTANTE! ---
  // Guardamos el precio base (número) en nuestra variable global
  modalBasePrice = productData.precio_raw;

  // Llenar el contenido del modal
  document.getElementById("modal-image").src = productData.foto;
  document.getElementById("modal-name").textContent = productData.nombre;
  document.getElementById("modal-description").textContent = productData.descripcion;

  // Usamos el precio pre-formateado que viene del PHP
  document.getElementById("modal-price").innerHTML = productData.precio_display;

  // Resetear la cantidad a 1
  document.getElementById("quantity-display").textContent = "1";

  // Mostrar el modal
  modal.classList.remove("hidden");
  document.body.style.overflow = "hidden"; // Quita el scroll del body
}

/* ======================================================================
  Listeners de Eventos Principales (DOMContentLoaded)
====================================================================== */
document.addEventListener("DOMContentLoaded", () => {
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
        const targetElement = targetId === "#" ? document.body : document.querySelector(targetId);
        if (targetElement) {
          targetElement.scrollIntoView({ behavior: "smooth" });
        }
      } catch (error) {
        console.error("Error en smooth scroll:", error);
      }
    });
  });

  // --- Lógica de ejemplo (botones .add-to-cart fuera del modal) ---
  const addButtons = document.querySelectorAll(".add-to-cart");
  addButtons.forEach((button) => {
    button.addEventListener("click", () => {
      const item = button.closest(".menu-item");
      if (item) {
        const itemName = item.querySelector("h3").textContent;
        alert(`"${itemName}" ha sido añadido (funcionalidad de carrito a implementar).`);
      }
    });
  });

  // --- ¡NUEVO! Lógica del Carrito en el Modal ---

  const modal = document.getElementById("product-modal");
  const btnMinus = document.getElementById("btn-minus");
  const btnPlus = document.getElementById("btn-plus");
  const quantityDisplay = document.getElementById("quantity-display");
  const btnAddToCart = document.getElementById("btn-add-to-cart");

  if (modal && btnMinus && btnPlus && quantityDisplay && btnAddToCart) {
    // --- Lógica de Cantidad (+) ---
    btnPlus.addEventListener("click", () => {
      let currentQuantity = parseInt(quantityDisplay.textContent);
      currentQuantity++;
      quantityDisplay.textContent = currentQuantity;

      // ¡Actualiza el precio!
      updateModalPrice();
    });

    // --- Lógica de Cantidad (-) ---
    btnMinus.addEventListener("click", () => {
      let currentQuantity = parseInt(quantityDisplay.textContent);
      if (currentQuantity > 1) {
        // No permitir bajar de 1
        currentQuantity--;
        quantityDisplay.textContent = currentQuantity;

        // ¡Actualiza el precio!
        updateModalPrice();
      }
    });

    // --- Lógica de "Añadir al Carrito" ---
    btnAddToCart.addEventListener("click", () => {
      const quantity = quantityDisplay.textContent;
      const productName = document.getElementById("modal-name").textContent;
      const finalPrice = document.getElementById("modal-price").innerHTML;

      alert(`¡Añadido!\n${quantity} x ${productName}\nTotal: ${finalPrice}`);

      closeModal();
    });
  } else {
    console.warn("Advertencia: No se encontraron todos los elementos del modal (btn-minus, btn-plus, etc).");
  }

  // --- Listeners para cerrar el modal ---

  if (modal) {
    // Cierra si se pulsa fuera (fondo oscuro)
    modal.addEventListener("click", (e) => {
      if (e.target.id === "product-modal") {
        closeModal();
      }
    });
  }

  // Cierra si se pulsa la tecla ESC
  document.addEventListener("keydown", (e) => {
    const modal = document.getElementById("product-modal");
    if (modal && e.key === "Escape" && !modal.classList.contains("hidden")) {
      closeModal();
    }
  });
});
