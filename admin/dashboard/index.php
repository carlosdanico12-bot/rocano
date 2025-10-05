<?php
$page_title = 'Dashboard de Administración';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Solo los administradores pueden acceder
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

// --- Lógica para obtener estadísticas ---
$stats = [
    'votantes' => 0,
    'voluntarios' => 0,
    'tareas_completadas' => 0,
    'eventos_proximos' => 0
];

try {
    $stats['votantes'] = $conn->query("SELECT COUNT(id) as total FROM votantes")->fetch_assoc()['total'] ?? 0;
    $stats['voluntarios'] = $conn->query("SELECT COUNT(u.id) as total FROM users u JOIN roles r ON u.role_id = r.id WHERE r.name = 'voluntario'")->fetch_assoc()['total'] ?? 0;
    $stats['tareas_completadas'] = $conn->query("SELECT COUNT(id) as total FROM tasks WHERE estado = 'Completada'")->fetch_assoc()['total'] ?? 0;
    $stats['eventos_proximos'] = $conn->query("SELECT COUNT(id) as total FROM events WHERE fecha_hora >= NOW()")->fetch_assoc()['total'] ?? 0;
} catch (Exception $e) {
    // Manejar error si la consulta falla, pero no detener la página.
    $error_message = "Error al cargar estadísticas: " . $e->getMessage();
}


require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Dashboard de Administración</h1>
    <p class="page-subtitle">Resumen general del estado de la campaña.</p>
</div>

<!-- Grid de Estadísticas -->
<div class="stats-grid">
    <div class="card stat-card">
        <div class="stat-icon icon-votantes"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?php echo number_format($stats['votantes']); ?></span>
            <span class="stat-label">Votantes Registrados</span>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon icon-voluntarios"><i class="fas fa-hands-helping"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?php echo number_format($stats['voluntarios']); ?></span>
            <span class="stat-label">Voluntarios Activos</span>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon icon-tareas"><i class="fas fa-tasks"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?php echo number_format($stats['tareas_completadas']); ?></span>
            <span class="stat-label">Tareas Completadas</span>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon icon-eventos"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <span class="stat-number"><?php echo number_format($stats['eventos_proximos']); ?></span>
            <span class="stat-label">Eventos Próximos</span>
        </div>
    </div>
</div>

<!-- Grid de Gráficos y Actividad -->
<div class="charts-grid">
    <div class="card chart-card">
        <h3>Distribución de Votantes</h3>
        <canvas id="votersPieChart"></canvas>
    </div>
    <div class="card chart-card">
        <h3>Progreso de la Campaña (Últimos 7 días)</h3>
        <canvas id="campaignLineChart"></canvas>
    </div>
    <div class="card activity-card">
        <h3>Actividad Reciente</h3>
        <ul class="activity-list">
            <li><span class="activity-user">Juan Pérez</span> registró un nuevo votante en la Zona Centro. <span class="activity-time">Hace 5 min</span></li>
            <li><span class="activity-user">María García</span> completó la tarea "Entrega de volantes". <span class="activity-time">Hace 25 min</span></li>
            <li>Nuevo evento "Reunión de Coordinadores" fue creado. <span class="activity-time">Hace 1 hora</span></li>
            <li><span class="activity-user">Carlos Sánchez</span> se unió como voluntario. <span class="activity-time">Hace 3 horas</span></li>
        </ul>
    </div>
</div>

<!-- Incluir Chart.js desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

