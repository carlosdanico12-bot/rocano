<?php
// Incluir el archivo de configuración para tener acceso a las sesiones
// y a la constante BASE_URL.
require_once __DIR__ . '/../includes/config.php';

// --- PROCESO DE CIERRE DE SESIÓN SEGURO ---

// 1. Vaciar todas las variables de sesión.
// Esto elimina los datos guardados como 'user_id', 'user_role', etc.
$_SESSION = [];

// 2. Destruir la sesión por completo.
// Esto elimina la sesión del servidor.
session_destroy();

// 3. Redirigir al usuario a la página de inicio de sesión.
// Usamos la constante BASE_URL para asegurar que la ruta sea siempre correcta.
header("Location: " . BASE_URL . "login/login/");

// 4. Detener la ejecución del script.
// Es una buena práctica usar exit() después de una redirección para
// asegurar que no se ejecute ningún código adicional.
exit;
?>
