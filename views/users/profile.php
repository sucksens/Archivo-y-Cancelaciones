<div class="max-w-2xl mx-auto">
    <div class="card">
        <div class="card-header flex items-center space-x-4">
            <div class="w-16 h-16 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-2xl">
                    <?= strtoupper(substr($userData['nombre_completo'], 0, 1)) ?>
                </span>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900"><?= htmlspecialchars($userData['nombre_completo']) ?></h2>
                <p class="text-sm text-gray-500">@<?= htmlspecialchars($userData['username']) ?></p>
            </div>
        </div>
        
        <form action="<?= BASE_URL ?>perfil" method="POST" class="card-body space-y-6">
            <?= \App\Helpers\AuthHelper::getCsrfField() ?>
            
            <!-- Información Personal -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información Personal</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="nombre_completo" class="form-label">Nombre Completo <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre_completo" id="nombre_completo" class="form-input" 
                               value="<?= htmlspecialchars($userData['nombre_completo']) ?>"
                               required>
                    </div>
                    
                    <div>
                        <label for="email" class="form-label">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" class="form-input" 
                               value="<?= htmlspecialchars($userData['email']) ?>"
                               required>
                    </div>
                    
                    <div>
                        <label for="departamento" class="form-label">Departamento</label>
                        <input type="text" name="departamento" id="departamento" class="form-input" 
                               value="<?= htmlspecialchars($userData['departamento'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <!-- Cambiar Contraseña -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Cambiar Contraseña</h3>
                <p class="text-sm text-gray-500 mb-4">Deja vacío si no deseas cambiar tu contraseña</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                        <input type="password" name="new_password" id="new_password" class="form-input" 
                               minlength="8" placeholder="Mínimo 8 caracteres">
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-input" 
                               placeholder="Repetir contraseña">
                    </div>
                </div>
            </div>
            
            <!-- Información de Cuenta (solo lectura) -->
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-4">Información de Cuenta</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Usuario:</span>
                        <span class="text-gray-900 ml-2 font-mono">@<?= htmlspecialchars($userData['username']) ?></span>
                    </div>
                    
                    <div>
                        <span class="text-gray-500">Empresa:</span>
                        <span class="text-gray-900 ml-2"><?= EMPRESAS[$userData['empresa']] ?? $userData['empresa'] ?></span>
                    </div>
                    
                    <div>
                        <span class="text-gray-500">Roles:</span>
                        <span class="text-gray-900 ml-2">
                            <?php 
                            $roleNames = array_column($roles, 'nombre');
                            echo htmlspecialchars(implode(', ', $roleNames) ?: 'Sin roles');
                            ?>
                        </span>
                    </div>
                    
                    <div>
                        <span class="text-gray-500">Último login:</span>
                        <span class="text-gray-900 ml-2">
                            <?= $userData['ultimo_login'] ? date('d/m/Y H:i', strtotime($userData['ultimo_login'])) : 'Ahora' ?>
                        </span>
                    </div>
                    
                    <div>
                        <span class="text-gray-500">Cuenta creada:</span>
                        <span class="text-gray-900 ml-2"><?= date('d/m/Y', strtotime($userData['fecha_creacion'])) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= BASE_URL ?>dashboard" class="btn btn-secondary">Cancelar</a>
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
