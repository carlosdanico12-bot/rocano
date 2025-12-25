// --- VARIABLES GLOBALES ---
let map;
let drawingManager;
let selectedShape;
let infoWindow;
const polygons = new Map();
let allZonesData = [];

const COLORS = {
    'A favor': '#28a745', 
    'Indeciso': '#ffc107', 
    'En contra': '#dc3545', 
    'Sin Votantes': '#6c757d'
};

// --- MODALES Y NOTIFICACIONES ---
let notificationModal, notificationTitle, notificationMessage, notificationOkBtn, notificationCancelBtn;

function showNotification(message, isError = false) {
    if (!notificationModal) return;
    notificationTitle.textContent = isError ? 'Error' : 'Aviso';
    notificationMessage.textContent = message;
    notificationOkBtn.textContent = 'Aceptar';
    notificationCancelBtn.style.display = 'none';
    notificationModal.style.display = 'block';

    const close = () => notificationModal.style.display = 'none';
    notificationOkBtn.onclick = close;
    notificationModal.querySelector('.close-btn').onclick = close;
}

function showConfirmation(message, onConfirm) {
    if (!notificationModal) return;
    notificationTitle.textContent = 'Confirmación';
    notificationMessage.textContent = message;
    notificationOkBtn.textContent = 'Aceptar';
    notificationCancelBtn.style.display = 'inline-block';
    notificationCancelBtn.textContent = 'Cancelar';
    notificationModal.style.display = 'block';
    
    const close = () => notificationModal.style.display = 'none';
    notificationCancelBtn.onclick = close;
    notificationModal.querySelector('.close-btn').onclick = close;

    notificationOkBtn.onclick = () => {
        close();
        onConfirm();
    };
}


// --- INICIALIZACIÓN DEL MAPA ---
// Esta función es llamada por el script de Google Maps una vez que está listo.
window.initMap = function() {
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.error('Google Maps API no se cargó correctamente.');
        document.getElementById('map').innerHTML = '<p style="padding: 20px; text-align: center;">Error: No se pudo cargar Google Maps. Revisa tu clave de API y la conexión a internet.</p>';
        return;
    }
    const tingoMaria = { lat: -9.294, lng: -76.007 };
    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 13, center: tingoMaria, mapTypeId: 'roadmap'
    });
    infoWindow = new google.maps.InfoWindow();
    drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: null, 
        drawingControl: false,
        polygonOptions: { editable: true, draggable: true, fillColor: '#E63946', strokeColor: '#E63946' }
    });
    drawingManager.setMap(map);
    google.maps.event.addListener(drawingManager, 'overlaycomplete', onPolygonComplete);
    
    // Una vez el mapa está listo, se inician las demás funciones.
    initializePageLogic();
}

// --- LÓGICA PRINCIPAL DE LA PÁGINA ---
// Se llama desde initMap para asegurar que el API de Google esté listo.
function initializePageLogic() {
    // Inicializar elementos de modales
    notificationModal = document.getElementById('notificationModal');
    notificationTitle = document.getElementById('notificationTitle');
    notificationMessage = document.getElementById('notificationMessage');
    notificationOkBtn = document.getElementById('notificationOk');
    notificationCancelBtn = document.getElementById('notificationCancel');

    fetchZonesAndRender();
    setupEventListeners();
}


// --- FUNCIONES DE OBTENCIÓN DE DATOS Y RENDERIZADO ---
function fetchZonesAndRender() {
    fetch('ajax_zonas.php?action=get_all_with_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                allZonesData = data.zones;
                renderZoneList(allZonesData);
                drawSavedZones(allZonesData);
                // CORRECCIÓN: Llamar a fetchAssignments después de que las zonas estén listas.
                fetchAssignments(); 
            } else {
                showNotification(`Error al cargar zonas: ${data.error}`, true);
            }
        }).catch(err => {
            showNotification(`Error de conexión al cargar las zonas.`, true);
            console.error("Fetch Error:", err);
        });
}

function renderZoneList(zones) {
    const listContainer = document.getElementById('zones-list-container');
    listContainer.innerHTML = zones.length ? '' : '<p class="empty-list">No hay zonas creadas.</p>';
    zones.forEach(zone => {
        const dotClass = `dot-${zone.status.toLowerCase().replace(/ /g, '-')}`;
        const item = document.createElement('div');
        item.className = 'zone-item';
        item.dataset.zoneId = zone.id;
        item.dataset.status = zone.status;
        item.innerHTML = `
            <div class="zone-info">
                <span class="zone-status-dot ${dotClass}"></span>
                <div>
                    <h4 class="zone-name">${zone.nombre}</h4>
                    <p class="zone-stats">A Favor: ${zone.stats.a_favor} | Ind: ${zone.stats.indeciso} | En Contra: ${zone.stats.en_contra}</p>
                    <p class="zone-assigned">Coordinadores: ${zone.coordinators || 'Ninguno'}</p>
                    <p class="zone-assigned">Voluntarios: ${zone.volunteers || 'Ninguno'}</p>
                </div>
            </div>
            <div class="zone-actions">
                <button class="action-btn edit-btn" title="Editar"><i class="fas fa-edit"></i></button>
                <button class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
            </div>`;
        listContainer.appendChild(item);
    });
}

