// --- VARIABLES GLOBALES ---
let allAssignments = [];
let myZones = [];
let allVolunteers = [];

// --- INICIALIZACIÓN ---
document.addEventListener('DOMContentLoaded', function() {
    loadMyZones();
    loadVolunteers();
    loadAssignments();
    setupEventListeners();
});

// --- FUNCIONES DE CARGA DE DATOS ---
function loadMyZones() {
    fetch('ajax_handler.php?action=get_my_zones')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                myZones = data.zones;
                populateZoneFilter(data.zones);
            }
        })
        .catch(err => console.error('Error loading my zones:', err));
}

function loadVolunteers() {
    fetch('ajax_handler.php?action=get_my_volunteers')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allVolunteers = data.volunteers;
                populateVolunteerFilter(data.volunteers);
            }
        })
        .catch(err => console.error('Error loading volunteers:', err));
}

function loadAssignments() {
    fetch('ajax_handler.php?action=get_my_assignments')
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
        container.innerHTML = '<p class="empty-list">No tienes zonas asignadas o no hay voluntarios asignados.</p>';
        return;
    }

    // Agrupar asignaciones por zona
    const assignmentsByZone = {};
    assignments.forEach(assignment => {
        if (!assignmentsByZone[assignment.zona_id]) {
            assignmentsByZone[assignment.zona_id] = {
                zoneName: assignment.zone_name,
                volunteers: []
            };
        }
        assignmentsByZone[assignment.zona_id].volunteers.push(assignment.name);
    });

    // Renderizar cada zona asignada al coordinador
    Object.keys(assignmentsByZone).forEach(zoneId => {
        const zoneData = assignmentsByZone[zoneId];
        const zoneDiv = document.createElement('div');
        zoneDiv.className = 'assignment-zone';
        zoneDiv.innerHTML = `
            <h4>${zoneData.zoneName}</h4>
            <p><strong>Voluntarios asignados:</strong> ${zoneData.volunteers.length ? zoneData.volunteers.join(', ') : 'Ninguno'}</p>
        `;
        container.appendChild(zoneDiv);
    });
}

// --- MANEJO DE EVENTOS ---
function setupEventListeners() {
    document.getElementById('filterZone').addEventListener('change', applyFilters);
    document.getElementById('filterVolunteer').addEventListener('change', applyFilters);
    document.getElementById('clearFilters').addEventListener('click', clearFilters);
}

function applyFilters() {
    const zoneFilter = document.getElementById('filterZone').value;
    const volunteerFilter = document.getElementById('filterVolunteer').value;

    let filteredAssignments = allAssignments;

    // Filtrar por zona
    if (zoneFilter !== 'all') {
        filteredAssignments = filteredAssignments.filter(a => a.zona_id == zoneFilter);
    }

    // Filtrar por voluntario
    if (volunteerFilter !== 'all') {
        filteredAssignments = filteredAssignments.filter(a => a.user_id == volunteerFilter);
    }

    renderAssignments(filteredAssignments);
}

function clearFilters() {
    document.getElementById('filterZone').value = 'all';
    document.getElementById('filterVolunteer').value = 'all';
    renderAssignments(allAssignments);
}
