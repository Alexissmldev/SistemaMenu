document.addEventListener("DOMContentLoaded", function () {
  // --- 0. NOTIFICACIONES TOAST (Sutiles) ---
  function mostrarNotificacion(mensaje, tipo = "error") {
    const container = document.getElementById("toast-container");
    const bgColor = tipo === "error" ? "bg-red-500" : "bg-green-500";
    const icon = tipo === "error" ? "fa-exclamation-circle" : "fa-check-circle";

    const toast = document.createElement("div");
    toast.className = `${bgColor} text-white px-4 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-y-5 opacity-0 flex items-center gap-3 pointer-events-auto min-w-[250px]`;
    toast.innerHTML = `<i class="fas ${icon} text-lg"></i><span class="text-sm font-medium">${mensaje}</span>`;

    container.appendChild(toast);
    requestAnimationFrame(() =>
      toast.classList.remove("translate-y-5", "opacity-0")
    );
    setTimeout(() => {
      toast.classList.add("opacity-0", "translate-y-5");
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // --- 1. LÓGICA DE CATEGORÍA (MODO SILENCIOSO / AJAX) ---
  const categorySelectorContainer = document.getElementById(
    "categorySelectorContainer"
  );
  const newCategoryForm = document.getElementById("newCategoryForm");
  const addCategoryBtn = document.getElementById("addCategoryBtn");
  const cancelCategoryBtn = document.getElementById("cancelCategoryBtn");
  const saveCategoryBtn = document.getElementById("saveCategoryBtn");
  const newCategoryAlerts = document.getElementById("newCategoryAlerts");
  const categorySelect = document.getElementById("producto_categoria");

  // Mostrar form pequeño
  const showNewCategoryForm = () => {
    if (categorySelectorContainer)
      categorySelectorContainer.classList.add("hidden");
    if (newCategoryForm) newCategoryForm.classList.remove("hidden");
    if (newCategoryAlerts) newCategoryAlerts.innerHTML = "";
    const input = document.getElementById("new_category_name");
    if (input) input.focus();
  };

  // Ocultar form pequeño
  const hideNewCategoryForm = () => {
    if (newCategoryForm) newCategoryForm.classList.add("hidden");
    if (categorySelectorContainer)
      categorySelectorContainer.classList.remove("hidden");
    const nameInput = document.getElementById("new_category_name");
    if (nameInput) nameInput.value = "";
    if (newCategoryAlerts) newCategoryAlerts.innerHTML = "";
  };

  // Event Listeners Botones
  if (addCategoryBtn)
    addCategoryBtn.addEventListener("click", showNewCategoryForm);
  if (cancelCategoryBtn)
    cancelCategoryBtn.addEventListener("click", hideNewCategoryForm);

  // Lógica de Guardado
  if (saveCategoryBtn) {
    saveCategoryBtn.addEventListener("click", async () => {
      // Validación UI
      if (newCategoryAlerts) newCategoryAlerts.innerHTML = "";
      const nameEl = document.getElementById("new_category_name");
      const name = nameEl ? nameEl.value.trim() : "";

      if (!name) {
        newCategoryAlerts.innerHTML =
          '<p class="text-xs font-bold text-red-600"><i class="fas fa-exclamation-circle"></i> El nombre es obligatorio.</p>';
        nameEl.focus();
        return;
      }

      // Estado de carga
      saveCategoryBtn.disabled = true;
      const originalText = saveCategoryBtn.innerHTML;
      saveCategoryBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

      try {
        const formData = new FormData();
        formData.append("categoria_nombre", name);

        // Petición Fetch a categoria_guardar.php
        const resp = await fetch("./php/categoria_guardar.php", {
          method: "POST",
          body: formData,
        });

        const data = await resp.json();
        // Compatibilidad: buscamos data.nuevaCategoria o data.datos
        const datosRecibidos = data.nuevaCategoria || data.datos;

        if (data.tipo === "success" && datosRecibidos) {
          // A. Crear opción en el select y seleccionarla
          if (categorySelect) {
            const option = new Option(
              datosRecibidos.nombre, // Texto
              datosRecibidos.id, // Value
              true, // defaultSelected
              true // selected
            );
            categorySelect.appendChild(option);
          }

          // B. Restaurar vista
          hideNewCategoryForm();

          // C. Mostrar mensaje verde discreto (Sin alerta popup)
          const mainAlerts = document.querySelector("#productForm .form-rest");
          if (mainAlerts) {
            mainAlerts.innerHTML = `
                                <div class="p-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg border border-green-200 flex items-center animate-fade-in">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                    <span>Categoría <b>${datosRecibidos.nombre}</b> creada y seleccionada.</span>
                                </div>`;

            setTimeout(() => (mainAlerts.innerHTML = ""), 4000);
          }
        } else {
          // Error controlado
          const msg = data.texto || data.mensaje || "Error al guardar";
          newCategoryAlerts.innerHTML = `<p class="text-xs font-bold text-red-600">${msg}</p>`;
        }
      } catch (error) {
        console.error("Error:", error);
        newCategoryAlerts.innerHTML =
          '<p class="text-xs font-bold text-red-600">Error de conexión.</p>';
      } finally {
        saveCategoryBtn.disabled = false;
        saveCategoryBtn.innerHTML = originalText;
      }
    });
  }

  // --- 2. LÓGICA DE AUTOCOMPLETADO PERSONALIZADO (VARIANTES) ---
  const inputVarName = document.getElementById("input_variante_nombre");
  const dropdownList = document.getElementById("custom-dropdown-list");
  const dropdownItems = dropdownList.querySelectorAll(".dropdown-item");
  const noResultsItem = document.getElementById("no-results-item");

  function showDropdown() {
    dropdownList.classList.remove("hidden");
    noResultsItem.classList.add("hidden");
    if (inputVarName.value.trim() === "") {
      dropdownItems.forEach((item) => item.classList.remove("hidden"));
    } else {
      filterDropdown();
    }
  }

  function hideDropdown() {
    dropdownList.classList.add("hidden");
  }

  function filterDropdown() {
    const filter = inputVarName.value.toLowerCase();
    let hasVisibleItems = false;
    dropdownItems.forEach((item) => {
      const text = item.textContent.trim().toLowerCase();
      if (text.includes(filter)) {
        item.classList.remove("hidden");
        hasVisibleItems = true;
      } else {
        item.classList.add("hidden");
      }
    });
    if (!hasVisibleItems && filter !== "") {
      noResultsItem.classList.remove("hidden");
    } else {
      noResultsItem.classList.add("hidden");
    }
  }

  inputVarName.addEventListener("focus", showDropdown);
  inputVarName.addEventListener("input", () => {
    showDropdown();
    filterDropdown();
  });

  dropdownItems.forEach((item) => {
    item.addEventListener("click", function () {
      inputVarName.value = this.textContent.trim();
      hideDropdown();
      document.getElementById("input_variante_precio").focus();
    });
  });

  document.addEventListener("click", function (e) {
    if (!inputVarName.contains(e.target) && !dropdownList.contains(e.target)) {
      hideDropdown();
    }
  });

  // --- 3. LÓGICA AGREGAR VARIANTE A TABLA ---
  const btnAdd = document.getElementById("btn-add-variant");
  const inputPrice = document.getElementById("input_variante_precio");
  const basePriceInput = document.getElementById("producto_precio");
  const tableBody = document.getElementById("tabla-variantes-body");
  const hiddenContainer = document.getElementById("hidden-inputs-container");
  const emptyState = document.getElementById("row-empty-state");

  basePriceInput.addEventListener("input", function () {
    if (inputPrice.value === "") inputPrice.placeholder = this.value;
  });

  document.querySelectorAll(".quick-fill").forEach((btn) => {
    btn.addEventListener("click", () => {
      inputVarName.value = btn.dataset.val;
      hideDropdown();
      btn.classList.add("ring-2", "ring-orange-300");
      setTimeout(() => btn.classList.remove("ring-2", "ring-orange-300"), 200);
      inputPrice.focus();
    });
  });

  btnAdd.addEventListener("click", function () {
    const name = inputVarName.value.trim();
    let price = inputPrice.value.trim();

    if (name === "") {
      mostrarNotificacion(
        "Escribe o selecciona un nombre para la variante.",
        "error"
      );
      inputVarName.focus();
      return;
    }

    const existingVariants = document.querySelectorAll(
      'input[name="variante_nombre[]"]'
    );
    let isDuplicate = false;
    existingVariants.forEach((input) => {
      if (input.value.toLowerCase() === name.toLowerCase()) isDuplicate = true;
    });

    if (isDuplicate) {
      mostrarNotificacion(
        `La variante "${name}" ya está en la lista.`,
        "error"
      );
      inputVarName.classList.add("border-red-500", "bg-red-50");
      setTimeout(
        () => inputVarName.classList.remove("border-red-500", "bg-red-50"),
        1000
      );
      return;
    }

    if (price === "") price = basePriceInput.value || "0";
    if (emptyState) emptyState.style.display = "none";

    const rowId = "var-" + Date.now();
    const row = document.createElement("tr");
    row.className =
      "bg-white border-b hover:bg-gray-50 transition-colors animate-fade-in";
    row.id = rowId;
    row.innerHTML = `
            <td class="px-6 py-3 font-medium text-gray-900">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-orange-50 text-orange-800 border border-orange-100">
                    ${name}
                </span>
            </td>
            <td class="px-6 py-3 text-gray-600 font-semibold">$${price}</td>
            <td class="px-6 py-3 text-right">
                <button type="button" onclick="removeVariant('${rowId}')" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded hover:bg-red-50">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;
    tableBody.appendChild(row);

    const hiddenDiv = document.createElement("div");
    hiddenDiv.id = "inputs-" + rowId;
    hiddenDiv.innerHTML = `
            <input type="hidden" name="variante_nombre[]" value="${name}">
            <input type="hidden" name="variante_precio[]" value="${price}">
        `;
    hiddenContainer.appendChild(hiddenDiv);

    inputVarName.value = "";
    inputPrice.value = "";
    inputPrice.placeholder = basePriceInput.value || "0.00";
    inputVarName.focus();
  });

  window.removeVariant = function (id) {
    const row = document.getElementById(id);
    const inputs = document.getElementById("inputs-" + id);
    if (row) row.remove();
    if (inputs) inputs.remove();
    if (tableBody.querySelectorAll("tr").length <= 1) {
      if (emptyState) emptyState.style.display = "table-row";
    }
  };

  // --- 4. PREVIEW IMAGEN ---
  const imgInput = document.getElementById("producto_foto");
  const nameDisplay = document.getElementById("file-name-display");

  if (imgInput) {
    imgInput.addEventListener("change", function () {
      if (this.files[0]) {
        nameDisplay.textContent = this.files[0].name;
        nameDisplay.classList.remove("hidden");
      } else {
        nameDisplay.classList.add("hidden");
      }
    });
  }
  initImagePreview("productForm");
});
