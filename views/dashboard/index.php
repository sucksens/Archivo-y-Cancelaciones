<?php
use App\Helpers\PermissionHelper;

$empresaLabels = [
    'grupo_motormexa' => 'Grupo Motormexa',
    'automotriz_motormexa' => 'Automotriz Motormexa'
];
$tipoLabels = [
    'autos_nuevos' => 'Autos Nuevos',
    'seminuevos' => 'Seminuevos'
];
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Bienvenido al Sistema de Archivo de Facturas</h1>
    <p class="mt-1 text-sm text-gray-500">Gestiona y administra los archivos de facturas del grupo.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Facturas</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['total'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Activas</p>
                    <p class="text-2xl font-bold text-success-600"><?= $stats['activas'] ?? 0 ?></p>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($stats['por_empresa'])): ?>
    <?php foreach ($stats['por_empresa'] as $empresa => $cantidad): ?>
    <div class="card">
        <div class="card-body">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-gold-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-gold-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500"><?= $empresaLabels[$empresa] ?? $empresa ?></p>
                    <p class="text-2xl font-bold text-gold-600"><?= $cantidad ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (!empty($stats['por_tipo'])): ?>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
    <?php foreach ($stats['por_tipo'] as $tipo => $cantidad): ?>
    <div class="card">
        <div class="card-body">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 bg-primary-50 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700"><?= $tipoLabels[$tipo] ?? $tipo ?></p>
                        <p class="text-xl font-bold text-gray-900"><?= $cantidad ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Acciones Rapidas</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <?php if (PermissionHelper::hasPermission('facturas.upload')): ?>
                    <a href="<?= BASE_URL ?>facturas/subir" class="flex flex-col items-center p-6 rounded-lg bg-primary-50 text-primary-700 hover:bg-primary-100 transition-colors">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <span class="font-medium">Subir Factura</span>
                    </a>
                    <?php endif; ?>

                    <a href="<?= BASE_URL ?>facturas" class="flex flex-col items-center p-6 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="font-medium">Ver Facturas</span>
                    </a>

                    <a href="<?= BASE_URL ?>perfil" class="flex flex-col items-center p-6 rounded-lg bg-gray-50 text-gray-700 hover:bg-gray-100 transition-colors">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="font-medium">Mi Perfil</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="space-y-6">
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
