document.addEventListener('DOMContentLoaded', function() {

    const modal = document.getElementById('voterModal');
    const addVoterBtn = document.getElementById('addVoterBtn');

    if (!modal || !addVoterBtn) return;

    const closeBtn = modal.querySelector('.close-btn');
    const cancelBtn = modal.querySelector('.cancel-btn');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const voterForm = document.getElementById('voterForm');
    const voterIdInput = document.getElementById('voter_id');

    function openModal() {
        modal.style.display = 'block';
    }

    function closeModal() {
        modal.style.display = 'none';
        voterForm.reset();
        modalTitle.textContent = 'Añadir Nuevo Votante';
        modalSubmitBtn.textContent = 'Guardar Votante';
        voterIdInput.value = '';
    }

    addVoterBtn.addEventListener('click', openModal);

    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
        }
    });

    // --- Lógica de Búsqueda y Filtrado ---
    const searchInput = document.getElementById('search_voter');
    const zoneFilter = document.getElementById('filter_zone');
    const statusFilter = document.getElementById('filter_status');
    const tableRows = document.querySelectorAll('.voters-table tbody tr');

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const zoneTerm = zoneFilter.value;
        const statusTerm = statusFilter.value;

        tableRows.forEach(row => {
            // Si es la fila de "no hay votantes", la ignoramos
            if (row.children.length === 1) return;

            const name = row.dataset.name || '';
            const dni = row.dataset.dni || '';
            const zone = row.dataset.zone || '';
            const status = row.dataset.status || '';

            const matchesSearch = name.includes(searchTerm) || dni.includes(searchTerm);
            const matchesZone = (zoneTerm === 'all' || zone === zoneTerm);
            const matchesStatus = (statusTerm === 'all' || status === statusTerm);

            if (matchesSearch && matchesZone && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (zoneFilter) zoneFilter.addEventListener('change', applyFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
});

