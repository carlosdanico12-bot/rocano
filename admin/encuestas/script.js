document.addEventListener('DOMContentLoaded', function() {
    
    const modal = document.getElementById('addSurveyModal');
    const addSurveyBtn = document.getElementById('addSurveyBtn');
    const closeBtn = modal.querySelector('.close-btn');
    const cancelBtn = modal.querySelector('.cancel-btn');

    // Función para mostrar el modal
    function openModal() {
        if (modal) {
            modal.style.display = 'block';
        }
    }

    // Función para cerrar el modal
    function closeModal() {
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Abrir el modal al hacer clic en el botón "Crear Nueva Encuesta"
    if (addSurveyBtn) {
        addSurveyBtn.addEventListener('click', openModal);
    }

    // Cerrar el modal al hacer clic en la 'x' o en "Cancelar"
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    // Cerrar el modal si se hace clic fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

});
