<?php
// Se asume que config.php y auth_check.php ya fueron requeridos.
$user_name = $_SESSION['user_name'] ?? 'Invitado';
$user_role = $_SESSION['user_role'] ?? 'invitado';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'Panel de Campaña'); ?> - Campaña Ing. Cori</title>
    
    <!-- ===================== CORRECCIÓN CLAVE AQUÍ ===================== -->
    <!-- Se ha añadido el enlace correcto a Font Awesome para que los íconos se muestren. -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Carga la hoja de estilos unificada para todo el panel -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin_style.css">
    
    <!-- Carga una hoja de estilos específica para la página, si existe -->
    <?php
    $page_specific_css = 'style.css';
    if (file_exists($page_specific_css)) {
        echo '<link rel="stylesheet" href="' . $page_specific_css . '">';
    }
    ?>
</head>
<body>
    <div class="admin-wrapper">
        <?php require_once __DIR__ . '/sidebar.php'; // Incluir el menú lateral ?>
        
        <div class="main-content">
            <header class="admin-header">
                <button id="menu-toggle" class="menu-toggle-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="user-info-container">
                    <span>Hola, <strong><?php echo e($user_name); ?></strong></span>
                    <a href="<?php echo BASE_URL; ?>perfil/" class="header-link">Mi Perfil</a>
                    <a href="<?php echo BASE_URL; ?>login/logout.php" class="cta-button">Cerrar Sesión</a>
                </div>
            </header>
            
            <main class="content-area">
                <!-- El contenido de cada página se insertará aquí -->

