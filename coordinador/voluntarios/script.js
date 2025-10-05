document.addEventListener('DOMContentLoaded', function() {
    
    // --- Lógica de Búsqueda y Filtrado ---
    const searchInput = document.getElementById('search_volunteer');
    const zoneFilter = document.getElementById('filter_zone');
    const volunteerCards = document.querySelectorAll('.volunteer-card');

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const zoneTerm = zoneFilter.value;

        volunteerCards.forEach(card => {
            const name = card.dataset.name || '';
            const zone = card.dataset.zone || '';

            const matchesSearch = name.includes(searchTerm);
            const matchesZone = (zoneTerm === 'all' || zone === zoneTerm);

            if (matchesSearch && matchesZone) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    }

    if(searchInput) searchInput.addEventListener('input', applyFilters);
    if(zoneFilter) zoneFilter.addEventListener('change', applyFilters);

});
