// JavaScript para el Dashboard del Coordinador

document.addEventListener('DOMContentLoaded', function() {
    console.log("Dashboard del Coordinador cargado correctamente.");

    // 1. Animación escalonada de las tarjetas al cargar (reutilizado)
    const cards = document.querySelectorAll('.stat-card, .card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });

    // 2. Lógica para el Gráfico de Votantes (usando Chart.js)
    const ctx = document.getElementById('votantesChart');
    if (ctx) {
        // Datos de ejemplo para el gráfico. En una aplicación real, estos datos
        // se obtendrían mediante una llamada AJAX a un script PHP.
        const data = {
            labels: ['A Favor', 'Indecisos', 'En Contra'],
            datasets: [{
                label: 'Distribución de Votantes',
                data: [125, 45, 30], // Datos simulados
                backgroundColor: [
                    'rgba(40, 199, 111, 0.7)',  // Verde para "A Favor"
                    'rgba(255, 159, 67, 0.7)',  // Naranja para "Indecisos"
                    'rgba(230, 57, 70, 0.7)'   // Rojo para "En Contra"
                ],
                borderColor: [
                    'rgba(40, 199, 111, 1)',
                    'rgba(255, 159, 67, 1)',
                    'rgba(230, 57, 70, 1)'
                ],
                borderWidth: 1
            }]
        };

        new Chart(ctx, {
            type: 'doughnut', // Tipo de gráfico: dona
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
