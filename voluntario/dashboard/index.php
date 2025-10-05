<?php
// Incluir archivos de configuración y seguridad
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Redirigir si el rol no es 'voluntario'
if ($_SESSION['user_role'] !== 'voluntario') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$page_title = "Mi Panel de Voluntario";
$body_class = "dashboard-body"; // Clase específica para el body del dashboard

// Incluir el header del panel de administración
include __DIR__ . '/../../includes/header.php';

// Simulación de datos de tareas para el voluntario
// En una implementación real, estos datos vendrían de la base de datos.
$user_id = $_SESSION['user_id'];
// $query = $conn->prepare("SELECT COUNT(*) AS total, SUM(IF(estado = 'completada', 1, 0)) AS completadas FROM tareas WHERE voluntario_id = ?");
// $query->bind_param("i", $user_id);
// $query->execute();
// $result = $query->get_result()->fetch_assoc();
// $tareas_completadas = $result['completadas'] ?? 0;
// $tareas_pendientes = ($result['total'] ?? 0) - $tareas_completadas;
$tareas_pendientes = 5; // Dato de ejemplo
$tareas_completadas = 12; // Dato de ejemplo
$votantes_registrados = 28; // Dato de ejemplo


?>

<div class="page-content">
    <div class="dashboard-header">
        <h1>Bienvenido de nuevo, <?php echo e(explode(' ', $_SESSION['user_name'])[0]); ?>!</h1>
        <p>Aquí tienes un resumen de tu actividad en la campaña. ¡Tu apoyo es fundamental!</p>
    </div>

    <!-- Contenedor de las tarjetas de resumen -->
    <div class="stats-cards-container">
        <div class="stat-card">
            <div class="card-icon" style="--icon-color: #ff9f43;">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="card-info">
                <h2><?php echo $tareas_pendientes; ?></h2>
                <p>Tareas Pendientes</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="card-icon" style="--icon-color: #28c76f;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-info">
                <h2><?php echo $tareas_completadas; ?></h2>
                <p>Tareas Completadas</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="card-icon" style="--icon-color: #00cfe8;">
                <i class="fas fa-user-plus"></i>
            </div>
            <div class="card-info">
                <h2><?php echo $votantes_registrados; ?></h2>
                <p>Votantes Registrados</p>
            </div>
        </div>
    </div>

    <!-- Sección de Acciones Rápidas -->
    <div class="quick-actions">
        <h2 class="section-title">Acciones Rápidas</h2>
        <div class="actions-container">
            <a href="<?php echo BASE_URL; ?>voluntario/mis-tareas/" class="action-card">
                <i class="fas fa-clipboard-list"></i>
                <span>Ver Mis Tareas</span>
            </a>
            <a href="<?php echo BASE_URL; ?>voluntario/registrar-votante/" class="action-card">
                <i class="fas fa-user-edit"></i>
                <span>Registrar Votante</span>
            </a>
            <a href="<?php echo BASE_URL; ?>voluntario/evidencias/" class="action-card">
                <i class="fas fa-camera-retro"></i>
                <span>Subir Evidencias</span>
            </a>
        </div>
    </div>
</div>

<?php
// Incluir el footer del panel
include __DIR__ . '/../../includes/footer.php';
?>