function drawSavedZones(zones) {
    polygons.forEach(p => p.setMap(null));
    polygons.clear();
    zones.forEach(zone => {
        if (zone.coordinates) {
            try {
                const coords = JSON.parse(zone.coordinates);
                if (coords.length === 0) return;
                const color = COLORS[zone.status] || COLORS['Sin Votantes'];
                const polygon = new google.maps.Polygon({
                    paths: coords,
                    strokeColor: color, strokeWeight: 2, strokeOpacity: 0.8,
                    fillColor: color, fillOpacity: 0.35, map: map
                });
                polygon.addListener('click', () => showInfoWindow(polygon, zone));
                polygons.set(parseInt(zone.id), polygon);
            } catch (e) {
                console.error(`Error al parsear coordenadas para la zona ${zone.id}:`, e);
            }
        }
    });
}




// --- MANEJO DE EVENTOS ---
function setupEventListeners() {
    const modal = document.getElementById('zoneModal');
    const zoneForm = document.getElementById('zoneForm');

    document.getElementById('addZoneBtn').addEventListener('click', () => {
        zoneForm.reset();
        document.getElementById('modalTitle').textContent = 'Crear Nueva Zona';
        document.getElementById('zone_id').value = '';
        if (selectedShape) selectedShape.setMap(null);
        selectedShape = null;
        document.getElementById('coordinates').value = '';
        modal.style.display = 'block';
        document.getElementById('drawPolygonBtn').click(); // Activar modo dibujo
    });

    modal.querySelector('.close-btn').addEventListener('click', () => {
        modal.style.display = 'none';
        if (selectedShape) selectedShape.setMap(null); // Limpiar polígono de edición/creación
    });
    modal.querySelector('.cancel-btn').addEventListener('click', () => {
        modal.style.display = 'none';
        if (selectedShape) selectedShape.setMap(null);
    });

    document.getElementById('zones-list-container').addEventListener('click', (e) => {
        const zoneInfo = e.target.closest('.zone-info');
        const editBtn = e.target.closest('.edit-btn');
        const deleteBtn = e.target.closest('.delete-btn');
        const zoneItem = e.target.closest('.zone-item');
        if (!zoneItem) return;
        const zoneId = zoneItem.dataset.zoneId;
        
        if (zoneInfo) onZoneItemClick(zoneId);
        if (editBtn) onEditZone(zoneId);
        if (deleteBtn) onDeleteZone(zoneId);
    });

    zoneForm.addEventListener('submit', handleFormSubmit);
    
    // Filtros
    document.getElementById('filterStatus').addEventListener('change', applyFilters);
    document.getElementById('filterZones').addEventListener('input', applyFilters);

    // Controles del mapa
    const moveBtn = document.getElementById('moveMapBtn');
    const drawBtn = document.getElementById('drawPolygonBtn');
    moveBtn.addEventListener('click', () => {
        drawingManager.setDrawingMode(null);
        moveBtn.classList.add('active');
        drawBtn.classList.remove('active');
    });
    drawBtn.addEventListener('click', () => {
        if (selectedShape) {
            selectedShape.setMap(null);
            selectedShape = null;
        }
        document.getElementById('coordinates').value = '';
        drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
        drawBtn.classList.add('active');
        moveBtn.classList.remove('active');
    });
}

function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('zoneForm'));
    if (!formData.get('coordinates') || formData.get('coordinates') === '[]') {
        showNotification('Por favor, dibuja el polígono en el mapa antes de guardar.', true);
        return;
    }
    
    const action = document.getElementById('zone_id').value ? 'update' : 'create';
    formData.append('action', action);

    fetch('ajax_zonas.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showNotification('Zona guardada con éxito.');
                document.getElementById('zoneModal').style.display = 'none';
                if (selectedShape) selectedShape.setMap(null);
                fetchZonesAndRender();
            } else {
                showNotification('Error: ' + data.error, true);
            }
        }).catch(err => showNotification('Error de conexión.', true));
}

function onPolygonComplete(event) {
    if (selectedShape) selectedShape.setMap(null);
    selectedShape = event.overlay;
    updateCoordinates(selectedShape);
    drawingManager.setDrawingMode(null);
    
    const path = selectedShape.getPath();
    google.maps.event.addListener(path, 'set_at', () => updateCoordinates(selectedShape));
    google.maps.event.addListener(path, 'insert_at', () => updateCoordinates(selectedShape));
    google.maps.event.addListener(path, 'remove_at', () => updateCoordinates(selectedShape));

    document.getElementById('moveMapBtn').classList.add('active');
    document.getElementById('drawPolygonBtn').classList.remove('active');
}

