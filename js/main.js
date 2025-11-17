//  INICIALIZACIÓN
document.addEventListener("DOMContentLoaded", () => {
  // Menú de Perfil
  const profileMenuButton = document.getElementById("profile-menu-button");
  const profileMenu = document.getElementById("profile-menu");

  if (profileMenuButton && profileMenu) {
    profileMenuButton.addEventListener("click", (event) => {
      event.stopPropagation();
      profileMenu.classList.toggle("hidden");
    });

    document.addEventListener("click", (event) => {
      if (
        !profileMenu.classList.contains("hidden") &&
        !profileMenu.contains(event.target) &&
        !profileMenuButton.contains(event.target)
      ) {
        profileMenu.classList.add("hidden");
      }
    });
  }

  // Menú Móvil
  const mobileMenuButton = document.getElementById("mobile-menu-button");
  const mobileMenuPanel = document.getElementById("mobile-menu-panel");
  const mobileMenuBackdrop = document.getElementById("mobile-menu-backdrop");
  const mobileMenuCloseButton = document.getElementById(
    "mobile-menu-close-button"
  );

  const openMobileMenu = () => {
    if (mobileMenuPanel && mobileMenuBackdrop) {
      mobileMenuBackdrop.classList.remove("hidden");
      setTimeout(() => mobileMenuBackdrop.classList.add("opacity-100"), 10); // Pequeño delay para la transición

      mobileMenuPanel.classList.remove("-translate-x-full");
      mobileMenuPanel.classList.add("translate-x-0");
    }
  };

  const closeMobileMenu = () => {
    if (mobileMenuPanel && mobileMenuBackdrop) {
      mobileMenuBackdrop.classList.remove("opacity-100");
      setTimeout(() => mobileMenuBackdrop.classList.add("hidden"), 300);

      mobileMenuPanel.classList.remove("translate-x-0");
      mobileMenuPanel.classList.add("-translate-x-full");
    }
  };

  mobileMenuButton?.addEventListener("click", openMobileMenu);
  mobileMenuCloseButton?.addEventListener("click", closeMobileMenu);
  mobileMenuBackdrop?.addEventListener("click", closeMobileMenu);

  //  LÓGICA PARA CERRAR MODALES
  document.body.addEventListener("click", (event) => {
    const target = event.target;
    const backdrop = target.closest('[data-role="modal-backdrop"]');

    if (
      target.closest(".modal-close-trigger") ||
      (backdrop && backdrop === target)
    ) {
      closeModal();
    }
  });

  window.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeModal();
      closeMobileMenu();
    }
  });
});

// FUNCIONES GLOBALES

// Cierra el modal activo
function closeModal() {
  const modalContainer = document.getElementById("modal-container");
  if (modalContainer) {
    const modalToClose = modalContainer.querySelector(".fixed.inset-0");
    if (modalToClose) {
      modalToClose.classList.add("opacity-0");
      setTimeout(() => {
        modalContainer.innerHTML = "";
      }, 300);
    }
  }
}

//Abre un modal cargando su contenido vía AJAX.

