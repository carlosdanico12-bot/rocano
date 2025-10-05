<?php
$page_title = 'Centro de Reportes';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Solo los coordinadores pueden acceder
if ($_SESSION['user_role'] !== 'coordinador') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

require_once __DIR__ . '/../../includes/header.php';

// Lista de reportes disponibles para el coordinador
$reports = [
    'votantes_mis_zonas' => [
        'title' => 'Padrón de Votantes en mis Zonas',
        'description' => 'Genera una lista de todos los votantes registrados en las zonas que tienes asignadas.',
        'icon' => 'fa-users'
    ],
    'indecisos_mis_zonas' => [
        'title' => 'Votantes Indecisos en mis Zonas',
        'description' => 'Crea un informe enfocado en los votantes marcados como "indecisos" dentro de tu territorio.',
        'icon' => 'fa-question-circle'
    ],
    'tareas_mi_equipo' => [
        'title' => 'Rendimiento de mi Equipo',
        'description' => 'Muestra un resumen de las tareas asignadas a los voluntarios que están bajo tu coordinación.',
        'icon' => 'fa-tasks'
    ],
];

?>

<div class="page-header">
    <h1>Reportes de mi Territorio</h1>
    <p class="page-subtitle">Genera informes en PDF con los datos específicos de tus zonas y equipos asignados.</p>
</div>

<div class="reports-grid">
    <?php foreach ($reports as $key => $report): ?>
        <div class="card report-card">
            <div class="report-icon">
                <i class="fas <?php echo e($report['icon']); ?>"></i>
            </div>
            <div class="report-info">
                <h3><?php echo e($report['title']); ?></h3>
                <p><?php echo e($report['description']); ?></p>
            </div>
            <div class="report-actions">
                <a href="<?php echo BASE_URL; ?>coordinador/reportes/generate_report.php?report=<?php echo $key; ?>" target="_blank" class="cta-button">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
