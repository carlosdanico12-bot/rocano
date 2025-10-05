<?php
$page_title = 'Perfil del Voluntario';
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$volunteer_id = $_GET['id'] ?? null;
if (!$volunteer_id || !filter_var($volunteer_id, FILTER_VALIDATE_INT)) {
    header("Location: " . BASE_URL . "admin/voluntarios/");
    exit;
}

$volunteer = null;
$stats = ['tasks_completed' => 0, 'voters_registered' => 0];
$recent_tasks = [];

try {
    // 1. Obtener datos del voluntario
    $stmt_vol = $conn->prepare("SELECT u.*, z.nombre as zona_nombre FROM users u LEFT JOIN zonas z ON u.zona_id = z.id WHERE u.id = ? AND u.role_id = 3");
    $stmt_vol->bind_param("i", $volunteer_id);
    $stmt_vol->execute();
    $volunteer = $stmt_vol->get_result()->fetch_assoc();

    if (!$volunteer) {
        header("Location: " . BASE_URL . "admin/voluntarios/");
        exit;
    }

    // 2. Obtener estadísticas
    $stmt_tasks = $conn->prepare("SELECT COUNT(id) as total FROM tasks WHERE asignado_a = ? AND estado = 'Completada'");
    $stmt_tasks->bind_param("i", $volunteer_id);
    $stmt_tasks->execute();
    $stats['tasks_completed'] = $stmt_tasks->get_result()->fetch_assoc()['total'] ?? 0;

    $stmt_voters = $conn->prepare("SELECT COUNT(id) as total FROM votantes WHERE created_by = ?");
    $stmt_voters->bind_param("i", $volunteer_id);
    $stmt_voters->execute();
    $stats['voters_registered'] = $stmt_voters->get_result()->fetch_assoc()['total'] ?? 0;

    // 3. Obtener últimas 5 tareas asignadas
    $stmt_recent = $conn->prepare("SELECT titulo, estado, fecha_limite FROM tasks WHERE asignado_a = ? ORDER BY fecha_limite DESC LIMIT 5");
    $stmt_recent->bind_param("i", $volunteer_id);
    $stmt_recent->execute();
    $result_recent = $stmt_recent->get_result();
    while($row = $result_recent->fetch_assoc()) {
        $recent_tasks[] = $row;
    }

} catch (Exception $e) {
    // Manejo de errores
}

require_once __DIR__ . '/../../../includes/header.php';
?>

<div class="profile-container">
    <div class="card profile-header-card">
        <div class="profile-main-info">
            <img src="<?php echo BASE_URL . e($volunteer['foto_url'] ?: 'assets/images/avatar-default.png'); ?>" alt="Foto de perfil" class="profile-avatar">
            <div>
                <h1><?php echo e($volunteer['name']); ?></h1>
                <p class="profile-role">Rol: Voluntario</p>
                <div class="profile-contact">
                    <span><i class="fas fa-envelope"></i> <?php echo e($volunteer['email']); ?></span>
                    <span><i class="fas fa-map-marker-alt"></i> Zona: <?php echo e($volunteer['zona_nombre'] ?: 'No asignada'); ?></span>
                </div>
            </div>
        </div>
        <div class="profile-actions">
            <a href="<?php echo BASE_URL; ?>admin/mensajes/?user_id=<?php echo e($volunteer['id']); ?>" class="cta-button"><i class="fas fa-paper-plane"></i> Enviar Mensaje</a>
        </div>
    </div>

    <div class="profile-stats-grid">
        <div class="card stat-card">
            <i class="fas fa-tasks stat-icon"></i>
            <div class="stat-value"><?php echo $stats['tasks_completed']; ?></div>
            <div class="stat-label">Tareas Completadas</div>
        </div>
        <div class="card stat-card">
            <i class="fas fa-users stat-icon"></i>
            <div class="stat-value"><?php echo $stats['voters_registered']; ?></div>
            <div class="stat-label">Votantes Registrados</div>
        </div>
         <div class="card stat-card">
            <i class="fas fa-check-double stat-icon"></i>
            <div class="stat-value">85%</div>
            <div class="stat-label">Tasa de Cumplimiento (Ej.)</div>
        </div>
    </div>

    <div class="card recent-activity-card">
        <h3>Actividad Reciente (Últimas Tareas Asignadas)</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tarea</th>
                        <th>Fecha Límite</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($recent_tasks)): ?>
                        <tr><td colspan="3">Este voluntario no tiene tareas recientes.</td></tr>
                    <?php else: ?>
                        <?php foreach($recent_tasks as $task): ?>
                            <tr>
                                <td><?php echo e($task['titulo']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($task['fecha_limite'])); ?></td>
                                <td><span class="status-badge status-<?php echo e(strtolower(str_replace(' ', '-', $task['estado']))); ?>"><?php echo e($task['estado']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>

