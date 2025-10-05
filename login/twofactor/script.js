document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('twoFactorForm');
    const inputs = form.querySelectorAll('.code-input');

    if (form && inputs.length > 0) {
        
        // Enfocar el primer input al cargar la página
        inputs[0].focus();
        inputs[0].select();

        inputs.forEach((input, index) => {
            
            // --- 1. Mover al siguiente input al escribir ---
            input.addEventListener('input', (e) => {
                // Solo permitir un caracter
                if (input.value.length > 1) {
                    input.value = input.value.slice(0, 1);
                }
                // Si se escribe un número, saltar al siguiente
                if (input.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            // --- 2. Manejar la tecla de retroceso (Backspace) ---
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                    // Si el input está vacío y se presiona backspace, mover al anterior
                    inputs[index - 1].focus();
                }
            });

            // --- 3. Manejar el pegado de código ---
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                const code = pasteData.replace(/\s/g, '').slice(0, inputs.length); // Limpiar y cortar el código

                if (/^\d+$/.test(code)) { // Asegurarse de que son solo dígitos
                    inputs.forEach((box, i) => {
                        box.value = code[i] || '';
                    });
                    
                    // Enfocar el último campo o el siguiente vacío
                    const lastFilledIndex = Math.min(code.length, inputs.length - 1);
                    inputs[lastFilledIndex].focus();
                }
            });

            // --- 4. Seleccionar el contenido al enfocar ---
            input.addEventListener('focus', () => {
                input.select();
            });

        });
    }
});
