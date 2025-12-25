<?php
$page_title = 'Gestión de Votantes';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Solo Administradores y Coordinadores pueden acceder
if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'coordinador') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$success = false;

// Manejar el envío del formulario para añadir/editar votante
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voter_id = $_POST['voter_id'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $zona_id = $_POST['zona_id'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $notas = trim($_POST['notas'] ?? '');

    if (empty($nombre) || empty($zona_id) || empty($estado)) {
        $errors[] = "Por favor, complete todos los campos obligatorios.";
    } else {
        try {
            if (!empty($voter_id)) {
                // Editar votante existente
                $stmt = $conn->prepare("UPDATE votantes SET nombre = ?, dni = ?, telefono = ?, direccion = ?, zona_id = ?, estado = ?, notas = ? WHERE id = ?");
                $stmt->bind_param("sssssssi", $nombre, $dni, $telefono, $direccion, $zona_id, $estado, $notas, $voter_id);
            } else {
                // Añadir nuevo votante
                $stmt = $conn->prepare("INSERT INTO votantes (nombre, dni, telefono, direccion, zona_id, estado, notas, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssssss", $nombre, $dni, $telefono, $direccion, $zona_id, $estado, $notas);
            }

            if ($stmt->execute()) {
                $success = true;
                // Redirigir para evitar reenvío del formulario
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                exit;
            } else {
                $errors[] = "Error al guardar el votante: " . $stmt->error;
            }
        } catch (Exception $e) {
            $errors[] = "Error al procesar la solicitud: " . $e->getMessage();
        }
    }
}

// Verificar si hay mensaje de éxito en la URL
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = true;
}

// Manejar eliminación de votante
if (isset($_POST['delete_voter_id'])) {
    $delete_id = $_POST['delete_voter_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM votantes WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit;
        } else {
            $errors[] = "Error al eliminar el votante: " . $stmt->error;
        }
    } catch (Exception $e) {
        $errors[] = "Error al procesar la solicitud: " . $e->getMessage();
    }
}
$voters = [];
$zones = [];

try {
    // Obtener todas las zonas para el filtro
    $zones_result = $conn->query("SELECT id, nombre FROM zonas ORDER BY nombre ASC");
    while ($row = $zones_result->fetch_assoc()) {
        $zones[] = $row;
    }

    // Obtener todos los votantes con el nombre de su zona
    $sql = "SELECT v.id, v.nombre, v.dni, v.telefono, v.direccion, v.zona_id, v.estado, v.notas, z.nombre as zona_nombre
            FROM votantes v
            LEFT JOIN zonas z ON v.zona_id = z.id
            ORDER BY v.created_at DESC";
    $voters_result = $conn->query($sql);
    while ($row = $voters_result->fetch_assoc()) {
        $voters[] = $row;
    }
} catch (Exception $e) {
    $errors[] = "Error al cargar los datos: " . $e->getMessage();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Gestión de Votantes</h1>
    <button id="addVoterBtn" class="cta-button"><i class="fas fa-plus"></i> Añadir Votante</button>
</div>

<div class="card">
    <?php if ($success): ?>
        <div class="success-message">Votante guardado correctamente.</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="error-message"><?php echo implode('<br>', $errors); ?></div>
    <?php endif; ?>
    <!-- Filtros -->
    <div class="filter-bar">
        <div class="filter-group">
            <i class="fas fa-search"></i>
            <input type="text" id="search_voter" placeholder="Buscar por nombre o DNI...">
        </div>
        <div class="filter-group">
            <select id="filter_zone">
                <option value="all">Todas las Zonas</option>
                <?php foreach ($zones as $zone): ?>
                    <option value="<?php echo e(strtolower($zone['nombre'])); ?>"><?php echo e($zone['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <select id="filter_status">
                <option value="all">Todos los Estados</option>
                <option value="A favor">A favor</option>
                <option value="Indeciso">Indeciso</option>
                <option value="En contra">En contra</option>
            </select>
        </div>
    </div>

    <!-- Tabla de Votantes -->
    <div class="table-responsive">
        <table class="voters-table">
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
            <tbody>
                <?php if (empty($voters)): ?>
                    <tr>
                        <td colspan="6">No hay votantes registrados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($voters as $voter): ?>
                        <tr data-voter-id="<?php echo e($voter['id']); ?>" data-name="<?php echo e(strtolower($voter['nombre'])); ?>" data-dni="<?php echo e($voter['dni']); ?>" data-telefono="<?php echo e($voter['telefono']); ?>" data-zone="<?php echo e(strtolower($voter['zona_nombre'])); ?>" data-status="<?php echo e($voter['estado']); ?>" data-zona-id="<?php echo e($voter['zona_id']); ?>" data-direccion="<?php echo e($voter['direccion']); ?>" data-notas="<?php echo e($voter['notas']); ?>">
                            <td><?php echo e($voter['nombre']); ?></td>
                            <td><?php echo e($voter['dni']); ?></td>
                            <td><?php echo e($voter['telefono']); ?></td>
                            <td><?php echo e($voter['zona_nombre'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', e($voter['estado']))); ?>">
                                    <?php echo e($voter['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn edit-btn" title="Editar"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Añadir/Editar Votante -->
<div id="voterModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modalTitle">Añadir Nuevo Votante</h2>
        <form id="voterForm" action="" method="POST">
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
                        <option value="">Seleccione una zona</option>
                        <?php foreach ($zones as $zone): ?>
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
            <div class="form-group">
                <label for="notas">Observaciones</label>
                <textarea id="notas" name="notas" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" id="modalSubmitBtn" class="cta-button">Guardar Votante</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" action="" method="POST" style="display:none;">
    <input type="hidden" name="delete_voter_id" id="delete_voter_id">
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

