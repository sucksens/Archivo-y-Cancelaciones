<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Editar Usuario</h2>
            <p class="text-sm text-gray-500 mt-1">Modificar datos de <?= htmlspecialchars($userData['nombre_completo']) ?></p>
        </div>
        
        <form action="<?= BASE_URL ?>usuarios/<?= $userData['id'] ?>" method="POST" class="card-body space-y-6">
            <?= \App\Helpers\AuthHelper::getCsrfField() ?>
            
            <!-- Datos de Acceso -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Datos de Acceso</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="form-label">Usuario <span class="text-red-500">*</span></label>
                        <input type="text" name="username" id="username" class="form-input" 
                               value="<?= htmlspecialchars($userData['username']) ?>"
                               minlength="3" maxlength="50" required>
                    </div>
                    
                    <div>
                        <label for="email" class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" class="form-input" 
                               value="<?= htmlspecialchars($userData['email']) ?>"
                               required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label for="password" class="form-label">Nueva Contraseña</label>
                        <input type="password" name="password" id="password" class="form-input" 
                               minlength="8" placeholder="Dejar vacío para mantener la actual">
                        <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres. Solo si desea cambiarla.</p>
                    </div>
                </div>
            </div>
            
            <!-- Datos Personales -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Datos Personales</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="nombre_completo" class="form-label">Nombre Completo <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre_completo" id="nombre_completo" class="form-input" 
                               value="<?= htmlspecialchars($userData['nombre_completo']) ?>"
                               required>
                    </div>
                    
                    <div>
                        <label for="empresa" class="form-label">Empresa <span class="text-red-500">*</span></label>
                        <select name="empresa" id="empresa" class="form-select" required>
                            <?php foreach ($empresas as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $userData['empresa'] === $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="departamento" class="form-label">Departamento</label>
                        <input type="text" name="departamento" id="departamento" class="form-input" 
                               value="<?= htmlspecialchars($userData['departamento'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <!-- Roles -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Roles</h3>
                
                <div class="space-y-3">
                    <?php foreach ($roles as $role): ?>
                    <label class="flex items-center cursor-pointer p-3 rounded-lg border border-gray-200 hover:bg-gray-50">
                        <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>" 
                               <?= in_array($role['id'], $userRoleIds) ? 'checked' : '' ?>
                               class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500">
                        <div class="ml-3">
                            <span class="text-sm font-medium text-gray-900"><?= htmlspecialchars($role['nombre']) ?></span>
                            <?php if ($role['descripcion']): ?>
                            <p class="text-xs text-gray-500"><?= htmlspecialchars($role['descripcion']) ?></p>
                            <?php endif; ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Estado -->
            <div>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="activo" value="1" <?= $userData['activo'] ? 'checked' : '' ?>
                           class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500">
                    <span class="ml-3 text-sm font-medium text-gray-900">Usuario Activo</span>
                </label>
            </div>
            
            <!-- Información adicional -->
            <div class="p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Creado:</span>
                        <span class="text-gray-900 ml-2"><?= date('d/m/Y H:i', strtotime($userData['fecha_creacion'])) ?></span>
                    </div>
                    <div>
                        <span class="text-gray-500">Último login:</span>
                        <span class="text-gray-900 ml-2">
                            <?= $userData['ultimo_login'] ? date('d/m/Y H:i', strtotime($userData['ultimo_login'])) : 'Nunca' ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= BASE_URL ?>usuarios" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
