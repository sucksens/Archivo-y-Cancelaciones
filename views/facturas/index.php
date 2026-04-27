<?php
/**
 * Vista: Lista de Facturas
 * Sistema de Tickets de Cancelación
 */

use App\Helpers\PermissionHelper;

$title = $title ?? 'Archivos de Facturas';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($title) ?></h1>
            <p class="text-gray-600">Gestiona los archivos de facturas</p>
        </div>
        <?php if (PermissionHelper::hasPermission('facturas.upload')): ?>
        <a href="<?= BASE_URL ?>facturas/subir" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors">
            <i class="fas fa-upload mr-2"></i>Subir Factura
        </a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="mb-4 p-4 rounded-lg <?= $_SESSION['flash_type'] ?? 'bg-green-100 text-green-800' ?>">
        <?= htmlspecialchars($_SESSION['flash']) ?>
        <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <form method="GET" action="<?= BASE_URL ?>facturas" class="p-4 border-b border-gray-200">
            <div class="flex flex-wrap gap-4">
                <?php if (PermissionHelper::hasPermission('facturas.view.all')): ?>
                <div>
                    <label for="empresa" class="block text-sm font-medium text-gray-700 mb-1">Empresa</label>
                    <select name="empresa" id="empresa" class="form-select rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todas las empresas</option>
                        <?php foreach ($empresas as $key => $name): ?>
                        <option value="<?= $key ?>" <?= ($filters['empresa'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php if (!($isConsultaWithEspecialidad ?? false)): ?>
                <div>
                    <label for="tipo_factura" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo_factura" id="tipo_factura" class="form-select rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tipos_auto as $key => $name): ?>
                        <option value="<?= $key ?>" <?= ($filters['tipo_factura'] ?? '') === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                <!-- Filtro deshabilitado para rol Consulta con especialidad -->
                <div>
                    <label for="tipo_factura" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="tipo_factura" id="tipo_factura" class="form-select rounded-lg border-gray-300 bg-gray-100 cursor-not-allowed" disabled>
                        <option value="<?= $filters['tipo_factura'] ?? '' ?>" selected>
                            <?= htmlspecialchars($userEspecialidadLabel) ?>
                        </option>
                    </select>
                </div>
                <?php endif; ?>
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="search" id="search" placeholder="UUID, serie, folio..." 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                           class="form-input rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                </div>
            </div>
        </form>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inventario</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serie/Folio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Archivos</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($facturas)): ?>
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-folder-open text-4xl mb-4 text-gray-300"></i>
                        <p>No se encontraron facturas</p>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($facturas as $factura): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            <?= $factura['empresa'] === 'grupo_motormexa' ? 'bg-blue-100 text-blue-800' : 
                               ($factura['empresa'] === 'ambas' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800') ?>">
                            <?= htmlspecialchars($empresas[$factura['empresa']] ?? $factura['empresa']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>" class="font-mono text-primary-600 hover:text-primary-900 transition-colors" title="<?= $factura['inventario'] ?>">
                            <?= $factura['inventario'] ?>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= htmlspecialchars($tipos_auto[$factura['tipo_factura']] ?? $factura['tipo_factura']) ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= htmlspecialchars($factura['serie'] ?? '-') ?>/<?= htmlspecialchars($factura['folio'] ?? '-') ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?php if ($factura['total']): ?>
                        $<?= number_format($factura['total'], 2) ?>
                        <?php else: ?>
                        <span class="text-gray-400">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <?= $factura['fecha_subida'] ? date('d/m/Y', strtotime($factura['fecha_subida'])) : '-' ?>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex space-x-1">
                            <?php if ($factura['archivo_xml']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                XML
                            </span>
                            <?php endif; ?>
                            <?php if ($factura['archivo_pdf']): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                PDF
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>" class="text-primary-600 hover:text-primary-900 font-medium mr-3">
                            Ver detalle
                        </a>
                        <?php if (PermissionHelper::hasPermission('facturas.download')): ?>
                        <?php if ($factura['archivo_xml']): ?>
                        <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/xml" class="text-red-600 hover:text-red-900 mr-3" title="Descargar XML">
                            <i class="fas fa-file-code"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($factura['archivo_pdf']): ?>
                        <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/pdf" class="text-blue-600 hover:text-blue-900" title="Descargar PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($pagination['pages'] > 1): ?>
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Mostrando <span class="font-medium"><?= (($pagination['page'] - 1) * $pagination['limit']) + 1 ?></span>
                        a <span class="font-medium"><?= min($pagination['page'] * $pagination['limit'], $pagination['total']) ?></span>
                        de <span class="font-medium"><?= $pagination['total'] ?></span> resultados
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($pagination['page'] > 1): ?>
                        <a href="<?= BASE_URL ?>facturas?page=<?= $pagination['page'] - 1 ?>&<?= http_build_query(array_filter(['empresa' => $filters['empresa'] ?? '', 'tipo_factura' => $filters['tipo_factura'] ?? '', 'search' => $filters['search'] ?? ''])) ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                        <a href="<?= BASE_URL ?>facturas?page=<?= $i ?>&<?= http_build_query(array_filter(['empresa' => $filters['empresa'] ?? '', 'tipo_factura' => $filters['tipo_factura'] ?? '', 'search' => $filters['search'] ?? ''])) ?>" 
                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium <?= $i === $pagination['page'] ? 'z-10 bg-primary-50 border-primary-500 text-primary-600' : 'text-gray-500 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                        <?php endfor; ?>
                        <?php if ($pagination['page'] < $pagination['pages']): ?>
                        <a href="<?= BASE_URL ?>facturas?page=<?= $pagination['page'] + 1 ?>&<?= http_build_query(array_filter(['empresa' => $filters['empresa'] ?? '', 'tipo_factura' => $filters['tipo_factura'] ?? '', 'search' => $filters['search'] ?? ''])) ?>" 
                           class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
