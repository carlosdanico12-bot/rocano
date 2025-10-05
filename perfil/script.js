document.addEventListener('DOMContentLoaded', function() {
    
    const avatarInput = document.getElementById('foto_url');
    const avatarImage = document.querySelector('.profile-avatar');

    if (avatarInput && avatarImage) {
        // Previsualizar la imagen de perfil al seleccionarla
        avatarInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarImage.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }

});
