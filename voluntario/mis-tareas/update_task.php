<?php
// Este archivo maneja las peticiones AJAX del voluntario para actualizar el estado de una tarea.
header('Content-Type: application/json');

require_once __DIR__ . '/../../includes/config.php';
session_start();

// Verificación de seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'voluntario') {
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado.']);
    exit;
}

$task_id = $_POST['task_id'] ?? null;
$new_status = $_POST['new_status'] ?? null;
$volunteer_id = $_SESSION['user_id'];
$allowed_statuses = ['En Progreso', 'Completada'];

if (!$task_id || !$new_status || !in_array($new_status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit;
}

try {
    // --- VERIFICACIÓN DE PERMISOS ---
    // Asegurarnos que el voluntario solo pueda modificar tareas que le pertenecen.
    $stmt_check = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND asignado_a = ?");
    $stmt_check->bind_param("ii", $task_id, $volunteer_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        throw new Exception('Permiso denegado. Esta tarea no te pertenece.');
    }

    // Si tiene permiso, proceder a actualizar
    $stmt_update = $conn->prepare("UPDATE tasks SET estado = ? WHERE id = ?");
    $stmt_update->bind_param("si", $new_status, $task_id);
    
    if ($stmt_update->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('No se pudo actualizar la tarea en la base de datos.');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
