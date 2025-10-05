<?php
$page_title = 'Encuestas de Campaña';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Solo Administradores y Coordinadores pueden acceder
if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'coordinador') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$surveys = [];

try {
    // Consulta para obtener todas las encuestas y contar sus respuestas
    $sql = "SELECT s.id, s.titulo, s.descripcion, s.fecha_inicio, s.fecha_fin, 
                   (SELECT COUNT(*) FROM responses WHERE survey_id = s.id) as response_count
            FROM surveys s
            ORDER BY s.fecha_inicio DESC";
    
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $surveys[] = $row;
    }
} catch (Exception $e) {
    $errors[] = "Error al cargar las encuestas: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Encuestas de Campaña</h1>
    <p class="page-subtitle">Visualiza y gestiona las encuestas para medir la opinión de los votantes.</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="message error-message">
        <?php foreach ($errors as $error): ?><p><?php echo e($error); ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="surveys-grid">
    <?php if (empty($surveys)): ?>
        <div class="card card-empty">
            <p>No hay encuestas disponibles en este momento.</p>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>admin/encuestas/" class="cta-button">Crear la primera encuesta</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($surveys as $survey): ?>
            <?php
                // Determinar el estado de la encuesta
                $today = new DateTime();
                $start_date = new DateTime($survey['fecha_inicio']);
                $end_date = new DateTime($survey['fecha_fin']);
                $status = '';
                $status_class = '';

                if ($today < $start_date) {
                    $status = 'Próxima';
                    $status_class = 'upcoming';
                } elseif ($today >= $start_date && $today <= $end_date) {
                    $status = 'Activa';
                    $status_class = 'active';
                } else {
                    $status = 'Finalizada';
                    $status_class = 'finished';
                }
            ?>
            <div class="card survey-card">
                <div class="survey-header">
                    <h3 class="survey-title"><?php echo e($survey['titulo']); ?></h3>
                    <span class="status-badge status-<?php echo $status_class; ?>"><?php echo $status; ?></span>
                </div>
                <p class="survey-description"><?php echo e($survey['descripcion'] ?: 'Sin descripción.'); ?></p>
                <div class="survey-meta">
                    <span><i class="fas fa-calendar-alt"></i> Del <?php echo date("d/m/Y", strtotime($survey['fecha_inicio'])); ?> al <?php echo date("d/m/Y", strtotime($survey['fecha_fin'])); ?></span>
                    <span><i class="fas fa-poll"></i> <?php echo $survey['response_count']; ?> Respuestas</span>
                </div>
                <div class="survey-actions">
                    <a href="#" class="action-btn view-results-btn"><i class="fas fa-chart-bar"></i> Ver Resultados</a>
                    <a href="#" class="action-btn edit-btn"><i class="fas fa-edit"></i></a>
                    <a href="#" class="action-btn delete-btn" data-survey-id="<?php echo $survey['id']; ?>"><i class="fas fa-trash-alt"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
