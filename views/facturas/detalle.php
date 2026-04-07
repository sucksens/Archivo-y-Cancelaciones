<?php
/**
 * Vista: Detalle de Factura
 * Sistema de Tickets de Cancelación
 */

use App\Helpers\PermissionHelper;

$title = $title ?? 'Detalle de Factura';
$factura = $factura ?? [];
$canDownload = $canDownload ?? false;
$canDelete = $canDelete ?? false;
?>

<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="<?= BASE_URL ?>facturas" class="text-primary-600 hover:text-primary-800 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Facturas
        </a>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="mb-4 p-4 rounded-lg <?= $_SESSION['flash_type'] ?? 'bg-green-100 text-green-800' ?>">
        <?= htmlspecialchars($_SESSION['flash']) ?>
        <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Detalle de Factura</h1>
                <p class="text-sm text-gray-600">UUID: <span class="font-mono"><?= htmlspecialchars($factura['uuid_factura']) ?></span></p>
            </div>
            <div class="flex space-x-3">
                <?php if ($canDownload): ?>
                <?php if ($factura['archivo_xml']): ?>
                <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/xml" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors">
                    <i class="fas fa-file-code mr-2"></i>XML
                </a>
                <?php endif; ?>
                <?php if ($factura['archivo_pdf']): ?>
                <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/pdf" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors">
                    <i class="fas fa-file-pdf mr-2"></i>PDF
                </a>
                <?php endif; ?>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                <form action="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta factura? Esta acción no se puede deshacer.');" style="display: inline;">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors">
                        <i class="fas fa-trash mr-2"></i>Eliminar
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Información de la Factura</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Empresa</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    <?= $factura['empresa'] === 'grupo_motormexa' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' ?>">
                                    <?= htmlspecialchars($empresas[$factura['empresa']] ?? $factura['empresa']) ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Tipo</label>
                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($tipos_auto[$factura['tipo_factura']] ?? $factura['tipo_factura']) ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Serie</label>
                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($factura['serie'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Folio</label>
                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($factura['folio'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Total</label>
                            <p class="mt-1 text-lg font-bold text-gray-900">
                                <?php if ($factura['total']): ?>
                                $<?= number_format($factura['total'], 2) ?>
                                <?php else: ?>
                                <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Fecha de Emisión</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <?= $factura['fecha_emision'] ? date('d/m/Y', strtotime($factura['fecha_emision'])) : '-' ?>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">RFC Emisor</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono"><?= htmlspecialchars($factura['rfc_emisor'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">RFC Receptor</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono"><?= htmlspecialchars($factura['rfc_receptor'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Datos BBj</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Sucursal (ID_SUC)</label>
                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($factura['id_suc'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Vendedor (ID_VENDEDOR)</label>
                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($factura['id_vendedor'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Inventario</label>
                            <p class="mt-1 text-sm text-gray-900"><?= htmlspecialchars($factura['inventario'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Fecha FAC (BBj)</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <?= $factura['fecfac'] ? date('d/m/Y', strtotime($factura['fecfac'])) : '-' ?>
                            </p>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-4 mt-8">Archivos</h3>
                    <div class="space-y-2">
                        <?php if ($factura['archivo_xml']): ?>
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <i class="fas fa-file-code text-blue-600 mr-3"></i>
                                <span class="text-sm text-blue-800">XML</span>
                            </div>
                            <?php if ($canDownload): ?>
                            <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/xml" class="text-blue-600 hover:text-blue-800" title="Descargar">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($factura['archivo_pdf']): ?>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                            <div class="flex items-center">
                                <i class="fas fa-file-pdf text-red-600 mr-3"></i>
                                <span class="text-sm text-red-800">PDF</span>
                            </div>
                            <?php if ($canDownload): ?>
                            <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/pdf" class="text-red-600 hover:text-red-800" title="Descargar">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-500 italic">No hay PDF disponible</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Información de Subida</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Usuario</label>
                        <p class="mt-1 text-gray-900"><?= htmlspecialchars($factura['usuario_nombre'] ?? 'Desconocido') ?></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Fecha de Subida</label>
                        <p class="mt-1 text-gray-900">
                            <?= $factura['fecha_subida'] ? date('d/m/Y H:i', strtotime($factura['fecha_subida'])) : '-' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
