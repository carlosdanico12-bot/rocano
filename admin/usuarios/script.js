document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const userModal = document.getElementById('userModal');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('modalTitle');
    const createUserBtn = document.getElementById('createUserBtn');
    const usersGrid = document.getElementById('usersGrid');
    const loader = document.getElementById('loader');
    
    // Elementos del Modal de Notificación/Confirmación
    const notificationModal = document.getElementById('notificationModal');
    const notificationTitle = document.getElementById('notificationTitle');
    const notificationMessage = document.getElementById('notificationMessage');
    const notificationOkBtn = document.getElementById('notificationOk');
    const notificationCancelBtn = document.getElementById('notificationCancel');

    let allUsers = [];
    let allRoles = {};
    let allZonas = {};

    // --- FUNCIONES DE UTILIDAD (MODALES Y CARGADOR) ---
    function toggleLoader(show) {
        loader.style.display = show ? 'block' : 'none';
        usersGrid.style.display = show ? 'none' : 'grid';
    }

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


    // --- LÓGICA DE DATOS (FETCH) ---
    function fetchData() {
        toggleLoader(true);
        fetch('ajax_handler.php?action=get_users')


            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allUsers = data.users;
                    allRoles = data.roles;
                    allZonas = data.zonas;
                    populateSelects();
                    applyFilters();
                } else {
                    showNotification(data.error || 'No se pudieron cargar los datos.', true);
                }
            })
            .catch(error => {
                showNotification('Error de conexión con el servidor.', true);
                console.error('Fetch Error:', error);
            })
            .finally(() => toggleLoader(false));
    }
    
    // --- RENDERIZADO Y MANIPULACIÓN DEL DOM ---
    function populateSelects() {
        const roleFilter = document.getElementById('roleFilter');
        const roleSelect = document.getElementById('role_id');
        const zonaSelect = document.getElementById('zona_id');
        
        roleFilter.innerHTML = '<option value="all">Todos los Roles</option>';
        roleSelect.innerHTML = '';
        zonaSelect.innerHTML = '<option value="">Ninguna</option>';
        
        for (const id in allRoles) {
            const name = allRoles[id].charAt(0).toUpperCase() + allRoles[id].slice(1);
            roleFilter.innerHTML += `<option value="${id}">${name}</option>`;
            roleSelect.innerHTML += `<option value="${id}">${name}</option>`;
        }
        for (const id in allZonas) {
            zonaSelect.innerHTML += `<option value="${id}">${allZonas[id]}</option>`;
        }
    }

    function renderUserGrid(users) {
        usersGrid.innerHTML = '';
        if (users.length === 0) {
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
                    <strong>Estado:</strong>
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </div>
                <div class="user-actions">
                    <button class="action-btn approve-btn" title="${approveTitle}"><i class="fas ${approveIcon}"></i></button>
                    <button class="action-btn edit-btn" title="Editar"><i class="fas fa-edit"></i></button>
                    <button class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                </div>`;
            usersGrid.appendChild(userCard);
        });
    }

    // --- MANEJO DE EVENTOS ---
    createUserBtn.addEventListener('click', () => {
        modalTitle.textContent = 'Añadir Nuevo Usuario';
        userForm.reset();
        document.getElementById('user_id').value = '';
        document.getElementById('password').setAttribute('required', 'required');
        toggleZonaSelect(document.getElementById('role_id').value);
        userModal.style.display = 'block';
    });

    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(userForm);
        formData.append('action', 'save_user');

        fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                userModal.style.display = 'none';
                fetchData();
                showNotification(formData.get('user_id') ? 'Usuario actualizado.' : 'Usuario creado.');
            } else {
                showNotification(data.error, true);
            }
        }).catch(err => showNotification('Error de conexión.', true));
    });
    
    usersGrid.addEventListener('click', e => {
        const button = e.target.closest('button.action-btn');
        if (!button) return;
        
        const card = e.target.closest('.user-card');
        const userId = card.dataset.userId;

        if(button.classList.contains('edit-btn')) {
            openEditModal(userId);
        } else if (button.classList.contains('delete-btn')) {
            const userName = card.querySelector('.user-name').textContent;
            showConfirmation(`¿Seguro que quieres eliminar a "${userName}"?`, () => {
                handleUserAction('delete_user', userId);
            });
        } else if (button.classList.contains('approve-btn')) {
            handleUserAction('toggle_approval', userId);
        }
    });

    function handleUserAction(action, userId) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('user_id', userId);

        fetch('index.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                fetchData(); // Recargar datos para reflejar el cambio
            } else {
                showNotification(data.error, true);
            }
        }).catch(err => showNotification('Error de conexión.', true));
    }

    function openEditModal(userId) {
        const user = allUsers.find(u => u.id == userId);
        if (user) {
            modalTitle.textContent = 'Editar Usuario';
            userForm.reset();
            document.getElementById('password').removeAttribute('required');
            document.getElementById('user_id').value = user.id;
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('dni').value = user.dni || '';
            document.getElementById('role_id').value = user.role_id;
            document.getElementById('zona_id').value = user.zona_id || '';
            toggleZonaSelect(user.role_id);
            userModal.style.display = 'block';
        }
    }
    
    document.getElementById('role_id').addEventListener('change', (e) => toggleZonaSelect(e.target.value));

    function toggleZonaSelect(roleId) {
        const zonaGroup = document.getElementById('zona-group');
        const roleName = (allRoles[roleId] || '').toLowerCase();
        const isVisible = roleName === 'coordinador' || roleName === 'voluntario';
        zonaGroup.style.display = isVisible ? 'block' : 'none';
        if (!isVisible) {
            document.getElementById('zona_id').value = '';
        }
    }
    
    // FILTROS
    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('roleFilter').addEventListener('change', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);

    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const roleFilter = document.getElementById('roleFilter').value;
        const statusFilter = document.getElementById('statusFilter').value;

        const filteredUsers = allUsers.filter(user => {
            const nameMatch = user.name.toLowerCase().includes(searchTerm);
            const emailMatch = user.email.toLowerCase().includes(searchTerm);
            const roleMatch = (roleFilter === 'all' || !roleFilter) || user.role_id == roleFilter;
            const statusMatch = (statusFilter === 'all' || statusFilter === '') || user.approved == statusFilter;
            return (nameMatch || emailMatch) && roleMatch && statusMatch;
        });
        renderUserGrid(filteredUsers);
    }
    
    // Cerrar modales
    [userModal, notificationModal].forEach(modal => {
        modal.querySelector('.close-btn').onclick = () => modal.style.display = 'none';
        modal.querySelector('.cancel-btn').onclick = () => modal.style.display = 'none';
    });
    window.onclick = (e) => {
        if (e.target == userModal || e.target == notificationModal) {
            e.target.style.display = 'none';
        }
    };

    // Carga inicial
    fetchData();
});

