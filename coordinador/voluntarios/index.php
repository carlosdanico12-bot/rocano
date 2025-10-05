<?php
$page_title = 'Gestión de Voluntarios';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'coordinador') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$coordinator_id = $_SESSION['user_id'];
$volunteers = [];
$coordinator_zones = [];

try {
    // 1. Obtener las zonas asignadas a este coordinador
    $stmt_zones = $conn->prepare("SELECT z.id, z.nombre FROM zonas z JOIN coordinador_zona cz ON z.id = cz.zona_id WHERE cz.user_id = ? ORDER BY z.nombre");
    $stmt_zones->bind_param("i", $coordinator_id);
    $stmt_zones->execute();
    $result_zones = $stmt_zones->get_result();
    while ($row = $result_zones->fetch_assoc()) {
        $coordinator_zones[] = $row;
    }
    
    // 2. Obtener los voluntarios que pertenecen a esas zonas
    $zone_ids = array_column($coordinator_zones, 'id');
    if (!empty($zone_ids)) {
        $placeholders = implode(',', array_fill(0, count($zone_ids), '?'));
        $sql_volunteers = "SELECT u.id, u.name, u.email, u.foto_url, z.nombre as zona_nombre
                           FROM users u
                           JOIN roles r ON u.role_id = r.id
                           LEFT JOIN zonas z ON u.zona_id = z.id
                           WHERE r.name = 'voluntario' AND u.approved = 1 AND u.zona_id IN ($placeholders)";
        $stmt_volunteers = $conn->prepare($sql_volunteers);
        $stmt_volunteers->bind_param(str_repeat('i', count($zone_ids)), ...$zone_ids);
        $stmt_volunteers->execute();
        $result_volunteers = $stmt_volunteers->get_result();
        while($row = $result_volunteers->fetch_assoc()) {
            $volunteers[] = $row;
        }
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar los datos de los voluntarios: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Voluntarios en mis Zonas</h1>
    <a href="<?php echo BASE_URL; ?>login/register/" class="cta-button"><i class="fas fa-user-plus"></i> Registrar Voluntario</a>
</div>

<div class="card filter-card">
    <div class="filter-group">
        <i class="fas fa-search"></i>
        <input type="text" id="search_volunteer" placeholder="Buscar por nombre...">
    </div>
    <div class="filter-group">
        <label for="filter_zone">Filtrar por Zona:</label>
        <select id="filter_zone">
            <option value="all">Todas mis Zonas</option>
             <?php foreach ($coordinator_zones as $zone): ?>
                <option value="<?php echo e($zone['nombre']); ?>"><?php echo e($zone['nombre']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="volunteers-grid">
    <?php if (empty($volunteers)): ?>
        <div class="card empty-state">
            <p>No tienes voluntarios asignados en tus zonas. Puedes registrarlos para empezar a organizar el equipo.</p>
        </div>
    <?php else: ?>
        <?php foreach ($volunteers as $volunteer): ?>
            <div class="card volunteer-card" data-name="<?php echo e(strtolower($volunteer['name'])); ?>" data-zone="<?php echo e($volunteer['zona_nombre']); ?>">
                <div class="volunteer-info">
                    <img src="<?php echo BASE_URL . e($volunteer['foto_url'] ?: 'assets/images/avatar-default.png'); ?>" alt="Foto de perfil" class="volunteer-avatar">
                    <div>
                        <h3 class="volunteer-name"><?php echo e($volunteer['name']); ?></h3>
                        <p class="volunteer-zone"><i class="fas fa-map-marker-alt"></i> <?php echo e($volunteer['zona_nombre'] ?: 'Sin zona asignada'); ?></p>
                    </div>
                </div>
                <div class="volunteer-stats">
                    <div class="stat-item">
                        <span>Tareas Asignadas</span>
                        <strong>5</strong> <!-- Dato de ejemplo -->
                    </div>
                    <div class="stat-item">
                        <span>Votantes Registrados</span>
                        <strong>18</strong> <!-- Dato de ejemplo -->
                    </div>
                </div>
                <div class="volunteer-actions">
                    <a href="#" class="cta-button secondary-button"><i class="fas fa-tasks"></i> Asignar Tarea</a>
                    <a href="#" class="cta-button tertiary-button"><i class="fas fa-paper-plane"></i> Enviar Mensaje</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
