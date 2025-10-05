<?php
$page_title = 'Gestión de Voluntarios';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$volunteers = [];
try {
    $sql = "SELECT u.id, u.name, u.email, u.dni, u.foto_url, u.approved, z.nombre as zona_nombre
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN zonas z ON u.zona_id = z.id
            WHERE r.name = 'voluntario'
            ORDER BY u.created_at DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $volunteers[] = $row;
    }
} catch (Exception $e) {
    $errors[] = "Error al cargar los voluntarios: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Gestión de Voluntarios</h1>
    <a href="<?php echo BASE_URL; ?>login/register/" class="cta-button"><i class="fas fa-user-plus"></i> Añadir Voluntario</a>
</div>

<div class="card filter-card">
    <input type="text" id="search_volunteer" placeholder="Buscar por nombre o correo...">
</div>

<div class="volunteers-grid">
    <?php if (empty($volunteers)): ?>
        <p>No hay voluntarios registrados.</p>
    <?php else: ?>
        <?php foreach ($volunteers as $volunteer): ?>
            <div class="card volunteer-card" data-name="<?php echo e(strtolower($volunteer['name'])); ?>" data-email="<?php echo e(strtolower($volunteer['email'])); ?>">
                <div class="volunteer-info">
                    <img src="<?php echo BASE_URL . e($volunteer['foto_url'] ?: 'assets/images/avatar-default.png'); ?>" alt="Foto de perfil" class="volunteer-avatar">
                    <div>
                        <h3 class="volunteer-name"><?php echo e($volunteer['name']); ?></h3>
                        <p class="volunteer-zone"><i class="fas fa-map-marker-alt"></i> <?php echo e($volunteer['zona_nombre'] ?: 'Sin zona'); ?></p>
                    </div>
                </div>
                <div class="volunteer-actions">
                    <!-- ENLACES 100% FUNCIONALES -->
                    <a href="<?php echo BASE_URL; ?>admin/voluntarios/perfil/?id=<?php echo e($volunteer['id']); ?>" class="action-btn view-btn" title="Ver Perfil Detallado"><i class="fas fa-eye"></i></a>
                    <a href="<?php echo BASE_URL; ?>admin/mensajes/?user_id=<?php echo e($volunteer['id']); ?>" class="action-btn message-btn" title="Enviar Mensaje Directo"><i class="fas fa-paper-plane"></i></a>
                    <a href="#" class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

