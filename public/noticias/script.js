// Este script está preparado para futuras interacciones, como filtros dinámicos.
// Por ahora, asegura que la página sea interactiva y se cargue correctamente.

document.addEventListener('DOMContentLoaded', function() {
    
    console.log("Página de noticias cargada.");

    // Animación sutil para las tarjetas de noticias al aparecer en pantalla
    const newsCards = document.querySelectorAll('.news-card');

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });

    newsCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(card);
    });

});