async function openModal(vista, id, id_name, initCallback = null) {
  const modalContainer = document.getElementById("modal-container");
  if (!modalContainer)
    return console.error("El div #modal-container no existe.");

  modalContainer.innerHTML =
    '<div class="fixed inset-0 flex items-center justify-center bg-black/50 z-50"><p class="text-white rounded-lg px-4 py-2">Cargando...</p></div>';

  try {
    const url = `index.php?vista=${vista}${id ? `&${id_name}=${id}` : ""}&ajax=true`;
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Error del servidor: ${response.status}`);
    modalContainer.innerHTML = await response.text();

    setTimeout(() => {
      const modal = modalContainer.querySelector(
        '[data-animation="fade-in-scale"]'
      );
      if (modal) {
        const modalContent = modal.querySelector("#modalContent");
        modal.classList.remove("opacity-0");
        if (modalContent) modalContent.classList.remove("scale-95");
      }
    }, 10);

    if (initCallback && typeof window[initCallback] === "function") {
      window[initCallback]();
    }
  } catch (error) {
    console.error(`Fallo al abrir modal:`, error);
    modalContainer.innerHTML = `<div class="fixed inset-0 flex items-center justify-center z-50"><p class="text-white bg-red-600 rounded-lg px-4 py-2">Error al cargar.</p></div>`;
    setTimeout(() => closeModal(), 2000);
  }
}

//  FUNCIONES DE INICIALIZACIÓN PARA MODALES ESPECÍFICOS

//Inicializa todos los scripts para el modal de 'Nuevo Producto'.
function initProductModalScripts() {
  //Selección de todos los elementos del modal
  const categorySelectorContainer = document.getElementById(
    "categorySelectorContainer"
  );
  const newCategoryForm = document.getElementById("newCategoryForm");
  const addCategoryBtn = document.getElementById("addCategoryBtn");
  const cancelCategoryBtn = document.getElementById("cancelCategoryBtn");
  const saveCategoryBtn = document.getElementById("saveCategoryBtn");
  const newCategoryAlerts = document.getElementById("newCategoryAlerts");

  // Función para MOSTRAR el formulario de nueva categoría
  const showNewCategoryForm = () => {
    if (categorySelectorContainer)
      categorySelectorContainer.classList.add("hidden");
    if (newCategoryForm) newCategoryForm.classList.remove("hidden");
    if (newCategoryAlerts) newCategoryAlerts.innerHTML = "";

    const input = document.getElementById("new_category_name");
    if (input) input.focus();
  };

  // Función para OCULTAR el formulario de nueva categoría
  const hideNewCategoryForm = () => {
    if (newCategoryForm) newCategoryForm.classList.add("hidden");
    if (categorySelectorContainer)
      categorySelectorContainer.classList.remove("hidden");

    const nameInput = document.getElementById("new_category_name");
    if (nameInput) nameInput.value = "";
    if (newCategoryAlerts) newCategoryAlerts.innerHTML = "";
  };

  if (addCategoryBtn) {
    addCategoryBtn.addEventListener("click", showNewCategoryForm);
  }

  if (cancelCategoryBtn) {
    cancelCategoryBtn.addEventListener("click", hideNewCategoryForm);
  }

  if (saveCategoryBtn) {
    saveCategoryBtn.addEventListener("click", async () => {
      if (!newCategoryAlerts) return;
      newCategoryAlerts.innerHTML = "";

      const nameEl = document.getElementById("new_category_name");
      const name = nameEl ? nameEl.value.trim() : "";

      if (!name) {
        newCategoryAlerts.innerHTML =
          '<p class="text-xs text-red-600">El nombre de la categoría es obligatorio.</p>';
        return;
      }

      saveCategoryBtn.disabled = true;
      const originalText = saveCategoryBtn.innerHTML;
      saveCategoryBtn.innerHTML = "Guardando...";

      try {
        const formData = new FormData();
        formData.append("categoria_nombre", name);

        const resp = await fetch("./php/categoria_guardar.php", {
          method: "POST",
          body: formData,
        });

        const data = await resp.json();
        if (data.tipo === "success" && data.nuevaCategoria) {
          const categorySelect = document.getElementById("producto_categoria");
          if (categorySelect) {
            const option = new Option(
              data.nuevaCategoria.nombre,
              data.nuevaCategoria.id,
              true,
              true
            );
            categorySelect.appendChild(option);
          } else {
            console.warn(
              "No se encontró #producto_categoria para agregar la nueva opción."
            );
          }

          hideNewCategoryForm();
          const mainAlerts = document.querySelector("#productForm .form-rest");
          if (mainAlerts) {
            mainAlerts.innerHTML = `<div class="p-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    Categoría '${data.nuevaCategoria.nombre}' creada y seleccionada.
                </div>`;
            setTimeout(() => (mainAlerts.innerHTML = ""), 4000);
          }
        } else {
          const msg = data.texto || "Error en el servidor";
          newCategoryAlerts.innerHTML = `<p class="text-xs text-red-600">Error: ${msg}</p>`;
        }
      } catch (error) {
        console.error("Error en fetch:", error);
        newCategoryAlerts.innerHTML =
          '<p class="text-xs text-red-600">Error de conexión. Inténtalo de nuevo.</p>';
      } finally {
        saveCategoryBtn.disabled = false;
        saveCategoryBtn.innerHTML = originalText;
      }
    });
  }
  initImagePreview("formNuevoProducto");
}

//previsualización de imágenes.
function initImagePreview(formId) {
  const form = document.getElementById(formId);
  if (!form) return;
  const fileInput = form.querySelector('input[type="file"]');
  const dropZone = form.querySelector(".drop-zone");
  if (!fileInput || !dropZone) return;

  const content = dropZone.querySelector(".drop-zone-content");

  fileInput.addEventListener("change", () =>
    displayPreview(fileInput.files[0])
  );

  function displayPreview(file) {
    if (!file) {
      if (content) content.style.display = "block";
      const existingPreview = dropZone.querySelector(".preview-container");
      if (existingPreview) existingPreview.remove();
      return;
    }
    if (content) content.style.display = "none";

    let previewContainer = dropZone.querySelector(".preview-container");
    if (!previewContainer) {
      previewContainer = document.createElement("div");
      previewContainer.className = "preview-container relative";
      dropZone.appendChild(previewContainer);
    }
    previewContainer.innerHTML = "";

    const reader = new FileReader();
    reader.onload = (e) => {
      const img = document.createElement("img");
      img.src = e.target.result;
      img.className = "max-h-40 rounded";
      previewContainer.appendChild(img);

      const removeBtn = document.createElement("button");
      removeBtn.type = "button";
      removeBtn.innerHTML = "&times;";
      removeBtn.className =
        "absolute top-0 right-0 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center";
      removeBtn.onclick = () => {
        fileInput.value = "";
        displayPreview(null);
      };
      previewContainer.appendChild(removeBtn);
    };
    reader.readAsDataURL(file);
  }
}

//Inicializa los scripts para el modal de 'Actualizar Producto'.
function initProductUpdateModal() {
  const selectContainer = document.querySelector("#custom-select-container");
  if (!selectContainer) {
    console.warn(
      "No se encontró el contenedor del select personalizado en el modal."
    );
    return;
  }

  const selectButton = selectContainer.querySelector("#custom-select-button");
  const selectPanel = selectContainer.querySelector("#custom-select-panel");
  const selectLabel = selectContainer.querySelector("#custom-select-label");
  const hiddenInput = selectContainer.querySelector(
    "#producto_categoria_hidden"
  );
  const options = selectPanel.querySelectorAll(".custom-select-option");

  //  Mostrar o oculta el panel de opciones
  selectButton.addEventListener("click", (e) => {
    e.stopPropagation();
    selectPanel.classList.toggle("hidden");
  });

  //Asigna la funcionalidad a cada opción del select.
  options.forEach((option) => {
    option.addEventListener("click", () => {
      const value = option.getAttribute("data-value");
      const label = option.getAttribute("data-label");

      // Actualiza el valor del input oculto y el texto visible del botón.
      hiddenInput.value = value;
      selectLabel.textContent = label;
      selectPanel.classList.add("hidden");

      const currentTick = selectPanel.querySelector(".selected-tick");
      if (currentTick) currentTick.remove();

      const tickSVG =
        '<span class="selected-tick absolute inset-y-0 left-0 flex items-center pl-3 text-indigo-600"><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.052-.143z" clip-rule="evenodd" /></svg></span>';
      option.insertAdjacentHTML("beforeend", tickSVG);
    });
  });

  //Cerrar el panel si se hace clic fuera de él.
  document.addEventListener("click", (e) => {
    if (
      !selectContainer.contains(e.target) &&
      !selectPanel.classList.contains("hidden")
    ) {
      selectPanel.classList.add("hidden");
    }
  });

  // --- LÓGICA PARA EL INTERRUPTOR DE ESTADO (TOGGLE) ---
  const estadoToggle = document.getElementById("estado_toggle");
  const productoEstadoInput = document.getElementById("producto_estado");

  if (estadoToggle && productoEstadoInput) {
    estadoToggle.addEventListener("change", function () {
      productoEstadoInput.value = this.checked ? "1" : "0";
    });
  }
}

//Inicializa todos los scripts para el modal de 'Actualizar Imagen de Producto'.
function initProductImageModalScripts() {
  initImagePreview("formActualizarImagen");
}

function initCategoryUpdateModal() {
  const estadoToggle = document.getElementById("categoria_estado_toggle");
  const estadoInput = document.getElementById("categoria_estado");

  if (estadoToggle && estadoInput) {
    estadoToggle.addEventListener("change", function () {
      // Actualiza el valor del campo oculto a 1 (si está marcado) o 0 (si no).
      estadoInput.value = this.checked ? "1" : "0";
    });
  }
}

// BLOQUE PARA ELIMIAR USUARIO, PRODUTOS Y CATEGORIAS

//Gestiona la eliminación de un usuario con una alerta de confirmación.
function eliminarUsuario(id, nombre) {
  Swal.fire({
    title: "¿Estás seguro?",
    text: `Deseas eliminar al usuario "${nombre}". Esta acción es irreversible.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, ¡eliminar!",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      let data = new FormData();
      data.append("user_id_del", id);

      fetch("./php/usuario_eliminar.php", { method: "POST", body: data })
        .then((res) => res.json())
        .then((res) => {
          return Swal.fire({
            icon: res.tipo,
            title: res.titulo,
            text: res.texto,
          });
        })
        .then((res) => {
          if (res.isConfirmed || res.isDismissed) {
            window.location.reload();
          }
        })
        .catch((err) => {
          console.error("Error:", err);
          Swal.fire("Error", "No se pudo comunicar con el servidor.", "error");
        });
    }
  });
}

