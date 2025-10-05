document.addEventListener('DOMContentLoaded', function() {

    // --- Lógica para el botón de eliminar ---
    const deleteButtons = document.querySelectorAll('.delete-btn');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            
            // Obtener el ID de la encuesta desde el atributo data
            const surveyId = this.dataset.surveyId;
            
            // Mostrar un diálogo de confirmación
            const confirmed = confirm('¿Estás seguro de que deseas eliminar esta encuesta? Esta acción no se puede deshacer.');

            if (confirmed) {
                // En una aplicación real, aquí se haría una llamada AJAX para eliminar
                // la encuesta de la base de datos.
                console.log(`Simulando eliminación de la encuesta con ID: ${surveyId}`);
                
                // Para la simulación, podemos eliminar la tarjeta del DOM
                const card = this.closest('.survey-card');
                if (card) {
                    card.style.transition = 'opacity 0.5s';
                    card.style.opacity = '0';
                    setTimeout(() => card.remove(), 500);
                }

                alert('Encuesta eliminada (simulación).');
            }
        });
    });

    // --- Lógica para otros botones (simulación) ---
    document.querySelectorAll('.view-results-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            alert('Cargando la página de resultados de la encuesta (simulación).');
            // Aquí se redirigiría a una página de resultados, pasando el ID de la encuesta.
        });
    });
    
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            alert('Abriendo el editor de encuestas (simulación).');
            // Aquí se redirigiría a una página para editar la encuesta.
        });
    });

});
