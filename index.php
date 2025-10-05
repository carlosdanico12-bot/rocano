<?php
/**
 * Archivo de Entrada Principal del Proyecto
 *
 * La única responsabilidad de este archivo es redirigir al usuario
 * a la página de inicio pública del sitio web.
 *
 * Utiliza el archivo de configuración para asegurar que la ruta
 * de redirección sea siempre la correcta.
 */

// 1. Incluir el archivo de configuración para tener acceso a la BASE_URL.
// Usamos require_once para asegurar que se incluya una sola vez y es esencial.
require_once __DIR__ . '/includes/config.php';

// 2. Construir la URL de destino.
// Se redirige a la carpeta 'public/home/'. Los servidores web
// como Apache cargarán automáticamente el archivo 'index.php' de ese directorio.
$url_destino = BASE_URL . 'public/home/';

// 3. Realizar la redirección.
// Se usa un encabezado de redirección HTTP 302 (redirección temporal).
// 'exit()' es crucial para detener la ejecución del script después de la redirección.
header("Location: " . $url_destino);
exit;

?>

