// INDICACIÓN PARA MODIFICAR:
// Este script es el corazón de la página. Puedes modificar los colores del semáforo en la constante `COLORS`.

document.addEventListener('DOMContentLoaded', function() {
    let map;
    let drawingManager;
    let selectedShape;
    let infoWindow;
    const polygons = new Map();
    let allZonesData = [];

    const COLORS = {
        'A favor': '#28a745', 'Indeciso': '#ffc107', 'En contra': '#dc3545', 'Sin Votantes': '#6c757d'
    };

    // --- 0. MODALES PERSONALIZADOS ---
    const notificationModal = document.getElementById('notificationModal');
    const notificationTitle = document.getElementById('notificationTitle');
    const notificationMessage = document.getElementById('notificationMessage');
    const notificationOkBtn = document.getElementById('notificationOk');
    const notificationCancelBtn = document.getElementById('notificationCancel');

    function showNotification(message, isError = false) {
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


    // --- 1. INICIALIZACIÓN ---
    function initMap() {
        const tingoMaria = { lat: -9.294, lng: -76.007 };
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 13, center: tingoMaria, mapTypeId: 'roadmap'
        });
        infoWindow = new google.maps.InfoWindow();
        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: null, drawingControl: false,
            polygonOptions: { editable: true, draggable: true, fillColor: '#E63946', strokeColor: '#E63946' }
        });
        drawingManager.setMap(map);
        google.maps.event.addListener(drawingManager, 'overlaycomplete', onPolygonComplete);
        
        fetchZonesAndRender();
        setupMapControls();
    }

    // --- 2. OBTENER DATOS Y RENDERIZAR ---
    function fetchZonesAndRender() {
        fetch('ajax_zonas.php?action=get_all_with_stats')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allZonesData = data.zones;
                    renderZoneList(allZonesData);
                    drawSavedZones(allZonesData);
                } else {
                    showNotification(`Error al cargar zonas: ${data.error}`, true);
                    document.getElementById('zones-list-container').innerHTML = `<p class="empty-list">Error: ${data.error}</p>`;
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
                    </div>
                </div>
                <div class="zone-actions">
                    <button class="action-btn edit-btn" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                </div>`;
            listContainer.appendChild(item);
            item.querySelector('.zone-info').addEventListener('click', () => onZoneItemClick(zone.id));
            item.querySelector('.edit-btn').addEventListener('click', () => onEditZone(zone.id));
            item.querySelector('.delete-btn').addEventListener('click', () => onDeleteZone(zone.id));
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
                } catch(e) {}
            }
        });
    }
    
    function onPolygonComplete(event) {
        if (selectedShape) selectedShape.setMap(null);
        selectedShape = event.overlay;
        updateCoordinates(selectedShape);
        drawingManager.setDrawingMode(null);
        
        // CORRECCIÓN: Añadir listeners para actualizar coordenadas al editar
        google.maps.event.addListener(selectedShape.getPath(), 'set_at', () => updateCoordinates(selectedShape));
        google.maps.event.addListener(selectedShape.getPath(), 'insert_at', () => updateCoordinates(selectedShape));
        google.maps.event.addListener(selectedShape.getPath(), 'remove_at', () => updateCoordinates(selectedShape));

        document.getElementById('moveMapBtn').classList.add('active');
        document.getElementById('drawPolygonBtn').classList.remove('active');
    }
    
    function updateCoordinates(shape) {
        const path = shape.getPath();
        const coords = [];
        for (let i = 0; i < path.getLength(); i++) {
            const latLng = path.getAt(i);
            coords.push({ lat: latLng.lat(), lng: latLng.lng() });
        }
        document.getElementById('coordinates').value = JSON.stringify(coords);
    }
    
    // --- 3. MANEJO DE EVENTOS E INTERACTIVIDAD ---
    const modal = document.getElementById('zoneModal');
    const zoneForm = document.getElementById('zoneForm');

    document.getElementById('addZoneBtn').addEventListener('click', () => {
        zoneForm.reset();
        document.getElementById('modalTitle').textContent = 'Crear Nueva Zona';
        document.getElementById('zone_id').value = '';
        if (selectedShape) selectedShape.setMap(null);
        selectedShape = null;
        modal.style.display = 'block';
        document.getElementById('drawPolygonBtn').click(); // Activar modo dibujo
    });
    
    modal.querySelector('.close-btn').addEventListener('click', () => modal.style.display = 'none');
    modal.querySelector('.cancel-btn').addEventListener('click', () => modal.style.display = 'none');

    function onZoneItemClick(zoneId) {
        document.querySelectorAll('.zone-item').forEach(i => i.classList.remove('selected'));
        const item = document.querySelector(`.zone-item[data-zone-id='${zoneId}']`);
        if(item) item.classList.add('selected');

        const polygon = polygons.get(parseInt(zoneId));
        const zoneData = allZonesData.find(z => z.id == zoneId);
        if (polygon && zoneData) {
            const bounds = new google.maps.LatLngBounds();
            polygon.getPath().forEach(path => bounds.extend(path));
            map.fitBounds(bounds);
            showInfoWindow(polygon, zoneData);
        }
    }

    function onEditZone(zoneId) {
        const zoneData = allZonesData.find(z => z.id == zoneId);
        if (!zoneData) return;

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
                        paths: coords,
                        editable: true, draggable: true,
                        fillColor: '#E63946', strokeColor: '#E63946',
                        map: map
                    });
                    updateCoordinates(selectedShape);
                    // Añadir listeners para actualizar coordenadas al editar
                    google.maps.event.addListener(selectedShape.getPath(), 'set_at', () => updateCoordinates(selectedShape));
                    google.maps.event.addListener(selectedShape.getPath(), 'insert_at', () => updateCoordinates(selectedShape));
                    google.maps.event.addListener(selectedShape.getPath(), 'remove_at', () => updateCoordinates(selectedShape));
                }
            } catch (e) {
                console.error("Error al parsear coordenadas para editar:", e);
                selectedShape = null;
            }
        } else {
             selectedShape = null;
        }
        modal.style.display = 'block';
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
        const content = `<div class="info-window-content"><h4>${zone.nombre}</h4><p><strong>Estado:</strong> ${zone.status}</p></div>`;
        infoWindow.setContent(content);
        const bounds = new google.maps.LatLngBounds();
        polygon.getPath().forEach(path => bounds.extend(path));
        infoWindow.setPosition(bounds.getCenter());
        infoWindow.open(map);
    }

    zoneForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
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
                    modal.style.display = 'none';
                    fetchZonesAndRender();
                } else {
                    showNotification('Error: ' + data.error, true);
                }
            }).catch(err => showNotification('Error de conexión.', true));
    });

    // --- 4. CONTROLES DEL MAPA Y FILTROS ---
    function setupMapControls() {
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
            drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
            drawBtn.classList.add('active');
            moveBtn.classList.remove('active');
        });
    }

    document.getElementById('filterStatus').addEventListener('change', applyFilters);
    document.getElementById('filterZones').addEventListener('input', applyFilters);

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
    
    initMap();
});
