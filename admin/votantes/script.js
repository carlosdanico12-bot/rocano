document.addEventListener('DOMContentLoaded', function () {
    // Emitir evento personalizado cuando se selecciona un votante
    const voterItems = document.querySelectorAll('.voter-item');
    voterItems.forEach(item => {
        item.addEventListener('click', () => {
            const event = new CustomEvent('voterSelected', { detail: { voterId: item.dataset.voterId } });
            document.dispatchEvent(event);
        });
    });

    const modal = document.getElementById('voterModal');
    const addVoterBtn = document.getElementById('addVoterBtn');

    if (!modal || !addVoterBtn) return;

    const closeBtn = modal.querySelector('.close-btn');
    const cancelBtn = modal.querySelector('.cancel-btn');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const voterForm = document.getElementById('voterForm');
    const voterIdInput = document.getElementById('voter_id');
    const nombreInput = document.getElementById('nombre');
    const dniInput = document.getElementById('dni');
    const telefonoInput = document.getElementById('telefono');
    const direccionInput = document.getElementById('direccion');
    const zonaSelect = document.getElementById('zona_id');
    const estadoSelect = document.getElementById('estado');
    const notasTextarea = document.getElementById('notas');

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

    // --- Lógica para Editar y Eliminar ---
    const deleteForm = document.getElementById('deleteForm');
    const deleteVoterIdInput = document.getElementById('delete_voter_id');

    document.querySelector('.voters-table tbody').addEventListener('click', (event) => {
        const editBtn = event.target.closest('.edit-btn');
        const deleteBtn = event.target.closest('.delete-btn');
        if (!editBtn && !deleteBtn) return;

        const row = event.target.closest('tr');
        if (!row) return;

        if (editBtn) {
            // Rellenar el formulario con los datos del votante
            voterIdInput.value = row.dataset.voterId || '';
            nombreInput.value = row.dataset.name || '';
            dniInput.value = row.dataset.dni || '';
            telefonoInput.value = row.dataset.telefono || '';
            direccionInput.value = row.dataset.direccion || '';
            zonaSelect.value = row.dataset.zonaId || '';
            estadoSelect.value = row.dataset.status || '';
            notasTextarea.value = row.dataset.notas || '';

            modalTitle.textContent = 'Editar Votante';
            modalSubmitBtn.textContent = 'Actualizar Votante';
            openModal();
        } else if (deleteBtn) {
            if (confirm(`¿Seguro que quieres eliminar al votante "${row.dataset.name}"?`)) {
                deleteVoterIdInput.value = row.dataset.voterId || '';
                deleteForm.submit();
            }
        }
    });
});

