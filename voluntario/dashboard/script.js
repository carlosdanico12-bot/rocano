// JavaScript para el Dashboard del Voluntario
// En el futuro, aquí se podría añadir lógica para gráficos dinámicos o notificaciones.

document.addEventListener('DOMContentLoaded', function() {
    console.log("Dashboard del Voluntario cargado correctamente.");

    // Ejemplo de interactividad: animación escalonada de las tarjetas al cargar
    const cards = document.querySelectorAll('.stat-card, .action-card');
    
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        
        // Disparar la animación
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });
});
