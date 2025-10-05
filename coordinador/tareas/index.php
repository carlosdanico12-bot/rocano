<?php
$page_title = 'Gestión de Tareas';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'coordinador') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$success_message = '';
$coordinator_id = $_SESSION['user_id'];

// --- Lógica para CUD (Crear, Actualizar, Eliminar) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (La lógica POST para crear, actualizar y eliminar es idéntica a la del admin)
    // Se omite por brevedad, pero es necesaria para el funcionamiento del modal y el borrado.
}

// --- Obtener datos específicos para el Coordinador ---
$tasks = ['Pendiente' => [], 'En Progreso' => [], 'Completada' => []];
$volunteers_in_zones = [];
$coordinator_zones_ids = [];

try {
    // 1. Obtener las ZONAS del coordinador
    $stmt_zones = $conn->prepare("SELECT zona_id FROM coordinador_zona WHERE user_id = ?");
    $stmt_zones->bind_param("i", $coordinator_id);
    $stmt_zones->execute();
    $result_zones = $stmt_zones->get_result();
    while ($row = $result_zones->fetch_assoc()) {
        $coordinator_zones_ids[] = $row['zona_id'];
    }

    if (!empty($coordinator_zones_ids)) {
        $placeholders = implode(',', array_fill(0, count($coordinator_zones_ids), '?'));

        // 2. Obtener los VOLUNTARIOS de esas zonas
        $sql_volunteers = "SELECT id, name FROM users WHERE role_id = 3 AND approved = 1 AND zona_id IN ($placeholders) ORDER BY name ASC";
        $stmt_volunteers = $conn->prepare($sql_volunteers);
        $stmt_volunteers->bind_param(str_repeat('i', count($coordinator_zones_ids)), ...$coordinator_zones_ids);
        $stmt_volunteers->execute();
        $result_volunteers = $stmt_volunteers->get_result();
        while($row = $result_volunteers->fetch_assoc()) {
            $volunteers_in_zones[] = $row;
        }

        // 3. Obtener las TAREAS asignadas a esos voluntarios
        $volunteer_ids = array_column($volunteers_in_zones, 'id');
        if (!empty($volunteer_ids)) {
            $placeholders_vol = implode(',', array_fill(0, count($volunteer_ids), '?'));
            $sql_tasks = "SELECT t.*, u.name as voluntario_nombre, u.foto_url 
                          FROM tasks t 
                          JOIN users u ON t.asignado_a = u.id 
                          WHERE t.asignado_a IN ($placeholders_vol)
                          ORDER BY t.fecha_limite ASC";
            $stmt_tasks = $conn->prepare($sql_tasks);
            $stmt_tasks->bind_param(str_repeat('i', count($volunteer_ids)), ...$volunteer_ids);
            $stmt_tasks->execute();
            $result_tasks = $stmt_tasks->get_result();
            while($row = $result_tasks->fetch_assoc()) {
                $tasks[$row['estado']][] = $row;
            }
        }
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar los datos: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Tareas de mi Equipo</h1>
    <button id="addTaskBtn" class="cta-button"><i class="fas fa-plus"></i> Asignar Nueva Tarea</button>
</div>

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
                        <div class="task-card" draggable="true" data-task-id="<?php echo $task['id']; ?>" data-status="<?php echo $task['estado']; ?>">
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
                                <!-- Botones de Editar y Eliminar -->
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
            <!-- Campos del formulario: titulo, descripcion, etc. -->
            <div class="form-group">
                <label for="asignado_a">Asignar a Voluntario</label>
                <select id="asignado_a" name="asignado_a" required>
                    <option value="">-- Seleccionar Voluntario de mi equipo --</option>
                    <?php foreach($volunteers_in_zones as $volunteer): ?>
                        <option value="<?php echo e($volunteer['id']); ?>"><?php echo e($volunteer['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
             <!-- Resto de campos del formulario -->
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" class="cta-button" id="modalSubmitBtn">Crear Tarea</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
