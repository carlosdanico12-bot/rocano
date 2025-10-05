<?php
// Incluir el archivo de configuración que inicia la sesión y conecta a la BD
require_once __DIR__ . '/../../includes/config.php';

// Si el usuario ya ha iniciado sesión, redirigirlo a su dashboard correspondiente
if (isset($_SESSION['user_id'])) {
    $role_dashboard = BASE_URL . $_SESSION['user_role'] . '/dashboard/';
    header("Location: " . $role_dashboard);
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Por favor, ingresa un correo electrónico válido.";
    }
    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria.";
    }

    if (empty($errors)) {
        try {
            // Consulta SQL para obtener el usuario y su rol
            $sql = "SELECT users.*, roles.name as role_name 
                    FROM users 
                    JOIN roles ON users.role_id = roles.id 
                    WHERE users.email = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // 1. Verificar si el usuario existe y la contraseña es correcta
            if ($user && password_verify($password, $user['password'])) {
                
                // 2. Verificar si la cuenta está aprobada
                if ($user['approved'] == 1) {
                    // Iniciar sesión
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role_name'];

                    // Redirigir al dashboard correspondiente
                    $role_dashboard = BASE_URL . $user['role_name'] . '/dashboard/';
                    header("Location: " . $role_dashboard);
                    exit;
                } else {
                    // La cuenta existe pero no está aprobada
                    $errors[] = "Tu cuenta está registrada, pero aún no ha sido aprobada por un administrador.";
                }

            } else {
                // El usuario no existe o la contraseña es incorrecta
                $errors[] = "Credenciales incorrectas. Por favor, verifica tu correo y contraseña.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Error en el sistema. Por favor, inténtalo más tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale-1.0">
    <title>Acceso al Sistema - Campaña Política</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>login/login/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="form-container">
        <a href="<?php echo BASE_URL; ?>" class="back-link" title="Volver a la página principal"><i class="fas fa-arrow-left"></i></a>
        
        <div class="form-header">
            <img src="<?php echo BASE_URL; ?>public/images/logo.png" alt="Logo Campaña" class="logo">
            <h1>Acceso al Sistema</h1>
            <p>Ingresa tus credenciales para continuar</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="message error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
            <div class="input-group">
                <input type="email" id="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>" placeholder=" ">
                <label for="email">Correo Electrónico</label>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password">Contraseña</label>
            </div>
            
            <div class="form-options">
                <div class="checkbox-group">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Recordar sesión</label>
                </div>
                <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="cta-button">Iniciar Sesión</button>
        </form>

        <div class="register-link">
            <p>¿No tienes una cuenta? <a href="<?php echo BASE_URL; ?>login/register/">Regístrate aquí</a></p>
        </div>
    </div>
    <script src="<?php echo BASE_URL; ?>login/login/script.js"></script>
</body>
</html>

