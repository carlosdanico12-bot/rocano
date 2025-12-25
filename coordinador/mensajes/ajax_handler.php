<?php
// #############################################################################
// API PARA EL SISTEMA DE MENSAJERÍA INTERNA BIDIRECCIONAL
// #############################################################################

header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. VERIFICACIÓN DE SEGURIDAD
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado.']);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['user_role'];
$response = ['success' => false, 'error' => 'Acción no válida.'];

try {
    switch ($action) {
        
        // --- OBTIENE LAS CONVERSACIONES PARA LA BANDEJA DE ENTRADA ---
        case 'get_conversations':
            $sql = "
                SELECT 
                    m.id, m.message_content, m.created_at,
                    sender.id as sender_id, sender.name as sender_name,
                    recipient.id as recipient_id, recipient.name as recipient_name,
                    mr.is_read
                FROM messages m
                JOIN users sender ON m.sent_by = sender.id
                JOIN message_recipients mr ON m.id = mr.message_id
                JOIN users recipient ON mr.recipient_user_id = recipient.id
                WHERE (m.sent_by = ? OR mr.recipient_user_id = ?)
                AND mr.is_archived = 0
                ORDER BY m.created_at DESC
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $current_user_id, $current_user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $conversations = [];
            while ($row = $result->fetch_assoc()) {
                // Determinar con quién es la conversación
                $partner_id = ($row['sender_id'] == $current_user_id) ? $row['recipient_id'] : $row['sender_id'];
                
                // Si la conversación no ha sido agregada, se agrega.
                if (!isset($conversations[$partner_id])) {
                    $conversations[$partner_id] = [
                        'partner_id' => $partner_id,
                        'partner_name' => ($row['sender_id'] == $current_user_id) ? $row['recipient_name'] : $row['sender_name'],
                        'last_message' => $row['message_content'],
                        'last_message_time' => $row['created_at'],
                        'unread_count' => 0
                    ];
                }
                // Contar mensajes no leídos
                if ($row['recipient_id'] == $current_user_id && $row['is_read'] == 0) {
                    $conversations[$partner_id]['unread_count']++;
                }
            }

            // Ordenar por la fecha del último mensaje
            uasort($conversations, function ($a, $b) {
                return strtotime($b['last_message_time']) - strtotime($a['last_message_time']);
            });

            $response = ['success' => true, 'conversations' => array_values($conversations)];
            break;

        // --- OBTIENE LOS MENSAJES DE UNA CONVERSACIÓN ESPECÍFICA ---
        case 'get_messages':
            $partner_id = (int)($_GET['partner_id'] ?? 0);
            if (!$partner_id) throw new Exception("ID de interlocutor no válido.");
            
            // Marcar mensajes como leídos
            $update_stmt = $conn->prepare("UPDATE message_recipients SET is_read = 1 WHERE message_id IN (SELECT id FROM messages WHERE sent_by = ?) AND recipient_user_id = ?");
            $update_stmt->bind_param("ii", $partner_id, $current_user_id);
            $update_stmt->execute();

            // Obtener el historial de la conversación
            $sql = "SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sent_by = u.id
                    WHERE (m.sent_by = ? AND m.id IN (SELECT message_id FROM message_recipients WHERE recipient_user_id = ?))
                       OR (m.sent_by = ? AND m.id IN (SELECT message_id FROM message_recipients WHERE recipient_user_id = ?))
                    ORDER BY m.created_at ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $current_user_id, $partner_id, $partner_id, $current_user_id);
            $stmt->execute();
            $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $response = ['success' => true, 'messages' => $messages];
            break;

        // --- ENVÍA UN NUEVO MENSAJE O RESPUESTA ---
        case 'send_message':
            $recipient_id = (int)($_POST['recipient_id'] ?? 0);
            $message_content = trim($_POST['message_content'] ?? '');
            $file_url = null;
            
            if (empty($recipient_id) || (empty($message_content) && empty($_FILES['attachment_file']['name']))) {
                throw new Exception("Se requiere un destinatario y un mensaje o archivo.");
            }

            // Manejo de archivo adjunto
            if (isset($_FILES['attachment_file']) && $_FILES['attachment_file']['error'] == 0) {
                $target_dir = __DIR__ . "/../../public/uploads/messages/";
                if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
                $file_name = uniqid() . '-' . basename($_FILES["attachment_file"]["name"]);
                $target_file = $target_dir . $file_name;
                if (move_uploaded_file($_FILES["attachment_file"]["tmp_name"], $target_file)) {
                    $file_url = 'public/uploads/messages/' . $file_name;
                }
            }

            $conn->begin_transaction();
            // Insertar en la tabla principal de mensajes
            $stmt_msg = $conn->prepare("INSERT INTO messages (sent_by, message_content, file_url) VALUES (?, ?, ?)");
            $stmt_msg->bind_param("iss", $current_user_id, $message_content, $file_url);
            if (!$stmt_msg->execute()) { $conn->rollback(); throw new Exception("Error al guardar el mensaje."); }
            $message_id = $conn->insert_id;

            // Vincular con el destinatario
            $stmt_rec = $conn->prepare("INSERT INTO message_recipients (message_id, recipient_user_id) VALUES (?, ?)");
            $stmt_rec->bind_param("ii", $message_id, $recipient_id);
            if (!$stmt_rec->execute()) { $conn->rollback(); throw new Exception("Error al asignar destinatario."); }

            $conn->commit();
            $response = ['success' => true];
            break;
            
        // --- OBTIENE LA LISTA DE POSIBLES DESTINATARIOS SEGÚN EL ROL ---
        case 'get_recipients':
            $recipients = [];

            if ($current_user_role === 'admin') {
                $sql_recipients = "SELECT id, name, (SELECT name FROM roles WHERE id=role_id) as role FROM users WHERE id != ? AND approved=1 ORDER BY name";
                $stmt = $conn->prepare($sql_recipients);
                $stmt->bind_param("i", $current_user_id);
            } elseif ($current_user_role === 'coordinador') {
                $sql_recipients = "
                    (SELECT id, name, 'Administrador' as role FROM users WHERE role_id = 1 AND approved=1)
                    UNION
                    (SELECT u.id, u.name, 'Voluntario' as role FROM users u 
                     WHERE u.role_id = 3 AND u.approved=1 AND u.zona_id IN (SELECT zona_id FROM coordinador_zona WHERE user_id = ?))
                    ORDER BY name";
                $stmt = $conn->prepare($sql_recipients);
                $stmt->bind_param("i", $current_user_id);
            } elseif ($current_user_role === 'voluntario') {
                $sql_recipients = "
                    (SELECT id, name, 'Administrador' as role FROM users WHERE role_id = 1 AND approved=1)
                    UNION
                    (SELECT DISTINCT u.id, u.name, 'Coordinador' as role FROM users u 
                     JOIN coordinador_zona cz ON u.id = cz.user_id
                     WHERE u.role_id = 2 AND u.approved=1 AND cz.zona_id = (SELECT zona_id FROM users WHERE id=? LIMIT 1))
                    ORDER BY name";
                $stmt = $conn->prepare($sql_recipients);
                $stmt->bind_param("i", $current_user_id);
            }
            
            if (isset($stmt)) {
                $stmt->execute();
                $recipients = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }
            $response = ['success' => true, 'recipients' => $recipients];
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
exit;

