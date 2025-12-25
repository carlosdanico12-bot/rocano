<?php
/**
 * Archivo de Configuración Principal del Sistema
 *
 * Centraliza la configuración de la sesión, rutas, base de datos y funciones globales.
 */

// --- 1. INICIO DE SESIÓN ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 2. CONFIGURACIÓN DE RUTA FIJA PARA EL HOSTING ---
define('BASE_URL', 'http://localhost/campana-politica-web/');


// --- 3. CONFIGURACIÓN DE LA BASE DE DATOS ---
define('DB_HOST', 'localhost'); // Usualmente se queda como 'localhost'.
define('DB_USER', 'root'); // El usuario de BD que creaste.
define('DB_PASS', ''); // La contraseña que asignaste.
define('DB_NAME', 'campana_politica'); // El nombre de la BD que creaste.

global $conn;

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Error de Conexión a la Base de Datos: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Error crítico: El sistema no está disponible en este momento.");
}

// --- 4. CONFIGURACIÓN DE SEGURIDAD (CSP) ---
header("Content-Security-Policy: script-src 'self' 'unsafe-inline' 'wasm-unsafe-eval' 'inline-speculation-rules' chrome-extension://0150f469-935e-42b0-a3c4-5deeaffffd71/ 'sha256-kPx0AsF0oz2kKiZ875xSvv693TBHkQ/0SkMJZnnNpnQ=' https://maps.googleapis.com https://cdn.jsdelivr.net; connect-src 'self' https://maps.googleapis.com");

// --- 5. FUNCIONES GLOBALES ---
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

