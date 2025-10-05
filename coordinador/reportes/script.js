// Este archivo está preparado para futuras interacciones.
// Por ejemplo, aquí se podría añadir lógica para mostrar un spinner de "Cargando..."
// mientras se genera el PDF en segundo plano.

document.addEventListener('DOMContentLoaded', function() {
    console.log("Módulo de reportes para coordinadores cargado.");

    document.querySelectorAll('.cta-button').forEach(button => {
        button.addEventListener('click', function() {
            // Se podría añadir un efecto visual al hacer clic
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando...';
            
            // Volver al estado original después de un tiempo (ya que el PDF abre en otra pestaña)
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-file-pdf"></i> Generar PDF';
            }, 3000);
        });
    });
});
