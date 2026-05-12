<?php
/**
 * Vista: Configuración de Email – Whitelist y Blacklist de dominios
 * Sistema de Tickets de Cancelación
 */

use App\Helpers\PermissionHelper;
?>

<div class="max-w-5xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Configuración de Email</h1>
            <p class="text-sm text-gray-500 mt-1">Gestiona los correos autorizados y los dominios bloqueados.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="mb-4 p-4 rounded-lg <?= $_SESSION['flash_type'] ?? 'bg-green-100 text-green-800' ?>">
        <?= htmlspecialchars($_SESSION['flash']) ?>
        <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- =====================================================
             WHITELIST DE CORREOS
             ===================================================== -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>Whitelist de Correos
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Correos explícitamente autorizados para recibir facturas. Tienen prioridad sobre la blacklist.</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <?= count($whitelist) ?> registros
                </span>
            </div>

            <!-- Formulario agregar correo -->
            <div class="px-6 py-4 border-b border-gray-100 bg-green-50">
                <form action="<?= BASE_URL ?>admin/email-config/whitelist" method="POST" class="flex flex-col gap-2">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div class="flex gap-2">
                        <input type="email" name="email" required
                               placeholder="correo@dominio.com"
                               class="flex-1 text-sm border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-md transition-colors whitespace-nowrap">
                            <i class="fas fa-plus mr-1"></i> Agregar
                        </button>
                    </div>
                    <input type="text" name="descripcion" placeholder="Descripción (opcional)"
                           class="text-sm border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-400">
                </form>
            </div>

            <!-- Tabla whitelist -->
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                <?php if (empty($whitelist)): ?>
                <div class="px-6 py-8 text-center text-sm text-gray-400 italic">
                    <i class="fas fa-inbox text-2xl mb-2 block"></i>
                    Sin correos en whitelist
                </div>
                <?php else: ?>
                <?php foreach ($whitelist as $entry): ?>
                <div class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            <?= htmlspecialchars($entry['email']) ?>
                        </p>
                        <?php if ($entry['descripcion']): ?>
                        <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($entry['descripcion']) ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-400">
                            Por <?= htmlspecialchars($entry['creado_por_nombre'] ?? 'Sistema') ?>
                            · <?= date('d/m/Y', strtotime($entry['creado_en'])) ?>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 ml-3 flex-shrink-0">
                        <!-- Toggle activo/inactivo -->
                        <?php if ($entry['activo']): ?>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Activo</span>
                        <?php else: ?>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Inactivo</span>
                        <?php endif; ?>

                        <form action="<?= BASE_URL ?>admin/email-config/whitelist/<?= $entry['id'] ?>/toggle" method="POST" style="display:inline">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" title="<?= $entry['activo'] ? 'Desactivar' : 'Activar' ?>"
                                    class="text-xs px-2 py-1 border rounded <?= $entry['activo'] ? 'border-yellow-400 text-yellow-600 hover:bg-yellow-50' : 'border-green-400 text-green-600 hover:bg-green-50' ?> transition-colors">
                                <i class="fas fa-<?= $entry['activo'] ? 'pause' : 'play' ?>"></i>
                            </button>
                        </form>

                        <form action="<?= BASE_URL ?>admin/email-config/whitelist/<?= $entry['id'] ?>/eliminar" method="POST" style="display:inline"
                              onsubmit="return confirm('¿Eliminar <?= htmlspecialchars($entry['email']) ?> de la whitelist?')">
                            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <button type="submit" title="Eliminar"
                                    class="text-xs px-2 py-1 border border-red-300 text-red-500 hover:bg-red-50 rounded transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- =====================================================
             BLACKLIST DE DOMINIOS
             ===================================================== -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">
                        <i class="fas fa-ban text-red-500 mr-2"></i>Blacklist de Dominios
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">Dominios bloqueados. Los correos de estos dominios son rechazados a menos que estén en whitelist.</p>
                </div>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <?= count($domainBlacklist) ?> registros
                </span>
            </div>

            <!-- Formulario agregar dominio -->
            <div class="px-6 py-4 border-b border-gray-100 bg-red-50">
                <form action="<?= BASE_URL ?>admin/email-config/blacklist" method="POST" class="flex flex-col gap-2">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                    <div class="flex gap-2">
                        <div class="flex flex-1 items-center border border-gray-300 rounded-md overflow-hidden focus-within:ring-2 focus-within:ring-red-400 bg-white">
                            <span class="px-2 text-sm text-gray-400 select-none">@</span>
                            <input type="text" name="dominio" required
                                   placeholder="ejemplo.com"
                                   class="flex-1 text-sm px-2 py-2 focus:outline-none">
                        </div>
                        <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-md transition-colors whitespace-nowrap">
                            <i class="fas fa-plus mr-1"></i> Bloquear
                        </button>
                    </div>
                    <input type="text" name="motivo" placeholder="Motivo (opcional)"
                           class="text-sm border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400">
                </form>
            </div>

            <!-- Tabla blacklist -->
            <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                <?php if (empty($domainBlacklist)): ?>
                <div class="px-6 py-8 text-center text-sm text-gray-400 italic">
                    <i class="fas fa-inbox text-2xl mb-2 block"></i>
                    Sin dominios bloqueados
                </div>
                <?php else: ?>
                <?php foreach ($domainBlacklist as $entry): ?>
                <div class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            <span class="text-red-400">@</span><?= htmlspecialchars($entry['dominio']) ?>
                        </p>
                        <?php if ($entry['motivo']): ?>
                        <p class="text-xs text-gray-400 truncate"><?= htmlspecialchars($entry['motivo']) ?></p>
                        <?php endif; ?>
                        <p class="text-xs text-gray-400">
                            Por <?= htmlspecialchars($entry['bloqueado_por_nombre'] ?? 'Sistema') ?>
                            · <?= date('d/m/Y', strtotime($entry['creado_en'])) ?>
                        </p>
                    </div>
                    <form action="<?= BASE_URL ?>admin/email-config/blacklist/<?= $entry['id'] ?>/eliminar" method="POST" style="display:inline"
                          onsubmit="return confirm('¿Desbloquear el dominio @<?= htmlspecialchars($entry['dominio']) ?>?')">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <button type="submit" title="Eliminar de blacklist"
                                class="text-xs px-2 py-1 border border-red-300 text-red-500 hover:bg-red-50 rounded transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Nota informativa -->
    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
        <i class="fas fa-info-circle mr-2"></i>
        <strong>Lógica de validación:</strong>
        Si un correo está en la whitelist <strong>activa</strong>, puede recibir facturas aunque su dominio esté bloqueado.
        Si no está en whitelist, se rechaza aunque el dominio no esté en blacklist (política restrictiva).
    </div>
</div>
