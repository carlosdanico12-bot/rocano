document.addEventListener('DOMContentLoaded', function() {
    
    const form = document.getElementById('registerVoterForm');
    const dniInput = document.getElementById('dni');

    if (!form) return;

    // --- Validación del formulario antes de enviar ---
    form.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Validar que el DNI, si se ingresa, sea numérico y de 8 dígitos
        if (dniInput && dniInput.value.trim() !== '') {
            if (!/^\d{8}$/.test(dniInput.value.trim())) {
                alert('El DNI debe contener exactamente 8 dígitos numéricos.');
                dniInput.focus();
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault(); // Detener el envío del formulario si no es válido
        }
    });

    // --- Limitar DNI a solo números ---
    if (dniInput) {
        dniInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

});
