document.addEventListener('DOMContentLoaded', function() {

    // --- MANEJO DEL MODAL ---
    const modal = document.getElementById('voterModal');
    const addVoterBtn = document.getElementById('addVoterBtn');

    if (!modal || !addVoterBtn) return;
    
    const closeBtn = modal.querySelector('.close-btn');
    const cancelBtn = modal.querySelector('.cancel-btn');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const voterForm = document.getElementById('voterForm');
    const voterIdInput = document.getElementById('voter_id');
    const formActionInput = document.getElementById('formAction');

    function openModal() { modal.style.display = 'block'; }
    function closeModal() {
        modal.style.display = 'none';
        voterForm.reset();
        modalTitle.textContent = 'Añadir Nuevo Votante';
        modalSubmitBtn.textContent = 'Añadir Votante';
        voterIdInput.value = '';
        formActionInput.value = 'create';
    }

    addVoterBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    // Poblar modal para editar
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            modalTitle.textContent = 'Editar Votante';
            modalSubmitBtn.textContent = 'Guardar Cambios';
            formActionInput.value = 'update';
            
            voterIdInput.value = this.dataset.id;
            document.getElementById('nombre').value = this.dataset.nombre;
            document.getElementById('dni').value = this.dataset.dni;
            document.getElementById('telefono').value = this.dataset.telefono;
            document.getElementById('direccion').value = this.dataset.direccion;
            document.getElementById('zona_id').value = this.dataset.zona_id;
            document.getElementById('estado').value = this.dataset.estado;

            openModal();
        });
    });

    // --- FILTROS Y PAGINACIÓN DE LA TABLA ---
    const searchInput = document.getElementById('searchInput');
    const zoneFilter = document.getElementById('zoneFilter');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('votersTableBody');
    const allRows = Array.from(tableBody.getElementsByTagName('tr'));
    
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');
    const tableInfo = document.getElementById('tableInfo');
    
    let currentPage = 1;
    const rowsPerPage = 10;
    let filteredRows = allRows;

    function applyFiltersAndPagination() {
        const searchTerm = searchInput.value.toLowerCase();
        const zoneTerm = zoneFilter.value;
        const statusTerm = statusFilter.value;

        filteredRows = allRows.filter(row => {
            if (row.children.length < 6) return false;
            const name = row.children[0].textContent.toLowerCase();
            const dni = row.children[1].textContent.toLowerCase();
            const zone = row.children[3].textContent;
            const status = row.children[4].textContent;

            const matchesSearch = name.includes(searchTerm) || dni.includes(searchTerm);
            const matchesZone = !zoneTerm || zone === zoneTerm;
            const matchesStatus = !statusTerm || status === statusTerm;
            
            return matchesSearch && matchesZone && matchesStatus;
        });

        currentPage = 1;
        renderTable();
    }

    function renderTable() {
        tableBody.innerHTML = '';
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const paginatedRows = filteredRows.slice(start, end);

        if (paginatedRows.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="6">No se encontraron resultados.</td></tr>';
        } else {
            paginatedRows.forEach(row => tableBody.appendChild(row));
        }
        
        updatePaginationControls();
    }

    function updatePaginationControls() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        
        prevPageBtn.disabled = currentPage === 1;
        nextPageBtn.disabled = currentPage === totalPages || totalPages === 0;
        
        const startEntry = filteredRows.length > 0 ? (currentPage - 1) * rowsPerPage + 1 : 0;
        const endEntry = Math.min(currentPage * rowsPerPage, filteredRows.length);
        
        tableInfo.textContent = `Mostrando ${startEntry}-${endEntry} de ${filteredRows.length} resultados`;
    }

    searchInput.addEventListener('input', applyFiltersAndPagination);
    zoneFilter.addEventListener('change', applyFiltersAndPagination);
    statusFilter.addEventListener('change', applyFiltersAndPagination);

    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });

    nextPageBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });
    
    // Render inicial
    if (allRows.length > 0 && allRows[0].children.length >= 6) {
        renderTable();
    } else {
        tableInfo.textContent = 'No hay datos para mostrar.';
    }
});
