// --- VARIABLES GLOBALES ---
let allAssignments = [];
let allZones = [];
let allCoordinators = [];
let allVolunteers = [];

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', function() {
    loadFilters();
    loadAssignments();
    setupEventListeners();
});

// --- FUNCIONES DE CARGA DE DATOS ---
function loadFilters() {
    // Cargar zonas
    fetch('../zonas/ajax_zonas.php?action=get_all_with_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allZones = data.zones;
                populateZoneFilter(data.zones);
            }
        })
        .catch(err => console.error('Error loading zones:', err));

    // Cargar coordinadores
    fetch('../usuarios/ajax_handler.php?action=get_users_by_role&role=coordinador')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allCoordinators = data.users;
                populateCoordinatorFilter(data.users);
            }
        })
        .catch(err => console.error('Error loading coordinators:', err));

    // Cargar voluntarios
    fetch('../usuarios/ajax_handler.php?action=get_users_by_role&role=voluntario')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allVolunteers = data.users;
                populateVolunteerFilter(data.users);
            }
        })
        .catch(err => console.error('Error loading volunteers:', err));
}

function loadAssignments() {
    fetch('ajax_handler.php?action=get_assignments')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allAssignments = data.assignments;
                renderAssignments(allAssignments);
            } else {
                document.getElementById('assignmentsContainer').innerHTML =
                    `<p class="empty-list">Error al cargar asignaciones: ${data.error}</p>`;
            }
        })
        .catch(err => {
            document.getElementById('assignmentsContainer').innerHTML =
                '<p class="empty-list">Error de conexión al cargar asignaciones.</p>';
            console.error('Error loading assignments:', err);
        });
}

// --- FUNCIONES DE POBLACIÓN DE FILTROS ---
function populateZoneFilter(zones) {
    const filterZone = document.getElementById('filterZone');
    zones.forEach(zone => {
        const option = document.createElement('option');
        option.value = zone.id;
        option.textContent = zone.nombre;
        filterZone.appendChild(option);
    });
}

function populateCoordinatorFilter(coordinators) {
    const filterCoordinator = document.getElementById('filterCoordinator');
    coordinators.forEach(coord => {
        const option = document.createElement('option');
        option.value = coord.id;
        option.textContent = coord.name;
        filterCoordinator.appendChild(option);
    });
}

function populateVolunteerFilter(volunteers) {
    const filterVolunteer = document.getElementById('filterVolunteer');
    volunteers.forEach(vol => {
        const option = document.createElement('option');
        option.value = vol.id;
        option.textContent = vol.name;
        filterVolunteer.appendChild(option);
    });
}

// --- FUNCIONES DE RENDERIZADO ---
function renderAssignments(assignments) {
    const container = document.getElementById('assignmentsContainer');
    container.innerHTML = '';

    if (assignments.length === 0) {
        container.innerHTML = '<p class="empty-list">No hay asignaciones para mostrar.</p>';
        return;
    }

    // Agrupar asignaciones por zona
    const assignmentsByZone = {};
    assignments.forEach(assignment => {
        if (!assignmentsByZone[assignment.zona_id]) {
            assignmentsByZone[assignment.zona_id] = {
                zoneName: assignment.zone_name,
                coordinators: [],
                volunteers: []
            };
        }
        if (assignment.role === 'Coordinador') {
            assignmentsByZone[assignment.zona_id].coordinators.push(assignment.name);
        } else {
            assignmentsByZone[assignment.zona_id].volunteers.push(assignment.name);
        }
    });

    // Renderizar cada zona
    Object.keys(assignmentsByZone).forEach(zoneId => {
        const zoneData = assignmentsByZone[zoneId];
        const zoneDiv = document.createElement('div');
        zoneDiv.className = 'assignment-zone';
        zoneDiv.innerHTML = `
            <h4>${zoneData.zoneName}</h4>
            <div class="assignment-details">
                <div class="coordinators-section">
                    <p><strong>Coordinadores:</strong> ${zoneData.coordinators.length ? zoneData.coordinators.join(', ') : 'Ninguno'}</p>
                    ${zoneData.coordinators.length ? `<button class="btn-small remove-btn" data-type="coordinator" data-zone="${zoneId}">Eliminar Coordinador</button>` : ''}
                </div>
                <div class="volunteers-section">
                    <p><strong>Voluntarios:</strong> ${zoneData.volunteers.length ? zoneData.volunteers.join(', ') : 'Ninguno'}</p>
                    ${zoneData.volunteers.length ? `<button class="btn-small remove-btn" data-type="volunteer" data-zone="${zoneId}">Eliminar Voluntario</button>` : ''}
                </div>
            </div>
            <div class="zone-actions">
                <button class="btn-small delegate-btn" data-zone="${zoneId}">Delegar Zona</button>
            </div>
        `;
        container.appendChild(zoneDiv);
    });
}

