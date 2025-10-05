<?php
$page_title = 'Encuestas para Participar';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Solo los voluntarios pueden acceder
if ($_SESSION['user_role'] !== 'voluntario') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$volunteer_id = $_SESSION['user_id'];
$surveys = [];

try {
    // 1. Obtener todas las encuestas que están actualmente activas
    $sql = "SELECT id, titulo, descripcion, fecha_fin 
            FROM surveys 
            WHERE CURDATE() BETWEEN fecha_inicio AND fecha_fin
            ORDER BY fecha_fin ASC";
    
    $result = $conn->query($sql);
    
    // 2. Para cada encuesta, verificar si este voluntario ya respondió
    while ($survey = $result->fetch_assoc()) {
        $stmt_check = $conn->prepare("SELECT id FROM responses WHERE survey_id = ? AND user_id = ?");
        $stmt_check->bind_param("ii", $survey['id'], $volunteer_id);
        $stmt_check->execute();
        $response_result = $stmt_check->get_result();
        
        // Añadir una bandera 'completada' al array de la encuesta
        $survey['completed'] = ($response_result->num_rows > 0);
        
        $surveys[] = $survey;
        $stmt_check->close();
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar las encuestas: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Encuestas Disponibles</h1>
    <p class="page-subtitle">Tu participación es clave. Responde las siguientes encuestas para ayudarnos a mejorar la campaña.</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="message error-message">
        <?php foreach ($errors as $error): ?><p><?php echo e($error); ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="surveys-grid">
    <?php if (empty($surveys)): ?>
        <div class="card card-empty">
            <i class="fas fa-poll-h"></i>
            <h3>No hay encuestas activas</h3>
            <p>Vuelve a consultar más tarde. ¡Gracias por tu compromiso!</p>
        </div>
    <?php else: ?>
        <?php foreach ($surveys as $survey): ?>
            <div class="card survey-card <?php echo $survey['completed'] ? 'completed' : ''; ?>">
                <div class="survey-icon">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div class="survey-content">
                    <h3 class="survey-title"><?php echo e($survey['titulo']); ?></h3>
                    <p class="survey-description"><?php echo e($survey['descripcion'] ?: 'Participa en esta encuesta para compartir tu opinión.'); ?></p>
                </div>
                <div class="survey-action">
                    <?php if ($survey['completed']): ?>
                        <button class="cta-button completed-btn" disabled>
                            <i class="fas fa-check-circle"></i> Completada
                        </button>
                    <?php else: ?>
                        <a href="#" class="cta-button">
                            <i class="fas fa-pencil-alt"></i> Participar
                        </a>
                    <?php endif; ?>
                    <small class="survey-due-date">Disponible hasta: <?php echo date('d/m/Y', strtotime($survey['fecha_fin'])); ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
