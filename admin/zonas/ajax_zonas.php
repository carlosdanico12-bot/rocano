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
        case 'get_all_with_stats':
            // CORRECCIÓN: Se eliminó la columna 'estado' que no existe en la tabla 'zonas'.
            $sql_zones = "SELECT id, nombre, descripcion, coordinates FROM zonas ORDER BY nombre ASC";
            $zones_result = $conn->query($sql_zones);
            if (!$zones_result) throw new Exception("Error en la consulta de zonas: " . $conn->error);
            
            $zones = [];

            while ($zone = $zones_result->fetch_assoc()) {
                $stmt = $conn->prepare("SELECT estado, COUNT(id) as count FROM votantes WHERE zona_id = ? GROUP BY estado");
                $stmt->bind_param("i", $zone['id']);
                $stmt->execute();
                $stats_result = $stmt->get_result();

                $stats = ['a_favor' => 0, 'indeciso' => 0, 'en_contra' => 0];
                while ($row = $stats_result->fetch_assoc()) {
                    $status_key = strtolower(str_replace(' ', '_', $row['estado']));
                    if (array_key_exists($status_key, $stats)) {
                        $stats[$status_key] = (int)$row['count'];
                    }
                }

                // Lógica Semáforo
                $dominant_status = 'Sin Votantes';
                if (array_sum(array_values($stats)) > 0) {
                    arsort($stats);
                    $top_status = key($stats);
                    if ($top_status === 'a_favor') $dominant_status = 'A favor';
                    elseif ($top_status === 'en_contra') $dominant_status = 'En contra';
                    else $dominant_status = 'Indeciso';
                }

                // Obtener coordinadores asignados a esta zona
                $coord_stmt = $conn->prepare("SELECT GROUP_CONCAT(u.name SEPARATOR ', ') as coordinators FROM coordinador_zona cz JOIN users u ON cz.user_id = u.id WHERE cz.zona_id = ? AND u.approved = 1");
                $coord_stmt->bind_param("i", $zone['id']);
                $coord_stmt->execute();
                $coord_result = $coord_stmt->get_result();
                $coordinators = $coord_result->fetch_assoc()['coordinators'] ?? '';

                // Obtener voluntarios asignados a esta zona
                $vol_stmt = $conn->prepare("SELECT GROUP_CONCAT(u.name SEPARATOR ', ') as volunteers FROM users u WHERE u.zona_id = ? AND u.role_id = 3 AND u.approved = 1");
                $vol_stmt->bind_param("i", $zone['id']);
                $vol_stmt->execute();
                $vol_result = $vol_stmt->get_result();
                $volunteers = $vol_result->fetch_assoc()['volunteers'] ?? '';

                $zone['stats'] = $stats;
                // CORRECCIÓN: Se asigna el estado calculado.
                $zone['status'] = $dominant_status;
                $zone['coordinators'] = $coordinators;
                $zone['volunteers'] = $volunteers;
                $zones[] = $zone;
            }
            $response = ['success' => true, 'zones' => $zones];
            break;

        case 'get_assignments':
            // Obtener coordinadores asignados a zonas
            $coordinators_sql = "SELECT cz.zona_id, u.id as user_id, u.name, 'Coordinador' as role
                FROM coordinador_zona cz
                JOIN users u ON cz.user_id = u.id
                WHERE u.approved = 1";
            $coordinators_result = $conn->query($coordinators_sql);
            $coordinators = $coordinators_result->fetch_all(MYSQLI_ASSOC);

            // Obtener voluntarios asignados a zonas
            $volunteers_sql = "SELECT u.zona_id, u.id as user_id, u.name, 'Voluntario' as role
                FROM users u
                WHERE u.role_id = 3 AND u.zona_id IS NOT NULL AND u.approved = 1";
            $volunteers_result = $conn->query($volunteers_sql);
            $volunteers = $volunteers_result->fetch_all(MYSQLI_ASSOC);

            $response = ['success' => true, 'coordinators' => $coordinators, 'volunteers' => $volunteers];
            break;

        case 'create':
        case 'update':
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $coordinates = $_POST['coordinates'] ?? null;
            $zone_id = $_POST['zone_id'] ?? null;

            if (empty($nombre) || empty($coordinates) || $coordinates === '[]') throw new Exception('Nombre y coordenadas son obligatorios.');

            if ($action === 'create') {
                $stmt = $conn->prepare("INSERT INTO zonas (nombre, descripcion, coordinates) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nombre, $descripcion, $coordinates);
            } else {
                $stmt = $conn->prepare("UPDATE zonas SET nombre = ?, descripcion = ?, coordinates = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nombre, $descripcion, $coordinates, $zone_id);
            }
            
            if ($stmt->execute()) {
                $response = ['success' => true];
            } else {
                throw new Exception('Error al guardar en la base de datos: ' . $stmt->error);
            }
            break;
            
        case 'delete':
            $zone_id = $_POST['zone_id'] ?? null;
            if (empty($zone_id)) throw new Exception('ID de zona no proporcionado.');
            
            $stmt = $conn->prepare("DELETE FROM zonas WHERE id = ?");
            $stmt->bind_param("i", $zone_id);
            if ($stmt->execute()) {
                $response = ['success' => true];
            } else {
                throw new Exception('Error al eliminar la zona.');
            }
            break;
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>