function updateCoordinates(shape) {
    const path = shape.getPath();
    const coords = path.getArray().map(latLng => ({ lat: latLng.lat(), lng: latLng.lng() }));
    document.getElementById('coordinates').value = JSON.stringify(coords);
}

function onZoneItemClick(zoneId) {
    document.querySelectorAll('.zone-item').forEach(i => i.classList.remove('selected'));
    const item = document.querySelector(`.zone-item[data-zone-id='${zoneId}']`);
    if (item) item.classList.add('selected');

    const polygon = polygons.get(parseInt(zoneId));
    const zoneData = allZonesData.find(z => z.id == zoneId);
    if (polygon && zoneData) {
        const bounds = new google.maps.LatLngBounds();
        polygon.getPath().forEach(path => bounds.extend(path));
        map.fitBounds(bounds);
        showInfoWindow(polygon, zoneData);
    } else if (zoneData && zoneData.coordinates) {
        // Si no hay polígono dibujado, intentar centrar el mapa en las coordenadas
        try {
            const coords = JSON.parse(zoneData.coordinates);
            if (coords.length > 0) {
                const bounds = new google.maps.LatLngBounds();
                coords.forEach(coord => bounds.extend(coord));
                map.fitBounds(bounds);
                // Mostrar info window en el centro
                const center = bounds.getCenter();
                const content = `<div class="info-window-content"><h4>${zoneData.nombre}</h4><p><strong>Estado:</strong> ${zoneData.status}</p><p>A Favor: ${zoneData.stats.a_favor} | Ind: ${zoneData.stats.indeciso} | En Contra: ${zoneData.stats.en_contra}</p></div>`;
                infoWindow.setContent(content);
                infoWindow.setPosition(center);
                infoWindow.open(map);
            }
        } catch (e) {
            console.error('Error al centrar mapa en zona:', e);
        }
    }
}

function onEditZone(zoneId) {
    const zoneData = allZonesData.find(z => z.id == zoneId);
    if (!zoneData) return;

    const zoneForm = document.getElementById('zoneForm');
    zoneForm.reset();
    if (selectedShape) selectedShape.setMap(null);

    document.getElementById('modalTitle').textContent = `Editar Zona: ${zoneData.nombre}`;
    document.getElementById('zone_id').value = zoneData.id;
    document.getElementById('nombre').value = zoneData.nombre;
    document.getElementById('descripcion').value = zoneData.descripcion;
    
    if (zoneData.coordinates) {
        try {
            const coords = JSON.parse(zoneData.coordinates);
            if (coords.length > 0) {
                selectedShape = new google.maps.Polygon({
                    paths: coords, editable: true, draggable: true,
                    fillColor: '#E63946', strokeColor: '#E63946', map: map
                });
                updateCoordinates(selectedShape);
                const path = selectedShape.getPath();
                google.maps.event.addListener(path, 'set_at', () => updateCoordinates(selectedShape));
                google.maps.event.addListener(path, 'insert_at', () => updateCoordinates(selectedShape));
                google.maps.event.addListener(path, 'remove_at', () => updateCoordinates(selectedShape));
            }
        } catch (e) {
            selectedShape = null;
        }
    } else {
        selectedShape = null;
    }
    document.getElementById('zoneModal').style.display = 'block';
}

function onDeleteZone(zoneId) {
    const zoneData = allZonesData.find(z => z.id == zoneId);
    if (!zoneData) return;
    
    showConfirmation(`¿Estás seguro de que deseas eliminar la zona "${zoneData.nombre}"?`, () => {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('zone_id', zoneId);

        fetch('ajax_zonas.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showNotification('Zona eliminada correctamente.');
                    fetchZonesAndRender();
                } else {
                    showNotification('Error: ' + data.error, true);
                }
            }).catch(err => showNotification('Error de conexión.', true));
    });
}

function showInfoWindow(polygon, zone) {
    const content = `<div class="info-window-content"><h4>${zone.nombre}</h4><p><strong>Estado:</strong> ${zone.status}</p><p>A Favor: ${zone.stats.a_favor} | Ind: ${zone.stats.indeciso} | En Contra: ${zone.stats.en_contra}</p></div>`;
    infoWindow.setContent(content);
    const bounds = new google.maps.LatLngBounds();
    polygon.getPath().forEach(path => bounds.extend(path));
    infoWindow.setPosition(bounds.getCenter());
    infoWindow.open(map);
}

function applyFilters() {
    const statusFilter = document.getElementById('filterStatus').value;
    const nameFilter = document.getElementById('filterZones').value.toLowerCase();
    
    const filteredZones = allZonesData.filter(zone => {
        const matchesStatus = (statusFilter === 'all' || zone.status === statusFilter);
        const matchesName = zone.nombre.toLowerCase().includes(nameFilter);
        return matchesStatus && matchesName;
    });

    renderZoneList(filteredZones);
    polygons.forEach((polygon, zoneId) => {
        const zoneIsVisible = filteredZones.some(z => z.id == zoneId);
        polygon.setVisible(zoneIsVisible);
    });
}

