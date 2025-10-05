<?php
// INDICACIÓN PARA MODIFICAR:
// Este archivo es la API interna de la sección de Zonas.
// Maneja todas las interacciones con la base de datos.
// No necesitas tocarlo a menos que quieras cambiar la lógica de cómo se calculan las estadísticas.

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
           $sql_zones = "SELECT id, nombre, descripcion, coordinates, 
              COALESCE(estado, 'Sin Votantes') as estado 
              FROM zonas ORDER BY nombre ASC";


            $zones_result = $conn->query($sql_zones);
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
                    arsort($stats); // Ordenar de mayor a menor
                    $top_status = key($stats);
                    if ($top_status === 'a_favor') $dominant_status = 'A favor';
                    elseif ($top_status === 'en_contra') $dominant_status = 'En contra';
                    else $dominant_status = 'Indeciso';
                }
                
                $zone['stats'] = $stats;
                $zone['status'] = $dominant_status ?: $zone['estado'];

                $zones[] = $zone;
            }
            $response = ['success' => true, 'zones' => $zones];
            break;

        case 'create':
        case 'update':
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $coordinates = $_POST['coordinates'] ?? null;
            $zone_id = $_POST['zone_id'] ?? null;

            if (empty($nombre)) throw new Exception('El nombre de la zona es obligatorio.');

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
                throw new Exception('Error al guardar en la base de datos.');
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

