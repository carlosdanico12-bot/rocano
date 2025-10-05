<?php
$page_title = 'Generación de Reportes';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

require_once __DIR__ . '/../../includes/header.php';

// Lista de reportes disponibles
$reports = [
    'votantes_por_zona' => [
        'title' => 'Padrón de Votantes por Zona',
        'description' => 'Genera una lista completa de todos los votantes registrados, agrupados y ordenados por su zona asignada.',
        'icon' => 'fa-users'
    ],
    'indecisos_por_sector' => [
        'title' => 'Votantes Indecisos por Sector',
        'description' => 'Crea un informe enfocado en los votantes marcados como "indecisos", ideal para acciones de campaña específicas.',
        'icon' => 'fa-question-circle'
    ],
    'tareas_por_voluntario' => [
        'title' => 'Tareas Cumplidas por Voluntario',
        'description' => 'Muestra el rendimiento de cada voluntario, listando las tareas que han completado y sus fechas.',
        'icon' => 'fa-tasks'
    ],
    'directorio_voluntarios' => [
        'title' => 'Directorio General de Voluntarios',
        'description' => 'Genera una lista de contacto con todos los voluntarios activos registrados en el sistema de campaña.',
        'icon' => 'fa-address-book'
    ],
];
?>

<div class="page-header">
    <h1>Centro de Reportes</h1>
    <p class="page-subtitle">Selecciona un reporte para generar y exportar los datos de la campaña en formato PDF.</p>
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
                <a href="<?php echo BASE_URL; ?>reports/generate_report.php?report=<?php echo $key; ?>" target="_blank" class="cta-button">
                    <i class="fas fa-file-pdf"></i> Generar PDF
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="page-header" style="margin-top: 40px;">
    <h2>Reportes de Encuestas</h2>
     <p class="page-subtitle">Para generar el reporte de una encuesta, ve a la sección de <a href="<?php echo BASE_URL; ?>admin/encuestas/">Gestión de Encuestas</a> y haz clic en el botón de reporte de la encuesta deseada.</p>
</div>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

