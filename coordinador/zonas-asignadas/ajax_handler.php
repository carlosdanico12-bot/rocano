<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinador') {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_REQUEST['action'] ?? '';
$response = ['success' => false, 'error' => 'Acción no válida.'];

try {
    switch ($action) {
        case 'get_my_zones':
            // Obtener zonas asignadas al coordinador actual
            $sql = "SELECT z.id, z.nombre
                    FROM zonas z
                    JOIN coordinador_zona cz ON z.id = cz.zona_id
                    WHERE cz.user_id = ?
                    ORDER BY z.nombre ASC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $zones = [];
            while ($row = $result->fetch_assoc()) {
                $zones[] = $row;
            }

            $response = ['success' => true, 'zones' => $zones];
            break;

        case 'get_my_volunteers':
            // Obtener voluntarios asignados a las zonas del coordinador
            $sql = "SELECT DISTINCT u.id, u.name
                    FROM users u
                    JOIN coordinador_zona cz ON u.zona_id = cz.zona_id
                    WHERE cz.user_id = ? AND u.role_id = 3 AND u.approved = 1
                    ORDER BY u.name ASC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $volunteers = [];
            while ($row = $result->fetch_assoc()) {
                $volunteers[] = $row;
            }

            $response = ['success' => true, 'volunteers' => $volunteers];
            break;

        case 'get_my_assignments':
            // Obtener asignaciones de voluntarios en las zonas del coordinador
            $sql = "SELECT
                        u.zona_id,
                        u.id as user_id,
                        u.name,
                        z.nombre as zone_name
                    FROM users u
                    JOIN coordinador_zona cz ON u.zona_id = cz.zona_id
                    JOIN zonas z ON u.zona_id = z.id
                    WHERE cz.user_id = ? AND u.role_id = 3 AND u.zona_id IS NOT NULL AND u.approved = 1
                    ORDER BY z.nombre, u.name";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $assignments = [];
            while ($row = $result->fetch_assoc()) {
                $assignments[] = $row;
            }

            $response = ['success' => true, 'assignments' => $assignments];
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
