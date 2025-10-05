<?php
require_once __DIR__ . '/../../includes/config.php';

// === LÓGICA DE SEGURIDAD ===
// 1. Simular que el usuario necesita 2FA. En el login real, después de verificar
// la contraseña, se establecería $_SESSION['2fa_required_user_id'] y se redirigiría aquí.
// Para probar, vamos a establecerlo manualmente si no existe.
if (!isset($_SESSION['2fa_required_user_id'])) {
    // En un caso real, lo redirigiríamos al login.
    // header("Location: " . BASE_URL . "/login/login/");
    // exit;

    // Para esta demostración, simulamos que el usuario 1 acaba de pasar el login.
    $_SESSION['2fa_required_user_id'] = 1; 
    $_SESSION['2fa_user_email'] = 'usuario_prueba@dominio.com';
}

// 2. Generar y almacenar un código 2FA si no existe uno.
if (!isset($_SESSION['2fa_code'])) {
    $code = rand(100000, 999999); // Genera un código de 6 dígitos
    $_SESSION['2fa_code'] = $code;
    // En un sistema real, aquí se enviaría el email:
    // mail($_SESSION['2fa_user_email'], "Tu código de acceso", "Tu código es: " . $code);
}


$error_message = '';
$user_email = $_SESSION['2fa_user_email'] ?? 'tu correo electrónico';

// Procesar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Unir los 6 dígitos del formulario en un solo string
    $submitted_code = implode('', $_POST['code']);

    if (empty($submitted_code)) {
        $error_message = "Por favor, ingresa el código de 6 dígitos.";
    } elseif ($submitted_code == $_SESSION['2fa_code']) {
        // ¡Código correcto! Finalizar el proceso de login.
        
        // Aquí se recuperarían los datos completos del usuario desde la BD
        // usando $_SESSION['2fa_required_user_id'] para establecer la sesión final.
        $_SESSION['user_id'] = $_SESSION['2fa_required_user_id'];
        $_SESSION['user_name'] = 'Usuario de Prueba'; // Simulado
        $_SESSION['user_role'] = 'admin'; // Simulado

        // Limpiar las variables de sesión de 2FA
        unset($_SESSION['2fa_required_user_id']);
        unset($_SESSION['2fa_user_email']);
        unset($_SESSION['2fa_code']);

        // Redirigir al dashboard
        header("Location: " . BASE_URL . "/admin/dashboard/");
        exit;
    } else {
        $error_message = "El código ingresado es incorrecto. Inténtalo de nuevo.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Dos Factores</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="<?php echo BASE_URL; ?>/assets/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="twofactor-container">
        <div class="twofactor-card">
            <div class="twofactor-header">
                <h2>Verifica tu Identidad</h2>
                <p>Hemos enviado un código de 6 dígitos a <strong><?php echo htmlspecialchars($user_email); ?></strong>.</p>
                <!-- Nota para pruebas: el código es <?php echo $_SESSION['2fa_code']; ?> -->
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-banner"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php" id="twoFactorForm">
                <div class="code-inputs">
                    <input type="text" name="code[]" maxlength="1" required class="code-input">
                    <input type="text" name="code[]" maxlength="1" required class="code-input">
                    <input type="text" name="code[]" maxlength="1" required class="code-input">
                    <span class="separator">-</span>
                    <input type="text" name="code[]" maxlength="1" required class="code-input">
                    <input type="text" name="code[]" maxlength="1" required class="code-input">
                    <input type="text" name="code[]" maxlength="1" required class="code-input">
                </div>
                <button type="submit" class="verify-button">Verificar Código</button>
            </form>
            <div class="twofactor-footer">
                <a href="#">¿No recibiste el código?</a>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
