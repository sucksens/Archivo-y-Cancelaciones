<?php
/**
 * Vista: Detalle de Factura
 * Sistema de Tickets de Cancelación
 */

use App\Helpers\PermissionHelper;

$title = $title ?? 'Detalle de Factura';
$factura = $factura ?? [];
$canDownload = $canDownload ?? false;
$canDownloadAll = $canDownloadAll ?? false;
$canDownloadVendedor = $canDownloadVendedor ?? false;
$userVendedor = $userVendedor ?? null;
$canDelete = $canDelete ?? false;
$canSendEmail = $canSendEmail ?? false;
$enviosEmail = $enviosEmail ?? [];
?>

<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="<?= BASE_URL ?>facturas" class="btn btn-secondary inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i> <- Volver a Facturas
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
                <?php if ($canSendEmail): ?>
                <button type="button" onclick="document.getElementById('modal-email').classList.remove('hidden')"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors text-sm">
                    <i class="fas fa-envelope mr-2"></i>Enviar por Email
                </button>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                <form action="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta factura? Esta acción no se puede deshacer.');" style="display: inline;">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors text-sm">
                        <i class="fas fa-trash mr-2"></i>Eliminar Factura
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
                                    <?= $factura['empresa'] === 'grupo_motormexa' ? 'bg-blue-100 text-blue-800' : 
                                       ($factura['empresa'] === 'ambas' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800') ?>">
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
                            <label class="block text-sm font-medium text-gray-500">Sucursal</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <?php 
                                    $sucursales = [
                                        '01' => 'Vallarta',
                                        '03' => 'Acueducto',
                                        '05' => 'Country'
                                    ];
                                    echo htmlspecialchars($sucursales[$factura['id_suc']] ?? $factura['id_suc'] ?? '-');
                                ?>
                            </p>
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
                    <div class="space-y-3">
                        <?php if ($factura['archivo_xml']): ?>
                        <div>
                            <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/xml"
                               class="download-btn w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors"
                               data-tipo="xml"
                               data-id-vendedor="<?= htmlspecialchars($factura['id_vendedor'] ?? '') ?>">
                                <i class="fas fa-file-code mr-2"></i> Descargar XML
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($factura['archivo_pdf']): ?>
                        <div>
                            <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/pdf"
                               class="download-btn w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors"
                               data-tipo="pdf"
                               data-id-vendedor="<?= htmlspecialchars($factura['id_vendedor'] ?? '') ?>">
                                <i class="fas fa-file-pdf mr-2"></i> Descargar PDF
                            </a>
                        </div>
                        <?php else: ?>
                        <p class="text-sm text-gray-500 italic">No hay PDF disponible</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <script>
            // Configuración de permisos desde PHP
            const canDownloadAll = <?= $canDownloadAll ? 'true' : 'false' ?>;
            const canDownloadVendedor = <?= $canDownloadVendedor ? 'true' : 'false' ?>;
            const userVendedor = <?= json_encode($userVendedor) ?>;
            
            // Validar descarga antes de navegar
            document.querySelectorAll('.download-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    // Si tiene permiso de descargar todas las facturas, permitir
                    if (canDownloadAll) {
                        return true;
                    }
                    
                    // Si tiene permiso de vendedor, verificar el id_vendedor de la factura
                    if (canDownloadVendedor) {
                        const facturaVendedor = this.getAttribute('data-id-vendedor');
                        
                        if (!userVendedor) {
                            e.preventDefault();
                            mostrarError('No tienes un vendedor asignado. Contacta al administrador.');
                            return false;
                        }
                        
                        if (facturaVendedor !== userVendedor) {
                            e.preventDefault();
                            mostrarError('No tienes permiso para descargar facturas de este vendedor.<br><br><strong>Vendedor de la factura:</strong> ' + facturaVendedor + '<br><strong>Tu vendedor asignado:</strong> ' + userVendedor);
                            return false;
                        }
                        
                        return true;
                    }
                    
                    // Si no tiene ningún permiso de descarga
                    e.preventDefault();
                    mostrarError('No tienes permiso para descargar archivos.');
                    return false;
                });
            });
            
            // Función para mostrar mensajes de error en el frontend
            function mostrarError(mensaje) {
                // Eliminar mensaje anterior si existe
                const mensajeAnterior = document.getElementById('error-descarga-mensaje');
                if (mensajeAnterior) {
                    mensajeAnterior.remove();
                }
                
                // Crear elemento de mensaje
                const mensajeDiv = document.createElement('div');
                mensajeDiv.id = 'error-descarga-mensaje';
                mensajeDiv.className = 'mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-300';
                mensajeDiv.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <div>
                            <p class="font-semibold">Error de permiso</p>
                            <p class="text-sm mt-1">${mensaje}</p>
                        </div>
                    </div>
                `;
                
                // Insertar el mensaje después del contenedor principal
                const mainContainer = document.querySelector('.max-w-5xl');
                if (mainContainer) {
                    mainContainer.insertBefore(mensajeDiv, mainContainer.firstChild);
                }
                
                // Scroll hacia arriba para mostrar el mensaje
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Eliminar el mensaje después de 5 segundos
                setTimeout(() => {
                    if (mensajeDiv) {
                        mensajeDiv.remove();
                    }
                }, 5000);
            }
            </script>

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

<?php if ($canSendEmail): ?>
<!-- =====================================================
     MODAL: Enviar factura por email
     ===================================================== -->
<div id="modal-email" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="px-6 py-4 bg-indigo-600 text-white flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold"><i class="fas fa-envelope mr-2"></i>Enviar Factura por Email</h3>
                <p class="text-xs text-indigo-200 mt-0.5">Se adjuntarán el XML y el PDF de la factura</p>
            </div>
            <button onclick="document.getElementById('modal-email').classList.add('hidden')" class="text-white hover:text-indigo-200 text-xl font-bold">&times;</button>
        </div>
        <form action="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/email" method="POST" class="px-6 py-5 space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correo destinatario <span class="text-red-500">*</span></label>
                <input type="email" name="email_destino" required
                       placeholder="cliente@empresa.com"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <p class="text-xs text-gray-400 mt-1">Solo correos en la whitelist autorizada pueden recibir facturas.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Asunto</label>
                <input type="text" name="asunto"
                       value="Factura <?= htmlspecialchars(($factura['serie'] ?? '') . '-' . ($factura['folio'] ?? '')) ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mensaje (opcional)</label>
                <textarea name="mensaje_cuerpo" rows="3"
                          placeholder="Se adjuntan los archivos de la factura."
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-email').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors font-semibold">
                    <i class="fas fa-paper-plane mr-1"></i>Enviar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($enviosEmail)): ?>
<!-- =====================================================
     HISTORIAL DE ENVÍOS POR EMAIL
     ===================================================== -->
<div class="max-w-5xl mx-auto mt-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-base font-semibold text-gray-800"><i class="fas fa-history mr-2 text-gray-400"></i>Historial de Envíos por Email</h3>
        </div>
        <div class="divide-y divide-gray-100">
            <?php foreach ($enviosEmail as $envio): ?>
            <div class="px-6 py-3 flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($envio['email_destino']) ?></p>
                    <p class="text-xs text-gray-500"><?= htmlspecialchars($envio['asunto']) ?></p>
                    <?php if ($envio['detalle']): ?>
                    <p class="text-xs text-gray-400 mt-0.5 truncate"><?= htmlspecialchars($envio['detalle']) ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Por <?= htmlspecialchars($envio['usuario_nombre'] ?? 'Sistema') ?>
                        · <?= date('d/m/Y H:i', strtotime($envio['enviado_en'])) ?>
                        <?php if ($envio['id_operacion_api']): ?>
                        · <span class="font-mono text-gray-300"><?= htmlspecialchars($envio['id_operacion_api']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <?php
                    $badgeClass = match($envio['resultado']) {
                        'enviado'  => 'bg-green-100 text-green-800',
                        'bloqueado'=> 'bg-yellow-100 text-yellow-800',
                        default    => 'bg-red-100 text-red-800',
                    };
                    $icon = match($envio['resultado']) {
                        'enviado'  => 'check-circle',
                        'bloqueado'=> 'ban',
                        default    => 'exclamation-circle',
                    };
                    ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $badgeClass ?>">
                        <i class="fas fa-<?= $icon ?> mr-1"></i><?= ucfirst($envio['resultado']) ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
