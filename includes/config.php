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

// --- 2. CONFIGURACIÓN DE RUTAS (Versión Definitiva y Automática) ---
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$project_folder_name = 'campana-politica-web';
$project_pos = strpos($script_name, '/' . $project_folder_name . '/');

if ($project_pos !== false) {
    $base_path = substr($script_name, 0, $project_pos + strlen('/' . $project_folder_name));
} else {
    $base_path = ''; // Fallback
}

// CORRECCIÓN: Se eliminó la barra inclinada extra al final para evitar URLs dobles (//).
define('BASE_URL', $protocol . $host . $base_path . '/');


// --- 3. CONFIGURACIÓN DE LA BASE DE DATOS ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campana_politica');

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

// --- 4. FUNCIONES GLOBALES ---
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

