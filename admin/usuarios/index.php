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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const BASE_URL = ''; // Ajusta si necesitas prefijo

    const userModal = document.getElementById('userModal');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('modalTitle');
    const createUserBtn = document.getElementById('createUserBtn');
    const usersGrid = document.getElementById('usersGrid');
    const loader = document.getElementById('loader');

    const notificationModal = document.getElementById('notificationModal');
    const notificationTitle = document.getElementById('notificationTitle');
    const notificationMessage = document.getElementById('notificationMessage');
    const notificationOkBtn = document.getElementById('notificationOk');
    const notificationCancelBtn = document.getElementById('notificationCancel');

    let allUsers = [];
    let allRoles = {};
    let allZonas = {};

    // Loader
    function toggleLoader(show) {
        loader.style.display = show ? 'block' : 'none';
        usersGrid.style.display = show ? 'none' : 'grid';
    }

    // Notificación
    function showNotification(message, isError = false) {
        notificationTitle.textContent = isError ? 'Error' : 'Éxito';
        notificationMessage.textContent = message;
        notificationOkBtn.textContent = 'Aceptar';
        notificationCancelBtn.style.display = 'none';
        notificationOkBtn.className = 'cta-button';
        notificationModal.style.display = 'block';
        const close = () => notificationModal.style.display = 'none';
        notificationOkBtn.onclick = close;
        notificationModal.querySelector('.close-btn').onclick = close;
    }

    function showConfirmation(message, onConfirm) {
        notificationTitle.textContent = 'Confirmar Acción';
        notificationMessage.textContent = message;
        notificationOkBtn.textContent = 'Confirmar';
        notificationCancelBtn.style.display = 'inline-block';
        notificationOkBtn.className = 'cta-button danger';
        notificationModal.style.display = 'block';
        const close = () => notificationModal.style.display = 'none';
        notificationCancelBtn.onclick = close;
        notificationModal.querySelector('.close-btn').onclick = close;
        notificationOkBtn.onclick = () => { close(); onConfirm(); };
    }

    // Fetch usuarios
    function fetchData() {
        toggleLoader(true);
        fetch('ajax_handler.php?action=get_users')
        .then(res => res.json())
        .then(data => {
            if(data.success){
                allUsers = data.users;
                allRoles = data.roles;
                allZonas = data.zonas;
                populateSelects();
                applyFilters();
            } else {
                showNotification(data.error || 'No se pudieron cargar los datos.', true);
            }
        })
        .catch(() => showNotification('Error de conexión con el servidor.', true))
        .finally(() => toggleLoader(false));
    }

    // Selects
    function populateSelects() {
        const roleFilter = document.getElementById('roleFilter');
        const roleSelect = document.getElementById('role_id');
        const zonaSelect = document.getElementById('zona_id');

        roleFilter.innerHTML = '<option value="all">Todos los Roles</option>';
        roleSelect.innerHTML = '';
        zonaSelect.innerHTML = '<option value="">Ninguna</option>';

        for (const id in allRoles){
            const name = allRoles[id].charAt(0).toUpperCase() + allRoles[id].slice(1);
            roleFilter.innerHTML += `<option value="${id}">${name}</option>`;
            roleSelect.innerHTML += `<option value="${id}">${name}</option>`;
        }

        for (const id in allZonas){
            zonaSelect.innerHTML += `<option value="${id}">${allZonas[id]}</option>`;
        }
    }

    // Render usuarios
    function renderUserGrid(users){
        usersGrid.innerHTML = '';
        if(users.length === 0){
            usersGrid.innerHTML = '<p>No se encontraron usuarios que coincidan con los filtros.</p>';
            return;
        }

        users.forEach(user => {
            const statusText = user.approved == 1 ? 'Aprobado' : 'Pendiente';
            const statusClass = user.approved == 1 ? 'status-aprobado' : 'status-pendiente';
            const roleName = user.role_name.toLowerCase();
            const approveIcon = user.approved == 1 ? 'fa-times-circle' : 'fa-check';
            const approveTitle = user.approved == 1 ? 'Desaprobar' : 'Aprobar';

            const userCard = document.createElement('div');
            userCard.className = 'card user-card';
            userCard.dataset.userId = user.id;
            userCard.dataset.name = user.name.toLowerCase();
            userCard.dataset.email = user.email.toLowerCase();
            userCard.dataset.roleId = user.role_id;
            userCard.dataset.status = user.approved;

            userCard.innerHTML = `
                <div class="user-info">
                    <img src="${BASE_URL}assets/images/avatar-default.png" alt="Foto de perfil" class="user-avatar">
                    <div>
                        <h3 class="user-name">${user.name}</h3>
                        <p class="user-email">${user.email}</p>
                        <span class="role-badge role-${roleName}">${roleName}</span>
                    </div>
                </div>
                <div class="user-status">
                    <strong>Estado:</strong> <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
                <div class="user-actions">
                    <button class="action-btn approve-btn" title="${approveTitle}"><i class="fas ${approveIcon}"></i></button>
                    <button class="action-btn edit-btn" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                </div>`;
            usersGrid.appendChild(userCard);
        });
    }

    // Crear Usuario
    createUserBtn.addEventListener('click', () => {
        modalTitle.textContent = 'Añadir Nuevo Usuario';
        userForm.reset();
        document.getElementById('user_id').value = '';
        document.getElementById('password').setAttribute('required','required');
        toggleZonaSelect(document.getElementById('role_id').value);
        userModal.style.display = 'block';
    });

    // Guardar Usuario
    userForm.addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(userForm);
        formData.append('action','save_user');

        fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                userModal.style.display = 'none';
                fetchData();
                showNotification(formData.get('user_id') ? 'Usuario actualizado.' : 'Usuario creado.');
            } else {
                showNotification(data.error,true);
            }
        })
        .catch(()=>showNotification('Error de conexión.', true));
    });

    // Botones en la grid
    usersGrid.addEventListener('click', e => {
        const button = e.target.closest('button.action-btn');
        if(!button) return;
        const card = e.target.closest('.user-card');
        const userId = card.dataset.userId;

        if(button.classList.contains('approve-btn')){
            showConfirmation('¿Deseas cambiar el estado del usuario?', () => {
                const fd = new FormData();
                fd.append('action','toggle_approval');
                fd.append('user_id', userId);
                fetch('ajax_handler.php', { method:'POST', body:fd })
                .then(res=>res.json())
                .then(data => {
                    if(data.success) fetchData();
                    else showNotification(data.error,true);
                });
            });
        }

        if(button.classList.contains('edit-btn')){
            const user = allUsers.find(u=>u.id==userId);
            modalTitle.textContent = 'Editar Usuario';
            userForm.reset();
            document.getElementById('user_id').value = user.id;
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('dni').value = user.dni || '';
            document.getElementById('role_id').value = user.role_id;
            toggleZonaSelect(user.role_id);
            document.getElementById('zona_id').value = user.zona_id || '';
            document.getElementById('password').removeAttribute('required');
            userModal.style.display = 'block';
        }

        if(button.classList.contains('delete-btn')){
            showConfirmation('¿Deseas eliminar este usuario?', () => {
                const fd = new FormData();
                fd.append('action','delete_user');
                fd.append('user_id',userId);
                fetch('ajax_handler.php',{method:'POST',body:fd})
                .then(res=>res.json())
                .then(data=>{
                    if(data.success) fetchData();
                    else showNotification(data.error,true);
                });
            });
        }
    });

    // Modal cerrar
    document.querySelectorAll('.modal .close-btn').forEach(btn => btn.onclick = () => btn.closest('.modal').style.display='none');
    document.querySelectorAll('.cancel-btn').forEach(btn => btn.onclick = () => btn.closest('.modal').style.display='none');

    // Filtros
    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('roleFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);

    function applyFilters(){
        const search = document.getElementById('searchInput').value.toLowerCase();
        const roleFilter = document.getElementById('roleFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;

        let filtered = allUsers.filter(u=>{
            const matchSearch = u.name.toLowerCase().includes(search) || u.email.toLowerCase().includes(search);
            const matchRole = roleFilter==='all' || u.role_id==roleFilter;
            const matchStatus = statusFilter==='all' || u.approved==statusFilter;
            return matchSearch && matchRole && matchStatus;
        });

        renderUserGrid(filtered);
    }

    // Mostrar/ocultar select zona según rol
    document.getElementById('role_id').addEventListener('change', e => toggleZonaSelect(e.target.value));
    function toggleZonaSelect(roleId){
        const zonaGroup = document.getElementById('zona-group');
        if(roleId==2){ // Por ejemplo, rol ID 2 requiere zona
            zonaGroup.style.display = 'block';
        } else {
            zonaGroup.style.display = 'none';
        }
    }

    fetchData();
});
</script>
