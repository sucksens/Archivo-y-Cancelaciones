<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Lista de Roles -->
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Roles del Sistema</h3>
                <button type="button" class="btn btn-primary" id="btnNewRole">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nuevo Rol
                </button>
            </div>
            
            <div class="divide-y divide-gray-200">
                <?php foreach ($roles as $role): ?>
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center
                                <?php if ($role['nivel'] >= 100): ?>
                                bg-purple-100 text-purple-700
                                <?php elseif ($role['nivel'] >= 50): ?>
                                bg-blue-100 text-blue-700
                                <?php else: ?>
                                bg-gray-100 text-gray-700
                                <?php endif; ?>
                            ">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($role['nombre']) ?></h4>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($role['descripcion'] ?? 'Sin descripción') ?></p>
                                <p class="text-xs text-gray-400 mt-1">
                                    <?= $role['user_count'] ?> usuario(s) · Nivel <?= $role['nivel'] ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <?php if ($role['nivel'] < 100): ?>
                            <button type="button" class="btn btn-secondary btn-edit-role" 
                                    data-id="<?= $role['id'] ?>"
                                    data-nombre="<?= htmlspecialchars($role['nombre']) ?>"
                                    data-descripcion="<?= htmlspecialchars($role['descripcion'] ?? '') ?>"
                                    data-nivel="<?= $role['nivel'] ?>">
                                Editar
                            </button>
                            
                            <?php if ($role['user_count'] == 0): ?>
                            <form action="<?= BASE_URL ?>admin/roles/<?= $role['id'] ?>/eliminar" method="POST" 
                                  onsubmit="return confirm('¿Eliminar este rol?')">
                                <?= \App\Helpers\AuthHelper::getCsrfField() ?>
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                            <?php endif; ?>
                            <?php else: ?>
                            <span class="badge badge-gray">Sistema</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Panel de Ayuda -->
    <div class="space-y-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Niveles de Rol</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <span class="text-purple-700 text-xs font-bold">100</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Administrador</p>
                        <p class="text-xs text-gray-500">Acceso completo</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-700 text-xs font-bold">50</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Supervisor</p>
                        <p class="text-xs text-gray-500">Gestión de tickets</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                        <span class="text-gray-700 text-xs font-bold">10</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">Usuario</p>
                        <p class="text-xs text-gray-500">Crear tickets propios</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Información</h3>
            </div>
            <div class="card-body">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-600">
                        Los roles con nivel 100 son del sistema y no pueden ser editados ni eliminados.
                        Un rol no puede ser eliminado si tiene usuarios asignados.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

 <!-- Modal para Nuevo/Editar Rol -->
 <div id="roleModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
     <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
         <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onclick="hideRoleModal()"></div>
         
         <div class="relative inline-block w-full max-w-2xl p-6 overflow-hidden text-left bg-white rounded-xl shadow-xl max-h-[90vh] overflow-y-auto">
            <form id="roleForm" method="POST">
                <?= \App\Helpers\AuthHelper::getCsrfField() ?>
                
                <h3 id="roleModalTitle" class="text-lg font-semibold text-gray-900 mb-4">Nuevo Rol</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="roleName" class="form-label">Nombre del Rol <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="roleName" class="form-input" required maxlength="50">
                    </div>
                    
                    <div>
                        <label for="roleDesc" class="form-label">Descripción</label>
                        <textarea name="descripcion" id="roleDesc" class="form-input resize-none" rows="2"></textarea>
                    </div>
                    
                    <div>
                        <label for="roleNivel" class="form-label">Nivel</label>
                        <input type="number" name="nivel" id="roleNivel" class="form-input" min="0" max="99" value="10">
                        <p class="text-xs text-gray-500 mt-1">Mayor nivel = más privilegios (máx 99)</p>
                    </div>
                </div>
                
                <!-- Sección de Permisos -->
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-medium text-gray-900">Permisos del Rol</h4>
                        <div class="flex space-x-2">
                            <button type="button" id="selectAllPerms" class="text-xs text-blue-600 hover:text-blue-800">Seleccionar Todo</button>
                            <span class="text-gray-300">|</span>
                            <button type="button" id="deselectAllPerms" class="text-xs text-gray-600 hover:text-gray-800">Deseleccionar</button>
                        </div>
                    </div>
                    
                    <div id="permissionsContainer" class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="flex items-center justify-center py-8">
                            <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="ml-2 text-sm text-gray-500">Cargando permisos...</span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="currentRole" id="currentRole">
                </div>
                
                <div class="flex justify-end space-x-4 mt-6 pt-4 border-t border-gray-200">
                    <button type="button" class="btn btn-secondary" onclick="hideRoleModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const roleModal = document.getElementById('roleModal');
