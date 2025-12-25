<?php
$page_title = 'Gestión de Zonas y Territorio';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

// -----------------------------------------------------------------------------
// IMPORTANTE: REEMPLAZA CON TU PROPIA CLAVE DE API DE GOOGLE MAPS
// -----------------------------------------------------------------------------
// Para que el mapa funcione, debes obtener una clave de API de Google Cloud Platform.
// Asegúrate de que la "Maps JavaScript API" y la "Places API" estén activadas
// y que la facturación esté configurada en tu cuenta.
$google_maps_api_key = 'AIzaSyCYdC0aQ1FWPFM35uDGOJOafl8Bfsshi1A'; 

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="zones-container">
    <div class="title-card card">
        <div class="page-header">
            <h1>Gestión de Zonas y Territorio</h1>
            <button id="addZoneBtn" class="cta-button"><i class="fas fa-plus"></i> Crear Nueva Zona</button>
        </div>
    </div>

    <div class="zones-layout">
        <div class="zones-list-column">
            <div class="card">
                <div class="card-header">
                    <h3>Listado de Zonas</h3>
                    <div class="filters">
                        <input type="text" id="filterZones" placeholder="Buscar zona...">
                        <select id="filterStatus">
                            <option value="all">Todos los estados</option>
                            <option value="A favor">A favor</option>
                            <option value="Indeciso">Indeciso</option>
                            <option value="En contra">En contra</option>
                            <option value="Sin Votantes">Sin Votantes</option>
                        </select>
                        <select id="filterCoordinator">
                            <option value="all">Todos los coordinadores</option>
                            <!-- Options will be populated dynamically -->
                        </select>
                        <select id="filterDate">
                            <option value="all">Todas las fechas</option>
                            <option value="recent">Recientes (última semana)</option>
                            <option value="month">Este mes</option>
                            <option value="year">Este año</option>
                        </select>
                    </div>
                </div>
                <div id="zones-list-container" class="zones-list">
                    <div class="loader">Cargando zonas...</div>
                </div>
            </div>
        </div>

        <div class="map-column">
            <div class="card map-card">
                <div id="map-controls">
                    <button id="moveMapBtn" class="map-control-btn active" title="Mover mapa">
                        <i class="fas fa-hand-paper"></i>
                    </button>
                    <button id="drawPolygonBtn" class="map-control-btn" title="Dibujar zona">
                        <i class="fas fa-draw-polygon"></i>
                    </button>
                </div>
                <div id="map"></div>
            </div>
        </div>
    </div>


</div>

<!-- Modal para Crear/Editar Zona -->
<div id="zoneModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modalTitle">Crear Nueva Zona</h2>
        <form id="zoneForm">
            <input type="hidden" name="zone_id" id="zone_id">
            <input type="hidden" name="coordinates" id="coordinates">
            <div class="form-group">
                <label for="nombre">Nombre de la Zona</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <p class="drawing-prompt">Usa las herramientas del mapa para dibujar el polígono. Haz clic en el primer punto para cerrar la forma.</p>
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" class="cta-button">Guardar Zona</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Notificaciones y Confirmaciones -->
<div id="notificationModal" class="modal">
    <div class="modal-content small">
        <span class="close-btn">&times;</span>
        <h2 id="notificationTitle">Aviso</h2>
        <p id="notificationMessage"></p>
        <div class="form-actions">
            <button type="button" class="cancel-btn" id="notificationCancel">Cancelar</button>
            <button type="button" class="cta-button" id="notificationOk">Aceptar</button>
        </div>
    </div>
</div>

<!-- Scripts al final del body -->
<script src="script.js"></script>
<script
    src="https://maps.googleapis.com/maps/api/js?key=<?php echo $google_maps_api_key; ?>&libraries=drawing,geometry&callback=initMap"
    async
    defer>
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