// --- MANEJO DE EVENTOS ---
function setupEventListeners() {
    document.getElementById('filterZone').addEventListener('change', applyFilters);
    document.getElementById('filterCoordinator').addEventListener('change', applyFilters);
    document.getElementById('filterVolunteer').addEventListener('change', applyFilters);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);

    // Botones de asignación
    document.getElementById('assignCoordinatorBtn').addEventListener('click', () => openAssignmentModal('coordinator'));
    document.getElementById('assignVolunteerBtn').addEventListener('click', () => openAssignmentModal('volunteer'));

    // Delegar eventos para botones dinámicos
    document.addEventListener('click', handleDynamicButtons);
}

function handleDynamicButtons(e) {
    if (e.target.classList.contains('remove-btn')) {
        const type = e.target.dataset.type;
        const zoneId = e.target.dataset.zone;
        openRemoveModal(type, zoneId);
    } else if (e.target.classList.contains('delegate-btn')) {
        const zoneId = e.target.dataset.zone;
        openDelegateModal(zoneId);
    }
}

function applyFilters() {
    const zoneFilter = document.getElementById('filterZone').value;
    const coordinatorFilter = document.getElementById('filterCoordinator').value;
    const volunteerFilter = document.getElementById('filterVolunteer').value;

    let filteredAssignments = allAssignments;

    // Filtrar por zona
    if (zoneFilter !== 'all') {
        filteredAssignments = filteredAssignments.filter(a => a.zona_id == zoneFilter);
    }

    // Filtrar por coordinador
    if (coordinatorFilter !== 'all') {
        filteredAssignments = filteredAssignments.filter(a =>
            a.role === 'Coordinador' && a.user_id == coordinatorFilter
        );
    }

    // Filtrar por voluntario
    if (volunteerFilter !== 'all') {
        filteredAssignments = filteredAssignments.filter(a =>
            a.role === 'Voluntario' && a.user_id == volunteerFilter
        );
    }

    renderAssignments(filteredAssignments);
}

function clearFilters() {
    document.getElementById('filterZone').value = 'all';
    document.getElementById('filterCoordinator').value = 'all';
    document.getElementById('filterVolunteer').value = 'all';
    renderAssignments(allAssignments);
}

// --- FUNCIONES DE ASIGNACIÓN ---
function openAssignmentModal(type) {
    // Crear modal dinámicamente
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${type === 'coordinator' ? 'Asignar Coordinador' : 'Asignar Voluntario'}</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="assignmentForm">
                    <div class="form-group">
                        <label for="assignmentZone">Zona:</label>
                        <select id="assignmentZone" required>
                            <option value="">Seleccionar zona...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="assignmentUser">${type === 'coordinator' ? 'Coordinador:' : 'Voluntario:'}</label>
                        <select id="assignmentUser" required>
                            <option value="">Seleccionar ${type === 'coordinator' ? 'coordinador' : 'voluntario'}...</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary cancel-btn">Cancelar</button>
                <button class="cta-button save-btn">Asignar</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Poblar selects
    populateAssignmentZoneSelect(type);
    populateAssignmentUserSelect(type);

    // Event listeners del modal
    modal.querySelector('.close-btn').addEventListener('click', () => modal.remove());
    modal.querySelector('.cancel-btn').addEventListener('click', () => modal.remove());
    modal.querySelector('.save-btn').addEventListener('click', () => saveAssignment(type, modal));

    // Cerrar al hacer click fuera
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

function populateAssignmentZoneSelect(type) {
    const select = document.getElementById('assignmentZone');
    allZones.forEach(zone => {
        const option = document.createElement('option');
        option.value = zone.id;
        option.textContent = zone.nombre;
        select.appendChild(option);
    });
}

function populateAssignmentUserSelect(type) {
    const select = document.getElementById('assignmentUser');
    const users = type === 'coordinator' ? allCoordinators : allVolunteers;
    users.forEach(user => {
        const option = document.createElement('option');
        option.value = user.id;
        option.textContent = user.name;
        select.appendChild(option);
    });
}

function saveAssignment(type, modal) {
    const form = document.getElementById('assignmentForm');
    const zoneId = document.getElementById('assignmentZone').value;
    const userId = document.getElementById('assignmentUser').value;

    if (!zoneId || !userId) {
        alert('Por favor complete todos los campos.');
        return;
    }

    const formData = new FormData();
    formData.append('action', type === 'coordinator' ? 'assign_coordinator' : 'assign_volunteer');
    formData.append('zone_id', zoneId);
    formData.append('user_id', userId);

    fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Asignación realizada con éxito.');
                modal.remove();
                loadAssignments(); // Recargar asignaciones
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => {
            alert('Error de conexión.');
            console.error('Error saving assignment:', err);
        });
}

