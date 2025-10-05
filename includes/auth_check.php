<?php
/**
 * Guardia de Autenticación
 *
 * Este script es el único responsable de verificar si un usuario
 * ha iniciado sesión. Si el usuario no está autenticado, lo redirige
 * a la página de login y detiene cualquier ejecución posterior.
 *
 * Debe ser incluido al principio de CADA página que requiera protección.
 */

// Asegurarnos de que config.php (que inicia la sesión) se haya cargado.
// Usamos __DIR__ para una ruta robusta.
require_once __DIR__ . '/config.php';

// Verificamos si la variable de sesión 'user_id' NO está definida.
// Esta variable solo debería existir después de un inicio de sesión exitoso.
if (!isset($_SESSION['user_id'])) {
    
    // Si no hay sesión, construimos la URL de login.
    $login_url = BASE_URL . 'login/login/';
    
    // Redirigimos al usuario a la página de login.
    header("Location: " . $login_url);
    
    // Detenemos la ejecución del script para prevenir que se cargue
    // el resto de la página protegida.
    exit;
}

// Si el script llega a este punto, significa que el usuario SÍ está autenticado
// y puede continuar cargando la página protegida.
?>
