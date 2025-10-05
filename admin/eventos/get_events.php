<?php
// Este archivo devuelve los eventos en formato JSON para que FullCalendar los pueda leer.
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';
session_start();

// Verificación de seguridad básica
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode([]);
    exit;
}

$events_json = [];
try {
    $sql = "SELECT id, titulo, fecha_hora as start, tipo_evento FROM events";
    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()) {
        // Añadir color según el tipo de evento
        switch ($row['tipo_evento']) {
            case 'Mitin': $row['backgroundColor'] = '#E63946'; break;
            case 'Caminata': $row['backgroundColor'] = '#457B9D'; break;
            case 'Reunion': $row['backgroundColor'] = '#F4A261'; break;
            default: $row['backgroundColor'] = '#2A9D8F'; break;
        }
        $row['title'] = $row['titulo']; // FullCalendar usa 'title'
        unset($row['titulo']);
        $events_json[] = $row;
    }
} catch (Exception $e) {
    // En caso de error, devolver un array vacío
    echo json_encode([]);
    exit;
}

echo json_encode($events_json);
?>
