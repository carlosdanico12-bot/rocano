// ===============================================
//  PUBLIC SITE GLOBAL SCRIPT V.2.0
//  Description: Maneja interacciones comunes del
//  sitio público como el menú móvil y las
//  animaciones de scroll.
// ===============================================

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Lógica del Menú Móvil (Hamburguesa) ---
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.getElementById('main-nav');

    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mainNav.classList.toggle('active');
        });
    }

    // --- Lógica de Animaciones al Hacer Scroll ---
    // Selecciona todos los elementos que tengan la clase 'animated-section'
    const animatedSections = document.querySelectorAll('.animated-section');

    // Si no hay elementos para animar, no hacemos nada más
    if (animatedSections.length > 0) {
        const observer = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                // Cuando la sección entra en la pantalla
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    // Dejar de observar el elemento una vez que la animación se ha disparado
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15 // La animación se activa cuando el 15% del elemento es visible
        });

        // Aplicar el observador a cada sección
        animatedSections.forEach(section => {
            observer.observe(section);
        });
    }

    // --- Lógica del Formulario de Contacto (si existe en la página) ---
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const message = document.getElementById('message').value.trim();

            if (name === '' || email === '' || message === '') {
                alert('Por favor, completa todos los campos obligatorios.');
                e.preventDefault();
                return;
            }

            // Validación simple de email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Por favor, ingresa un correo electrónico válido.');
                e.preventDefault();
                return;
            }
        });
    }

});