// --- FUNCIONES DE ELIMINACIÓN ---
function openRemoveModal(type, zoneId) {
    // Crear modal de eliminación
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Eliminar ${type === 'coordinator' ? 'Coordinador' : 'Voluntario'}</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar ${type === 'coordinator' ? 'el coordinador' : 'el voluntario'} de esta zona?</p>
                <div class="form-group">
                    <label for="removeUser">Seleccionar ${type === 'coordinator' ? 'coordinador' : 'voluntario'}:</label>
                    <select id="removeUser" required>
                        <option value="">Seleccionar...</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary cancel-btn">Cancelar</button>
                <button class="btn-danger remove-btn">Eliminar</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Poblar select con usuarios asignados a la zona
    populateRemoveUserSelect(type, zoneId);

    // Event listeners del modal
    modal.querySelector('.close-btn').addEventListener('click', () => modal.remove());
    modal.querySelector('.cancel-btn').addEventListener('click', () => modal.remove());
    modal.querySelector('.remove-btn').addEventListener('click', () => removeAssignment(type, zoneId, modal));

    // Cerrar al hacer click fuera
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

function populateRemoveUserSelect(type, zoneId) {
    const select = document.getElementById('removeUser');
    const assignments = allAssignments.filter(a =>
        a.zona_id == zoneId && a.role === (type === 'coordinator' ? 'Coordinador' : 'Voluntario')
    );

    assignments.forEach(assignment => {
        const option = document.createElement('option');
        option.value = assignment.user_id;
        option.textContent = assignment.name;
        select.appendChild(option);
    });
}

function removeAssignment(type, zoneId, modal) {
    const userId = document.getElementById('removeUser').value;

    if (!userId) {
        alert('Por favor seleccione un usuario.');
        return;
    }

    if (!confirm('¿Está completamente seguro de que desea eliminar esta asignación?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', type === 'coordinator' ? 'remove_coordinator' : 'remove_volunteer');
    formData.append('zone_id', zoneId);
    formData.append('user_id', userId);

    fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Asignación eliminada con éxito.');
                modal.remove();
                loadAssignments(); // Recargar asignaciones
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => {
            alert('Error de conexión.');
            console.error('Error removing assignment:', err);
        });
}

// --- FUNCIONES DE DELEGACIÓN ---
function openDelegateModal(zoneId) {
    // Crear modal de delegación
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Delegar Zona</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>Seleccione el nuevo coordinador responsable de esta zona:</p>
                <div class="form-group">
                    <label for="delegateCoordinator">Nuevo Coordinador:</label>
                    <select id="delegateCoordinator" required>
                        <option value="">Seleccionar coordinador...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="delegateReason">Razón de delegación:</label>
                    <textarea id="delegateReason" placeholder="Opcional: explique la razón de la delegación..." rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary cancel-btn">Cancelar</button>
                <button class="cta-button delegate-btn">Delegar</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Poblar select con coordinadores disponibles
    populateDelegateCoordinatorSelect(zoneId);

    // Event listeners del modal
    modal.querySelector('.close-btn').addEventListener('click', () => modal.remove());
    modal.querySelector('.cancel-btn').addEventListener('click', () => modal.remove());
    modal.querySelector('.delegate-btn').addEventListener('click', () => delegateZone(zoneId, modal));

    // Cerrar al hacer click fuera
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

function populateDelegateCoordinatorSelect(zoneId) {
    const select = document.getElementById('delegateCoordinator');
    // Mostrar todos los coordinadores, incluyendo los ya asignados a otras zonas
    allCoordinators.forEach(coord => {
        const option = document.createElement('option');
        option.value = coord.id;
        option.textContent = coord.name;
        select.appendChild(option);
    });
}

function delegateZone(zoneId, modal) {
    const newCoordinatorId = document.getElementById('delegateCoordinator').value;
    const reason = document.getElementById('delegateReason').value;

    if (!newCoordinatorId) {
        alert('Por favor seleccione un coordinador.');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delegate_zone');
    formData.append('zone_id', zoneId);
    formData.append('new_coordinator_id', newCoordinatorId);
    formData.append('reason', reason);

    fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Zona delegada con éxito.');
                modal.remove();
                loadAssignments(); // Recargar asignaciones
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(err => {
            alert('Error de conexión.');
            console.error('Error delegating zone:', err);
        });
}
