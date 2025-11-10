// js/script.js
document.addEventListener('DOMContentLoaded', () => {
    const hamburgerMenu = document.querySelector('.hamburger-menu');
    const navLinks = document.querySelector('.nav-links');

    hamburgerMenu.addEventListener('click', () => {
        hamburgerMenu.classList.toggle('active');
        navLinks.classList.toggle('active');
    });

    // Cierra el menú cuando se hace clic en un enlace (en móvil)
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (navLinks.classList.contains('active')) {
                hamburgerMenu.classList.remove('active');
                navLinks.classList.remove('active');
            }
        });
    });

    // Smooth scroll para los enlaces de navegación
    document.querySelectorAll('nav a').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();

            const targetId = this.getAttribute('href');
            // Si el targetId es solo '#', se refiere al inicio de la página,
            // si no, se refiere a una sección específica
            const targetElement = targetId === '#' ? document.body : document.querySelector(targetId);

            targetElement.scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    
    // Ejemplo de añadir funcionalidad a los botones "Añadir"
    const addButtons = document.querySelectorAll('.add-to-cart');
    addButtons.forEach(button => {
        button.addEventListener('click', () => {
            const item = button.closest('.menu-item');
            const itemName = item.querySelector('h3').textContent;
            alert(`"${itemName}" ha sido añadido (funcionalidad de carrito a implementar).`);
        });
    });
});


   // Función para abrir el modal y poblarlo con datos
    function openModal(productData) {
      const modal = document.getElementById('product-modal');

      // Llenar el contenido del modal
      document.getElementById('modal-image').src = productData.foto;
      document.getElementById('modal-name').textContent = productData.nombre;
      document.getElementById('modal-price').innerHTML = productData.precio;
      document.getElementById('modal-description').textContent = productData.descripcion;

      // Mostrar el modal
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    // Función para cerrar el modal
    function closeModal() {
      const modal = document.getElementById('product-modal');
      modal.classList.add('hidden');
      document.body.style.overflow = '';
    }

    // Cierra el modal si se pulsa fuera de él
    document.getElementById('product-modal').addEventListener('click', (e) => {
      if (e.target.id === 'product-modal') {
        closeModal();
      }
    });

    // Cierra el modal si se pulsa ESC
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && !document.getElementById('product-modal').classList.contains('hidden')) {
        closeModal();
      }
    });