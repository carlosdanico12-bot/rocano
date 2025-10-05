// Este script añade animaciones sutiles a las tarjetas de propuestas
// a medida que el usuario se desplaza, mejorando la experiencia visual.

document.addEventListener('DOMContentLoaded', function() {
    
    console.log("Página de propuestas cargada.");

    const animatedSections = document.querySelectorAll('.animated-section');

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15
    });

    animatedSections.forEach(section => {
        observer.observe(section);
    });

});
