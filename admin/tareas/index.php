<?php
$page_title = 'Gestión de Tareas';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$success_message = '';

// --- Lógica de Creación/Actualización/Eliminación de Tareas ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $task_id = $_POST['task_id'] ?? null;
    
    // Eliminar Tarea
    if ($action === 'delete' && $task_id) {
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->bind_param("i", $task_id);
        if ($stmt->execute()) $success_message = "Tarea eliminada con éxito.";
        else $errors[] = "Error al eliminar la tarea.";
        $stmt->close();
    } 
    // Crear o Actualizar Tarea
    else {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $asignado_a = $_POST['asignado_a'] ?? null;
        $fecha_limite = $_POST['fecha_limite'] ?? null;
        $estado = $_POST['estado'] ?? 'Pendiente';

        if(empty($titulo) || empty($asignado_a)) $errors[] = "El título y el voluntario asignado son obligatorios.";
        
        if (empty($errors)) {
            if ($action === 'update' && $task_id) { // Actualizar
                $stmt = $conn->prepare("UPDATE tasks SET titulo = ?, descripcion = ?, asignado_a = ?, fecha_limite = ?, estado = ? WHERE id = ?");
                $stmt->bind_param("ssissi", $titulo, $descripcion, $asignado_a, $fecha_limite, $estado, $task_id);
                $success_message = "Tarea actualizada con éxito.";
            } else { // Crear
                $stmt = $conn->prepare("INSERT INTO tasks (titulo, descripcion, asignado_a, fecha_limite, estado, created_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssissi", $titulo, $descripcion, $asignado_a, $fecha_limite, $estado, $_SESSION['user_id']);
                $success_message = "Tarea creada y asignada con éxito.";
            }
            if (!$stmt->execute()) {
                $errors[] = "Error al guardar la tarea.";
                $success_message = '';
            }
            $stmt->close();
        }
    }
}

// --- Obtener Tareas y Voluntarios ---
$tasks = ['Pendiente' => [], 'En Progreso' => [], 'Completada' => []];
$volunteers = [];

try {
    // Obtener todas las tareas y unirlas con la info del voluntario asignado
    $sql_tasks = "SELECT t.*, u.name as voluntario_nombre, u.foto_url 
                  FROM tasks t 
                  JOIN users u ON t.asignado_a = u.id 
                  ORDER BY t.fecha_limite ASC";
    $result_tasks = $conn->query($sql_tasks);
    while($row = $result_tasks->fetch_assoc()) {
        $tasks[$row['estado']][] = $row;
    }

    // Obtener todos los voluntarios para el formulario
    $sql_volunteers = "SELECT id, name FROM users WHERE role_id = (SELECT id FROM roles WHERE name = 'voluntario') AND approved = 1 ORDER BY name ASC";
    $result_volunteers = $conn->query($sql_volunteers);
    while($row = $result_volunteers->fetch_assoc()) {
        $volunteers[] = $row;
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar los datos: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Tablero de Tareas (Kanban)</h1>
    <button id="addTaskBtn" class="cta-button"><i class="fas fa-plus"></i> Asignar Nueva Tarea</button>
</div>

<?php if (!empty($errors)): ?>
    <div class="message error-message">
        <?php foreach ($errors as $error): ?><p><?php echo e($error); ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($success_message): ?>
    <div class="message success-message"><p><?php echo e($success_message); ?></p></div>
<?php endif; ?>

<div class="kanban-board">
    <?php foreach ($tasks as $status => $task_list): ?>
        <div class="kanban-column" data-status="<?php echo $status; ?>">
            <h3 class="column-title">
                <?php echo $status; ?>
                <span class="task-count"><?php echo count($task_list); ?></span>
            </h3>
            <div class="task-list">
                <?php if (empty($task_list)): ?>
                    <div class="task-card empty-card">Arrastra una tarea aquí</div>
                <?php else: ?>
                    <?php foreach ($task_list as $task): ?>
                        <div class="task-card" draggable="true" data-task-id="<?php echo $task['id']; ?>">
                            <h4><?php echo e($task['titulo']); ?></h4>
                            <p><?php echo e($task['descripcion']); ?></p>
                            <div class="task-footer">
                                <div class="task-assignee">
                                    <img src="<?php echo BASE_URL . e($task['foto_url'] ?: 'assets/images/avatar-default.png'); ?>" alt="<?php echo e($task['voluntario_nombre']); ?>" title="<?php echo e($task['voluntario_nombre']); ?>">
                                    <span><?php echo e($task['voluntario_nombre']); ?></span>
                                </div>
                                <span class="task-due-date">
                                    <i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($task['fecha_limite'])); ?>
                                </span>
                            </div>
                            <div class="task-actions">
                                <button class="action-btn edit-btn" title="Editar"
                                    data-id="<?php echo e($task['id']); ?>"
                                    data-titulo="<?php echo e($task['titulo']); ?>"
                                    data-descripcion="<?php echo e($task['descripcion']); ?>"
                                    data-asignado="<?php echo e($task['asignado_a']); ?>"
                                    data-limite="<?php echo e($task['fecha_limite']); ?>"
                                    data-estado="<?php echo e($task['estado']); ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" onsubmit="return confirm('¿Seguro que quieres eliminar esta tarea?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="task_id" value="<?php echo e($task['id']); ?>">
                                    <button type="submit" class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal para Crear/Editar Tarea -->
<div id="taskModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modalTitle">Asignar Nueva Tarea</h2>
        <form id="taskForm" action="" method="POST">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="task_id" id="task_id">

            <div class="form-group">
                <label for="titulo">Título de la Tarea</label>
                <input type="text" id="titulo" name="titulo" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="3"></textarea>
            </div>
            <div class="form-group-grid">
                <div class="form-group">
                    <label for="asignado_a">Asignar a Voluntario</label>
                    <select id="asignado_a" name="asignado_a" required>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach($volunteers as $volunteer): ?>
                            <option value="<?php echo e($volunteer['id']); ?>"><?php echo e($volunteer['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="fecha_limite">Fecha Límite</label>
                    <input type="date" id="fecha_limite" name="fecha_limite">
                </div>
            </div>
             <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="Pendiente">Pendiente</option>
                    <option value="En Progreso">En Progreso</option>
                    <option value="Completada">Completada</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" class="cta-button" id="modalSubmitBtn">Crear Tarea</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

