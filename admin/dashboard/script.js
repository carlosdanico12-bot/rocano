document.addEventListener('DOMContentLoaded', function () {

    // --- Gráfico de Torta (Pie Chart) para Votantes ---
    const votersPieChartCtx = document.getElementById('votersPieChart');
    if (votersPieChartCtx) {
        // Datos de ejemplo (en una aplicación real, vendrían de la BD con AJAX)
        const votersData = {
            labels: ['A favor', 'Indecisos', 'En contra'],
            datasets: [{
                label: 'Distribución de Votantes',
                data: [150, 275, 45], // Datos de ejemplo
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',  // Verde
                    'rgba(255, 193, 7, 0.7)',   // Amarillo
                    'rgba(220, 53, 69, 0.7)'    // Rojo
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        };

        new Chart(votersPieChartCtx, {
            type: 'pie',
            data: votersData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
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


    // --- Gráfico de Líneas (Line Chart) para Progreso de Campaña ---
    const campaignLineChartCtx = document.getElementById('campaignLineChart');
    if (campaignLineChartCtx) {
        // Datos de ejemplo
        const lineChartData = {
            labels: ['Hace 6 días', 'Hace 5 días', 'Hace 4 días', 'Hace 3 días', 'Ayer', 'Hoy'],
            datasets: [{
                label: 'Nuevos Votantes Registrados',
                data: [12, 19, 10, 25, 18, 28], // Datos de ejemplo
                fill: true,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
            }]
        };

        new Chart(campaignLineChartCtx, {
            type: 'line',
            data: lineChartData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

});

