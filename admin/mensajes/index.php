<?php
$page_title = 'Centro de Comunicación';
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="style.css">

<div class="page-header">
    <h1><i class="fas fa-inbox"></i> Centro de Comunicación Interno</h1>
</div>

<div class="chat-container">
    <!-- Columna de Conversaciones -->
    <div id="conversations-list-container" class="card conversations-list">
        <div class="list-header">
            <h3>Conversaciones</h3>
            <button id="newMessageBtn" class="cta-button small"><i class="fas fa-plus"></i> Nuevo Mensaje</button>
        </div>
        <div id="loader-conversations" class="loader">Cargando...</div>
        <div id="conversation-items"></div>
    </div>

    <!-- Columna de Chat Activo -->
    <div id="chat-window-container" class="card chat-window">
        <div id="chat-welcome" class="welcome-message">
            <i class="fas fa-comments"></i>
            <p>Selecciona una conversación o inicia una nueva.</p>
        </div>
        <div id="chat-active" style="display: none;">
            <div id="chat-header" class="chat-header"></div>
            <div id="chat-messages" class="chat-messages"></div>
            <div id="reply-area" class="reply-area">
                <form id="replyForm" enctype="multipart/form-data">
                    <input type="hidden" name="recipient_id" id="replyRecipientId">
                    <div class="reply-input-wrapper">
                        <textarea name="message_content" id="replyMessageContent" placeholder="Escribe tu respuesta..." required></textarea>
                        <label for="replyAttachment" class="attachment-label"><i class="fas fa-paperclip"></i></label>
                        <input type="file" name="attachment_file" id="replyAttachment" style="display: none;">
                    </div>
                    <button type="submit" id="sendReplyBtn" class="cta-button"><i class="fas fa-paper-plane"></i></button>
                </form>
                <div id="reply-filename" class="attachment-filename"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Nuevo Mensaje -->
<div id="newMessageModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Nuevo Mensaje</h2>
        <form id="newMessageForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="recipientSelect">Destinatario</label>
                <select name="recipient_id" id="recipientSelect" required></select>
            </div>
            <div class="form-group">
                <label for="newMessageContent">Mensaje</label>
                <textarea name="message_content" id="newMessageContent" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="newAttachment">Adjuntar Archivo (Opcional)</label>
                <input type="file" name="attachment_file" id="newAttachment">
            </div>
            <div id="new-attachment-filename" class="attachment-filename"></div>
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" id="sendNewMessageBtn" class="cta-button">Enviar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Notificación -->
<div id="notificationModal" class="modal">
    <div class="modal-content small">
         <span class="close-btn">&times;</span>
        <h2 id="notificationTitle"></h2>
        <p id="notificationMessage"></p>
        <div class="form-actions">
             <button type="button" class="cta-button" id="notificationOk">Aceptar</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

