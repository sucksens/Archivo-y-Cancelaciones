<!-- Filtros -->
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>usuarios" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                       placeholder="Buscar por nombre, usuario o email..."
                       class="form-input">
            </div>
            
            <div class="w-40">
                <select name="empresa" class="form-select">
                    <option value="">Todas las empresas</option>
                    <?php foreach ($empresas as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['empresa'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="w-32">
                <select name="activo" class="form-select">
                    <option value="">Todos</option>
                    <option value="1" <?= ($filters['activo'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= ($filters['activo'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
                <a href="<?= BASE_URL ?>usuarios" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de usuarios -->
<div class="card">
    <div class="card-header flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">Usuarios</h3>
            <p class="text-sm text-gray-500"><?= $pagination['total'] ?> usuario(s)</p>
        </div>
        
        <a href="<?= BASE_URL ?>usuarios/crear" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Usuario
        </a>
    </div>
    
    <div class="overflow-x-auto">
        <?php if (empty($users)): ?>
        <div class="p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No se encontraron usuarios</h3>
        </div>
        <?php else: ?>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Login</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($users as $u): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                                <span class="text-primary-700 font-semibold">
                                    <?= strtoupper(substr($u['nombre_completo'], 0, 1)) ?>
                                </span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($u['nombre_completo']) ?></div>
                                <div class="text-xs text-gray-500">@<?= htmlspecialchars($u['username']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <?= htmlspecialchars($u['email']) ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <?= $empresas[$u['empresa']] ?? $u['empresa'] ?>
                    </td>
                    <td class="px-6 py-4">
                        <?php if ($u['activo']): ?>
                        <span class="badge badge-green">Activo</span>
                        <?php else: ?>
                        <span class="badge badge-gray">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        <?= $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : 'Nunca' ?>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="<?= BASE_URL ?>usuarios/<?= $u['id'] ?>/editar" 
                           class="text-primary-600 hover:text-primary-700 font-medium text-sm">
                            Editar
                        </a>
                        
                        <?php if ($u['id'] !== $user['id']): ?>
                        <form action="<?= BASE_URL ?>usuarios/<?= $u['id'] ?>/toggle" method="POST" class="inline">
                            <?= \App\Helpers\AuthHelper::getCsrfField() ?>
                            <button type="submit" class="text-gray-500 hover:text-gray-700 font-medium text-sm">
                                <?= $u['activo'] ? 'Desactivar' : 'Activar' ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    
    <!-- Paginación -->
    <?php if ($pagination['pages'] > 1): ?>
    <div class="card-body border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Página <?= $pagination['page'] ?> de <?= $pagination['pages'] ?>
            </div>
            
            <nav class="flex space-x-2">
                <?php if ($pagination['page'] > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page'] - 1])) ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Anterior
                </a>
                <?php endif; ?>
                
                <?php if ($pagination['page'] < $pagination['pages']): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $pagination['page'] + 1])) ?>" 
                   class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Siguiente
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>