const roleForm = document.getElementById('roleForm');
const modalTitle = document.getElementById('roleModalTitle');

 document.getElementById('btnNewRole').addEventListener('click', () => {
     roleForm.action = '<?= BASE_URL ?>admin/roles';
     modalTitle.textContent = 'Nuevo Rol';
     roleForm.reset();
     document.getElementById('currentRole').value = '';
     loadPermissions(null);
     roleModal.classList.remove('hidden');
 });
 
 document.querySelectorAll('.btn-edit-role').forEach(btn => {
     btn.addEventListener('click', function() {
         const id = this.dataset.id;
         roleForm.action = '<?= BASE_URL ?>admin/roles/' + id;
         modalTitle.textContent = 'Editar Rol';
         document.getElementById('roleName').value = this.dataset.nombre;
         document.getElementById('roleDesc').value = this.dataset.descripcion;
         document.getElementById('roleNivel').value = this.dataset.nivel;
         document.getElementById('currentRole').value = id;
         loadPermissions(id);
         roleModal.classList.remove('hidden');
     });
 });

 function hideRoleModal() {
     roleModal.classList.add('hidden');
 }
 
 async function loadPermissions(roleId = null) {
     const container = document.getElementById('permissionsContainer');
     
     try {
         container.innerHTML = `
             <div class="flex items-center justify-center py-8">
                 <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24">
                     <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                     <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                 </svg>
                 <span class="ml-2 text-sm text-gray-500">Cargando permisos...</span>
             </div>
         `;
         
         const permissionsResponse = await fetch('<?= BASE_URL ?>admin/permisos', {
             headers: {
                 'X-Requested-With': 'XMLHttpRequest'
             }
         });
         const permissionsData = await permissionsResponse.json();
         
         let rolePermissions = [];
         if (roleId) {
             const rolePermsResponse = await fetch(`<?= BASE_URL ?>admin/roles/${roleId}/permisos`, {
                 headers: {
                     'X-Requested-With': 'XMLHttpRequest'
                 }
             });
             rolePermissions = await rolePermsResponse.json();
         }
         
         renderPermissions(permissionsData, rolePermissions);
         
     } catch (error) {
         container.innerHTML = `
             <div class="flex items-center justify-center py-8 text-red-500">
                 <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                 </svg>
                 <span class="text-sm">Error al cargar permisos</span>
             </div>
         `;
     }
 }
 
 function renderPermissions(permissionsData, rolePermissions = []) {
     const container = document.getElementById('permissionsContainer');
     
     if (!permissionsData || Object.keys(permissionsData).length === 0) {
         container.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No hay permisos disponibles</p>';
         return;
     }
     
     let html = '';
     
     for (const [module, permissions] of Object.entries(permissionsData)) {
         html += `<div class="mb-4">`;
         html += `<h5 class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">${module}</h5>`;
         html += `<div class="grid grid-cols-1 md:grid-cols-2 gap-2">`;
         
         permissions.forEach(perm => {
             const isChecked = rolePermissions.includes(perm.id) ? 'checked' : '';
             html += `
                 <label class="flex items-center p-2 rounded hover:bg-gray-100 cursor-pointer transition-colors">
                     <input type="checkbox" name="permissions[]" value="${perm.id}" ${isChecked} 
                            class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 permission-checkbox">
                     <span class="ml-2 text-sm text-gray-700 flex-1">${perm.nombre}</span>
                     ${perm.descripcion ? `<span class="text-xs text-gray-500 ml-2 hidden md:inline">${perm.descripcion}</span>` : ''}
                 </label>
             `;
         });
         
         html += `</div></div>`;
     }
     
     container.innerHTML = html;
 }
 
 document.getElementById('selectAllPerms').addEventListener('click', function() {
     document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
 });
 
 document.getElementById('deselectAllPerms').addEventListener('click', function() {
     document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
 });

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') hideRoleModal();
});
</script>
