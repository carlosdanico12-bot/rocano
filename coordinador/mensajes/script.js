document.addEventListener('DOMContentLoaded', function() {
    
    const messageTextarea = document.getElementById('message_content');
    const charCountSpan = document.getElementById('char_count');
    const previewContentP = document.getElementById('preview_content');

    if (!messageTextarea || !charCountSpan || !previewContentP) return;

    // --- Lógica de Contador de Caracteres y Previsualización en Tiempo Real ---
    messageTextarea.addEventListener('input', function() {
        const message = this.value;
        const charLength = message.length;

        // Actualizar contador
        charCountSpan.textContent = `${charLength}/160`;
        if (charLength > 160) {
            charCountSpan.style.color = 'var(--color-principal)';
        } else {
            charCountSpan.style.color = '';
        }

        // Actualizar previsualización
        if (message.trim() === '') {
            previewContentP.textContent = 'Tu mensaje aparecerá aquí...';
        } else {
            // Reemplazar la variable {{nombre}} por un ejemplo
            let previewText = message.replace(/\{\{nombre\}\}/g, 'Juan Pérez');
            previewContentP.textContent = previewText;
        }
    });

    // --- Confirmación antes de enviar (opcional pero recomendado) ---
    const composeForm = document.querySelector('.compose-card form');
    if (composeForm) {
        composeForm.addEventListener('submit', function(e) {
            const segmentSelect = document.getElementById('target_segment');
            const selectedOption = segmentSelect.options[segmentSelect.selectedIndex];
            const targetText = selectedOption.text;

            if (!confirm(`¿Estás seguro de que deseas enviar este mensaje a:\n\n${targetText}?`)) {
                e.preventDefault(); // Cancelar el envío del formulario
            }
        });
    }

});
