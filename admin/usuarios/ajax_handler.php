<?php
// Ruta: admin/usuarios/ajax_handler.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificación de seguridad: solo administradores
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$response = ['success' => false, 'error' => 'Acción no especificada.'];

try {
    switch ($action) {

        case 'get_users':
            // Obtener roles y zonas
            $roles_result = $conn->query("SELECT id, name FROM roles");
            if (!$roles_result) throw new Exception("Error en consulta de roles: " . $conn->error);
            $roles = [];
            while ($row = $roles_result->fetch_assoc()) {
                $roles[$row['id']] = $row['name'];
            }

            $zonas_result = $conn->query("SELECT id, nombre FROM zonas");
            if (!$zonas_result) throw new Exception("Error en consulta de zonas: " . $conn->error);
            $zonas = [];
            while ($row = $zonas_result->fetch_assoc()) {
                $zonas[$row['id']] = $row['nombre'];
            }

            // Obtener usuarios
            $sql = "SELECT u.id, u.name, u.email, u.dni, u.role_id, u.zona_id, u.approved, r.name as role_name
                    FROM users u
                    LEFT JOIN roles r ON u.role_id = r.id
                    ORDER BY u.id DESC";
            $result = $conn->query($sql);
            if (!$result) throw new Exception("Error en consulta de usuarios: " . $conn->error);
            $users = [];
            while ($user = $result->fetch_assoc()) {
                $user['zona_name'] = $zonas[$user['zona_id']] ?? 'N/A';
                $users[] = $user;
            }

            $response = ['success' => true, 'users' => $users, 'roles' => $roles, 'zonas' => $zonas];
            break;

        case 'save_user':
            $user_id = $_POST['user_id'] ?? null;
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $dni = trim($_POST['dni'] ?? '');
            $role_id = $_POST['role_id'] ?? 3;
            $zona_id = empty($_POST['zona_id']) ? null : (int)$_POST['zona_id'];

            if (empty($name) || empty($email) || empty($role_id)) {
                throw new Exception("Nombre, email y rol son obligatorios.");
            }
            if (empty($user_id) && empty($password)) {
                throw new Exception("La contraseña es obligatoria para nuevos usuarios.");
            }

            if ($user_id) {
                // Actualizar usuario
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=?, dni=?, role_id=?, zona_id=? WHERE id=?");
                    $stmt->bind_param("ssssiii", $name, $email, $hashed_password, $dni, $role_id, $zona_id, $user_id);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, dni=?, role_id=?, zona_id=? WHERE id=?");
                    $stmt->bind_param("sssiii", $name, $email, $dni, $role_id, $zona_id, $user_id);
                }
            } else {
                // Crear nuevo usuario
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, dni, role_id, zona_id, approved) VALUES (?, ?, ?, ?, ?, ?, 1)");
                $stmt->bind_param("ssssii", $name, $email, $hashed_password, $dni, $role_id, $zona_id);
            }

            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                throw new Exception("Error al guardar usuario: " . $stmt->error);
            }
            break;

        case 'toggle_approval':
            $user_id = $_POST['user_id'] ?? null;
            if (!$user_id) throw new Exception("ID de usuario no proporcionado.");
            $stmt = $conn->prepare("UPDATE users SET approved = NOT approved WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                throw new Exception("Error al cambiar el estado.");
            }
            break;

        case 'delete_user':
            $user_id = $_POST['user_id'] ?? null;
            if (!$user_id) throw new Exception("ID de usuario no proporcionado.");
            if ($user_id == $_SESSION['user_id']) throw new Exception("No puedes eliminar tu propia cuenta.");
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            if ($stmt->execute()) {
                $response['success'] = true;
            } else {
                throw new Exception("Error al eliminar el usuario.");
            }
            break;

        case 'get_users_by_role':
            $role = $_GET['role'] ?? '';
            if (empty($role)) {
                throw new Exception("Rol no especificado.");
            }

            // Mapear nombres de rol a IDs
            $role_map = [
                'coordinador' => 2,
                'voluntario' => 3
            ];

            if (!isset($role_map[$role])) {
                throw new Exception("Rol no válido.");
            }

            $role_id = $role_map[$role];

            // Obtener usuarios por rol, solo aprobados
            $stmt = $conn->prepare("SELECT id, name FROM users WHERE role_id = ? AND approved = 1 ORDER BY name");
            $stmt->bind_param("i", $role_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }

            $response = ['success' => true, 'users' => $users];
            break;

        default:
            $response['error'] = 'Acción no válida.';
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
exit;
