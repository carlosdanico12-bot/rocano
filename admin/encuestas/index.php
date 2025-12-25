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

// --- Lógica para eliminar encuesta ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_survey'])) {
    $id = (int)$_POST['survey_id'];
    try {
        $sql = "DELETE FROM surveys WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success_message = "Encuesta eliminada con éxito.";
        } else {
            $errors[] = "Error al eliminar la encuesta.";
        }
        $stmt->close();
    } catch (Exception $e) {
        $errors[] = "Error en la base de datos: " . $e->getMessage();
    }
}

// --- Lógica para editar encuesta ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_survey'])) {
    $id = (int)$_POST['survey_id'];
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';

    if (empty($titulo)) $errors[] = "El título es obligatorio.";
    if (empty($fecha_inicio)) $errors[] = "La fecha de inicio es obligatoria.";

    if (empty($errors)) {
        try {
            $sql = "UPDATE surveys SET titulo = ?, descripcion = ?, fecha_inicio = ?, fecha_fin = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $fecha_fin_db = empty($fecha_fin) ? null : $fecha_fin;
            $stmt->bind_param("ssssi", $titulo, $descripcion, $fecha_inicio, $fecha_fin_db, $id);
            if ($stmt->execute()) {
                $success_message = "Encuesta actualizada con éxito.";
            } else {
                $errors[] = "Error al actualizar la encuesta.";
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
        <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="message error-message"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
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
                        <h3><?php echo htmlspecialchars($survey['titulo']); ?></h3>
                    </div>
                    <p class="survey-description"><?php echo htmlspecialchars($survey['descripcion'] ?: 'Sin descripción.'); ?></p>
                    <div class="survey-dates">
                        <span><i class="fas fa-play"></i> Inicio: <?php echo date('d/m/Y', strtotime($survey['fecha_inicio'])); ?></span>
                        <?php if(!empty($survey['fecha_fin'])): ?>
                            <span><i class="fas fa-stop"></i> Fin: <?php echo date('d/m/Y', strtotime($survey['fecha_fin'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="survey-actions">
                        <a href="../reports/encuestas-resultados.php?id=<?php echo $survey['id']; ?>" class="action-btn results-btn"><i class="fas fa-chart-pie"></i> Resultados</a>
                        <button type="button" class="action-btn edit-btn edit-survey-btn" onclick="openEditModal(<?php echo e($survey['id']); ?>, '<?php echo e($survey['titulo']); ?>', '<?php echo e($survey['descripcion']); ?>', '<?php echo e($survey['fecha_inicio']); ?>', '<?php echo e($survey['fecha_fin']); ?>')" data-id="<?php echo $survey['id']; ?>" data-titulo="<?php echo addslashes(htmlspecialchars($survey['titulo'])); ?>" data-descripcion="<?php echo addslashes(htmlspecialchars($survey['descripcion'] ?: '')); ?>" data-fecha-inicio="<?php echo $survey['fecha_inicio']; ?>" data-fecha-fin="<?php echo $survey['fecha_fin'] ?: ''; ?>"><i class="fas fa-edit"></i> Editar</button>
                        <form method="POST" style="display:inline;" class="delete-survey-form">
                            <input type="hidden" name="delete_survey" value="1">
                            <input type="hidden" name="survey_id" value="<?php echo $survey['id']; ?>">
                            <button type="submit" class="action-btn delete-btn"><i class="fas fa-trash-alt"></i> Eliminar</button>
                        </form>
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

<!-- Modal para Editar Encuesta -->
<div id="editSurveyModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Editar Encuesta</h2>
        <form action="index.php" method="POST">
            <input type="hidden" name="edit_survey" value="1">
            <input type="hidden" id="edit_survey_id" name="survey_id" value="">
            <div class="form-group">
                <label for="edit_titulo">Título de la Encuesta</label>
                <input type="text" id="edit_titulo" name="titulo" required>
            </div>
            <div class="form-group">
                <label for="edit_descripcion">Descripción (Opcional)</label>
                <textarea id="edit_descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <div class="form-group-grid">
                <div class="form-group">
                    <label for="edit_fecha_inicio">Fecha de Inicio</label>
                    <input type="date" id="edit_fecha_inicio" name="fecha_inicio" required>
                </div>
                <div class="form-group">
                    <label for="edit_fecha_fin">Fecha de Finalización (Opcional)</label>
                    <input type="date" id="edit_fecha_fin" name="fecha_fin">
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" class="cta-button">Actualizar Encuesta</button>
            </div>
        </form>
    </div>
</div>



<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
