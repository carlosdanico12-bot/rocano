// ===============================================
//  ADMIN PANEL GLOBAL SCRIPT V.3.0
// ===============================================
document.addEventListener('DOMContentLoaded', function() {

    // --- MENÚ LATERAL ---
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.getElementById('menu-toggle');
    const overlay = document.querySelector('.overlay');

    if (menuToggle && sidebar && overlay) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    }

    // --- MODALES ---
    const openModalButtons = document.querySelectorAll('[data-modal-target]');
    openModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = document.querySelector(button.dataset.modalTarget);
            if(modal) modal.style.display = 'block';
        });
    });

    const closeModalButtons = document.querySelectorAll('.modal .cancel-btn, .modal .close-btn');
    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if(modal) modal.style.display = 'none';
        });
    });

    window.addEventListener('click', (event) => {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });

    // ===============================================
    // ZONAS Y MAPA
    // ===============================================
    if(document.getElementById('map')) {

        let map, drawingManager, currentPolygon;
        const zonesListContainer = document.getElementById('zones-list-container');
        const filterZonesInput = document.getElementById('filterZones');
        const filterStatusSelect = document.getElementById('filterStatus');

        // Inicializar mapa
        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: {lat: -9.4316, lng: -75.9968}, // Tingo María
                zoom: 13
            });

            drawingManager = new google.maps.drawing.DrawingManager({
                drawingMode: null,
                drawingControl: true,
                drawingControlOptions: {
                    position: google.maps.ControlPosition.TOP_CENTER,
                    drawingModes: ['polygon']
                },
                polygonOptions: {
                    editable: true,
                    fillColor: '#FF0000',
                    fillOpacity: 0.4,
                    strokeWeight: 2,
                    clickable: true
                }
            });
            drawingManager.setMap(map);

            // Evento al completar polígono
            google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
                if(currentPolygon) currentPolygon.setMap(null);
                currentPolygon = polygon;

                const coords = polygon.getPath().getArray().map(pt => ({lat: pt.lat(), lng: pt.lng()}));
                document.getElementById('coordinates').value = JSON.stringify(coords);
            });
        }

        // Cargar zonas
        async function loadZones() {
            zonesListContainer.innerHTML = '<div class="loader">Cargando zonas...</div>';
            const res = await fetch('../../admin/zonas/ajax_zonas.php');
            const zonas = await res.json();

            zonesListContainer.innerHTML = '';

            zonas.forEach(zona => {
                const div = document.createElement('div');
                div.className = 'zone-item';
                div.innerHTML = `
                    <h4>${zona.nombre}</h4>
                    <p>${zona.descripcion || ''}</p>
                    <span class="status ${zona.estado.replace(' ', '').toLowerCase()}">${zona.estado}</span>
                    <button class="edit-zone" data-id="${zona.id}" data-nombre="${zona.nombre}" data-descripcion="${zona.descripcion}" data-coords='${zona.coordinates}'>Editar</button>
                `;
                zonesListContainer.appendChild(div);

                // Dibujar polígono en mapa
                if(zona.coordinates) {
                    const coords = JSON.parse(zona.coordinates);
                    const poly = new google.maps.Polygon({
                        paths: coords,
                        strokeColor: getColorByEstado(zona.estado),
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: getColorByEstado(zona.estado),
                        fillOpacity: 0.35,
                        map: map
                    });

                    // Al hacer click en la zona de la lista
                    div.querySelector('h4').addEventListener('click', () => {
                        map.fitBounds(poly.getBounds());
                    });
                }

                // Editar zona
                div.querySelector('.edit-zone').addEventListener('click', () => {
                    document.getElementById('zone_id').value = zona.id;
                    document.getElementById('nombre').value = zona.nombre;
                    document.getElementById('descripcion').value = zona.descripcion || '';
                    document.getElementById('coordinates').value = zona.coordinates || '';
                    document.getElementById('modalTitle').textContent = 'Editar Zona';
                    document.getElementById('zoneModal').style.display = 'block';
                });
            });
        }

        function getColorByEstado(estado) {
            switch(estado) {
                case 'A favor': return '#28a745';
                case 'Indeciso': return '#ffc107';
                case 'En contra': return '#dc3545';
                case 'Sin Votantes': return '#6c757d';
                default: return '#000000';
            }
        }

        // Guardar zona
        const zoneForm = document.getElementById('zoneForm');
        zoneForm.addEventListener('submit', async e => {
            e.preventDefault();
            const formData = new FormData(zoneForm);
            const res = await fetch('../../admin/zonas/ajax_zonas.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            alert(data.message);
            if(data.success) {
                zoneForm.reset();
                document.getElementById('zoneModal').style.display = 'none';
                loadZones();
                if(currentPolygon) currentPolygon.setMap(null);
            }
        });

        // Filtros
        filterZonesInput.addEventListener('input', loadZones);
        filterStatusSelect.addEventListener('change', loadZones);

        initMap();
        loadZones();
    }

});
