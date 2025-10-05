<?php
// Incluir la configuración que inicia la sesión y conecta a la BD
require_once __DIR__ . '/../../includes/config.php';

$errors = [];
$success_message = '';

// Obtener los roles de la base de datos para el menú desplegable
try {
    $roles_query = $conn->query("SELECT id, name FROM roles WHERE name != 'admin'");
    $roles = $roles_query->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $errors[] = "Error al cargar los roles de usuario.";
    $roles = []; // Asegurarse de que $roles sea un array vacío si la consulta falla
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Recoger y sanear los datos del formulario
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role_id = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);

    // 2. Validaciones
    if (empty($name)) $errors[] = "El nombre es obligatorio.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "El correo electrónico no es válido.";
    if (strlen($password) < 8) $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    if ($role_id === false) $errors[] = "Debes seleccionar un rol válido.";

    // 3. Si no hay errores, proceder con el registro
    if (empty($errors)) {
        try {
            // Verificar si el correo ya existe para evitar duplicados
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $result = $stmt_check->get_result();

            if ($result->num_rows > 0) {
                $errors[] = "Este correo electrónico ya está registrado.";
            } else {
                // Encriptar la contraseña (¡muy importante!)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Preparar la consulta SQL para insertar el nuevo usuario
                $sql = "INSERT INTO users (name, email, password, role_id, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
                $stmt = $conn->prepare($sql);
                
                // Vincular los parámetros
                $stmt->bind_param("sssi", $name, $email, $hashed_password, $role_id);

                // Ejecutar la consulta
                if ($stmt->execute()) {
                    $success_message = "¡Usuario registrado con éxito!";
                } else {
                    $errors[] = "Error al registrar el usuario. Por favor, inténtalo de nuevo.";
                }
                $stmt->close();
            }
            $stmt_check->close();
        } catch (mysqli_sql_exception $e) {
            // Capturar errores específicos de SQL
            $errors[] = "Error en la base de datos: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Nuevo Usuario - Sistema de Campaña</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>login/register/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="form-container">
        <a href="<?php echo BASE_URL; ?>login/login/" class="back-link"><i class="fas fa-arrow-left"></i> Volver al Login</a>
        
        <div class="form-header">
            <img src="<?php echo BASE_URL; ?>public/images/logo.png" alt="Logo Campaña" class="logo">
            <h1>Crear Nueva Cuenta</h1>
            <p>Registra a un nuevo coordinador o voluntario.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="message error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo e($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="message success-message">
                <p><?php echo e($success_message); ?></p>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
            <div class="input-group">
                <input type="text" id="name" name="name" required value="<?php echo e($_POST['name'] ?? ''); ?>">
                <label for="name">Nombre Completo</label>
            </div>
            <div class="input-group">
                <input type="email" id="email" name="email" required value="<?php echo e($_POST['email'] ?? ''); ?>">
                <label for="email">Correo Electrónico</label>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" required>
                <label for="password">Contraseña</label>
            </div>
             <div class="input-group select-group">
                <select id="role_id" name="role_id" required>
                    <option value="" disabled selected>Selecciona un rol...</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo e($role['id']); ?>" <?php if(isset($_POST['role_id']) && $_POST['role_id'] == $role['id']) echo 'selected'; ?>>
                            <?php echo e(ucfirst($role['name'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="role_id" class="select-label">Rol de Usuario</label>
            </div>
            <button type="submit" class="cta-button">Registrar Usuario</button>
        </form>
    </div>
    <script src="<?php echo BASE_URL; ?>login/register/script.js"></script>
</body>
</html>

