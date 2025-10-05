<?php
// Se asume que config.php ya ha sido incluido.
$is_user_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'Campaña Política'); ?> - Ing. Cori</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/global.css">
</head>
<body>
<header class="main-header">
    <div class="header-container">
        <a href="<?php echo BASE_URL; ?>public/home/" class="logo-container">
            <img src="<?php echo BASE_URL; ?>public/images/logo.png" alt="Logo Campaña" class="logo-img">
            <div>
                <span class="logo-text">Ing. Cori</span>
                <span class="partido-text">Ahora Nación</span>
            </div>
        </a>
        <nav class="main-nav" id="main-nav">
            <a href="<?php echo BASE_URL; ?>public/home/" class="nav-link">Inicio</a>
            <a href="<?php echo BASE_URL; ?>public/propuestas/" class="nav-link">Propuestas</a>
            <a href="<?php echo BASE_URL; ?>public/noticias/" class="nav-link">Noticias</a>
            <a href="<?php echo BASE_URL; ?>public/about/" class="nav-link">Nosotros</a>
            
            <?php if ($is_user_logged_in): ?>
                <div class="user-menu-public">
                    <a href="<?php echo BASE_URL; ?>perfil/" class="nav-link">Hola, <?php echo e(explode(' ', $user_name)[0]); ?></a>
                    <a href="<?php echo BASE_URL; ?>login/logout.php" class="nav-link-button">Cerrar Sesión</a>
                </div>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>login/login/" class="nav-link-button">Acceso Interno</a>
            <?php endif; ?>
        </nav>
        <button class="menu-toggle" id="menu-toggle" aria-label="Abrir menú">
            <span class="hamburger"></span>
        </button>
    </div>
</header>

