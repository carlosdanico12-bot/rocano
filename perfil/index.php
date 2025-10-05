<?php
$page_title = 'Mi Perfil';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = '';
$user = null;

try {
    // 1. Obtener los datos actuales del usuario
    $stmt = $conn->prepare("SELECT name, email, dni, foto_url FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        // Si no se encuentra el usuario, redirigir al login (caso anómalo)
        header("Location: " . BASE_URL . "login/logout.php");
        exit;
    }

    // 2. Procesar el formulario cuando se envía
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Actualizar información personal
        if (isset($_POST['update_personal_info'])) {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $dni = trim($_POST['dni'] ?? '');

            if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "El nombre y el correo electrónico válido son obligatorios.";
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, dni = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $dni, $user_id);
                if ($stmt->execute()) {
                    $success_message = "¡Información personal actualizada con éxito!";
                    // Actualizar datos en la sesión
                    $_SESSION['user_name'] = $name;
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $user['dni'] = $dni;
                } else {
                    $errors[] = "Error al actualizar la información.";
                }
                $stmt->close();
            }
        }
        // Cambiar contraseña
        elseif (isset($_POST['update_password'])) {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (strlen($new_password) < 8) {
                $errors[] = "La nueva contraseña debe tener al menos 8 caracteres.";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "Las nuevas contraseñas no coinciden.";
            } else {
                // Verificar la contraseña actual
                $stmt_pass = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt_pass->bind_param("i", $user_id);
                $stmt_pass->execute();
                $pass_result = $stmt_pass->get_result()->fetch_assoc();

                if ($pass_result && password_verify($current_password, $pass_result['password'])) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt_update->bind_param("si", $hashed_password, $user_id);
                    if ($stmt_update->execute()) {
                        $success_message = "¡Contraseña cambiada con éxito!";
                    } else {
                        $errors[] = "Error al cambiar la contraseña.";
                    }
                    $stmt_update->close();
                } else {
                    $errors[] = "La contraseña actual es incorrecta.";
                }
                $stmt_pass->close();
            }
        }
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar los datos del perfil: " . $e->getMessage();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Mi Perfil</h1>
    <p class="page-subtitle">Actualiza tu información personal y de seguridad.</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="message error-message">
        <?php foreach ($errors as $error): ?><p><?php echo e($error); ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="message success-message">
        <p><?php echo e($success_message); ?></p>
    </div>
<?php endif; ?>


<div class="profile-layout">
    <!-- Columna de Información Personal -->
    <div class="card">
        <h2>Información Personal</h2>
        <form action="" method="POST" class="profile-form">
            <div class="avatar-upload">
                <img src="<?php echo BASE_URL . e($user['foto_url'] ?: 'assets/images/avatar-default.png'); ?>" alt="Foto de perfil" class="profile-avatar">
                <label for="foto_url" class="upload-btn"><i class="fas fa-camera"></i> Cambiar Foto</label>
                <input type="file" id="foto_url" name="foto_url" class="hidden-file-input">
            </div>

            <div class="form-group">
                <label for="name">Nombre Completo</label>
                <input type="text" id="name" name="name" value="<?php echo e($user['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="<?php echo e($user['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="dni">DNI</label>
                <input type="text" id="dni" name="dni" value="<?php echo e($user['dni'] ?? ''); ?>">
            </div>
            <div class="form-actions">
                <button type="submit" name="update_personal_info" class="cta-button">Guardar Cambios</button>
            </div>
        </form>
    </div>

    <!-- Columna de Seguridad -->
    <div class="card">
        <h2>Seguridad</h2>
        <form action="" method="POST" class="profile-form">
            <p>Para cambiar tu contraseña, completa los siguientes campos.</p>
            <div class="form-group">
                <label for="current_password">Contraseña Actual</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Nueva Contraseña</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirmar Nueva Contraseña</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <button type="submit" name="update_password" class="cta-button">Cambiar Contraseña</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

