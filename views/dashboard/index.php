<?php
use App\Helpers\PermissionHelper;
?>

<!-- Encabezado de Tickets Totales -->
 <div class = "header">
    <h1 class = "text-xl font-bold text-gray-900">Tickets Totales: <?= $stats['total'] ?? 0?><br><br></h1>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
    <!-- Pendientes -->
    <?php if (PermissionHelper::hasPermission('tickets.view.all')): ?>
    <a href="<?= BASE_URL ?>tickets?estado=pendiente">
    <?php elseif (PermissionHelper::isConsulta()): ?>
    <a href="<?= BASE_URL ?>solicitudes?estado=pendiente">
    <?php else: ?>
    <a href="<?= BASE_URL ?>mis-solicitudes?estado=pendiente">   
        <div class="card">
            <div class="card-body">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-12 h-12 bg-gold-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gold-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pendientes</p>
                        <p class="text-2xl font-bold text-gold-600"><?= $stats['pendientes'] ?? 0 ?></p>
                    </div>
                </div>
            </div>
        </div>
    </a>
    </a>
    </a>
    
    <!-- En Revision -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">En Revision</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['en_revision'] ?? 0?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- En Proceso -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-warning-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">En Proceso</p>
                    <p class="text-2xl font-bold text-warning-600"><?=$stats['en_proceso'] ?? 0?></p>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Liberados -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.67 13 l 0 -6 m 0 9 l 0 0 m 9 -4 a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Liberados</p>
                    <p class="text-2xl font-bold text-purple-600"><?= $stats['liberados'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rechazados -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.67 9 l 6 6 m -6 0 l 6 -6 m 0 9 m 6 -6 a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Rechazados</p>
                    <p class="text-2xl font-bold text-red-600"><?= $stats['rechazados'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancelados -->
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Completados</p>
                    <p class="text-2xl font-bold text-success-600"><?= ($stats['completados'] ?? 0) + ($stats['cancelados'] ?? 0) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Tickets Recientes -->
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Tickets Recientes</h3>
                <?php if (PermissionHelper::hasPermission('tickets.view.all')): ?>
                <a href="<?= BASE_URL ?>tickets" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Ver todos →
                </a>
                <?php elseif (PermissionHelper::isConsulta()): ?>
                <a href="<?= BASE_URL ?>solicitudes" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Ver todos →
                </a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>mis-solicitudes" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                    Ver todos →
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentTickets)): ?>
                <div class="p-6 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>No hay tickets registrados</p>
                    <?php if (PermissionHelper::hasPermission('tickets.create')): ?>
                    <a href="<?= BASE_URL ?>tickets/crear" class="inline-block mt-4 btn btn-primary">
                        Crear primer ticket
                    </a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recentTickets as $ticket): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($ticket['nombre_cliente']) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= htmlspecialchars($ticket['usuario_nombre'] ?? 'Usuario') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                    $estadoInfo = $estados[$ticket['estado']] ?? ['label' => $ticket['estado'], 'color' => 'gray'];
                                    ?>
                                    <span class="badge badge-<?= $estadoInfo['color'] ?>">
                                        <?= $estadoInfo['label'] ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= BASE_URL ?>tickets/<?= $ticket['id'] ?>" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Panel lateral -->
    <div class="space-y-6">
        
        <!-- Acciones Rápidas -->
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Acciones Rápidas</h3>
            </div>
            <div class="card-body space-y-3">
                <?php if (PermissionHelper::hasPermission('tickets.create')): ?>
                <a href="<?= BASE_URL ?>tickets/crear" class="flex items-center p-3 rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="font-medium">Nuevo Ticket</span>
                </a>
                <?php endif; ?>
                
                <?php if (PermissionHelper::isConsulta()): ?>
                <a href="<?= BASE_URL ?>solicitudes" class="flex items-center p-3 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="font-medium">Solicitudes</span>
                </a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>mis-solicitudes" class="flex items-center p-3 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="font-medium">Mis Solicitudes</span>
                </a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>perfil" class="flex items-center p-3 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-medium">Mi Perfil</span>
                </a>
            </div>
        </div>
        
        <!-- Actividad Reciente (solo admin) -->
        <?php if (!empty($recentLogs) && PermissionHelper::hasPermission('reports.logs')): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="text-lg font-semibold text-gray-900">Actividad Reciente</h3>
            </div>
            <div class="card-body p-0">
                <div class="divide-y divide-gray-100">
                    <?php foreach (array_slice($recentLogs, 0, 5) as $log): ?>
                    <div class="p-4 hover:bg-gray-50">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <?php if ($log['tipo_log'] === 'login'): ?>
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                </svg>
                                <?php elseif ($log['tipo_log'] === 'error'): ?>
                                <svg class="w-4 h-4 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <?php else: ?>
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 truncate"><?= htmlspecialchars($log['accion']) ?></p>
                                <p class="text-xs text-gray-500">
                                    <?= htmlspecialchars($log['nombre_completo'] ?? 'Sistema') ?> · 
                                    <?= date('d/m H:i', strtotime($log['fecha'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>
