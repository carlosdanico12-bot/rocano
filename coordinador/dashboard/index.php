<?php
// Incluir archivos de configuración y seguridad
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Redirigir si el rol no es 'coordinador' o 'admin' (admin puede ver todo)
if (!in_array($_SESSION['user_role'], ['coordinador', 'admin'])) {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$page_title = "Panel de Coordinador";
$body_class = "dashboard-body"; // Clase específica para el body del dashboard

// Incluir el header del panel de administración
include __DIR__ . '/../../includes/header.php';

// Simulación de datos para el coordinador
// En una implementación real, estos datos vendrían de consultas a la BD filtradas por las zonas del coordinador.
$user_id = $_SESSION['user_id'];
// $query_zonas = $conn->prepare("SELECT GROUP_CONCAT(nombre) as zonas_asignadas FROM zonas WHERE coordinador_id = ?");
// ... etc
$zonas_asignadas = "Caserío Central, Sector Progreso"; // Ejemplo
$voluntarios_activos = 15; // Ejemplo
$votantes_registrados_zona = 125; // Ejemplo
$indecisos_detectados = 45; // Ejemplo

// Datos para la tabla de actividad reciente (simulados)
$actividad_reciente = [
    ['tipo' => 'Nuevo Votante', 'detalle' => 'María Rojas fue registrada en Sector Progreso.', 'hace' => '15 min'],
    ['tipo' => 'Tarea Completada', 'detalle' => 'Juan Pérez completó el volanteo en Caserío Central.', 'hace' => '1 hora'],
    ['tipo' => 'Nuevo Voluntario', 'detalle' => 'Carlos Vega se unió al equipo de Sector Progreso.', 'hace' => '3 horas'],
    ['tipo' => 'Votante Actualizado', 'detalle' => 'Luis Torres cambió su estado a "A Favor".', 'hace' => 'Ayer']
];
?>

<div class="page-content">
    <div class="dashboard-header">
        <h1>Panel de Coordinación</h1>
        <p>Gestiona tus zonas asignadas: <strong><?php echo e($zonas_asignadas); ?></strong></p>
    </div>

    <!-- Contenedor de las tarjetas de resumen -->
    <div class="stats-cards-container">
        <div class="stat-card">
            <div class="card-icon" style="--icon-color: #00cfe8;"><i class="fas fa-map-marked-alt"></i></div>
            <div class="card-info"><h2><?php echo $votantes_registrados_zona; ?></h2><p>Votantes en tus Zonas</p></div>
        </div>
        <div class="stat-card">
            <div class="card-icon" style="--icon-color: #ff9f43;"><i class="fas fa-users"></i></div>
            <div class="card-info"><h2><?php echo $voluntarios_activos; ?></h2><p>Voluntarios Activos</p></div>
        </div>
        <div class="stat-card">
            <div class="card-icon" style="--icon-color: #e63946;"><i class="fas fa-question-circle"></i></div>
            <div class="card-info"><h2><?php echo $indecisos_detectados; ?></h2><p>Indecisos Detectados</p></div>
        </div>
    </div>
    
    <!-- Contenedor para gráficos y actividad reciente -->
    <div class="dashboard-main-grid">
        <div class="chart-container card">
            <h3>Distribución de Votantes</h3>
            <canvas id="votantesChart"></canvas>
        </div>
        <div class="activity-container card">
            <h3>Actividad Reciente</h3>
            <ul class="activity-list">
                <?php foreach ($actividad_reciente as $actividad): ?>
                <li class="activity-item">
                    <div class="activity-icon">
                        <?php if($actividad['tipo'] == 'Nuevo Votante') echo '<i class="fas fa-user-plus"></i>'; ?>
                        <?php if($actividad['tipo'] == 'Tarea Completada') echo '<i class="fas fa-check"></i>'; ?>
                        <?php if($actividad['tipo'] == 'Nuevo Voluntario') echo '<i class="fas fa-hands-helping"></i>'; ?>
                         <?php if($actividad['tipo'] == 'Votante Actualizado') echo '<i class="fas fa-user-edit"></i>'; ?>
                    </div>
                    <div class="activity-details">
                        <p class="detail-text"><?php echo e($actividad['detalle']); ?></p>
                        <p class="detail-time"><?php echo e($actividad['hace']); ?></p>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

</div>

<?php
// Incluir el footer del panel, que cargará Chart.js y nuestro script
include __DIR__ . '/../../includes/footer.php';
?>
