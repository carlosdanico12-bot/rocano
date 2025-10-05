document.addEventListener('DOMContentLoaded', function() {

    // --- MANEJO DEL MODAL (similar al del admin) ---
    const modal = document.getElementById('taskModal');
    const addTaskBtn = document.getElementById('addTaskBtn');
    
    if (!modal || !addTaskBtn) return;

    // ... (Aquí iría la lógica completa para abrir, cerrar y poblar el modal al editar)


    // --- LÓGICA DE DRAG & DROP ---
    const taskCards = document.querySelectorAll('.task-card:not(.empty-card)');
    const columns = document.querySelectorAll('.kanban-column');

    taskCards.forEach(card => {
        card.addEventListener('dragstart', () => {
            card.classList.add('dragging');
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
        });
    });

    columns.forEach(column => {
        column.addEventListener('dragover', e => {
            e.preventDefault();
            const afterElement = getDragAfterElement(column, e.clientY);
            const dragging = document.querySelector('.dragging');
            if (afterElement == null) {
                column.querySelector('.task-list').appendChild(dragging);
            } else {
                column.querySelector('.task-list').insertBefore(dragging, afterElement);
            }
        });

        column.addEventListener('drop', e => {
            e.preventDefault();
            const draggingCard = document.querySelector('.dragging');
            const newStatus = column.dataset.status;
            const taskId = draggingCard.dataset.taskId;
            
            // --- AJAX para actualizar el estado en la BD ---
            updateTaskStatus(taskId, newStatus);
        });
    });

    function getDragAfterElement(column, y) {
        const draggableElements = [...column.querySelectorAll('.task-card:not(.dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    // --- Función AJAX con Fetch API ---
    function updateTaskStatus(taskId, newStatus) {
        const formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('new_status', newStatus);

        // La petición se hace a un archivo específico del coordinador
        fetch('tareas/update_task_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Tarea actualizada con éxito.');
                // Actualizar el atributo data-status para que el color del borde se mantenga
                const movedCard = document.querySelector(`[data-task-id='${taskId}']`);
                if(movedCard) movedCard.dataset.status = newStatus;
            } else {
                console.error('Error al actualizar la tarea:', data.error);
                alert('Hubo un error al actualizar la tarea. La página se recargará para asegurar la consistencia de los datos.');
                location.reload(); // Recargar la página si falla la actualización
            }
        })
        .catch(error => {
            console.error('Error de red:', error);
            location.reload();
        });
    }
});
