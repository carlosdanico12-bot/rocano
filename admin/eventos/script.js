document.addEventListener('DOMContentLoaded', function() {

    const modal = document.getElementById('eventModal');
    const addEventBtn = document.getElementById('addEventBtn');
    
    if (!modal || !addEventBtn) return;

    // --- Elementos del Modal ---
    const closeBtn = modal.querySelector('.close-btn');
    const cancelBtn = modal.querySelector('.cancel-btn');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const eventForm = document.getElementById('eventForm');
    const eventIdInput = document.getElementById('event_id');
    const formActionInput = document.getElementById('formAction');
    const existingImageUrlInput = document.getElementById('existing_image_url');

    function openModal() { modal.style.display = 'block'; }
    function closeModal() {
        modal.style.display = 'none';
        eventForm.reset();
        modalTitle.textContent = 'Crear Nuevo Evento';
        modalSubmitBtn.textContent = 'Crear Evento';
        formActionInput.value = 'create';
        eventIdInput.value = '';
        existingImageUrlInput.value = '';
    }

    addEventBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // --- Lógica para poblar modal al editar ---
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            modalTitle.textContent = 'Editar Evento';
            modalSubmitBtn.textContent = 'Guardar Cambios';
            formActionInput.value = 'update';
            
            eventIdInput.value = this.dataset.id;
            document.getElementById('titulo').value = this.dataset.titulo;
            document.getElementById('descripcion').value = this.dataset.descripcion;
            document.getElementById('lugar').value = this.dataset.lugar;
            document.getElementById('fecha_hora').value = this.dataset.fecha_hora;
            document.getElementById('tipo_evento').value = this.dataset.tipo_evento;
            document.getElementById('zona_id').value = this.dataset.zona_id;
            existingImageUrlInput.value = this.dataset.imagen_url;

            openModal();
        });
    });

    // --- Lógica del View Switcher ---
    const cardViewBtn = document.getElementById('cardViewBtn');
    const calendarViewBtn = document.getElementById('calendarViewBtn');
    const cardView = document.getElementById('cardView');
    const calendarView = document.getElementById('calendarView');
    
    cardViewBtn.addEventListener('click', () => {
        cardView.style.display = 'block';
        calendarView.style.display = 'none';
        cardViewBtn.classList.add('active');
        calendarViewBtn.classList.remove('active');
    });

    calendarViewBtn.addEventListener('click', () => {
        cardView.style.display = 'none';
        calendarView.style.display = 'block';
        calendarViewBtn.classList.add('active');
        cardViewBtn.classList.remove('active');
        calendar.render(); // Re-renderizar el calendario por si cambió de tamaño
    });

    // --- Lógica de Filtros para Vista de Tarjetas ---
    const searchInput = document.getElementById('searchEvent');
    const zoneFilter = document.getElementById('filterZone');
    const eventCards = document.querySelectorAll('.event-card');

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const zoneTerm = zoneFilter.value;

        eventCards.forEach(card => {
            const title = card.dataset.title || '';
            const zone = card.dataset.zone || '';
            
            const matchesSearch = title.includes(searchTerm);
            const matchesZone = (zoneTerm === 'all' || zone === zoneTerm);

            if (matchesSearch && matchesZone) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (zoneFilter) zoneFilter.addEventListener('change', applyFilters);

    // --- Configuración de FullCalendar ---
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: 'get_events.php', // URL para obtener los eventos en formato JSON
        eventClick: function(info) {
            // Simulación: Abre el modal de edición al hacer clic en un evento del calendario
            const editButton = document.querySelector(`.edit-btn[data-id='${info.event.id}']`);
            if (editButton) {
                editButton.click();
            } else {
                alert('Evento: ' + info.event.title);
            }
        }
    });

});

