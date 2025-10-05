document.addEventListener('DOMContentLoaded', function() {

    // --- Lógica del View Switcher ---
    const cardViewBtn = document.getElementById('cardViewBtn');
    const calendarViewBtn = document.getElementById('calendarViewBtn');
    const cardView = document.getElementById('cardView');
    const calendarView = document.getElementById('calendarView');
    const calendarEl = document.getElementById('calendar');

    if (!cardViewBtn || !calendarViewBtn || !calendarEl) return;

    let calendar; // Definir la variable del calendario fuera para que sea accesible

    function initializeCalendar() {
        // Formatear los datos de PHP para FullCalendar
        const calendarEvents = allEventsData.map(event => {
            let color = '#2A9D8F'; // Color por defecto
            switch (event.tipo_evento) {
                case 'Mitin': color = '#E63946'; break;
                case 'Caminata': color = '#457B9D'; break;
                case 'Reunion': color = '#F4A261'; break;
                case 'Caravana': color = '#1D3557'; break;
            }
            return {
                id: event.id,
                title: event.titulo,
                start: event.fecha_hora,
                backgroundColor: color,
                borderColor: color
            };
        });

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            events: calendarEvents,
            eventClick: function(info) {
                // En esta vista, solo mostraremos una alerta con los detalles
                const eventData = allEventsData.find(e => e.id === info.event.id);
                if(eventData) {
                    alert(
                        `Evento: ${eventData.titulo}\n` +
                        `Tipo: ${eventData.tipo_evento}\n` +
                        `Fecha: ${new Date(eventData.fecha_hora).toLocaleString('es-PE')}\n` +
                        `Lugar: ${eventData.lugar || 'No especificado'}\n` +
                        `Zona: ${eventData.zona_nombre || 'General'}`
                    );
                }
            }
        });
        calendar.render();
    }
    
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
        
        // Inicializar el calendario solo la primera vez que se muestra
        if (!calendar) {
            initializeCalendar();
        }
        calendar.render();
    });

});
