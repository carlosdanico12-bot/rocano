<?php
$page_title = 'Registrar Nuevo Votante';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'voluntario') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$success_message = '';
$volunteer_id = $_SESSION['user_id'];
$volunteer_zone = null;

// --- 1. Obtener la zona del voluntario ---
try {
    $stmt_zone = $conn->prepare("SELECT z.id, z.nombre FROM users u JOIN zonas z ON u.zona_id = z.id WHERE u.id = ?");
    $stmt_zone->bind_param("i", $volunteer_id);
    $stmt_zone->execute();
    $result_zone = $stmt_zone->get_result();
    if ($result_zone->num_rows > 0) {
        $volunteer_zone = $result_zone->fetch_assoc();
    } else {
        $errors[] = "No tienes una zona asignada. Contacta a tu coordinador para que te asigne una.";
    }
} catch (Exception $e) {
    $errors[] = "Error al verificar tu zona asignada.";
}


// --- 2. Procesar el formulario de registro ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    $nombre = trim($_POST['nombre'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $estado = $_POST['estado'] ?? 'Indeciso';
    $zona_id = $volunteer_zone['id']; // La zona se toma automáticamente del voluntario

    if (empty($nombre)) {
        $errors[] = "El nombre completo del votante es obligatorio.";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO votantes (nombre, dni, telefono, direccion, zona_id, estado, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssisi", $nombre, $dni, $telefono, $direccion, $zona_id, $estado, $volunteer_id);
            if ($stmt->execute()) {
                $success_message = "¡Votante '".e($nombre)."' registrado con éxito en la zona de ".e($volunteer_zone['nombre'])."!";
            } else {
                // Verificar si es un DNI duplicado
                if ($conn->errno == 1062) {
                    $errors[] = "El DNI '".e($dni)."' ya se encuentra registrado en el sistema.";
                } else {
                    $errors[] = "Error al guardar el votante en la base de datos.";
                }
            }
        } catch (Exception $e) {
            $errors[] = "Error de base de datos: " . $e->getMessage();
        }
    }
}

// Obtener votantes registrados por este voluntario
if ($volunteer_zone) {
    try {
        $stmt_voters = $conn->prepare("SELECT nombre, dni, telefono, direccion, estado, created_at FROM votantes WHERE created_by = ? ORDER BY created_at DESC");
        $stmt_voters->bind_param("i", $volunteer_id);
        $stmt_voters->execute();
        $result_voters = $stmt_voters->get_result();
        $voters = [];
        while ($row = $result_voters->fetch_assoc()) {
            $voters[] = $row;
        }
    } catch (Exception $e) {
        $errors[] = "Error al cargar la lista de votantes.";
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Registrar Nuevo Votante</h1>
    <p class="page-subtitle">Añade aquí la información de los nuevos simpatizantes que contactes en tu zona.</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="message error-message">
        <?php foreach ($errors as $error): ?><p><?php echo e($error); ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($success_message): ?>
    <div class="message success-message"><p><?php echo e($success_message); ?></p></div>
<?php endif; ?>

<div class="card form-card">
    <form action="" method="POST" id="registerVoterForm">
        <div class="form-section">
            <i class="fas fa-user-circle section-icon"></i>
            <h3>Información Personal</h3>
        </div>
        
        <div class="form-group">
            <label for="nombre">Nombre Completo <span class="required">*</span></label>
            <input type="text" id="nombre" name="nombre" required placeholder="Ej: Juan Pérez García">
        </div>

        <div class="form-group-grid">
            <div class="form-group">
                <label for="dni">DNI</label>
                <input type="text" id="dni" name="dni" placeholder="Ej: 12345678" maxlength="8">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" placeholder="Ej: 987654321">
            </div>
        </div>

        <div class="form-group">
            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" placeholder="Ej: Av. Principal 123">
        </div>

        <div class="form-section">
            <i class="fas fa-map-marker-alt section-icon"></i>
            <h3>Información de Campaña</h3>
        </div>

        <div class="form-group-grid">
            <div class="form-group">
                <label for="zona">Tu Zona Asignada</label>
                <input type="text" id="zona" name="zona" value="<?php echo e($volunteer_zone['nombre'] ?? 'No asignada'); ?>" readonly disabled>
            </div>
            <div class="form-group">
                <label for="estado">Estado de Apoyo <span class="required">*</span></label>
                <select id="estado" name="estado" required>
                    <option value="Indeciso" selected>Indeciso</option>
                    <option value="A favor">A favor</option>
                    <option value="En contra">En contra</option>
                </select>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="cta-button" <?php if(!$volunteer_zone) echo 'disabled'; ?>>
                <i class="fas fa-save"></i> Guardar Votante
            </button>
        </div>

    </form>
</div>

<div class="card">
    <h3>Votantes Registrados por Ti</h3>
    <div class="table-responsive">
        <table class="voters-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Estado</th>
                    <th>Fecha Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($voters)): ?>
                    <tr><td colspan="6">Aún no has registrado ningún votante.</td></tr>
                <?php else: ?>
                    <?php foreach ($voters as $voter): ?>
                        <tr>
                            <td><?php echo e($voter['nombre']); ?></td>
                            <td><?php echo e($voter['dni']); ?></td>
                            <td><?php echo e($voter['telefono']); ?></td>
                            <td><?php echo e($voter['direccion']); ?></td>
                            <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', e($voter['estado']))); ?>"><?php echo e($voter['estado']); ?></span></td>
                            <td><?php echo e(date('d/m/Y H:i', strtotime($voter['created_at']))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
