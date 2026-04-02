<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= $title ?></h1>
            <p class="text-gray-600">Registro de actividades y eventos del sistema</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
        <form method="GET" action="<?= BASE_URL ?>admin/logs" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Log</label>
                <select name="tipo_log" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todos</option>
                    <option value="accion" <?= ($filters['tipo_log'] ?? '') === 'accion' ? 'selected' : '' ?>>Acción</option>
                    <option value="login" <?= ($filters['tipo_log'] ?? '') === 'login' ? 'selected' : '' ?>>Login</option>
                    <option value="error" <?= ($filters['tipo_log'] ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Módulo</label>
                <select name="modulo" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todos</option>
                    <?php foreach ($modules as $module): ?>
                        <option value="<?= $module ?>" <?= ($filters['modulo'] ?? '') === $module ? 'selected' : '' ?>><?= ucfirst($module) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                <select name="usuario_id" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    <option value="">Todos</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>" <?= ($filters['usuario_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nombre_completo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="<?= $filters['fecha_desde'] ?? '' ?>" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="<?= $filters['fecha_hasta'] ?? '' ?>" class="w-full rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition duration-150 flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo/Módulo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP/Vía</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No se encontraron logs con los filtros seleccionados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    <?= date('d/m/Y H:i:s', strtotime($l['fecha'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-bold text-gray-600">
                                            <?= strtoupper(substr($l['username'] ?? 'S', 0, 1)) ?>
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <p class="text-gray-900 font-medium"><?= htmlspecialchars($l['nombre_completo'] ?? 'Sistema') ?></p>
                                            <p class="text-gray-500"><?= htmlspecialchars($l['username'] ?? 'system') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php 
                                        $badgeClass = 'bg-gray-100 text-gray-800';
                                        if ($l['tipo_log'] === 'error') $badgeClass = 'bg-red-100 text-red-800';
                                        if ($l['tipo_log'] === 'login') $badgeClass = 'bg-blue-100 text-blue-800';
                                        if ($l['tipo_log'] === 'accion') $badgeClass = 'bg-green-100 text-green-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>">
                                        <?= strtoupper($l['tipo_log']) ?>
                                    </span>
                                    <span class="block text-gray-500 text-xs mt-1"><?= ucfirst($l['modulo']) ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= htmlspecialchars($l['accion']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="<?= htmlspecialchars($l['detalles'] ?? '') ?>">
                                    <?= htmlspecialchars($l['detalles'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    <p class="font-medium"><?= $l['ip_address'] ?? 'N/A' ?></p>
                                    <p class="truncate max-w-[150px]"><?= $l['user_agent'] ?? 'N/A' ?></p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['pages'] > 1): ?>
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <?php if ($pagination['page'] > 1): ?>
                    <a href="?page=<?= $pagination['page'] - 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Anterior</a>
                <?php endif; ?>
                <?php if ($pagination['page'] < $pagination['pages']): ?>
                    <a href="?page=<?= $pagination['page'] + 1 ?>&<?= http_build_query(array_filter($filters)) ?>" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Siguiente</a>
                <?php endif; ?>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando <span class="font-medium"><?= ($pagination['page'] - 1) * $pagination['limit'] + 1 ?></span> a <span class="font-medium"><?= min($pagination['page'] * $pagination['limit'], $pagination['total']) ?></span> de <span class="font-medium"><?= $pagination['total'] ?></span> resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                            <a href="?page=<?= $i ?>&<?= http_build_query(array_filter($filters)) ?>" class="relative inline-flex items-center px-4 py-2 border <?= $i === $pagination['page'] ? 'bg-primary-50 border-primary-500 text-primary-600 z-10' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' ?> text-sm font-medium">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
