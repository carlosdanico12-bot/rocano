document.addEventListener('DOMContentLoaded', function() {

    const modal = document.getElementById('taskModal');
    const addTaskBtn = document.getElementById('addTaskBtn');
    
    if (!modal || !addTaskBtn) return;

    const closeBtn = modal.querySelector('.close-btn');
    const cancelBtn = modal.querySelector('.cancel-btn');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const taskForm = document.getElementById('taskForm');
    const taskIdInput = document.getElementById('task_id');
    const formActionInput = document.getElementById('formAction');

    function openModal() { modal.style.display = 'block'; }
    function closeModal() {
        modal.style.display = 'none';
        taskForm.reset();
        modalTitle.textContent = 'Asignar Nueva Tarea';
        modalSubmitBtn.textContent = 'Crear Tarea';
        formActionInput.value = 'create';
        taskIdInput.value = '';
    }

    addTaskBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // --- Lógica para poblar modal al editar ---
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            modalTitle.textContent = 'Editar Tarea';
            modalSubmitBtn.textContent = 'Guardar Cambios';
            formActionInput.value = 'update';
            
            taskIdInput.value = this.dataset.id;
            document.getElementById('titulo').value = this.dataset.titulo;
            document.getElementById('descripcion').value = this.dataset.descripcion;
            document.getElementById('asignado_a').value = this.dataset.asignado;
            document.getElementById('fecha_limite').value = this.dataset.limite;
            document.getElementById('estado').value = this.dataset.estado;

            openModal();
        });
    });

    // --- Lógica de Drag & Drop ---
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

        fetch('update_task_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error('Error al actualizar la tarea:', data.error);
                // Opcional: Revertir el movimiento de la tarjeta si falla la actualización
            } else {
                console.log('Tarea actualizada con éxito.');
            }
        })
        .catch(error => {
            console.error('Error de red:', error);
        });
    }
});

