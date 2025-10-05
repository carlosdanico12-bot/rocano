document.addEventListener('DOMContentLoaded', function() {
    
    const form = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthIndicator = document.getElementById('password-strength');

    if (form && passwordInput && confirmPasswordInput && strengthIndicator) {
        
        // --- 1. VALIDACIÓN DE COINCIDENCIA DE CONTRASEÑAS ---
        form.addEventListener('submit', function(event) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                // Prevenir el envío del formulario
                event.preventDefault();
                
                // (Opcional) Mostrar un mensaje de error más dinámico
                alert('Las contraseñas no coinciden. Por favor, verifíquelas.');
                
                // Añadir un borde rojo para indicar el error
                passwordInput.style.borderColor = 'red';
                confirmPasswordInput.style.borderColor = 'red';
            }
        });

        // Limpiar el error visual cuando el usuario corrige
        confirmPasswordInput.addEventListener('input', function() {
             if (passwordInput.value === confirmPasswordInput.value) {
                passwordInput.style.borderColor = '#ced4da';
                confirmPasswordInput.style.borderColor = '#ced4da';
             }
        });

        // --- 2. INDICADOR DE FORTALEZA DE CONTRASEÑA ---
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            // Criterios de fortaleza
            if (password.length >= 8) strength++; // Longitud mínima
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++; // Mayúsculas y minúsculas
            if (password.match(/[0-9]/)) strength++; // Números
            if (password.match(/[^a-zA-Z0-9]/)) strength++; // Símbolos

            // Actualizar la barra indicadora
            strengthIndicator.className = ''; // Reset class
            if (password.length > 0) {
                if (strength <= 2) {
                    strengthIndicator.classList.add('weak');
                } else if (strength === 3) {
                    strengthIndicator.classList.add('medium');
                } else {
                    strengthIndicator.classList.add('strong');
                }
            }
        });
    }
});
