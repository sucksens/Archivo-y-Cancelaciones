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

// Obtener iniciales del usuario
$userInitials = strtoupper(substr($ticket['usuario_nombre'] ?? 'U', 0, 2));
?>

<!-- Header unificado del Ticket -->
<section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
    <div class="flex flex-col gap-6">
        <!-- Línea superior: Título, Estado, UUID y Acciones -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="flex items-center flex-wrap gap-3">
                <h2 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Ticket #<?= $ticket['id'] ?></h2>
                <span class="px-3 py-1 rounded-full bg-<?= $estadoInfo['color'] ?>-100 text-<?= $estadoInfo['color'] ?>-700 text-xs font-bold uppercase">
                    <?= $estadoInfo['label'] ?>
                </span>
                <p class="font-bold text-primary-500 leading-tight uppercase"><?= TIPOS_CANCELACION[$ticket['tipo_cancelacion']] ?? $ticket['tipo_cancelacion'] ?></p>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] uppercase font-bold text-slate-400">UUID:</span>
                    <span class="text-xs text-slate-500 font-mono"><?= htmlspecialchars($ticket['uuid_factura']) ?></span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <?php if ($canChangeStatus): ?>
                <form id="statusForm" class="inline-flex">
                    <?= \App\Helpers\AuthHelper::getCsrfField() ?>
                    <select name="estado" id="estadoSelect" 
                            class="text-xs font-bold px-3 py-2 rounded-l-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <?php foreach ($estados as $key => $info): ?>
                        <option value="<?= $key ?>" <?= $ticket['estado'] === $key ? 'selected' : '' ?>>
                            <?= $info['label'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" 
                            class="bg-update-500 hover:bg-update-600 text-white px-4 py-2 rounded-r-lg text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Actualizar
                    </button>
                </form>
                <?php endif; ?>
                
                <button type="button" 
                        id="btnVerificarSat"
                        class="bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-lg text-xs font-bold shadow-sm hover:bg-slate-50 transition-all flex items-center justify-center gap-2 <?= $ticket['estado'] !== 'proceso_cancelacion' ? 'opacity-50 cursor-not-allowed' : '' ?>"
                        <?= $ticket['estado'] !== 'proceso_cancelacion' ? 'disabled' : '' ?>>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Validar SAT
                </button>
            </div>
        </div>
        
        <!-- Grid de información del ticket -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-6">
            <!-- Empresa Solicitante -->
            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Empresa Solicitante</p>
                <?php 
                $empresaColor = 'gray';
                if ($ticket['empresa_solicitante'] === 'grupo_motormexa') {
                    $empresaColor = 'blue';
                } elseif ($ticket['empresa_solicitante'] === 'automotriz_motormexa') {
                    $empresaColor = 'red';
                }
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold <?= $empresaColor === 'blue' ? 'bg-primary-100 text-primary-700' : ($empresaColor === 'red' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') ?>">
                    <?= EMPRESAS[$ticket['empresa_solicitante']] ?? $ticket['empresa_solicitante'] ?>
                </span>
            </div>
            
            <!-- Cliente / Receptor -->
            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Cliente / Receptor</p>
                <p class="font-bold text-slate-800 leading-tight"><?= htmlspecialchars($ticket['nombre_cliente']) ?></p>
                <p class="text-[20px] text-slate-500 font-mono uppercase"><?= htmlspecialchars($ticket['rfc_receptor']) ?></p>
            </div>
            
            <!-- Tipo de Factura -->
            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Factura</p>
                <p class="font-bold text-slate-800 leading-tight"><?= $ticket['serie'] ?>-<?= $ticket['folio']?></p>

                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tipo de Factura</p>
                <p class="font-bold text-slate-800 leading-tight"><?= TIPOS_AUTO[$ticket['tipo_factura']] ?? $ticket['tipo_factura'] ?></p>
                <!--
                <div class="flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                    <span class="text-[10px] text-slate-500 font-bold uppercase"><?= $ticket['uuid'] ?></span>
                </div>
                -->
            </div>
            
            <!-- Motivo de Cancelación -->
            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Motivo de Cancelación</p>
                <!--<p class="font-bold text-primary-500 leading-tight uppercase"><?= TIPOS_CANCELACION[$ticket['tipo_cancelacion']] ?? $ticket['tipo_cancelacion'] ?></p>-->
                <p class="font-bold text-primary-500 leading-tight uppercase"><?= htmlspecialchars($ticket['motivo']) ?></p>
            </div>
            
            <!-- Monto Total -->
            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Total de la Factura</p>
                <div class="flex items-baseline gap-2">
                    <p class="font-black text-2xl text-slate-900 leading-tight">$<?= number_format($ticket['total_factura'], 2) ?></p>
                    <p class="text-[10px] text-slate-500 font-mono font-bold uppercase tracking-widest">MXN</p>
                </div>
            </div>
            
            
            <!-- Fechas -->
            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Fecha de Creación</p>
                <p class="text-sm font-semibold text-slate-800"><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></p>
                <?php if ($ticket['fecha_envio_cancelacion']): ?>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Fecha de Solicitud</p>
                <p class="text-[10px] text-slate-500">Enviado: <?= date('d/m/Y H:i', strtotime($ticket['fecha_envio_cancelacion'])) ?></p>
                <?php endif; ?>
                <?php if ($ticket['fecha_cancelacion_sat']): ?>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Fecha de Cancelación</p>
                <p class="text-[10px] text-slate-500">Cancelado: <?= date('d/m/Y H:i', strtotime($ticket['fecha_cancelacion_sat'])) ?></p>
                <?php endif; ?>
            </div>

            <!-- Solicitante Interno -->
            <div class="space-y-1">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Solicitante Interno</p>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600">
                        <?= $userInitials ?>
                    </div>
                    <div>
                        <p class="text-sm font-semibold"><?= htmlspecialchars($ticket['usuario_nombre'] ?? 'Usuario') ?></p>
                        <p class="text-[10px] text-slate-500"><?= htmlspecialchars($ticket['usuario_email'] ?? '') ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Documentos Adjuntos -->
            <div class="lg:col-span-1 space-y-2">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Archivo de Autorizacion</p>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>/archivo"
                       class="flex items-center gap-1.5 bg-red-50 px-3 py-1.5 rounded-lg border border-red-100 cursor-pointer hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-xs font-bold text-red-700"><?= basename($ticket['archivo_autorizacion']) ?></span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- UUID Factura Nueva (solo para refacturaciones) -->
        <?php if ($ticket['tipo_cancelacion'] === 'refacturacion'): ?>
        <div class="border-t border-slate-100 pt-4">
            <?php if (!empty($ticket['uuid_factura_nueva'])): ?>
            <div class="flex items-center gap-3 bg-purple-50 rounded-lg p-3">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-[10px] uppercase font-bold text-purple-600 tracking-wider">UUID de Factura Nueva</p>
                    <p class="text-sm text-purple-900 font-mono"><?= htmlspecialchars($ticket['uuid_factura_nueva']) ?></p>
                </div>
            </div>
            <?php elseif (PermissionHelper::isRegularUser() && $ticket['estado'] === 'liberado'): ?>
            <!-- Formulario para ingresar UUID -->
            <form id="uuidForm" class="bg-purple-50 rounded-lg p-4">
                <?= \App\Helpers\AuthHelper::getCsrfField() ?>
                <label class="text-[10px] uppercase font-bold text-purple-600 tracking-wider">Ingresar UUID de Factura Nueva</label>
                <div class="flex gap-2 mt-2">
                    <input type="text"
                           name="uuid_factura_nueva"
                           id="uuid_factura_nueva"
                           class="flex-1 px-3 py-2 text-sm font-mono border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Ej: 123e4567-e89b-12d3-a456-426614174000"
                           pattern="[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"
                           required
                           maxlength="36">
                    <button type="submit" id="btnGuardarUuid" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-xs font-bold transition-all">
                        Guardar
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Layout de 2 columnas -->
<div class="flex flex-col lg:flex-row gap-6">
    <!-- Columna izquierda (62%) -->
    <div class="lg:w-[62%] flex flex-col gap-6">
        
        <!-- Operaciones Relacionadas -->
        <?php if (!empty($ticket['operaciones'])): ?>
        <section class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="font-bold text-slate-800">Operaciones Relacionadas</h3>
                </div>
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Vista Detallada</div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2 font-bold uppercase text-[10px] tracking-wider">Factura</th>
                            <th class="px-4 py-2 font-bold uppercase text-[10px] tracking-wider">Tipo</th>
                            <th class="px-4 py-2 font-bold uppercase text-[10px] tracking-wider text-right">Monto</th>
                            <th class="px-4 py-2 font-bold uppercase text-[10px] tracking-wider text-center">Requiere Canc.</th>
                            <th class="px-4 py-2 font-bold uppercase text-[10px] tracking-wider text-center">Solicitada Canc.</th>
                            <th class="px-4 py-2 font-bold uppercase text-[10px] tracking-wider text-center">Canc. Sistema</th>
                            <th class="px-4 py-2 font-bold uppercase text-[10px] tracking-wider text-center">Canc. SAT</th>
                            <?php if ($canChangeStatus): ?>
                            <th class="px-4 py-2 font-bold uppercase text-[10px] tracking-wider text-center">Acciones</th>
                            <?php endif; ?>
                            <?php if ($canVerifySat): ?>
                            <th class="px-4 py-2 font-bold uppercase text-[10px] tracking-wider text-center">Validar</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($ticket['operaciones'] as $op): ?>
                        <tr class="hover:bg-slate-50 transition-colors" data-op-id="<?= $op['id'] ?>">
                            <td class="px-4 py-2 font-bold text-slate-700"><?= $op['serie'] ?>-<?= $op['id_compago'] ?></td>
                            <td class="px-4 py-2 text-slate-500"><?= htmlspecialchars($tipos_operacion[$op['tipo_operacion']] ?? $op['tipo_operacion']) ?></td>
                            <td class="px-4 py-2 text-right font-black"><?= $op['monto'] ? '$' . number_format($op['monto'], 2) : '-' ?></td>
                            <td class="px-4 py-2 text-center">
                                <?php if ($op['requiere_cancelacion']): ?>
                                <span class="px-2.5 py-0.7 rounded-full bg-blue-100 text-blue-700 text-xs font-black uppercase tracking-tight shadow-sm">Sí</span>
                                <?php else: ?>
                                <span class="px-2.5 py-0.7 rounded-full bg-slate-200 text-slate-600 text-xs font-bold uppercase tracking-tight">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span class="flag-badge" data-flag="solicitada_cancelacion">
                                    <?php if ($op['solicitada_cancelacion']): ?>
                                    <span class="px-2.5 py-0.7 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black uppercase tracking-tight shadow-sm">Sí</span>
                                    <?php else: ?>
                                    <span class="px-2.5 py-0.7 rounded-full bg-slate-200 text-slate-600 text-xs font-bold uppercase tracking-tight">No</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span class="flag-badge" data-flag="cancelado_sistema">
                                    <?php if ($op['cancelado_sistema']): ?>
                                    <span class="px-2 py-0.5 rounded bg-update-50 text-update-600 text-[10px] font-bold">Sí</span>
                                    <?php else: ?>
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-bold">No</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="px-4 py-2 text-center">
                                <span class="flag-badge" data-flag="cancelado_sat">
                                    <?php if ($op['cancelado_sat']): ?>
                                    <span class="px-2 py-0.5 rounded bg-update-50 text-update-600 text-[10px] font-bold">Sí</span>
                                    <?php else: ?>
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[10px] font-bold">No</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <?php if ($canChangeStatus): ?>
                            <td class="px-4 py-2">
                                <div class="flex items-center justify-center gap-2 <?= !$op['requiere_cancelacion'] ? 'opacity-25' : '' ?>">
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-primary-500 hover:text-primary-700 transition-colors"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="solicitada_cancelacion"
                                            title="Solicitada Cancelación"
                                            <?= !$op['requiere_cancelacion'] ? 'disabled' : '' ?>>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-update-500 hover:text-update-700 transition-colors"
                                            data-op-id="<?= $op['id'] ?>"
                                            data-flag="cancelado_sistema"
                                            title="Cancelado Sistema"
                                            <?= !$op['requiere_cancelacion'] ? 'disabled' : '' ?>>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </button>
                                    <button type="button" 
                                            class="btn-toggle-flag p-1 text-brand-purple hover:text-purple-700 transition-colors"
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
                            <?php if ($canVerifySat): ?>
                            <td class="px-4 py-2 text-center">
                                <?php 
                                $mostrarBoton = $op['requiere_cancelacion'] && 
                                                $op['solicitada_cancelacion'] && 
                                                !$op['cancelado_sat'];
                                ?>
                                <?php if ($mostrarBoton): ?>
                                <button type="button"
                                        class="btn-validate-sat-ops p-2 text-warning-500 hover:text-warning-700 transition-colors"
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
                                <span class="text-slate-300">
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
        </section>
        <?php endif; ?>
        
        <!-- Volver al listado -->
        <div class="flex">
            <?php if (PermissionHelper::isConsulta()): ?>
            <a href="<?= BASE_URL ?>solicitudes" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-primary-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al listado
            </a>
            <?php elseif (PermissionHelper::hasPermission('tickets.view.all')): ?>
            <a href="<?= BASE_URL ?>tickets" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-primary-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al listado
            </a>
            <?php else: ?>
            <a href="<?= BASE_URL ?>mis-solicitudes" class="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-primary-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al listado
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Columna derecha (38%) - Stream de Actividad y Comentarios -->
    <div class="lg:w-[38%] flex flex-col">
        <section class="bg-slate-50 rounded-xl shadow-lg border-2 border-slate-200 flex flex-col overflow-hidden lg:sticky lg:top-24">
            <!-- Header del panel -->
            <div class="p-4 bg-white border-b border-slate-200 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <h3 class="font-bold text-slate-800">Actividad y Comentarios</h3>
                </div>
                <span class="bg-primary-500 text-white px-2.5 py-1 rounded text-[10px] font-black uppercase tracking-tighter">
                    Stream Unificado
                </span>
            </div>
            
            <!-- Stream de actividad -->
            <?php
            // Preparar stream unificado de actividad (auditoría + comentarios)
            $unifiedActivity = [];
            
            if (!empty($ticket['auditoria'])) {
                foreach ($ticket['auditoria'] as $audit) {
                    $unifiedActivity[] = [
                        'type' => 'audit',
                        'timestamp' => strtotime($audit['fecha']),
                        'data' => $audit
                    ];
                }
            }
            
            if (!empty($comentarios)) {
                foreach ($comentarios as $comentario) {
                    $unifiedActivity[] = [
                        'type' => 'comment',
                        'timestamp' => strtotime($comentario['fecha_creacion']),
                        'data' => $comentario
                    ];
                }
            }
            
            // Ordenar por timestamp descendente (lo más nuevo arriba)
            usort($unifiedActivity, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });
            ?>
            
            <div class="p-5 overflow-y-auto custom-scrollbar space-y-6" id="activityStream" 
                 style="position: relative; max-height: 300px;">
                <!-- Línea vertical del timeline -->
                <style>
                    #activityStream::before {
                        content: '';
                        position: absolute;
                        left: 31px;
                        top: 20px;
                        bottom: 0px;
                        width: 2px;
                        background-color: #f1f5f9;
                        z-index: 0;
                    }
                </style>
                
                <div id="listaActividad" class="space-y-6">
                    <?php if (empty($unifiedActivity)): ?>
                        <div class="text-center py-8 text-slate-400">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="text-sm">No hay actividad aún</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($unifiedActivity as $item): ?>
                            <?php if ($item['type'] === 'audit'): ?>
                                <?php $audit = $item['data']; ?>
                                <div class="relative z-10 flex gap-4">
                                    <div class="w-8 h-8 rounded-full bg-primary-500 flex-shrink-0 flex items-center justify-center text-white ring-4 ring-slate-50 shadow-sm transition-transform hover:scale-110">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-primary-50 p-3 rounded-xl border border-primary-100 shadow-sm">
                                            <div class="flex justify-between items-center mb-1">
                                                <span class="text-[10px] font-black uppercase text-primary-700 tracking-wider">Cambio de Estado</span>
                                                <span class="text-[10px] text-slate-400 font-bold"><?= date('d/m H:i', strtotime($audit['fecha'])) ?></span>
                                            </div>
                                            <p class="text-xs font-bold text-primary-900"><?= htmlspecialchars($audit['accion']) ?> a <?= htmlspecialchars($audit['valor_nuevo']) ?></p>
                                            <p class="text-[11px] text-primary-700/80 mt-1 font-medium">Por: <?= htmlspecialchars($audit['usuario_nombre']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php 
                                $comentario = $item['data'];
                                $isAdmin = $comentario['rol_nombre'] === 'Administrador';
                                $bgColor = $isAdmin ? 'bg-red-500' : 'bg-primary-600';
                                $cardBg = $isAdmin ? 'border-red-100 ring-red-50' : 'border-primary-100 ring-primary-50';
                                ?>
                                <div class="relative z-10 flex gap-4" id="comentario-<?= $comentario['id'] ?>">
                                    <div class="w-8 h-8 rounded-full <?= $bgColor ?> flex-shrink-0 flex items-center justify-center text-white font-bold text-[10px] ring-4 ring-slate-50 shadow-sm transition-transform hover:scale-110">
                                        <?= strtoupper(substr($comentario['usuario_nombre'], 0, 2)) ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 ring-4 <?= $cardBg ?>">
                                            <div class="flex justify-between items-center mb-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-black text-slate-800"><?= htmlspecialchars($comentario['usuario_nombre']) ?></span>
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-tighter <?= $isAdmin ? 'bg-red-100 text-red-700' : 'bg-primary-100 text-primary-700' ?>">
                                                        <?= $comentario['rol_nombre'] ?>
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] text-slate-400 font-bold"><?= timeAgo($comentario['fecha_creacion']) ?></span>
                                                    <?php if (PermissionHelper::isAdmin()): ?>
                                                    <button onclick="eliminarComentario(<?= $comentario['id'] ?>)"
                                                            class="text-red-300 hover:text-red-500 transition-colors"
                                                            title="Eliminar comentario">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($comentario['comentario']) ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Formulario para agregar comentario -->
            <?php if (PermissionHelper::hasPermission('tickets.comments.add')): ?>
            <div class="p-4 bg-white border-t border-slate-200 mt-auto">
                <form id="formComentario">
                    <?= \App\Helpers\AuthHelper::getCsrfField() ?>
                    <div class="relative">
                        <textarea id="comentario"
                                  name="comentario"
                                  class="w-full bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-primary-500 focus:border-primary-500 p-4 pb-12 transition-all placeholder:text-slate-400 resize-none"
                                  placeholder="Añadir un comentario o nota interna..."
                                  rows="3"
                                  required
                                  minlength="5"
                                  maxlength="1000"></textarea>
                        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-[10px] text-slate-400">Mín. 5 caracteres</span>
                            </div>
                            <button type="submit" 
                                    class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-1.5 rounded-lg text-xs font-black uppercase tracking-wider flex items-center gap-2 transition-all shadow-md">
                                Enviar
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle de banderas en operaciones
    const toggleButtons = document.querySelectorAll('.btn-toggle-flag');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const opId = this.dataset.opId;
            const flag = this.dataset.flag;
            const tr = this.closest('tr');
            const badgeContainer = tr.querySelector(`.flag-badge[data-flag="${flag}"]`);
            
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
                    if (result.nuevo_valor) {
                        badgeContainer.innerHTML = `<span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black uppercase tracking-tight shadow-sm">Sí</span>`;
                    } else {
                        badgeContainer.innerHTML = `<span class="px-2.5 py-1 rounded-full bg-slate-200 text-slate-600 text-xs font-bold uppercase tracking-tight">No</span>`;
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

    // Verificación SAT del ticket principal
    const btnVerificarSat = document.getElementById('btnVerificarSat');
    if (btnVerificarSat) {
        btnVerificarSat.addEventListener('click', async function() {
            try {
                this.disabled = true;
                const originalContent = this.innerHTML;
                this.innerHTML = `
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
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

    // Validación SAT para operaciones individuales
    document.querySelectorAll('.btn-validate-sat-ops').forEach(button => {
        button.addEventListener('click', async function() {
            const opId = this.dataset.opId;
            
            try {
                this.disabled = true;
                const originalContent = this.innerHTML;
                this.innerHTML = `
                    <svg class="animate-spin w-5 h-5 text-warning-500" fill="none" viewBox="0 0 24 24">
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

    // Formulario de cambio de estado
    const statusForm = document.getElementById('statusForm');
    if (statusForm) {
        statusForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const select = document.getElementById('estadoSelect');
            const btn = this.querySelector('button[type="submit"]');
            const originalContent = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
            
            try {
                const formData = new FormData(this);
                const response = await fetch(`<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>/estado`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                if (response.ok) {
                    showToast('Estado actualizado correctamente', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error al actualizar estado', 'error');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión', 'error');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        });
    }

    // Formulario de agregar comentario
    const formComentario = document.getElementById('formComentario');
    if (formComentario) {
        formComentario.addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = formComentario.querySelector('button[type="submit"]');
            const btnOriginalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Enviando...
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

                    if (result.comentario) {
                        addComentarioToUI(result.comentario);
                    }

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
    
    // UUID Form
    const uuidForm = document.getElementById('uuidForm');
    if (uuidForm) {
        uuidForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = document.getElementById('btnGuardarUuid');
            btn.disabled = true;
            btn.textContent = 'Guardando...';

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

                if (response.ok && result.success) {
                    showToast(result.message || 'UUID actualizado correctamente', 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast(result.error || 'Error al guardar el UUID', 'error');
                    btn.disabled = false;
                    btn.textContent = 'Guardar';
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión con el servidor.', 'error');
                btn.disabled = false;
                btn.textContent = 'Guardar';
            }
        });
    }
});

// Función para agregar comentario al DOM
function addComentarioToUI(comentario) {
    const lista = document.getElementById('listaComentarios');
    const emptyState = lista.querySelector('.text-center');
    if (emptyState) {
        emptyState.remove();
    }

    const isAdmin = comentario.rol_nombre === 'Administrador';
    const bgColor = isAdmin ? 'bg-red-500' : 'bg-primary-600';
    const cardBorder = isAdmin ? 'border-red-100' : 'border-primary-100';
    const badgeClass = isAdmin ? 'bg-red-100 text-red-700' : 'bg-primary-100 text-primary-700';

    const html = `
        <div class="relative z-10 flex gap-4 animate-slide-in" id="comentario-${comentario.id}">
            <div class="w-8 h-8 rounded-full ${bgColor} flex-shrink-0 flex items-center justify-center text-white font-bold text-xs ring-4 ring-slate-50">
                ${comentario.usuario_nombre.substring(0, 2).toUpperCase()}
            </div>
            <div class="flex-1">
                <div class="bg-white p-4 rounded-xl shadow-sm border-2 ${cardBorder}">
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-slate-800">${comentario.usuario_nombre}</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold ${badgeClass}">
                                ${comentario.rol_nombre}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">hace un momento</span>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">${comentario.comentario}</p>
                </div>
            </div>
        </div>
    `;

    lista.insertAdjacentHTML('beforeend', html);
    
    // Scroll to new comment
    const activityStream = document.getElementById('activityStream');
    activityStream.scrollTop = activityStream.scrollHeight;
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
</script>
