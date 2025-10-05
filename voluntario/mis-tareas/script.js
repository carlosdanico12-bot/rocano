document.addEventListener('DOMContentLoaded', function() {

    // --- LÓGICA DE FILTRADO ---
    const statusFilter = document.getElementById('filter_status');
    const taskCards = document.querySelectorAll('.task-card');

    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            const selectedStatus = this.value;

            taskCards.forEach(card => {
                const cardStatus = card.dataset.status;
                if (selectedStatus === 'all' || cardStatus === selectedStatus) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // --- LÓGICA PARA ACTUALIZAR ESTADO DE TAREA (AJAX) ---
    document.querySelectorAll('.update-status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.dataset.taskId;
            const newStatus = this.dataset.newStatus;
            
            if (!confirm(`¿Estás seguro de que deseas cambiar el estado de esta tarea a "${newStatus}"?`)) {
                return;
            }

            // Deshabilitar botón para evitar clics múltiples
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';

            updateTaskStatus(taskId, newStatus, this);
        });
    });

    function updateTaskStatus(taskId, newStatus, buttonElement) {
        const formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('new_status', newStatus);

        fetch('mis-tareas/update_task.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Si la actualización fue exitosa, recargamos la página
                // para mostrar los cambios y los nuevos botones de acción.
                alert('¡Estado de la tarea actualizado con éxito!');
                location.reload();
            } else {
                alert('Error al actualizar la tarea: ' + data.error);
                // Reactivar el botón si falla
                buttonElement.disabled = false;
                // Revertir el texto del botón al original
                if(newStatus === 'En Progreso') buttonElement.innerHTML = '<i class="fas fa-play"></i> Iniciar Tarea';
                if(newStatus === 'Completada') buttonElement.innerHTML = '<i class="fas fa-check"></i> Marcar como Completada';
            }
        })
        .catch(error => {
            console.error('Error de red:', error);
            alert('Hubo un problema de conexión. Inténtalo de nuevo.');
            buttonElement.disabled = false;
            // Revertir texto del botón
            if(newStatus === 'En Progreso') buttonElement.innerHTML = '<i class="fas fa-play"></i> Iniciar Tarea';
            if(newStatus === 'Completada') buttonElement.innerHTML = '<i class="fas fa-check"></i> Marcar como Completada';
        });
    }

});
