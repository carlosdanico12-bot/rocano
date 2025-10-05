document.addEventListener('DOMContentLoaded', function() {

    const reportButtons = document.querySelectorAll('.generate-report-btn');

    reportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reportId = this.dataset.reportId;
            const reportTitle = this.closest('.report-card').querySelector('h3').textContent;

            // Simulación de la generación de un reporte
            alert(`Generando el reporte: "${reportTitle}".\n\nEn una aplicación real, esto iniciaría la descarga de un archivo PDF o Excel.`);

            // Aquí se podría añadir una llamada AJAX a un script PHP que genere el archivo.
            // Por ejemplo: window.location.href = 'generate_pdf.php?report=' + reportId;
        });
    });

});
