<?php
// Este archivo maneja las peticiones AJAX para actualizar el estado de una tarea.
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';

// Verificación de seguridad básica
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado.']);
    exit;
}

$task_id = $_POST['task_id'] ?? null;
$new_status = $_POST['new_status'] ?? null;
$allowed_statuses = ['Pendiente', 'En Progreso', 'Completada'];

if (!$task_id || !$new_status || !in_array($new_status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE tasks SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $task_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No se pudo actualizar la tarea en la base de datos.']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
