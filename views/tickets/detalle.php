<?php
use App\Helpers\FileUploadHelper;
use App\Helpers\PermissionHelper;

/**
 * Función helper para mostrar tiempo relativo
 *
 * @param string $datetime Fecha en formato de base de datos
 * @return string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'hace un momento';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return 'hace ' . $minutes . ' minuto' . ($minutes > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return 'hace ' . $hours . ' hora' . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return 'hace ' . $days . ' día' . ($days > 1 ? 's' : '');
    } else {
        return date('d/m/Y H:i', $time);
    }
}

$estadoInfo = $estados[$ticket['estado']] ?? ['label' => $ticket['estado'], 'color' => 'gray'];
?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Información Principal -->
    <div class="lg:col-span-2 space-y-6 min-w-0">
        
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
                    
                    <!-- Tipo de Factura -->
                    <div>
                        <label class="text-sm font-medium text-gray-500">Tipo de Factura</label>
                        <p class="text-gray-900"><?= TIPOS_AUTO[$ticket['tipo_factura']] ?? $ticket['tipo_factura'] ?></p>
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

                    <!-- UUID de Factura Nueva (solo para refacturaciones) -->
                    <?php if ($ticket['tipo_cancelacion'] === 'refacturacion' && !empty($ticket['uuid_factura_nueva'])): ?>
                    <div class="col-span-2 md:col-span-1">
                        <label class="text-sm font-medium text-gray-500">UUID de Factura Nueva</label>
                        <p class="text-sm text-gray-900 font-mono break-all mt-1">
                            <?= htmlspecialchars($ticket['uuid_factura_nueva']) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Motivo -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <label class="text-sm font-medium text-gray-500">Motivo de Cancelación</label>
                    <p class="text-gray-900 mt-2 whitespace-pre-line break-words"><?= htmlspecialchars($ticket['motivo']) ?></p>
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

        <!-- SECCIÓN DE COMENTARIOS -->
        <?php if (!empty($comentarios) || PermissionHelper::hasPermission('tickets.comments.add')): ?>
        <div class="card mt-6" id="comentariosSection">
            <div class="card-header flex items-center justify-between bg-gradient-to-r from-primary-500 to-primary-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white">💬 Comentarios</h3>
                        <?php if (!empty($comentarios)): ?>
                        <p class="text-primary-100 text-sm">
                            <?= count($comentarios) ?> comentario(s)
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Formulario para agregar comentario (solo Admin y Supervisor Cancelaciones) -->
                <?php if (PermissionHelper::hasPermission('tickets.comments.add')): ?>
                <form id="formComentario" class="mb-6">
                    <?= \App\Helpers\AuthHelper::getCsrfField() ?>

                    <div>
                        <label for="comentario" class="form-label">
                            Agregar un comentario
                        </label>
                        <textarea id="comentario"
                                  name="comentario"
                                  rows="3"
                                  class="form-input"
                                  placeholder="Escribe tu comentario aquí..."
                                  required
                                  minlength="5"
                                  maxlength="1000"></textarea>
                        <p class="text-xs text-gray-500 mt-1">
                            Mínimo 5 caracteres. Máximo 1000.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Publicar Comentario
                    </button>
                </form>
                <?php endif; ?>

                <!-- Lista de comentarios -->
                <?php if (!empty($comentarios)): ?>
                <div id="listaComentarios" class="space-y-4">
                    <?php foreach ($comentarios as $comentario): ?>
                    <div class="border-l-4
                        <?= $comentario['rol_nombre'] === 'Administrador'
                           ? 'border-l-red-500 bg-red-50'
                           : 'border-l-primary-500 bg-primary-50' ?>
                        rounded-r-lg p-4"
                         id="comentario-<?= $comentario['id'] ?>">
                        <div class="flex items-start space-x-3">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center
                                     <?= $comentario['rol_nombre'] === 'Administrador'
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-primary-100 text-primary-700' ?>">
                                    <span class="font-bold text-sm">
                                        <?= strtoupper(substr($comentario['usuario_nombre'], 0, 1)) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Contenido -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-semibold text-gray-900">
                                            <?= htmlspecialchars($comentario['usuario_nombre']) ?>
                                        </span>
                                        <span class="badge
                                            <?= $comentario['rol_nombre'] === 'Administrador'
                                               ? 'badge-red' : 'badge-blue' ?>">
                                            <?= $comentario['rol_nombre'] ?>
                                        </span>
                                    </div>

                                    <!-- Botón eliminar (solo Admin) -->
                                    <?php if (PermissionHelper::isAdmin()): ?>
                                    <button onclick="eliminarComentario(<?= $comentario['id'] ?>)"
                                            class="text-red-500 hover:text-red-700 transition-colors"
                                            title="Eliminar comentario">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    <?php endif; ?>
                                </div>

                                <p class="text-xs text-gray-500 mt-1">
                                    <?= timeAgo($comentario['fecha_creacion']) ?>
                                </p>

                                <p class="text-gray-900 mt-2 whitespace-pre-line break-words">
                                    <?= htmlspecialchars($comentario['comentario']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p>Aún no hay comentarios en este ticket.</p>
                    <?php if (PermissionHelper::hasPermission('tickets.comments.add')): ?>
                    <p class="text-sm mt-1">Sé el primero en comentar.</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Operaciones Relacionadas -->
        <?php if (!empty($ticket['operaciones'])): ?>
        <div class="card">
            <div class="card-header grid grid-2">
                <h3 class="text-lg font-semibold text-gray-900 grid-span-1">Operaciones Relacionadas</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Factura</th>
                            <!--<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">UUID</th>-->
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Requiere Canc.</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Solicitada Cancelación</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cancelado Sistema</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cancelado SAT</th>
                            <?php if ($canChangeStatus): ?>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            <?php endif; ?>
                            <?php if ($canVerifySat): ?>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Validar SAT</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($ticket['operaciones'] as $op): ?>
                        <tr data-op-id="<?= $op['id'] ?>">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?= $op['serie'] .'-'. $op['id_compago'] ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?= htmlspecialchars($tipos_operacion[$op['tipo_operacion']] ?? $op['tipo_operacion']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">
                                <?= $op['monto'] ? '$' . number_format($op['monto'], 2) : '-' ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if ($op['requiere_cancelacion']): ?>
                                <span class="badge badge-blue">Sí</span>
                                <?php else: ?>
                                <span class="badge badge-gray">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="flag-badge" data-flag="solicitada_cancelacion">
                                    <?php if ($op['solicitada_cancelacion']): ?>
                                    <span class="badge badge-blue">Sí</span>
                                    <?php else: ?>
                                    <span class="badge badge-gray">No</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="flag-badge" data-flag="cancelado_sistema">
                                    <?php if ($op['cancelado_sistema']): ?>
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
                                            class="btn-toggle-flag p-1 text-blue-600 hover:text-blue-800 transition-colors <?= !$op['requiere_cancelacion'] ? 'opacity-25 cursor-not-allowed' : '' ?>"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="solicitada_cancelacion"
                                            title="Solicitada Cancelación"
                                            <?= !$op['requiere_cancelacion'] ? 'disabled' : '' ?>>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-green-600 hover:text-green-800 transition-colors <?= !$op['requiere_cancelacion'] ? 'opacity-25 cursor-not-allowed' : '' ?>"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="cancelado_sistema"
                                            title="Cancelado Sistema"
                                            <?= !$op['requiere_cancelacion'] ? 'disabled' : '' ?>>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-purple-600 hover:text-purple-800 transition-colors <?= !$op['requiere_cancelacion'] ? 'opacity-25 cursor-not-allowed' : '' ?>"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="cancelado_sat"
                                            title="Cancelado SAT"
                                            <?= !$op['requiere_cancelacion'] ? 'disabled' : '' ?>>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4 0 003 15z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <?php else: ?>
                                <?php if ($canChangeStatus): ?>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-2">
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-blue-600 hover:text-blue-800 transition-colors <?= !$op['requiere_cancelacion'] ? 'opacity-25 cursor-not-allowed' : '' ?>"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="solicitada_cancelacion"
                                            title="Solicitada Cancelación"
                                            <?= !$op['requiere_cancelacion'] ? 'disabled' : '' ?>>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-green-600 hover:text-green-800 transition-colors <?= !$op['requiere_cancelacion'] ? 'opacity-25 cursor-not-allowed' : '' ?>"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="cancelado_sistema"
                                            title="Cancelado Sistema"
                                            <?= !$op['requiere_cancelacion'] ? 'disabled' : '' ?>>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-purple-600 hover:text-purple-800 transition-colors <?= !$op['requiere_cancelacion'] ? 'opacity-25 cursor-not-allowed' : '' ?>"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="cancelado_sat"
                                            title="Cancelado SAT"
                                            <?= !$op['requiere_cancelacion'] ? 'disabled' : '' ?>>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4 0 003 15z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($canVerifySat): ?>
                            <td class="px-6 py-4 text-center">
                                <?php 
                                $mostrarBoton = $op['requiere_cancelacion'] && 
                                                $op['solicitada_cancelacion'] && 
                                                !$op['cancelado_sat'];
                                ?>
                                <?php if ($mostrarBoton): ?>
                                <button type="button"
                                        class="btn-validate-sat-ops p-2 text-orange-600 hover:text-orange-800 transition-colors"
                                        data-op-id="<?= $op['id'] ?>"
                                        data-op-uuid="<?= htmlspecialchars($op['uuid_operacion']) ?>"
                                        data-op-serie="<?= htmlspecialchars($op['serie']) ?>"
                                        data-ticket-id="<?= $ticket['id'] ?>"
                                        title="Validar Status SAT">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                <?php else: ?>
                                <span class="text-gray-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                                <?php endif; ?>
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
    <div class="space-y-6 min-w-0">
        
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
        
        <div class="flex flex-col space-y-3">
            <button type="button" 
                    id="btnVerificarSat"
                    class="btn btn-info w-full <?= $ticket['estado'] !== 'proceso_cancelacion' ? 'opacity-50 cursor-not-allowed' : '' ?>"
                    <?= $ticket['estado'] !== 'proceso_cancelacion' ? 'disabled' : '' ?>>
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Verificar Status SAT
            </button>
        </div>

        <!-- Ingresar UUID Factura Nueva (solo rol Usuario, estado liberado, refacturación) -->
        <?php
        $canIngresarUuid = PermissionHelper::isRegularUser()
            && $ticket['tipo_cancelacion'] === 'refacturacion'
            && $ticket['estado'] === 'liberado'
            && empty($ticket['uuid_factura_nueva']);
        ?>

        <?php if ($canIngresarUuid): ?>
        <div class="card mt-4 border-l-4 border-l-purple-500">
            <div class="card-header bg-purple-50">
                <h3 class="text-lg font-semibold text-purple-900">Ingresar UUID de Factura Nueva</h3>
                <p class="text-sm text-purple-700 mt-1">
                    Ingrese el UUID de la factura nueva emitida para poder proceder con la cancelación con relación.
                </p>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>/uuid-nueva" method="POST" id="uuidForm">
                    <?= \App\Helpers\AuthHelper::getCsrfField() ?>

                    <div class="mb-4">
                        <label for="uuid_factura_nueva" class="form-label">
                            UUID de la Factura Nueva <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               name="uuid_factura_nueva"
                               id="uuid_factura_nueva"
                               class="form-input font-mono text-sm"
                               placeholder="Ej: 123e4567-e89b-12d3-a456-426614174000"
                               pattern="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"
                               title="Formato: 123e4567-e89b-12d3-a456-426614174000"
                               required
                               maxlength="36">
                        <p class="text-xs text-gray-500 mt-1">
                            Ingrese el UUID de la factura nueva emitida en la refacturación.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-primary w-full" id="btnGuardarUuid">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Guardar UUID de Factura Nueva
                    </button>
                </form>
            </div>
        </div>

        <script>
        document.getElementById('uuidForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('btnGuardarUuid');
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Guardando...';

            try {
                const formData = new FormData(this);
                const response = await fetch('<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>/uuid-nueva', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= \App\Helpers\AuthHelper::generateCsrfToken() ?>'
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    if (result.success) {
                        showToast(result.message || 'UUID actualizado correctamente', 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showToast(result.error || 'Error al guardar el UUID', 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Guardar UUID de Factura Nueva';
                    }
                } else {
                    showToast(result.error || 'Error al guardar el UUID', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Guardar UUID de Factura Nueva';
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión con el servidor.', 'error');
                btn.disabled = false;
                btn.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Guardar UUID de Factura Nueva';
            }
        });
        </script>
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
                        <p class="text-sm font-medium text-gray-900 break-words"><?= htmlspecialchars($audit['accion']) .' a '. htmlspecialchars($audit['valor_nuevo']) ?></p>
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
        
        <div class="flex flex-col space-y-3">

            <?php if (PermissionHelper::isConsulta()): ?>
            <a href="<?= BASE_URL ?>solicitudes" class="btn btn-secondary w-full">
                ← Volver al listado
            </a>
            <?php elseif (PermissionHelper::hasPermission('tickets.view.all')): ?>
            <a href="<?= BASE_URL ?>tickets" class="btn btn-secondary w-full">
                ← Volver al listado
            </a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>mis-solicitudes" class="btn btn-secondary w-full">
                ← Volver al listado
            </a>
            <?php endif; ?>
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
                        '_csrf_token': '<?= \App\Helpers\AuthHelper::generateCsrfToken() ?>'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Actualizar badge
                    if (result.nuevo_valor) {
                        badgeContainer.innerHTML = `<span class="badge badge-green">Sí</span>`;
                    } else {
                        badgeContainer.innerHTML = `<span class="badge badge-gray">No</span>`;
                    }
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

    // Verificación SAT
    const btnVerificarSat = document.getElementById('btnVerificarSat');
    if (btnVerificarSat) {
        btnVerificarSat.addEventListener('click', async function() {
            try {
                this.disabled = true;
                const originalContent = this.innerHTML;
                this.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Verificando...
                `;

                const response = await fetch(`<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>/validar-sat`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= \App\Helpers\AuthHelper::generateCsrfToken() ?>'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    let mensaje = `<strong>${result.estado_validacion}</strong><br>Cancelacion: ${result.estatus_cancelacion}`;
                    mensaje += `<br><small class="opacity-75">${result.mensaje}</small>`;
                    
                    showToast(mensaje, result.procesamiento_exitoso ? 'success' : 'warning');
                } else {
                    showToast(result.error || 'No se pudo verificar el estatus.', 'error');
                }

                this.innerHTML = originalContent;
                this.disabled = false;
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión con el servidor.');
                this.disabled = false;
            }
        });
    }

    // Validación SAT para operaciones
    document.querySelectorAll('.btn-validate-sat-ops').forEach(button => {
        button.addEventListener('click', async function() {
            const opId = this.dataset.opId;
            const tr = this.closest('tr');
            
            try {
                this.disabled = true;
                const originalContent = this.innerHTML;
                this.innerHTML = `
                    <svg class="animate-spin w-5 h-5 text-orange-600" 
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;

                const response = await fetch(`<?= BASE_URL ?>tickets/operacion/${opId}/validar-sat`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= \App\Helpers\AuthHelper::generateCsrfToken() ?>'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    let mensaje = `<strong>${result.estado_validacion}</strong><br>Cancelación: ${result.estatus_cancelacion}`;
                    mensaje += `<br><small class="opacity-75">${result.mensaje}</small>`;
                    
                    showToast(mensaje, result.procesamiento_exitoso ? 'success' : 'warning');
                    
                    if (result.updated_cancelado_sat) {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    showToast(result.error || 'No se pudo verificar el estatus.', 'error');
                }

                this.innerHTML = originalContent;
                this.disabled = false;
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión con el servidor.', 'error');
                this.disabled = false;
            }
            });
        });
    });

    // Formulario de agregar comentario
    const formComentario = document.getElementById('formComentario');
    if (formComentario) {
        formComentario.addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = formComentario.querySelector('button[type="submit"]');
            const btnOriginalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Publicando...
            `;

            try {
                const formData = new FormData(formComentario);
                const response = await fetch(`<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>/comentarios`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= \App\Helpers\AuthHelper::generateCsrfToken() ?>'
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    showToast('Comentario publicado correctamente', 'success');

                    // Agregar comentario al DOM
                    if (result.comentario) {
                        addComentarioToUI(result.comentario);
                    }

                    // Limpiar formulario
                    formComentario.reset();
                    btn.innerHTML = btnOriginalContent;
                    btn.disabled = false;
                } else {
                    showToast(result.error || 'Error al publicar comentario', 'error');
                    btn.innerHTML = btnOriginalContent;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión con el servidor', 'error');
                btn.innerHTML = btnOriginalContent;
                btn.disabled = false;
            }
        });
    }

    // Función para agregar comentario al DOM
    function addComentarioToUI(comentario) {
        const lista = document.getElementById('listaComentarios');
        const emptyState = lista.querySelector('.text-center');
        if (emptyState) {
            emptyState.remove();
        }

        const isAdmin = comentario.rol_nombre === 'Administrador';
        const borderColor = isAdmin ? 'border-l-red-500 bg-red-50' : 'border-l-primary-500 bg-primary-50';
        const badgeClass = isAdmin ? 'badge-red' : 'badge-blue';
        const avatarClass = isAdmin ? 'bg-red-100 text-red-700' : 'bg-primary-100 text-primary-700';

        const html = `
            <div class="border-l-4 ${borderColor} rounded-r-lg p-4 animate-slide-in"
                 id="comentario-${comentario.id}">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center ${avatarClass}">
                            <span class="font-bold text-sm">
                                ${comentario.usuario_nombre.charAt(0).toUpperCase()}
                            </span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="font-semibold text-gray-900">
                                    ${comentario.usuario_nombre}
                                </span>
                                <span class="badge ${badgeClass}">
                                    ${comentario.rol_nombre}
                                </span>
                            </div>
                            <?php if (PermissionHelper::isAdmin()): ?>
                            <button onclick="eliminarComentario(${comentario.id})"
                                    class="text-red-500 hover:text-red-700 transition-colors"
                                    title="Eliminar comentario">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">hace un momento</p>
                        <p class="text-gray-900 mt-2 whitespace-pre-line break-words">
                            ${comentario.comentario}
                        </p>
                    </div>
                </div>
            </div>
        `;

        lista.insertAdjacentHTML('afterbegin', html);
    }

    // Función para eliminar comentario
    async function eliminarComentario(comentarioId) {
        if (!confirm('¿Estás seguro de eliminar este comentario? Esta acción no se puede deshacer.')) {
            return;
        }

        try {
            const response = await fetch(`<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>/comentarios/${comentarioId}/eliminar`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?= \App\Helpers\AuthHelper::generateCsrfToken() ?>'
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                showToast('Comentario eliminado correctamente', 'success');

                const comentarioElement = document.getElementById(`comentario-${comentarioId}`);
                if (comentarioElement) {
                    comentarioElement.classList.add('animate-fade-out');
                    setTimeout(() => comentarioElement.remove(), 300);
                }
            } else {
                showToast(result.error || 'Error al eliminar comentario', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Error de conexión con el servidor', 'error');
        }
    }
});
</script>
