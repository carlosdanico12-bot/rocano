<?php
$page_title = 'Gestión de Votantes';
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
    $action = $_POST['action'] ?? '';
    $voter_id = $_POST['voter_id'] ?? null;
    
    // Eliminar Votante
    if ($action === 'delete' && $voter_id) {
        $stmt = $conn->prepare("DELETE FROM votantes WHERE id = ?");
        $stmt->bind_param("i", $voter_id);
        if ($stmt->execute()) $success_message = "Votante eliminado con éxito.";
        else $errors[] = "Error al eliminar el votante.";
    } 
    // Crear o Actualizar
    else {
        $nombre = trim($_POST['nombre'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $zona_id = $_POST['zona_id'] ?? null;
        $estado = $_POST['estado'] ?? 'Indeciso';

        if(empty($nombre) || empty($zona_id)) $errors[] = "Nombre y zona son obligatorios.";

        if (empty($errors)) {
            if ($action === 'update' && $voter_id) {
                $stmt = $conn->prepare("UPDATE votantes SET nombre=?, dni=?, telefono=?, direccion=?, zona_id=?, estado=? WHERE id=?");
                $stmt->bind_param("ssssisi", $nombre, $dni, $telefono, $direccion, $zona_id, $estado, $voter_id);
                $success_message = "Votante actualizado.";
            } else {
                $stmt = $conn->prepare("INSERT INTO votantes (nombre, dni, telefono, direccion, zona_id, estado, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssisi", $nombre, $dni, $telefono, $direccion, $zona_id, $estado, $coordinator_id);
                $success_message = "Votante añadido con éxito.";
            }
            if(!$stmt->execute()) $errors[] = "Error al guardar: " . $stmt->error;
        }
    }
}


// --- Obtener datos para la página ---
$voters = [];
$coordinator_zones = [];
try {
    // Obtener las zonas asignadas a este coordinador
    $stmt_zones = $conn->prepare("SELECT z.id, z.nombre FROM zonas z JOIN coordinador_zona cz ON z.id = cz.zona_id WHERE cz.user_id = ? ORDER BY z.nombre");
    $stmt_zones->bind_param("i", $coordinator_id);
    $stmt_zones->execute();
    $result_zones = $stmt_zones->get_result();
    while ($row = $result_zones->fetch_assoc()) {
        $coordinator_zones[] = $row;
    }
    
    // Obtener los votantes de las zonas asignadas al coordinador
    $zone_ids = array_column($coordinator_zones, 'id');
    if (!empty($zone_ids)) {
        $placeholders = implode(',', array_fill(0, count($zone_ids), '?'));
        $sql_voters = "SELECT v.*, z.nombre as zona_nombre 
                       FROM votantes v 
                       LEFT JOIN zonas z ON v.zona_id = z.id 
                       WHERE v.zona_id IN ($placeholders)
                       ORDER BY v.created_at DESC";
        $stmt_voters = $conn->prepare($sql_voters);
        $stmt_voters->bind_param(str_repeat('i', count($zone_ids)), ...$zone_ids);
        $stmt_voters->execute();
        $result_voters = $stmt_voters->get_result();
        while($row = $result_voters->fetch_assoc()) {
            $voters[] = $row;
        }
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar datos: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Votantes en mis Zonas</h1>
    <button id="addVoterBtn" class="cta-button"><i class="fas fa-user-plus"></i> Añadir Votante</button>
</div>

<?php if (!empty($errors)): ?>
    <div class="message error-message">
        <?php foreach ($errors as $error): ?><p><?php echo e($error); ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($success_message): ?>
    <div class="message success-message"><p><?php echo e($success_message); ?></p></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Listado de Votantes</h3>
        <div class="filters">
            <input type="text" id="searchInput" placeholder="Buscar por nombre o DNI...">
            <select id="zoneFilter">
                <option value="">Todas mis Zonas</option>
                 <?php foreach ($coordinator_zones as $zone): ?>
                    <option value="<?php echo e($zone['nombre']); ?>"><?php echo e($zone['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="statusFilter">
                <option value="">Todos los Estados</option>
                <option value="A favor">A favor</option>
                <option value="Indeciso">Indeciso</option>
                <option value="En contra">En contra</option>
            </select>
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>DNI</th>
                    <th>Teléfono</th>
                    <th>Zona</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="votersTableBody">
                <?php if (empty($voters)): ?>
                    <tr><td colspan="6">No hay votantes registrados en tus zonas.</td></tr>
                <?php else: ?>
                    <?php foreach ($voters as $voter): ?>
                        <tr>
                            <td><?php echo e($voter['nombre']); ?></td>
                            <td><?php echo e($voter['dni']); ?></td>
                            <td><?php echo e($voter['telefono']); ?></td>
                            <td><?php echo e($voter['zona_nombre']); ?></td>
                            <td><span class="status-badge status-<?php echo e(strtolower(str_replace(' ', '-', $voter['estado']))); ?>"><?php echo e($voter['estado']); ?></span></td>
                            <td>
                                <button class="action-btn edit-btn" title="Editar"
                                    data-id="<?php echo e($voter['id']); ?>"
                                    data-nombre="<?php echo e($voter['nombre']); ?>"
                                    data-dni="<?php echo e($voter['dni']); ?>"
                                    data-telefono="<?php echo e($voter['telefono']); ?>"
                                    data-direccion="<?php echo e($voter['direccion']); ?>"
                                    data-zona_id="<?php echo e($voter['zona_id']); ?>"
                                    data-estado="<?php echo e($voter['estado']); ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" onsubmit="return confirm('¿Seguro?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="voter_id" value="<?php echo e($voter['id']); ?>">
                                    <button type="submit" class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="pagination-footer">
        <span id="tableInfo"></span>
        <div class="pagination-controls">
            <button id="prevPageBtn" disabled>Anterior</button>
            <button id="nextPageBtn" disabled>Siguiente</button>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="voterModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modalTitle">Añadir Nuevo Votante</h2>
        <form id="voterForm" action="" method="POST">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="voter_id" id="voter_id">
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
             <div class="form-group-grid">
                <div class="form-group">
                    <label for="dni">DNI</label>
                    <input type="text" id="dni" name="dni">
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono">
                </div>
            </div>
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion">
            </div>
            <div class="form-group-grid">
                <div class="form-group">
                    <label for="zona_id">Zona</label>
                    <select id="zona_id" name="zona_id" required>
                        <option value="">-- Seleccionar Zona --</option>
                        <?php foreach ($coordinator_zones as $zone): ?>
                            <option value="<?php echo e($zone['id']); ?>"><?php echo e($zone['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estado">Estado de Apoyo</label>
                    <select id="estado" name="estado" required>
                        <option value="Indeciso">Indeciso</option>
                        <option value="A favor">A favor</option>
                        <option value="En contra">En contra</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" id="modalSubmitBtn" class="cta-button">Añadir Votante</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