/**
 * @param {number} id
 */
function eliminarImagen(id) {
  Swal.fire({
    title: "¿Estás seguro?",
    text: "Esta acción eliminará la imagen del producto de forma permanente. No se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, ¡eliminar!",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      let data = new FormData();
      data.append("img_del_id", id);

      fetch("./php/producto_img_eliminar.php", {
        method: "POST",
        body: data,
      })
        .then((res) => res.json())
        .then((respuesta) => {
          return Swal.fire({
            icon: respuesta.tipo,
            title: respuesta.titulo,
            text: respuesta.texto,
          });
        })
        .then(() => {
          location.reload();
        })
        .catch((err) => {
          console.error("Error en fetch:", err);
          Swal.fire("Error", "No se pudo comunicar con el servidor.", "error");
        });
    }
  });
}

/**
 * @param {number} id
 * @param {string} nombre
 */
function eliminarProducto(id, nombre) {
  Swal.fire({
    title: `¿Eliminar "${nombre}"?`,
    text: "Esta acción es irreversible y borrará el producto permanentemente.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, ¡eliminar!",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      let data = new FormData();
      data.append("product_id_del", id);

      fetch("./php/producto_eliminar.php", {
        method: "POST",
        body: data,
      })
        .then((res) => res.json())
        .then((respuesta) => {
          Swal.fire({
            icon: respuesta.tipo,
            title: respuesta.titulo,
            text: respuesta.texto,
          }).then(() => {
            if (respuesta.tipo === "success") {
              location.reload();
            }
          });
        })
        .catch((err) => {
          console.error("Error en fetch:", err);
          Swal.fire("Error", "No se pudo comunicar con el servidor.", "error");
        });
    }
  });
}
/**
 * @param {number} id
 * @param {string} nombre
 */
