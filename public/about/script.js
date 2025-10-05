// Este script añade animaciones sutiles a las secciones de la página
// a medida que el usuario se desplaza, mejorando la experiencia visual.

document.addEventListener('DOMContentLoaded', function() {
    
    console.log("Página 'Sobre Nosotros' cargada.");

    const animatedSections = document.querySelectorAll('.animated-section');

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            // Cuando la sección entra en el viewport (la pantalla del usuario)
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); // Dejar de observar una vez que es visible
            }
        });
    }, {
        threshold: 0.15 // La animación se dispara cuando el 15% de la sección es visible
    });

    // Observar cada una de las secciones marcadas para animar
    animatedSections.forEach(section => {
        observer.observe(section);
    });

});

