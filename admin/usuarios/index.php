<?php
$page_title = 'Gestión de Usuarios - Campaña Ing. Cori';
require_once __DIR__ . '/../../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificación de sesión y rol
require_once __DIR__ . '/../../includes/auth_check.php';
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="users-container">
    <div class="page-header">
        <h1>Gestión de Usuarios</h1>
        <button id="createUserBtn" class="cta-button"><i class="fas fa-plus"></i> Añadir Usuario</button>
    </div>

    <div class="card filter-card">
        <div class="filter-group">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Buscar por nombre o correo...">
        </div>
        <div class="filter-group">
            <label for="roleFilter">Filtrar por Rol:</label>
            <select id="roleFilter">
                <option value="all">Todos los Roles</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="statusFilter">Estado:</label>
            <select id="statusFilter">
                <option value="all">Todos</option>
                <option value="1">Aprobado</option>
                <option value="0">Pendiente</option>
            </select>
        </div>
    </div>

    <div id="loader" class="loader">Cargando usuarios...</div>
    <div id="usersGrid" class="users-grid" style="display: none;"></div>
</div>

<!-- Modal Crear/Editar Usuario -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2 id="modalTitle">Añadir Nuevo Usuario</h2>
        <form id="userForm">
            <input type="hidden" name="user_id" id="user_id">
            <div class="form-group">
                <label for="name">Nombre Completo</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password">
                <small>Dejar en blanco para no cambiar la contraseña existente.</small>
            </div>
            <div class="form-group-grid">
                <div class="form-group">
                    <label for="role_id">Rol</label>
                    <select id="role_id" name="role_id" required></select>
                </div>
                <div class="form-group">
                    <label for="dni">DNI</label>
                    <input type="text" id="dni" name="dni">
                </div>
            </div>
            <div class="form-group" id="zona-group" style="display: none;">
                <label for="zona_id">Zona Asignada</label>
                <select id="zona_id" name="zona_id"></select>
            </div>
            <div class="form-actions">
                <button type="button" class="cancel-btn">Cancelar</button>
                <button type="submit" class="cta-button">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Notificación / Confirmación -->
<div id="notificationModal" class="modal">
    <div class="modal-content small">
        <span class="close-btn">&times;</span>
        <h2 id="notificationTitle"></h2>
        <p id="notificationMessage"></p>
        <div class="form-actions">
            <button type="button" class="cancel-btn" id="notificationCancel">Cancelar</button>
            <button type="button" class="cta-button" id="notificationOk">Aceptar</button>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
