<?php
use App\Helpers\FileUploadHelper;
$estadoInfo = $estados[$ticket['estado']] ?? ['label' => $ticket['estado'], 'color' => 'gray'];
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Información Principal -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Datos del Ticket -->
        <div class="card card-accent-top card-accent-blue">
            <div class="card-header flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Ticket #<?= $ticket['id'] ?></h3>
                    <p class="text-sm text-gray-500">UUID: <?= htmlspecialchars($ticket['uuid']) ?></p>
                </div>
                <span class="badge badge-<?= $estadoInfo['color'] ?> text-base px-4 py-2">
                    <?= $estadoInfo['label'] ?>
                </span>
            </div>
            
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cliente -->
                    <div>
                        <label class="text-sm font-medium text-gray-500">Cliente</label>
                        <p class="text-gray-900 font-medium"><?= htmlspecialchars($ticket['nombre_cliente']) ?></p>
                        <p class="text-sm text-gray-500">RFC: <?= htmlspecialchars($ticket['rfc_receptor']) ?></p>
                    </div>
                    
                    <!-- Factura -->
                    <div>
                        <label class="text-sm font-medium text-gray-500">Factura</label>
                        <p class="text-gray-900 font-medium"><?= htmlspecialchars($ticket['serie']) ?>-<?= htmlspecialchars($ticket['folio']) ?></p>
                        <p class="text-xs text-gray-500 font-mono break-all"><?= htmlspecialchars($ticket['uuid_factura']) ?></p>
                    </div>
                    
                    <!-- Empresa -->
                    <div>
                        <label class="text-sm font-medium text-gray-500">Empresa Solicitante</label>
                        <p class="text-gray-900"><?= EMPRESAS[$ticket['empresa_solicitante']] ?? $ticket['empresa_solicitante'] ?></p>
                    </div>
                    
                    <!-- Total -->
                    <div>
                        <label class="text-sm font-medium text-gray-500">Total de Factura</label>
                        <p class="text-2xl font-bold text-gray-900">$<?= number_format($ticket['total_factura'], 2) ?></p>
                    </div>
                    
                    <!-- Inventario -->
                    <?php if ($ticket['inventario']): ?>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Inventario</label>
                        <p class="text-gray-900"><?= htmlspecialchars($ticket['inventario']) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Tipo de Cancelación -->
                    <div>
                        <label class="text-sm font-medium text-gray-500">Tipo de Cancelación</label>
                        <p class="text-gray-900"><?= TIPOS_CANCELACION[$ticket['tipo_cancelacion']] ?? $ticket['tipo_cancelacion'] ?></p>
                    </div>
                </div>
                
                <!-- Motivo -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label class="text-sm font-medium text-gray-500">Motivo de Cancelación</label>
                    <p class="text-gray-900 mt-2 whitespace-pre-line"><?= htmlspecialchars($ticket['motivo']) ?></p>
                </div>
                
                <!-- Archivo -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label class="text-sm font-medium text-gray-500">Archivo de Autorización</label>
                    <a href="<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>/archivo" 
                       class="mt-2 flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-10 h-10 text-danger-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900"><?= basename($ticket['archivo_autorizacion']) ?></p>
                            <p class="text-sm text-gray-500">Click para descargar</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Operaciones Relacionadas -->
        <div class="card">
            <div class="card-header grid grid-2">
                <h3 class="text-lg font-semibold text-gray-900 grid-span-1">Operaciones Relacionadas</h3>
            </div>
            <?php if (!empty($ticket['operaciones'])): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Factura</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">UUID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Requiere Canc.</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Can. Sistema</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Can. SAT</th>
                            <?php if ($canChangeStatus): ?>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($ticket['operaciones'] as $op): ?>
                        <tr data-op-id="<?= $op['id'] ?>">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?= $op['serie'] .'-'. $op['id_compago'] ?>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-gray-500">
                                <?= htmlspecialchars(substr($op['uuid_operacion'], 0, 18)) ?>...
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?= htmlspecialchars($tipos_operacion[$op['tipo_operacion']] ?? $op['tipo_operacion']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">
                                <?= $op['monto'] ? '$' . number_format($op['monto'], 2) : '-' ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="flag-badge" data-flag="requiere_cancelacion">
                                    <?php if ($op['requiere_cancelacion']): ?>
                                    <span class="badge badge-blue">Sí</span>
                                    <?php else: ?>
                                    <span class="badge badge-gray">No</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="flag-badge" data-flag="cancelado_sistema">
                                    <?php if ($op['cancelado_sistema'] || $op['cancelada']): ?>
                                    <span class="badge badge-green">Sí</span>
                                    <?php else: ?>
                                    <span class="badge badge-gray">No</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="flag-badge" data-flag="cancelado_sat">
                                    <?php if ($op['cancelado_sat']): ?>
                                    <span class="badge badge-green">Sí</span>
                                    <?php else: ?>
                                    <span class="badge badge-gray">No</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <?php if ($canChangeStatus): ?>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-blue-600 hover:text-blue-800 transition-colors"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="requiere_cancelacion"
                                            title="Alternar Requiere Cancelación">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-green-600 hover:text-green-800 transition-colors"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="cancelado_sistema"
                                            title="Alternar Cancelado Sistema">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-purple-600 hover:text-purple-800 transition-colors"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="cancelado_sat"
                                            title="Alternar Cancelado SAT">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Panel Lateral -->
    <div class="space-y-6">
        
        <!-- Cambiar Estado -->
        <?php if ($canChangeStatus): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Cambiar Estado</h3>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>/estado" method="POST" id="statusForm">
                    <?= \App\Helpers\AuthHelper::getCsrfField() ?>
                    
                    <select name="estado" class="form-select mb-4" id="estadoSelect">
                        <?php foreach ($estados as $key => $info): ?>
                        <option value="<?= $key ?>" <?= $ticket['estado'] === $key ? 'selected' : '' ?>>
                            <?= $info['label'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit" class="btn btn-update w-full">
                        Actualizar Estado
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Información del Usuario -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Solicitante</h3>
            </div>
            <div class="card-body">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                        <span class="text-primary-700 font-semibold text-lg">
                            <?= strtoupper(substr($ticket['usuario_nombre'] ?? 'U', 0, 1)) ?>
                        </span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($ticket['usuario_nombre'] ?? 'Usuario') ?></p>
                        <p class="text-sm text-gray-500"><?= htmlspecialchars($ticket['usuario_email'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fechas -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Fechas</h3>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Fecha de Creación</label>
                    <p class="text-gray-900"><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></p>
                </div>
                
                <?php if ($ticket['fecha_envio_cancelacion']): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Enviado a Cancelar</label>
                    <p class="text-gray-900"><?= date('d/m/Y H:i', strtotime($ticket['fecha_envio_cancelacion'])) ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($ticket['fecha_cancelacion_sat']): ?>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Cancelado en SAT</label>
                    <p class="text-gray-900"><?= date('d/m/Y H:i', strtotime($ticket['fecha_cancelacion_sat'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Timeline de Auditoría -->
        <?php if (!empty($ticket['auditoria'])): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Historial</h3>
            </div>
            <div class="card-body p-0">
                <div class="divide-y divide-gray-100">
                    <?php foreach (array_slice($ticket['auditoria'], 0, 5) as $audit): ?>
                    <div class="p-4">
                        <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($audit['accion']) ?></p>
                        <p class="text-xs text-gray-500 mt-1">
                            <?= htmlspecialchars($audit['usuario_nombre']) ?> · 
                            <?= date('d/m/Y H:i', strtotime($audit['fecha'])) ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Acciones -->
        <div class="flex flex-col space-y-3">
            <a href="<?= BASE_URL ?>tickets" class="btn btn-secondary w-full">
                ← Volver al listado
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.btn-toggle-flag');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const opId = this.dataset.opId;
            const flag = this.dataset.flag;
            const tr = this.closest('tr');
            const badgeContainer = tr.querySelector(`.flag-badge[data-flag="${flag}"]`);
            
            // Si la bandera que tocamos es 'cancelado_sistema', también queremos actualizar visualmente 'cancelada' si existe
            // pero en la DB ya manejamos que cancelado_sistema es el nuevo estándar.
            
            try {
                this.disabled = true;
                this.classList.add('opacity-50');
                
                const response = await fetch(`<?= BASE_URL ?>tickets/operacion/${opId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        'flag': flag,
                        'csrf_token': '<?= \App\Helpers\AuthHelper::generateCsrfToken() ?>'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Actualizar badge
                    if (result.nuevo_valor) {
                        const color = flag === 'requiere_cancelacion' ? 'blue' : 'green';
                        badgeContainer.innerHTML = `<span class="badge badge-${color}">Sí</span>`;
                    } else {
                        badgeContainer.innerHTML = `<span class="badge badge-gray">No</span>`;
                    }
                    
                    // Notificación (opcional, si hay un sistema de toasts)
                    // showToast(result.message, 'success');
                } else {
                    alert(result.error || 'Error al actualizar');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión');
            } finally {
                this.disabled = false;
                this.classList.remove('opacity-50');
            }
        });
    });
});
</script>
