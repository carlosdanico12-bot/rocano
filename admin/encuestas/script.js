console.log('script.js loaded');

document.addEventListener('DOMContentLoaded', function() {

    // --- Modal para Crear Nueva Encuesta ---
    const addModal = document.getElementById('addSurveyModal');
    const addSurveyBtn = document.getElementById('addSurveyBtn');
    const addCloseBtn = addModal ? addModal.querySelector('.close-btn') : null;
    const addCancelBtn = addModal ? addModal.querySelector('.cancel-btn') : null;

    // Función para mostrar el modal de añadir
    function openAddModal() {
        if (addModal) {
            addModal.style.display = 'block';
            document.querySelector('.overlay').style.display = 'block';
        }
    }

    // Función para cerrar el modal de añadir
    function closeAddModal() {
        if (addModal) {
            addModal.style.display = 'none';
            document.querySelector('.overlay').style.display = 'none';
        }
    }

    // Abrir el modal al hacer clic en el botón "Crear Nueva Encuesta"
    if (addSurveyBtn) {
        addSurveyBtn.addEventListener('click', openAddModal);
    }

    // Cerrar el modal al hacer clic en la 'x' o en "Cancelar"
    if (addCloseBtn) {
        addCloseBtn.addEventListener('click', closeAddModal);
    }
    if (addCancelBtn) {
        addCancelBtn.addEventListener('click', closeAddModal);
    }

    // --- Modal para Editar Encuesta ---
    const editModal = document.getElementById('editSurveyModal');
    const editCloseBtn = editModal ? editModal.querySelector('.close-btn') : null;
    const editCancelBtn = editModal ? editModal.querySelector('.cancel-btn') : null;

    // Función para cerrar el modal de editar
    function closeEditModal() {
        if (editModal) {
            editModal.style.display = 'none';
            document.querySelector('.overlay').style.display = 'none';
        }
    }

    // Cerrar el modal de editar al hacer clic en la 'x' o en "Cancelar"
    if (editCloseBtn) {
        editCloseBtn.addEventListener('click', closeEditModal);
    }
    if (editCancelBtn) {
        editCancelBtn.addEventListener('click', closeEditModal);
    }

    // Función para abrir el modal de editar
    window.openEditModal = function(id, titulo, descripcion, fechaInicio, fechaFin) {
        console.log('openEditModal called with', id, titulo, descripcion, fechaInicio, fechaFin);
        document.getElementById('edit_survey_id').value = id;
        document.getElementById('edit_titulo').value = titulo;
        document.getElementById('edit_descripcion').value = descripcion;
        document.getElementById('edit_fecha_inicio').value = fechaInicio;
        document.getElementById('edit_fecha_fin').value = fechaFin;
        if (editModal) {
            editModal.style.display = 'block';
            document.querySelector('.overlay').style.display = 'block';
            window.scrollTo(0, 0);
        }
    };

    // --- Event listeners para botones de editar ---
    document.querySelectorAll('.edit-survey-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            console.log('Edit button clicked');
            const id = btn.dataset.id;
            const titulo = btn.dataset.titulo;
            const descripcion = btn.dataset.descripcion;
            const fechaInicio = btn.dataset.fechaInicio;
            const fechaFin = btn.dataset.fechaFin;
            console.log('Data:', id, titulo, descripcion, fechaInicio, fechaFin);
            openEditModal(id, titulo, descripcion, fechaInicio, fechaFin);
        });
    });

    // --- Event listeners para formularios de eliminar ---
    document.querySelectorAll('.delete-survey-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!confirm('¿Estás seguro de eliminar esta encuesta?')) {
                e.preventDefault();
            }
        });
    });

    // Cerrar modales si se hace clic fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target === addModal) {
            closeAddModal();
        }
        if (event.target === editModal) {
            closeEditModal();
        }
    });

});
