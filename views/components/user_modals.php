<?php
// views/components/user_modals.php
?>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Agregar Nuevo Usuario</h2>
            <span class="close" onclick="closeModal('addUserModal')">&times;</span>
        </div>
        <form action="router.php" method="POST">
            <input type="hidden" name="action" value="add_user">
            <div class="form-group">
                <label for="add_nombre">Nombre</label>
                <input type="text" id="add_nombre" name="nombre" required placeholder="Ej. Juan">
            </div>
            <div class="form-group">
                <label for="add_apellido">Apellido</label>
                <input type="text" id="add_apellido" name="apellido" required placeholder="Ej. Pérez">
            </div>
            <div class="form-group">
                <label for="add_email">Correo Electrónico</label>
                <input type="email" id="add_email" name="email" required placeholder="juan.perez@ejemplo.com">
            </div>
            <div class="form-group">
                <label for="add_password">Contraseña (opcional, defecto: 123456)</label>
                <input type="password" id="add_password" name="password" placeholder="Min. 6 caracteres">
            </div>
            <div class="form-group">
                <label for="add_rol">Rol del Sistema</label>
                <select id="add_rol" name="rol_id" required>
                    <option value="4">Usuario / Vecino</option>
                    <option value="3">Recolector</option>
                    <option value="2">Gestor de Pagos</option>
                    <option value="1">Administrador</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('addUserModal')">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Editar Usuario</h2>
            <span class="close" onclick="closeModal('editUserModal')">&times;</span>
        </div>
        <form action="router.php" method="POST">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" id="edit_user_id" name="user_id">
            <div class="form-group">
                <label for="edit_nombre">Nombre</label>
                <input type="text" id="edit_nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="edit_apellido">Apellido</label>
                <input type="text" id="edit_apellido" name="apellido" required>
            </div>
            <div class="form-group">
                <label for="edit_email">Correo Electrónico</label>
                <input type="email" id="edit_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="edit_password">Nueva Contraseña (dejar vacío para no cambiar)</label>
                <input type="password" id="edit_password" name="password" placeholder="Nueva contraseña">
            </div>
            <div class="form-group">
                <label for="edit_rol">Rol del Sistema</label>
                <select id="edit_rol" name="rol_id" required>
                    <option value="4">Usuario / Vecino</option>
                    <option value="3">Recolector</option>
                    <option value="2">Gestor de Pagos</option>
                    <option value="1">Administrador</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('editUserModal')">Cancelar</button>
                <button type="submit" class="btn-primary">Actualizar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- View Details Modal -->
<div id="viewDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Detalles del Usuario</h2>
            <span class="close" onclick="closeModal('viewDetailsModal')">&times;</span>
        </div>
        <div class="details-body" id="detailsContent">
            <!-- Cargado dinámicamente con JS -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="closeModal('viewDetailsModal')">Cerrar</button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteUserModal" class="modal">
    <div class="modal-content modal-sm">
        <div class="modal-header">
            <h2 style="color: #DC2626;">¿Eliminar Usuario?</h2>
            <span class="close" onclick="closeModal('deleteUserModal')">&times;</span>
        </div>
        <div class="modal-body">
            <p>Esta acción no se puede deshacer. El usuario dejará de tener acceso al sistema.</p>
            <p id="deleteUserName" style="font-weight: bold; margin-top: 10px;"></p>
        </div>
        <form action="router.php" method="POST">
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" id="delete_user_id" name="user_id">
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('deleteUserModal')">Cancelar</button>
                <button type="submit" class="btn-danger">Eliminar permanentemente</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Modal Base Styles */
.modal {
    display: none; 
    position: fixed; 
    z-index: 1000; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto; 
    background-color: rgba(0,0,0,0.4); 
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto; 
    padding: 0;
    border-radius: 12px;
    width: 500px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    animation: slideIn 0.3s;
    overflow: hidden;
}

.modal-sm {
    width: 400px;
}

@keyframes slideIn {
    from { transform: translateY(-30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    padding: 20px 25px;
    border-bottom: 1px solid #E5E7EB;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 600;
}

.close {
    color: #9CA3AF;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.2s;
}

.close:hover {
    color: #1F2937;
}

.modal-body, form {
    padding: 25px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    font-size: 14px;
    color: #374151;
}

.form-group input, .form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    font-family: inherit;
    font-size: 15px;
    transition: 0.2s;
    box-sizing: border-box;
}

.form-group input:focus, .form-group select:focus {
    outline: none;
    border-color: #10B981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.modal-footer {
    padding: 15px 25px;
    background: #F9FAFB;
    border-top: 1px solid #E5E7EB;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn-primary {
    background: #10B981;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.btn-primary:hover {
    background: #059669;
}

.btn-secondary {
    background: #E5E7EB;
    color: #4B5563;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.btn-secondary:hover {
    background: #D1D5DB;
}

.btn-danger {
    background: #DC2626;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.btn-danger:hover {
    background: #B91C1C;
}

.details-row {
    display: flex;
    margin-bottom: 12px;
    border-bottom: 1px solid #F3F4F6;
    padding-bottom: 8px;
}

.details-label {
    width: 120px;
    font-weight: 600;
    color: #6B7280;
    font-size: 14px;
}

.details-value {
    flex: 1;
    color: #1F2937;
    font-size: 14px;
}
</style>

<script>
function openModal(id) {
    document.getElementById(id).style.display = "block";
}

function closeModal(id) {
    document.getElementById(id).style.display = "none";
}

// Close when clicking outside
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = "none";
    }
}

function openEditModal(user) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_nombre').value = user.nombre;
    document.getElementById('edit_apellido').value = user.apellido;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_rol').value = user.rol_id;
    openModal('editUserModal');
}

function openViewModal(user) {
    const content = `
        <div class="details-row"><span class="details-label">ID:</span><span class="details-value">#${user.id}</span></div>
        <div class="details-row"><span class="details-label">Nombre:</span><span class="details-value">${user.nombre}</span></div>
        <div class="details-row"><span class="details-label">Apellido:</span><span class="details-value">${user.apellido}</span></div>
        <div class="details-row"><span class="details-label">Email:</span><span class="details-value">${user.email}</span></div>
        <div class="details-row"><span class="details-label">Rol:</span><span class="details-value">${user.rol_nombre}</span></div>
        <div class="details-row"><span class="details-label">Registrado:</span><span class="details-value">${user.creado_en}</span></div>
    `;
    document.getElementById('detailsContent').innerHTML = content;
    openModal('viewDetailsModal');
}

function openDeleteModal(id, name) {
    document.getElementById('delete_user_id').value = id;
    document.getElementById('deleteUserName').innerText = "Usuario: " + name;
    openModal('deleteUserModal');
}
</script>
