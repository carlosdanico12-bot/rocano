<?php
$page_title = 'Mis Tareas Asignadas';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'voluntario') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$volunteer_id = $_SESSION['user_id'];
$tasks = [];

try {
    // Consulta para obtener todas las tareas asignadas a este voluntario
    $sql = "SELECT t.id, t.titulo, t.descripcion, t.fecha_limite, t.estado, 
                   u.name as created_by_name
            FROM tasks t
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.asignado_a = ?
            ORDER BY 
                CASE t.estado
                    WHEN 'Pendiente' THEN 1
                    WHEN 'En Progreso' THEN 2
                    WHEN 'Completada' THEN 3
                END, t.fecha_limite ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $volunteer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar tus tareas: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Mis Tareas Asignadas</h1>
    <p class="page-subtitle">Aquí puedes ver y gestionar las tareas que te han sido asignadas por tu coordinador.</p>
</div>

<!-- Filtros de Tareas -->
<div class="card filter-card">
    <div class="filter-group">
        <label for="filter_status">Filtrar por Estado:</label>
        <select id="filter_status">
            <option value="all">Todas las Tareas</option>
            <option value="Pendiente">Pendientes</option>
            <option value="En Progreso">En Progreso</option>
            <option value="Completada">Completadas</option>
        </select>
    </div>
</div>


<div class="tasks-grid">
    <?php if (empty($tasks)): ?>
        <div class="card card-empty">
            <i class="fas fa-check-double"></i>
            <h3>¡Todo al día!</h3>
            <p>No tienes tareas asignadas en este momento. ¡Buen trabajo!</p>
        </div>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <div class="card task-card" data-status="<?php echo e($task['estado']); ?>">
                <div class="task-header">
                    <h3 class="task-title"><?php echo e($task['titulo']); ?></h3>
                    <span class="status-badge status-<?php echo e(strtolower(str_replace(' ', '-', $task['estado']))); ?>"><?php echo e($task['estado']); ?></span>
                </div>
                <div class="task-body">
                    <p class="task-description"><?php echo e($task['descripcion'] ?: 'No hay descripción detallada para esta tarea.'); ?></p>
                </div>
                <div class="task-footer">
                    <div class="task-meta">
                        <span><i class="fas fa-calendar-alt"></i> Límite: <?php echo date('d/m/Y', strtotime($task['fecha_limite'])); ?></span>
                        <span><i class="fas fa-user-tie"></i> Asignada por: <?php echo e($task['created_by_name'] ?: 'Admin'); ?></span>
                    </div>
                    <div class="task-actions">
                        <?php if ($task['estado'] === 'Pendiente'): ?>
                            <button class="cta-button secondary-button update-status-btn" data-task-id="<?php echo e($task['id']); ?>" data-new-status="En Progreso">
                                <i class="fas fa-play"></i> Iniciar Tarea
                            </button>
                        <?php elseif ($task['estado'] === 'En Progreso'): ?>
                            <button class="cta-button complete-btn update-status-btn" data-task-id="<?php echo e($task['id']); ?>" data-new-status="Completada">
                                <i class="fas fa-check"></i> Marcar como Completada
                            </button>
                        <?php else: // Completada ?>
                            <a href="<?php echo BASE_URL; ?>voluntario/evidencias/" class="cta-button evidence-btn">
                                <i class="fas fa-upload"></i> Subir Evidencia
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
