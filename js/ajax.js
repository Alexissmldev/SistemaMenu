document.addEventListener("DOMContentLoaded", function () {
  document.body.addEventListener("submit", function (e) {
    let form = e.target.closest(".FormularioAjax");

    if (form) {
      e.preventDefault();

      let data = new FormData(form);
      let method = form.getAttribute("method");
      let action = form.getAttribute("action");

      fetch(action, {
        method: method,
        body: data,
      })
        .then((res) => {
          if (!res.ok) {
            throw new Error("Respuesta del servidor no fue OK");
          }
          return res.json();
        })
        .then((respuesta) => {
          Swal.fire({
            icon: respuesta.tipo,
            title: respuesta.titulo,
            text: respuesta.texto,
            confirmButtonText: "Aceptar",
          }).then((result) => {
            if (result.isConfirmed && respuesta.tipo === "success") {
              // --- MODIFICACIÓN AQUÍ ---
              // Si el PHP envió una variable "url", redireccionamos allí
              if (respuesta.url) {
                window.location.href = respuesta.url;
              } else {
                // Si no, hacemos lo de siempre (recargar)
                location.reload();
              }
              // -------------------------
            }
          });
        })
        .catch((error) => {
          console.error("Error en fetch:", error);
          Swal.fire({
            icon: "error",
            title: "Error de Comunicación",
            text: "No se pudo conectar con el servidor. Revisa la consola para más detalles.",
          });
        });
    }
  });
});
