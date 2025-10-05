document.addEventListener('DOMContentLoaded', function() {
    
    // --- Lógica de Búsqueda y Filtrado ---
    const searchInput = document.getElementById('search_volunteer');
    const statusFilter = document.getElementById('filter_status');
    const volunteerCards = document.querySelectorAll('.volunteer-card');

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = statusFilter.value;

        volunteerCards.forEach(card => {
            const name = card.dataset.name || '';
            const email = card.dataset.email || '';
            const status = card.dataset.status || '';

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesStatus = (statusTerm === 'all' || status === statusTerm);

            if (matchesSearch && matchesStatus) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if(searchInput) searchInput.addEventListener('input', applyFilters);
    if(statusFilter) statusFilter.addEventListener('change', applyFilters);

    // --- Simulación de acciones ---
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if(confirm('¿Estás seguro de que deseas eliminar a este voluntario? Esta acción es irreversible.')) {
                alert('Voluntario eliminado (simulación).');
                // Aquí iría una llamada AJAX para eliminar el usuario con rol de voluntario
            }
        });
    });

    document.querySelectorAll('.message-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            alert('Abriendo interfaz de mensajería (simulación).');
            // Aquí se podría redirigir a la sección de mensajes con el ID del voluntario
        });
    });

});
