<?php
$page_title = 'Zonas Asignadas a Personal';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="assignments-container">
    <div class="title-card card">
        <div class="page-header">
            <h1>Zonas Asignadas a Personal</h1>
        </div>
    </div>

    <div class="filters-section card">
        <h3>Filtros</h3>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="filterZone">Zona:</label>
                <select id="filterZone">
                    <option value="all">Todas las zonas</option>
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="filter-group">
                <label for="filterCoordinator">Coordinador:</label>
                <select id="filterCoordinator">
                    <option value="all">Todos los coordinadores</option>
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="filter-group">
                <label for="filterVolunteer">Voluntario:</label>
                <select id="filterVolunteer">
                    <option value="all">Todos los voluntarios</option>
                    <!-- Options will be populated dynamically -->
                </select>
            </div>
            <div class="filter-group">
                <button id="clearFilters" class="btn-secondary">Limpiar Filtros</button>
            </div>
        </div>
    </div>

    <div class="assignments-content card">
        <div class="content-header">
            <h3>Asignaciones Actuales</h3>
            <div class="action-buttons">
                <button id="assignCoordinatorBtn" class="cta-button"><i class="fas fa-user-tie"></i> Asignar Coordinador</button>
                <button id="assignVolunteerBtn" class="cta-button"><i class="fas fa-users"></i> Asignar Voluntario</button>
            </div>
        </div>
        <div id="assignmentsContainer">
            <div class="loader">Cargando asignaciones...</div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="script.js"></script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
