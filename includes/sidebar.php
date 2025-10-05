<?php
// Se asume que config.php ya ha sido incluido y la sesión iniciada.
$current_dir = basename(dirname($_SERVER['SCRIPT_NAME']));
$user_role = $_SESSION['user_role'] ?? 'invitado';

function isActive($directory, $current_dir) {
    return $directory === $current_dir ? 'class="active"' : '';
}

// Determina la ruta base según el rol para las páginas compartidas
$base_role_path = BASE_URL . $user_role . '/';
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo $base_role_path; ?>dashboard/">
            <img src="<?php echo BASE_URL; ?>public/images/logo.png" alt="Logo Campaña" class="sidebar-logo">
            <span class="sidebar-title">Campaña Ing. Cori</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <!-- Rutas Comunes para todos los roles logueados -->
            <li><a href="<?php echo $base_role_path; ?>dashboard/" <?php echo isActive('dashboard', $current_dir); ?>><i class="fas fa-tachometer-alt fa-fw"></i><span>Dashboard</span></a></li>
            <li><a href="<?php echo $base_role_path; ?>mensajes/" <?php echo isActive('mensajes', $current_dir); ?>><i class="fas fa-inbox fa-fw"></i><span>Mensajes</span></a></li>
            <li><a href="<?php echo $base_role_path; ?>cumpleanos/" <?php echo isActive('cumpleanos', $current_dir); ?>><i class="fas fa-birthday-cake fa-fw"></i><span>Cumpleaños</span></a></li>

            <!-- Admin y Coordinador -->
            <?php if ($user_role === 'admin' || $user_role === 'coordinador'): ?>
                <li><a href="<?php echo $base_role_path; ?>votantes/" <?php echo isActive('votantes', $current_dir); ?>><i class="fas fa-users fa-fw"></i><span>Votantes</span></a></li>
                <li><a href="<?php echo $base_role_path; ?>voluntarios/" <?php echo isActive('voluntarios', $current_dir); ?>><i class="fas fa-hands-helping fa-fw"></i><span>Voluntarios</span></a></li>
                <li><a href="<?php echo $base_role_path; ?>tareas/" <?php echo isActive('tareas', $current_dir); ?>><i class="fas fa-tasks fa-fw"></i><span>Tareas</span></a></li>
                <li><a href="<?php echo $base_role_path; ?>eventos/" <?php echo isActive('eventos', $current_dir); ?>><i class="fas fa-calendar-alt fa-fw"></i><span>Eventos</span></a></li>
                <li><a href="<?php echo $base_role_path; ?>encuestas/" <?php echo isActive('encuestas', $current_dir); ?>><i class="fas fa-poll-h fa-fw"></i><span>Encuestas</span></a></li>
                <li><a href="<?php echo $base_role_path; ?>reportes/" <?php echo isActive('reportes', $current_dir); ?>><i class="fas fa-chart-bar fa-fw"></i><span>Reportes</span></a></li>
            <?php endif; ?>

            <!-- Solo Admin -->
            <?php if ($user_role === 'admin'): ?>
                <li><a href="<?php echo BASE_URL; ?>admin/zonas/" <?php echo isActive('zonas', $current_dir); ?>><i class="fas fa-map-marked-alt fa-fw"></i><span>Zonas</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/usuarios/" <?php echo isActive('usuarios', $current_dir); ?>><i class="fas fa-user-shield fa-fw"></i><span>Usuarios</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/configuracion/" <?php echo isActive('configuracion', $current_dir); ?>><i class="fas fa-cogs fa-fw"></i><span>Configuración</span></a></li>
            <?php endif; ?>

            <!-- Solo Voluntario -->
            <?php if ($user_role === 'voluntario'): ?>
                <li><a href="<?php echo BASE_URL; ?>voluntario/mis-tareas/" <?php echo isActive('mis-tareas', $current_dir); ?>><i class="fas fa-tasks fa-fw"></i><span>Mis Tareas</span></a></li>
                <li><a href="<?php echo BASE_URL; ?>voluntario/registrar-votante/" <?php echo isActive('registrar-votante', $current_dir); ?>><i class="fas fa-user-plus fa-fw"></i><span>Registrar Votante</span></a></li>
                 <li><a href="<?php echo BASE_URL; ?>voluntario/evidencias/" <?php echo isActive('evidencias', $current_dir); ?>><i class="fas fa-upload fa-fw"></i><span>Evidencias</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="<?php echo BASE_URL; ?>login/logout.php" class="logout-button">
            <i class="fas fa-sign-out-alt fa-fw"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>

