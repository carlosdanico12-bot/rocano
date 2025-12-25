document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = window.BASE_URL || document.body.dataset.baseUrl || '';
    const CURRENT_USER_ID = document.body.dataset.userId || '';

    // Modals & Forms
    const newMessageModal = document.getElementById('newMessageModal');
    const newMessageForm = document.getElementById('newMessageForm');
    const replyForm = document.getElementById('replyForm');
    const notificationModal = document.getElementById('notificationModal');
    
    // Buttons & Inputs
    const newMessageBtn = document.getElementById('newMessageBtn');
    const recipientSelect = document.getElementById('recipientSelect');
    const newMessageContent = document.getElementById('newMessageContent');
    const newAttachment = document.getElementById('newAttachment');
    const replyAttachment = document.getElementById('replyAttachment');
    const replyMessageContent = document.getElementById('replyMessageContent');
    const replyRecipientId = document.getElementById('replyRecipientId');

    // Display Areas
    const conversationsContainer = document.getElementById('conversation-items');
    const chatWindow = document.getElementById('chat-window-container');
    const chatWelcome = document.getElementById('chat-welcome');
    const chatActive = document.getElementById('chat-active');
    const chatHeader = document.getElementById('chat-header');
    const chatMessages = document.getElementById('chat-messages');
    
    // Loaders & Indicators
    const conversationsLoader = document.getElementById('loader-conversations');
    const newAttachmentFilename = document.getElementById('new-attachment-filename');
    const replyFilename = document.getElementById('reply-filename');
    
    let activePartnerId = null;
    let conversationsInterval;

    // --- UTILITY FUNCTIONS ---
    function showNotification(message, isError = false) {
        document.getElementById('notificationTitle').textContent = isError ? 'Error' : 'Éxito';
        document.getElementById('notificationMessage').textContent = message;
        notificationModal.style.display = 'block';
    }

    function toggleLoader(loaderElement, show) {
        if (loaderElement) loaderElement.style.display = show ? 'block' : 'none';
    }

    function closeAllModals() {
        document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
    }
    
    // --- DATA FETCHING & RENDERING ---
    function fetchConversations() {
        toggleLoader(conversationsLoader, true);
        conversationsContainer.style.display = 'none';

        fetch('ajax_handler.php?action=get_conversations')
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderConversations(data.conversations);
                } else {
                    showNotification(data.error, true);
                }
            })
            .catch(() => showNotification("Error de red al cargar conversaciones.", true))
            .finally(() => {
                toggleLoader(conversationsLoader, false);
                conversationsContainer.style.display = 'block';
            });
    }

    function renderConversations(conversations) {
        conversationsContainer.innerHTML = '';
        if (conversations.length === 0) {
            conversationsContainer.innerHTML = '<p class="empty-state">No tienes conversaciones.</p>';
            return;
        }
        conversations.forEach(convo => {
            const convoItem = document.createElement('div');
            convoItem.className = 'conversation-item';
            convoItem.dataset.partnerId = convo.partner_id;
            if (convo.unread_count > 0) {
                convoItem.classList.add('unread');
            }
            if (convo.partner_id == activePartnerId) {
                convoItem.classList.add('active');
            }

            convoItem.innerHTML = `
                <i class="fas fa-user-circle"></i>
                <div class="convo-details">
                    <div class="convo-header">
                        <span class="convo-partner">${convo.partner_name}</span>
                        <span class="convo-time">${formatTime(convo.last_message_time)}</span>
                    </div>
                    <p class="convo-preview">${convo.last_message}</p>
                </div>
                ${convo.unread_count > 0 ? `<span class="unread-badge">${convo.unread_count}</span>` : ''}
            `;
            conversationsContainer.appendChild(convoItem);
        });
    }
    
    function fetchMessages(partnerId) {
        activePartnerId = partnerId;
        chatWelcome.style.display = 'none';
        chatActive.style.display = 'flex';
        chatMessages.innerHTML = '<div class="loader">Cargando mensajes...</div>';
        
        const partner = document.querySelector(`.conversation-item[data-partner-id='${partnerId}']`);
        if(partner) {
             document.querySelectorAll('.conversation-item').forEach(c => c.classList.remove('active'));
             partner.classList.add('active');
             chatHeader.innerHTML = `<h3>Conversación con ${partner.querySelector('.convo-partner').textContent}</h3>`;
             replyRecipientId.value = partnerId;
        }

        fetch(`ajax_handler.php?action=get_messages&partner_id=${partnerId}`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    renderMessages(data.messages);
                    // After messages are loaded and marked as read, refresh sidebar
                    fetchConversations();
                } else {
                    chatMessages.innerHTML = `<p class="empty-state">${data.error}</p>`;
                }
            });
    }
    
    function renderMessages(messages) {
        chatMessages.innerHTML = '';
        messages.forEach(msg => {
            const isSent = msg.sent_by == CURRENT_USER_ID;
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isSent ? 'sent' : 'received'}`;
            
            let fileHtml = '';
            if (msg.file_url) {
                const isImage = /\.(jpg|jpeg|png|gif)$/i.test(msg.file_url);
                if(isImage){
                    fileHtml = `<a href="${BASE_URL}${msg.file_url}" target="_blank"><img src="${BASE_URL}${msg.file_url}" class="message-attachment-image"></a>`;
                } else {
                    fileHtml = `<a href="${BASE_URL}${msg.file_url}" target="_blank" class="message-attachment-file"><i class="fas fa-file-alt"></i> ${msg.file_url.split('/').pop()}</a>`;
                }
            }
            
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${fileHtml}
                    <p>${msg.message_content}</p>
                </div>
                <div class="message-meta">
                    <span>${isSent ? 'Tú' : msg.sender_name}</span> - <span>${formatTime(msg.created_at)}</span>
                </div>
            `;
            chatMessages.appendChild(messageDiv);
        });
        chatMessages.scrollTop = chatMessages.scrollHeight; // Scroll to bottom
    }

    function fetchRecipients() {
        fetch('ajax_handler.php?action=get_recipients')
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    recipientSelect.innerHTML = '<option value="">-- Seleccionar destinatario --</option>';
                    data.recipients.forEach(rec => {
                        recipientSelect.innerHTML += `<option value="${rec.id}">${rec.name} (${rec.role})</option>`;
                    });
                }
            });
    }

    // --- EVENT LISTENERS ---

    // Open new message modal
    newMessageBtn.addEventListener('click', () => {
        newMessageForm.reset();
        newAttachmentFilename.textContent = '';
        fetchRecipients();
        newMessageModal.style.display = 'block';
    });

    // Handle conversation click
    conversationsContainer.addEventListener('click', e => {
        const convoItem = e.target.closest('.conversation-item');
        if(convoItem){
            const partnerId = convoItem.dataset.partnerId;
            fetchMessages(partnerId);
        }
    });

    // New message form submission
    newMessageForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(newMessageForm);
        sendMessage(formData);
    });
    
    // Reply form submission
    replyForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(replyForm);
        sendMessage(formData);
    });

    function sendMessage(formData) {
        fetch('ajax_handler.php?action=send_message', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                closeAllModals();
                // If it was a new conversation, set it as active
                const newRecipientId = formData.get('recipient_id');
                if(!activePartnerId || activePartnerId !== newRecipientId){
                    activePartnerId = newRecipientId;
                }
                fetchConversations(); // Refresh conversation list
                if(activePartnerId){
                    setTimeout(() => fetchMessages(activePartnerId), 100); // Refresh active chat with a small delay
                }
                replyForm.reset();
                newMessageForm.reset();
                replyFilename.textContent = '';
                newAttachmentFilename.textContent = '';
            } else {
                showNotification(data.error, true);
            }
        })
        .catch(() => showNotification('Error de conexión.', true));
    }
    
    // File input change handlers
    newAttachment.addEventListener('change', () => {
        newAttachmentFilename.textContent = newAttachment.files.length > 0 ? newAttachment.files[0].name : '';
    });
    replyAttachment.addEventListener('change', () => {
        replyFilename.textContent = replyAttachment.files.length > 0 ? replyAttachment.files[0].name : '';
    });

    // Close modals
    document.querySelectorAll('.modal .close-btn, .modal .cancel-btn, #notificationOk').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.modal').style.display = 'none';
        });
    });
    window.onclick = (e) => {
      if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
      }
    };


    // --- UTILITY ---
    function formatTime(dateTimeStr) {
        const date = new Date(dateTimeStr.replace(' ', 'T')+'Z'); // Adjust for UTC if needed
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return date.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: true });
        }
        if(date.toDateString() === yesterday.toDateString()){
            return 'Ayer';
        }
        return date.toLocaleDateString('es-PE', {day: '2-digit', month: '2-digit', year: 'numeric'});
    }

    // --- INITIALIZATION ---
    fetchConversations();
    // Refresh conversations every 30 seconds
    conversationsInterval = setInterval(fetchConversations, 30000);
});

