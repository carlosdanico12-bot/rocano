<?php
$page_title = 'Subir Evidencias';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'voluntario') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$success_message = '';
$volunteer_id = $_SESSION['user_id'];

// --- Lógica de subida de evidencias ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_id = $_POST['task_id'] ?? null;
    $description = trim($_POST['description'] ?? '');

    if (empty($task_id)) {
        $errors[] = "Debes seleccionar la tarea a la que corresponde la evidencia.";
    }
    if (!isset($_FILES['evidence_file']) || $_FILES['evidence_file']['error'] != 0) {
        $errors[] = "Debes seleccionar un archivo de evidencia.";
    }

    if (empty($errors)) {
        // Manejo del archivo
        $target_dir = __DIR__ . "/../../public/images/evidencias/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $file_name = uniqid() . '-' . basename($_FILES["evidence_file"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_url = 'public/images/evidencias/' . $file_name;

        if (move_uploaded_file($_FILES["evidence_file"]["tmp_name"], $target_file)) {
            try {
                $stmt = $conn->prepare("INSERT INTO evidencias (task_id, user_id, file_url, description) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $task_id, $volunteer_id, $file_url, $description);
                if ($stmt->execute()) {
                    $success_message = "¡Evidencia subida con éxito!";
                } else {
                    $errors[] = "Error al guardar la evidencia en la base de datos.";
                }
            } catch (Exception $e) {
                $errors[] = "Error de base de datos: " . $e->getMessage();
            }
        } else {
            $errors[] = "Hubo un error al subir el archivo.";
        }
    }
}

// --- Obtener datos para la página ---
$completed_tasks = [];
$evidences = [];
try {
    // Tareas completadas del voluntario que aún no tienen evidencia
    $sql_tasks = "SELECT t.id, t.titulo FROM tasks t 
                  LEFT JOIN evidencias e ON t.id = e.task_id
                  WHERE t.asignado_a = ? AND t.estado = 'Completada' AND e.id IS NULL
                  ORDER BY t.titulo ASC";
    $stmt_tasks = $conn->prepare($sql_tasks);
    $stmt_tasks->bind_param("i", $volunteer_id);
    $stmt_tasks->execute();
    $result_tasks = $stmt_tasks->get_result();
    while ($row = $result_tasks->fetch_assoc()) {
        $completed_tasks[] = $row;
    }

    // Evidencias ya subidas por el voluntario
    $sql_evidences = "SELECT e.*, t.titulo as task_title FROM evidencias e JOIN tasks t ON e.task_id = t.id WHERE e.user_id = ? ORDER BY e.uploaded_at DESC";
    $stmt_evidences = $conn->prepare($sql_evidences);
    $stmt_evidences->bind_param("i", $volunteer_id);
    $stmt_evidences->execute();
    $result_evidences = $stmt_evidences->get_result();
    while ($row = $result_evidences->fetch_assoc()) {
        $evidences[] = $row;
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar los datos: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Mis Evidencias</h1>
    <p class="page-subtitle">Sube aquí las pruebas (fotos, capturas) de tus tareas completadas.</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="message error-message">
        <?php foreach ($errors as $error): ?><p><?php echo e($error); ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($success_message): ?>
    <div class="message success-message"><p><?php echo e($success_message); ?></p></div>
<?php endif; ?>

<div class="evidence-layout">
    <!-- Columna del Formulario -->
    <div class="card upload-card">
        <h3><i class="fas fa-upload"></i> Subir Nueva Evidencia</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="task_id">1. ¿A qué tarea completada corresponde?</label>
                <select name="task_id" id="task_id" required>
                    <option value="" disabled selected>-- Selecciona una tarea --</option>
                    <?php foreach($completed_tasks as $task): ?>
                        <option value="<?php echo e($task['id']); ?>"><?php echo e($task['titulo']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="evidence_file">2. Selecciona el archivo (imagen)</label>
                <div class="file-drop-zone">
                    <i class="fas fa-file-image"></i>
                    <p>Arrastra y suelta una imagen aquí, o haz clic para seleccionar</p>
                    <input type="file" name="evidence_file" id="evidence_file" accept="image/*" required>
                </div>
                <div id="image-preview-container"></div>
            </div>
            <div class="form-group">
                <label for="description">3. Añade una descripción (opcional)</label>
                <textarea name="description" id="description" rows="3" placeholder="Ej: Foto de volanteo en la plaza central."></textarea>
            </div>
            <button type="submit" class="cta-button"><i class="fas fa-check-circle"></i> Enviar Evidencia</button>
        </form>
    </div>

    <!-- Columna del Historial -->
    <div class="history-column">
        <h3><i class="fas fa-history"></i> Historial de Subidas</h3>
        <div class="evidence-grid">
            <?php if(empty($evidences)): ?>
                <p>Aún no has subido ninguna evidencia.</p>
            <?php else: ?>
                <?php foreach($evidences as $evidence): ?>
                <div class="card evidence-card">
                    <a href="<?php echo BASE_URL . e($evidence['file_url']); ?>" target="_blank">
                        <img src="<?php echo BASE_URL . e($evidence['file_url']); ?>" alt="Evidencia de <?php echo e($evidence['task_title']); ?>">
                    </a>
                    <div class="evidence-info">
                        <strong><?php echo e($evidence['task_title']); ?></strong>
                        <small>Subido el <?php echo date('d/m/Y', strtotime($evidence['uploaded_at'])); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
