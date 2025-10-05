<?php
$page_title = 'Gestión de Encuestas';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Solo los administradores pueden acceder
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$success_message = '';

// --- Lógica para manejar la creación de una nueva encuesta ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_survey'])) {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';

    if (empty($titulo)) $errors[] = "El título de la encuesta es obligatorio.";
    if (empty($fecha_inicio)) $errors[] = "La fecha de inicio es obligatoria.";

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO surveys (titulo, descripcion, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            // Si la fecha de fin está vacía, la insertamos como NULL
            $fecha_fin_db = empty($fecha_fin) ? null : $fecha_fin;
            $stmt->bind_param("ssss", $titulo, $descripcion, $fecha_inicio, $fecha_fin_db);
            
            if ($stmt->execute()) {
                $success_message = "¡Encuesta creada con éxito!";
            } else {
                $errors[] = "Error al crear la encuesta.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// --- Lógica para obtener las encuestas existentes ---
$surveys = [];
try {
    $result = $conn->query("SELECT * FROM surveys ORDER BY fecha_inicio DESC");
    while($row = $result->fetch_assoc()) {
        $surveys[] = $row;
    }
} catch (Exception $e) {
    $errors[] = "Error al cargar las encuestas.";
}


require_once __DIR__ . '/../../includes/header.php';
?>

<div class="surveys-container">
    <div class="page-header">
        <h1>Gestión de Encuestas</h1>
        <button id="addSurveyBtn" class="cta-button"><i class="fas fa-plus"></i> Crear Nueva Encuesta</button>
    </div>

    <?php if ($success_message): ?>
        <div class="message success-message"><?php echo e($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="message error-message"><?php echo e(implode('<br>', $errors)); ?></div>
    <?php endif; ?>

    <div class="surveys-grid">
        <?php if (empty($surveys)): ?>
            <div class="card no-data-card">
                <p>Aún no se han creado encuestas. ¡Crea la primera ahora!</p>
            </div>
        <?php else: ?>
            <?php foreach ($surveys as $survey): ?>
                <div class="card survey-card">
                    <div class="survey-card-header">
                        <span class="status-badge status-<?php 
                            $today = date('Y-m-d');
                            if ($survey['fecha_inicio'] > $today) echo 'scheduled';
                            elseif (!empty($survey['fecha_fin']) && $survey['fecha_fin'] < $today) echo 'finished';
                            else echo 'active';
                        ?>">
                            <?php 
                                if ($survey['fecha_inicio'] > $today) echo 'Programada';
                                elseif (!empty($survey['fecha_fin']) && $survey['fecha_fin'] < $today) echo 'Finalizada';
                                else echo 'Activa';
                            ?>
                        </span>
                        <h3><?php echo e($survey['titulo']); ?></h3>
                    </div>
                    <p class="survey-description"><?php echo e($survey['descripcion'] ?: 'Sin descripción.'); ?></p>
                    <div class="survey-dates">
                        <span><i class="fas fa-play"></i> Inicio: <?php echo date('d/m/Y', strtotime($survey['fecha_inicio'])); ?></span>
                        <?php if(!empty($survey['fecha_fin'])): ?>
                            <span><i class="fas fa-stop"></i> Fin: <?php echo date('d/m/Y', strtotime($survey['fecha_fin'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="survey-actions">
                        <a href="#" class="action-btn results-btn"><i class="fas fa-chart-pie"></i> Resultados</a>
                        <a href="#" class="action-btn edit-btn"><i class="fas fa-edit"></i> Editar</a>
                        <a href="#" class="action-btn delete-btn"><i class="fas fa-trash-alt"></i> Eliminar</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para Crear Nueva Encuesta -->
<div id="addSurveyModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Crear Nueva Encuesta</h2>
        <form action="index.php" method="POST">
            <input type="hidden" name="create_survey" value="1">
            <div class="form-group">
                <label for="titulo">Título de la Encuesta</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción (Opcional)</label>
                <textarea id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <div class="form-group-grid">
                <div class="form-group">
                    <label for="fecha_inicio">Fecha de Inicio</label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label for="fecha_fin">Fecha de Finalización (Opcional)</label>
                    <input type="date" id="fecha_fin" name="fecha_fin">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" class="cta-button">Guardar Encuesta</button>
            </div>
        </form>
    </div>
</div>


<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
