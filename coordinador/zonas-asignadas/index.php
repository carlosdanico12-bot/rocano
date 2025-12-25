<?php
$page_title = 'Mis Zonas Asignadas';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'coordinador') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="assignments-container">
    <div class="title-card card">
        <div class="page-header">
            <h1>Mis Zonas Asignadas</h1>
        </div>
    </div>

    <div class="filters-section card">
        <h3>Filtros</h3>
        <div class="filters-grid">
            <div class="filter-group">
                <label for="filterZone">Zona:</label>
                <select id="filterZone">
                    <option value="all">Todas mis zonas</option>
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
        <div id="assignmentsContainer">
            <div class="loader">Cargando asignaciones...</div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="script.js"></script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
