<?php
$page_title = 'Configuración de la Campaña';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Solo los administradores pueden acceder a esta página
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$success_message = '';
$settings = [];

// Función para obtener todas las configuraciones
function get_all_settings($conn) {
    $settings = [];
    $result = $conn->query("SELECT * FROM settings");
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

// Función para actualizar una configuración
function update_setting($conn, $key, $value) {
    $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $stmt->bind_param("ss", $value, $key);
    return $stmt->execute();
}

// Cargar las configuraciones actuales
$settings = get_all_settings($conn);

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_settings = [
        'candidate_full_name'  => $_POST['candidate_full_name'] ?? '',
        'candidate_short_name' => $_POST['candidate_short_name'] ?? '',
        'position_aspiring'    => $_POST['position_aspiring'] ?? '',
        'political_party'      => $_POST['political_party'] ?? '',
        'electoral_symbol'     => $_POST['electoral_symbol'] ?? '',
        'campaign_colors'      => $_POST['campaign_colors'] ?? ''
    ];

    $all_updated = true;
    foreach ($new_settings as $key => $value) {
        if (!update_setting($conn, $key, $value)) {
            $all_updated = false;
        }
    }

    if ($all_updated) {
        $success_message = '¡Configuración actualizada con éxito!';
        // Recargar los datos para mostrarlos actualizados en el formulario
        $settings = get_all_settings($conn);
    } else {
        $errors[] = 'Ocurrió un error al actualizar la configuración.';
    }
}


require_once __DIR__ . '/../../includes/header.php';
?>

<div class="settings-container">
    <div class="card-header">
        <h1>Configuración General de la Campaña</h1>
        <p>Aquí puedes modificar los datos principales que se mostrarán en el sistema.</p>
    </div>

    <?php if ($success_message): ?>
        <div class="message success-message"><?php echo e($success_message); ?></div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="message error-message"><?php echo e(implode('<br>', $errors)); ?></div>
    <?php endif; ?>

    <div class="card">
        <form action="index.php" method="POST" class="settings-form">
            <div class="form-section">
                <h2>Datos del Candidato</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="candidate_full_name">Nombre Completo del Candidato</label>
                        <input type="text" id="candidate_full_name" name="candidate_full_name" value="<?php echo e($settings['candidate_full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="candidate_short_name">Nombre Abreviado (de campaña)</label>
                        <input type="text" id="candidate_short_name" name="candidate_short_name" value="<?php echo e($settings['candidate_short_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="position_aspiring">Cargo al que Postula</label>
                        <input type="text" id="position_aspiring" name="position_aspiring" value="<?php echo e($settings['position_aspiring'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Información de la Campaña</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="political_party">Partido o Movimiento Político</label>
                        <input type="text" id="political_party" name="political_party" value="<?php echo e($settings['political_party'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="electoral_symbol">Símbolo Electoral</label>
                        <input type="text" id="electoral_symbol" name="electoral_symbol" value="<?php echo e($settings['electoral_symbol'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="campaign_colors">Colores de Campaña (Hex, separados por coma)</label>
                        <input type="text" id="campaign_colors" name="campaign_colors" value="<?php echo e($settings['campaign_colors'] ?? ''); ?>" placeholder="#E63946, #FFFFFF">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="cta-button">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
?>
