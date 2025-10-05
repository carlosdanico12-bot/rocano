document.addEventListener('DOMContentLoaded', function() {
    
    const fileInput = document.getElementById('evidence_file');
    const dropZone = document.querySelector('.file-drop-zone');
    const previewContainer = document.getElementById('image-preview-container');

    if (!fileInput || !dropZone || !previewContainer) return;

    // --- Lógica para el Drop Zone (Arrastrar y Soltar) ---
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('dragover');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect();
        }
    });

    // --- Lógica para la Previsualización de Imagen ---
    fileInput.addEventListener('change', handleFileSelect);

    function handleFileSelect() {
        previewContainer.innerHTML = ''; // Limpiar previsualización anterior
        const file = fileInput.files[0];

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                previewContainer.appendChild(img);
            }

            reader.readAsDataURL(file);

            // Cambiar texto del dropzone para dar feedback
            dropZone.querySelector('p').textContent = `Archivo seleccionado: ${file.name}`;
        } else if(file) {
            dropZone.querySelector('p').textContent = 'Por favor, selecciona un archivo de imagen válido.';
        }
    }

});