function eliminarCategoria(id, nombre) {
  Swal.fire({
    title: `¿Eliminar "${nombre}"?`,
    text: "No podrás eliminarla si tiene productos asociados. Esta acción es irreversible.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, ¡eliminar!",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      let data = new FormData();
      data.append("category_id_del", id);

      fetch("./php/categoria_eliminar.php", {
        method: "POST",
        body: data,
      })
        .then((res) => res.json())
        .then((respuesta) => {
          Swal.fire({
            icon: respuesta.tipo,
            title: respuesta.titulo,
            text: respuesta.texto,
          }).then(() => {
            if (respuesta.tipo === "success") {
              location.reload();
            }
          });
        })
        .catch((err) => {
          console.error("Error en fetch:", err);
          Swal.fire("Error", "No se pudo comunicar con el servidor.", "error");
        });
    }
  });
}

/**
 * @param {number} id
 * @param {string} mensaje
 */
function eliminarAnuncio(id, mensaje) {
  Swal.fire({
    title: "¿Estás seguro?",
    html: `Se eliminará el anuncio: <br><strong>"${mensaje}"</strong><br><br>Esta acción es irreversible.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, ¡eliminar!",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      let data = new FormData();
      data.append("ad_id_del", id);

      fetch("./php/anuncio_eliminar.php", {
        method: "POST",
        body: data,
      })
        .then((res) => res.json())
        .then((respuesta) => {
          Swal.fire({
            icon: respuesta.tipo,
            title: respuesta.titulo,
            text: respuesta.texto,
          }).then(() => {
            if (respuesta.tipo === "success") {
              location.reload();
            }
          });
        })
        .catch((err) => {
          console.error("Error en fetch:", err);
          Swal.fire("Error", "No se pudo comunicar con el servidor.", "error");
        });
    }
  });
}

function eliminarPromocion(id, nombre) {
  Swal.fire({
    title: "¿Estás seguro?",
    text: `Se eliminará la promoción: "${nombre}". Esta acción no se puede revertir.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      let data = new FormData();
      data.append("promo_id_del", id);

      fetch("./php/promo_eliminar.php", {
        method: "POST",
        body: data,
      })
        .then((res) => res.json())
        .then((respuesta) => {
          Swal.fire({
            icon: respuesta.tipo,
            title: respuesta.titulo,
            text: respuesta.texto,
          }).then(() => {
            if (respuesta.tipo === "success") {
              location.reload();
            }
          });
        })
        .catch((err) => {
          Swal.fire("Error", "No se pudo comunicar con el servidor.", "error");
        });
    }
  });
}
