<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$response = ['success' => false, 'error' => 'Acción no válida.'];

try {
    switch ($action) {
        case 'get_assignments':
            // Obtener todas las asignaciones con nombres de zonas
            $sql = "SELECT
                        cz.zona_id,
                        cz.user_id,
                        u.name,
                        'Coordinador' as role,
                        z.nombre as zone_name
                    FROM coordinador_zona cz
                    JOIN users u ON cz.user_id = u.id
                    JOIN zonas z ON cz.zona_id = z.id
                    WHERE u.approved = 1
                    UNION ALL
                    SELECT
                        u.zona_id,
                        u.id as user_id,
                        u.name,
                        'Voluntario' as role,
                        z.nombre as zone_name
                    FROM users u
                    JOIN zonas z ON u.zona_id = z.id
                    WHERE u.role_id = 3 AND u.zona_id IS NOT NULL AND u.approved = 1
                    ORDER BY zone_name, role, name";

            $result = $conn->query($sql);
            if (!$result) throw new Exception("Error en la consulta: " . $conn->error);

            $assignments = [];
            while ($row = $result->fetch_assoc()) {
                $assignments[] = $row;
            }

            $response = ['success' => true, 'assignments' => $assignments];
            break;

        case 'assign_coordinator':
            // Asignar coordinador a zona
            $zone_id = $_POST['zone_id'] ?? '';
            $user_id = $_POST['user_id'] ?? '';

            if (empty($zone_id) || empty($user_id)) {
                throw new Exception("Zona y usuario son requeridos.");
            }

            // Verificar que el usuario sea coordinador aprobado
            $check_sql = "SELECT id FROM users WHERE id = ? AND role_id = 2 AND approved = 1";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception("Usuario no válido o no aprobado.");
            }

            // Verificar si ya existe la asignación
            $exists_sql = "SELECT id FROM coordinador_zona WHERE user_id = ? AND zona_id = ?";
            $exists_stmt = $conn->prepare($exists_sql);
            $exists_stmt->bind_param("ii", $user_id, $zone_id);
            $exists_stmt->execute();
            if ($exists_stmt->get_result()->num_rows > 0) {
                throw new Exception("Este coordinador ya está asignado a esta zona.");
            }

            // Insertar asignación
            $insert_sql = "INSERT INTO coordinador_zona (user_id, zona_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ii", $user_id, $zone_id);
            if (!$insert_stmt->execute()) {
                throw new Exception("Error al asignar coordinador: " . $conn->error);
            }

            $response = ['success' => true, 'message' => 'Coordinador asignado exitosamente.'];
            break;

        case 'assign_volunteer':
            // Asignar voluntario a zona
            $zone_id = $_POST['zone_id'] ?? '';
            $user_id = $_POST['user_id'] ?? '';

            if (empty($zone_id) || empty($user_id)) {
                throw new Exception("Zona y usuario son requeridos.");
            }

            // Verificar que el usuario sea voluntario aprobado
            $check_sql = "SELECT id FROM users WHERE id = ? AND role_id = 3 AND approved = 1";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception("Usuario no válido o no aprobado.");
            }

            // Actualizar zona del voluntario
            $update_sql = "UPDATE users SET zona_id = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $zone_id, $user_id);
            if (!$update_stmt->execute()) {
                throw new Exception("Error al asignar voluntario: " . $conn->error);
            }

            $response = ['success' => true, 'message' => 'Voluntario asignado exitosamente.'];
            break;

        case 'remove_coordinator':
            // Eliminar asignación de coordinador
            $zone_id = $_POST['zone_id'] ?? '';
            $user_id = $_POST['user_id'] ?? '';

            if (empty($zone_id) || empty($user_id)) {
                throw new Exception("Zona y usuario son requeridos.");
            }

            // Eliminar asignación
            $delete_sql = "DELETE FROM coordinador_zona WHERE user_id = ? AND zona_id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("ii", $user_id, $zone_id);
            if (!$delete_stmt->execute()) {
                throw new Exception("Error al eliminar asignación: " . $conn->error);
            }

            $response = ['success' => true, 'message' => 'Coordinador eliminado exitosamente.'];
            break;

        case 'remove_volunteer':
            // Eliminar asignación de voluntario
            $zone_id = $_POST['zone_id'] ?? '';
            $user_id = $_POST['user_id'] ?? '';

            if (empty($zone_id) || empty($user_id)) {
                throw new Exception("Zona y usuario son requeridos.");
            }

            // Actualizar zona del voluntario a NULL
            $update_sql = "UPDATE users SET zona_id = NULL WHERE id = ? AND zona_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $user_id, $zone_id);
            if (!$update_stmt->execute()) {
                throw new Exception("Error al eliminar asignación: " . $conn->error);
            }

            $response = ['success' => true, 'message' => 'Voluntario eliminado exitosamente.'];
            break;

        case 'delegate_zone':
            // Delegar zona a otro coordinador
            $zone_id = $_POST['zone_id'] ?? '';
            $new_coordinator_id = $_POST['new_coordinator_id'] ?? '';
            $reason = $_POST['reason'] ?? '';

            if (empty($zone_id) || empty($new_coordinator_id)) {
                throw new Exception("Zona y nuevo coordinador son requeridos.");
            }

            // Verificar que el nuevo coordinador sea válido
            $check_sql = "SELECT id FROM users WHERE id = ? AND role_id = 2 AND approved = 1";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $new_coordinator_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception("Nuevo coordinador no válido.");
            }

            // Actualizar la asignación de zona
            $update_sql = "UPDATE coordinador_zona SET user_id = ? WHERE zona_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_coordinator_id, $zone_id);
            if (!$update_stmt->execute()) {
                throw new Exception("Error al delegar zona: " . $conn->error);
            }

            // Opcional: registrar la delegación en un log si es necesario
            // Aquí podrías insertar en una tabla de logs si tienes una

            $response = ['success' => true, 'message' => 'Zona delegada exitosamente.'];
            break;

        default:
            $response['error'] = 'Acción no válida.';
            break;
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
