<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900">Solicitudes</h2>
        <p class="text-sm text-gray-500">Tickets de cancelación de tu empresa</p>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-6">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>solicitudes" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                       placeholder="Buscar por cliente, RFC, UUID o folio..."
                       class="form-input">
            </div>
            
            <div class="w-40">
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <?php foreach ($estados as $key => $info): ?>
                    <option value="<?= $key ?>" <?= ($filters['estado'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= $info['label'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="w-40">
                <select name="tipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($tipos as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['tipo_cancelacion'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if (!($isConsultaWithEspecialidad ?? false)): ?>
            <div class="w-40">
                <select name="tipo_factura" class="form-select">
                    <option value="">Todas las facturas</option>
                    <?php foreach ($tipos_auto as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($filters['tipo_factura'] ?? '') === $key ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
            <!-- Filtro deshabilitado para rol Consulta con especialidad -->
            <div class="w-40">
                <select name="tipo_factura" class="form-select bg-gray-100 cursor-not-allowed" disabled>
                    <option value="<?= $filters['tipo_factura'] ?? '' ?>" selected>
                        <?= htmlspecialchars($userEspecialidadLabel) ?>
                    </option>
                </select>
            </div>
            <?php endif; ?>

            <div class="w-40">
                <input type="date" name="fecha_desde" value="<?= htmlspecialchars($filters['fecha_desde'] ?? '') ?>" 
                       placeholder="Desde" class="form-input">
            </div>

            <div class="w-40">
                <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($filters['fecha_hasta'] ?? '') ?>" 
                       placeholder="Hasta" class="form-input">
            </div>
            
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Buscar
                </button>
                <a href="<?= BASE_URL ?>solicitudes" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de tickets -->
<div class="card">
    <?php if (empty($tickets)): ?>
    <div class="p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay solicitudes</h3>
        <p class="text-gray-500">No se encontraron tickets de cancelación para tu empresa</p>
    </div>
    <?php else: ?>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Factura</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo Cancelación</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo Factura</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado por</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($tickets as $ticket): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-900">#<?= $ticket['id'] ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($ticket['nombre_cliente']) ?>
                        </div>
                        <div class="text-xs text-gray-500">
                            RFC: <?= htmlspecialchars($ticket['rfc_receptor']) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            <?= htmlspecialchars($ticket['serie']) ?>-<?= htmlspecialchars($ticket['folio']) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            <?= htmlspecialchars($tipos[$ticket['tipo_cancelacion']]) ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-700">
                            <?= $tipos_auto[$ticket['tipo_factura']] ?? $ticket['tipo_factura'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php $estadoInfo = $estados[$ticket['estado']] ?? ['label' => $ticket['estado'], 'color' => 'gray']; ?>
                        <span class="badge badge-<?= $estadoInfo['color'] ?>">
                            <?= $estadoInfo['label'] ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= date('d/m/Y', strtotime($ticket['fecha_creacion'])) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= htmlspecialchars($ticket['usuario_nombre'] ?? 'N/A') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>" 
                           class="text-primary-600 hover:text-primary-700 font-medium text-sm mr-3">
                            Ver detalle →
                        </a>
                        <?php if ($canVerifySat && in_array($ticket['estado'], ['pendiente', 'en_revision'])): ?>
                        <button type="button" 
                                onclick="verifySatStatus(<?= $ticket['id'] ?>)"
                                class="text-orange-600 hover:text-orange-700 font-medium text-sm"
                                title="Verificar status en SAT">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Verificar SAT
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Paginación -->
    <?php if ($pagination['pages'] > 1): ?>
    <div class="card-body border-t border-gray-200">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Mostrando <?= (($pagination['page'] - 1) * $pagination['limit']) + 1 ?> 
                a <?= min($pagination['page'] * $pagination['limit'], $pagination['total']) ?> 
                de <?= $pagination['total'] ?>
            </div>
            
            <nav class="flex space-x-2">
                <?php
                $queryParams = [];
                if (!empty($filters['search'])) $queryParams[] = 'search=' . urlencode($filters['search']);
                if (!empty($filters['estado'])) $queryParams[] = 'estado=' . urlencode($filters['estado']);
                if (!empty($filters['tipo'])) $queryParams[] = 'tipo=' . urlencode($filters['tipo']);
                if (!empty($filters['fecha_desde'])) $queryParams[] = 'fecha_desde=' . urlencode($filters['fecha_desde']);
                if (!empty($filters['fecha_hasta'])) $queryParams[] = 'fecha_hasta=' . urlencode($filters['fecha_hasta']);
                $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
                ?>
                
                <?php if ($pagination['page'] > 1): ?>
                <a href="?page=<?= $pagination['page'] - 1 ?><?= $queryString ?>" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Anterior
                </a>
                <?php endif; ?>
                
                <?php if ($pagination['page'] < $pagination['pages']): ?>
                <a href="?page=<?= $pagination['page'] + 1 ?><?= $queryString ?>" class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Siguiente
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
</div>

<script>
function verifySatStatus(ticketId) {
    if (!confirm('¿Deseas verificar el estatus de esta factura ante el SAT?')) {
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="text-gray-400">Verificando...</span>';
    
    fetch('<?= BASE_URL ?>tickets/' + ticketId + '/validar-sat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let message = 'Estatus SAT: ' + (data.estatus || 'Consultado');
            if (data.cancelable !== undefined) {
                message += '\nCancelable: ' + (data.cancelable ? 'Sí' : 'No');
            }
            alert(message);
        } else {
            alert('Error: ' + (data.error || 'No se pudo verificar el estatus'));
        }
    })
    .catch(error => {
        alert('Error al conectar con el servidor: ' + error.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}
</script>
