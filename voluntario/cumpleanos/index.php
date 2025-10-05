<?php
$page_title = 'Cumpleaños del Equipo';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$today = new DateTime();
$today_str = $today->format('m-d');

// Cumpleaños de hoy
$today_sql = "SELECT name, email, fecha_nacimiento, role_id, (SELECT name FROM roles WHERE id=role_id) as role_name 
              FROM users 
              WHERE DATE_FORMAT(fecha_nacimiento, '%m-%d') = ? 
              AND approved = 1";
$stmt_today = $conn->prepare($today_sql);
$stmt_today->bind_param("s", $today_str);
$stmt_today->execute();
$today_birthdays = $stmt_today->get_result()->fetch_all(MYSQLI_ASSOC);

// Próximos cumpleaños (próximos 30 días)
$upcoming_sql = "SELECT name, email, fecha_nacimiento, role_id, (SELECT name FROM roles WHERE id=role_id) as role_name
                 FROM users
                 WHERE fecha_nacimiento IS NOT NULL AND approved = 1
                 ORDER BY 
                   CASE
                     WHEN DATE_FORMAT(fecha_nacimiento, '%m-%d') >= ? THEN 0
                     ELSE 1
                   END,
                   DATE_FORMAT(fecha_nacimiento, '%m-%d') ASC
                 LIMIT 30";
$stmt_upcoming = $conn->prepare($upcoming_sql);
$stmt_upcoming->bind_param("s", $today_str);
$stmt_upcoming->execute();
$upcoming_birthdays_all = $stmt_upcoming->get_result()->fetch_all(MYSQLI_ASSOC);

// Filtrar para excluir los de hoy
$upcoming_birthdays = array_filter($upcoming_birthdays_all, function($user) use ($today_str) {
    return date('m-d', strtotime($user['fecha_nacimiento'])) != $today_str;
});


require_once __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="style.css">

<div class="page-header">
    <h1><i class="fas fa-birthday-cake"></i> Cumpleaños del Equipo</h1>
</div>

<div class="birthday-container">
    <div class="card birthday-today">
        <div class="card-header">
            <h3>¡Felicidades a los de Hoy! (<?php echo $today->format('d/m'); ?>)</h3>
        </div>
        <?php if (count($today_birthdays) > 0): ?>
            <div class="birthday-grid">
                <?php foreach ($today_birthdays as $user): ?>
                    <div class="birthday-card">
                        <i class="fas fa-user-circle fa-3x"></i>
                        <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                        <span class="role-badge role-<?php echo strtolower($user['role_name']);?>"><?php echo htmlspecialchars(ucfirst($user['role_name'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-state">Nadie del equipo cumple años hoy.</p>
        <?php endif; ?>
    </div>

    <div class="card birthday-upcoming">
        <div class="card-header">
            <h3>Próximos Cumpleaños</h3>
        </div>
        <?php if (count($upcoming_birthdays) > 0): ?>
             <div class="birthday-grid">
                <?php foreach ($upcoming_birthdays as $user): ?>
                    <div class="birthday-card">
                        <i class="fas fa-user-circle fa-3x"></i>
                        <div class="info">
                           <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                           <span class="birthday-date"><?php echo date('d \d\e F', strtotime($user['fecha_nacimiento'])); ?></span>
                        </div>
                         <span class="role-badge role-<?php echo strtolower($user['role_name']);?>"><?php echo htmlspecialchars(ucfirst($user['role_name'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-state">No hay cumpleaños próximos en los siguientes 30 días.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
