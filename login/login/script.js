// Este script añade interactividad a los campos del formulario
// para crear el efecto de "etiqueta flotante" de forma robusta.

document.addEventListener('DOMContentLoaded', () => {
    // CORRECCIÓN: Se cambió '.form-group' por '.input-group' para que coincida con el HTML.
    const inputs = document.querySelectorAll('.input-group input');

    /**
     * Función que maneja el estado visual del input y su etiqueta.
     * @param {HTMLInputElement} input - El elemento input a evaluar.
     */
    const handleInputState = (input) => {
        // Si el campo tiene contenido, se asegura de que la etiqueta flote.
        if (input.value.trim() !== '') {
            input.classList.add('has-content');
        } else {
            // Si el campo está vacío, quita la clase para que la etiqueta vuelva a su posición.
            input.classList.remove('has-content');
        }
    };

    inputs.forEach(input => {
        // 1. Revisa el estado inicial al cargar la página.
        // Esto es útil si el navegador autocompleta los campos.
        handleInputState(input);

        // 2. Cuando el usuario entra al campo (focus).
        input.addEventListener('focus', () => {
            input.classList.add('has-content');
        });

        // 3. Cuando el usuario sale del campo (blur).
        input.addEventListener('blur', () => {
            handleInputState(input);
        });

        // 4. SOLUCIÓN PARA AUTOCOMPLETADO:
        // Un temporizador vuelve a comprobar el estado 100ms después de cargar la página.
        setTimeout(() => {
            handleInputState(input);
        }, 100);
    });

    console.log('Página de login interactiva y corregida.');
});

