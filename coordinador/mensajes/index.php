<?php
$page_title = 'Comunicación y Mensajes';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';

if ($_SESSION['user_role'] !== 'coordinador') {
    header("Location: " . BASE_URL . "login/login/");
    exit;
}

$errors = [];
$success_message = '';
$coordinator_id = $_SESSION['user_id'];

// --- Lógica de envío de mensajes ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_content = trim($_POST['message_content'] ?? '');
    $target_segment = $_POST['target_segment'] ?? '';

    if (empty($message_content) || empty($target_segment)) {
        $errors[] = "Debes escribir un mensaje y seleccionar un destinatario.";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO messages (message_content, target_segment, sent_by) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $message_content, $target_segment, $coordinator_id);
            if ($stmt->execute()) {
                $success_message = "Mensaje enviado (registrado en el historial) con éxito.";
            } else {
                $errors[] = "Error al registrar el mensaje en la base de datos.";
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Error en el sistema: " . $e->getMessage();
        }
    }
}


// --- Obtener datos para la segmentación y el historial ---
$coordinator_zones_ids = [];
$segment_counts = ['votantes_todos' => 0, 'votantes_indecisos' => 0, 'voluntarios_todos' => 0];
$sent_messages = [];

try {
    // 1. Obtener las ZONAS del coordinador
    $stmt_zones = $conn->prepare("SELECT zona_id FROM coordinador_zona WHERE user_id = ?");
    $stmt_zones->bind_param("i", $coordinator_id);
    $stmt_zones->execute();
    $result_zones = $stmt_zones->get_result();
    while ($row = $result_zones->fetch_assoc()) {
        $coordinator_zones_ids[] = $row['zona_id'];
    }

    if (!empty($coordinator_zones_ids)) {
        $placeholders = implode(',', array_fill(0, count($coordinator_zones_ids), '?'));
        $types = str_repeat('i', count($coordinator_zones_ids));

        // 2. Contar Votantes en esas zonas
        $stmt_votantes = $conn->prepare("SELECT estado, COUNT(id) as count FROM votantes WHERE zona_id IN ($placeholders) GROUP BY estado");
        $stmt_votantes->bind_param($types, ...$coordinator_zones_ids);
        $stmt_votantes->execute();
        $result_votantes = $stmt_votantes->get_result();
        while($row = $result_votantes->fetch_assoc()) {
            $segment_counts['votantes_todos'] += $row['count'];
            if ($row['estado'] === 'Indeciso') {
                $segment_counts['votantes_indecisos'] = $row['count'];
            }
        }

        // 3. Contar Voluntarios en esas zonas
        $stmt_vol = $conn->prepare("SELECT COUNT(id) as count FROM users WHERE role_id = 3 AND approved = 1 AND zona_id IN ($placeholders)");
        $stmt_vol->bind_param($types, ...$coordinator_zones_ids);
        $stmt_vol->execute();
        $result_vol = $stmt_vol->get_result();
        $segment_counts['voluntarios_todos'] = $result_vol->fetch_assoc()['count'] ?? 0;
    }

    // 4. Obtener historial de mensajes enviados por este coordinador
    $stmt_hist = $conn->prepare("SELECT * FROM messages WHERE sent_by = ? ORDER BY sent_at DESC LIMIT 10");
    $stmt_hist->bind_param("i", $coordinator_id);
    $stmt_hist->execute();
    $result_hist = $stmt_hist->get_result();
    while($row = $result_hist->fetch_assoc()) {
        $sent_messages[] = $row;
    }

} catch (Exception $e) {
    $errors[] = "Error al cargar los datos de la página: " . $e->getMessage();
}


require_once __DIR__ . '/../../includes/header.php';
?>

<div class="page-header">
    <h1>Centro de Mensajes</h1>
    <p class="page-subtitle">Comunícate directamente con los votantes y voluntarios de tus zonas.</p>
</div>

<?php if (!empty($errors)): ?>
    <div class="message error-message">
        <?php foreach ($errors as $error): ?><p><?php echo e($error); ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($success_message): ?>
    <div class="message success-message"><p><?php echo e($success_message); ?></p></div>
<?php endif; ?>

<div class="messaging-layout">
    <!-- Columna de Composición -->
    <div class="card compose-card">
        <h3>Componer Mensaje</h3>
        <form action="" method="POST">
            <div class="form-group">
                <label for="target_segment">1. Elige los Destinatarios</label>
                <select name="target_segment" id="target_segment" required>
                    <option value="" disabled selected>-- Seleccionar un grupo --</option>
                    <option value="votantes_todos">Todos los Votantes de mis Zonas (<?php echo $segment_counts['votantes_todos']; ?>)</option>
                    <option value="votantes_indecisos">Votantes Indecisos (<?php echo $segment_counts['votantes_indecisos']; ?>)</option>
                    <option value="voluntarios_todos">Mi Equipo de Voluntarios (<?php echo $segment_counts['voluntarios_todos']; ?>)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="message_content">2. Escribe tu Mensaje</label>
                <textarea name="message_content" id="message_content" rows="8" placeholder="Hola {{nombre}}, te invitamos a nuestro próximo evento..."></textarea>
                <div class="textarea-footer">
                    <span>Puedes usar <strong>{{nombre}}</strong> como variable.</span>
                    <span id="char_count">0/160</span>
                </div>
            </div>
            <button type="submit" class="cta-button"><i class="fas fa-paper-plane"></i> Enviar Mensaje</button>
        </form>
    </div>

    <!-- Columna de Previsualización e Historial -->
    <div class="preview-history-column">
        <div class="card preview-card">
            <h3>Previsualización</h3>
            <div class="phone-mockup">
                <div class="screen">
                    <div class="message-bubble received">
                        <p id="preview_content">Tu mensaje aparecerá aquí...</p>
                        <span class="timestamp"><?php echo date('H:i'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="card history-card">
            <h3><i class="fas fa-history"></i> Historial de Envíos Recientes</h3>
            <div class="history-list">
                <?php if(empty($sent_messages)): ?>
                    <p>Aún no has enviado mensajes.</p>
                <?php else: ?>
                    <?php foreach($sent_messages as $msg): ?>
                    <div class="history-item">
                        <p class="history-content">"<?php echo e(substr($msg['message_content'], 0, 50)); ?>..."</p>
                        <small>Enviado a: <strong><?php echo e(ucwords(str_replace('_', ' ', $msg['target_segment']))); ?></strong> el <?php echo date('d/m/Y H:i', strtotime($msg['sent_at'])); ?></small>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
