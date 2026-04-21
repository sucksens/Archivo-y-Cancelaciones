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

<div class="max-w-6xl mx-auto md:p-8">
    <!-- Navigation Actions -->
    <div class="mb-6">
        <a href="<?= BASE_URL ?>facturas"
            class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-linecap="round" stroke-linejoin="round"
                    stroke-width="2"></path>
            </svg>
            Volver a Facturas
        </a>
    </div>

    <?php if (isset($_SESSION['flash'])): ?>
    <div class="mb-4 p-4 rounded-lg <?= $_SESSION['flash_type'] ?? 'bg-green-100 text-green-800 border border-green-300' ?>">
        <?= htmlspecialchars($_SESSION['flash']) ?>
        <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
    </div>
    <?php endif; ?>

    <!-- BEGIN: InvoiceDetailsCard -->
    <section class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-8"
        data-purpose="invoice-main-card">
        <!-- Card Header -->
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Detalle de Factura</h2>
                <p class="text-sm text-slate-500 mt-1">UUID: <span
                        class="font-mono text-xs"><?= htmlspecialchars($factura['uuid_factura']) ?></span></p>
            </div>
            <div class="flex flex-wrap gap-3">
                <?php if ($canSendEmail): ?>
                <button type="button" onclick="document.getElementById('modal-email').classList.remove('hidden')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all">
                    Enviar por Email
                </button>
                <?php endif; ?>
                <?php if ($canDelete): ?>
                <form action="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta factura? Esta acción no se puede deshacer.');" style="display: inline;">
                    <input type="hidden" name="_method" value="DELETE">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition-all">
                        Eliminar Factura
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <!-- Card Body Content -->
        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Column 1: Informacion de la Factura -->
            <div class="lg:col-span-2 space-y-6">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-400 mb-6 flex items-center">
                        <span class="w-8 h-px bg-slate-200 mr-3"></span>
                        Información de la Factura
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-tight">Empresa</label>
                            <p class="mt-1 text-base font-semibold inline-flex px-2 py-0.5 rounded <?= $factura['empresa'] === 'grupo_motormexa' ? 'text-blue-700 bg-blue-50' : ($factura['empresa'] === 'ambas' ? 'text-purple-700 bg-purple-50' : 'text-red-700 bg-red-50') ?>">
                                <?= htmlspecialchars($empresas[$factura['empresa']] ?? $factura['empresa']) ?>
                            </p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-tight">Tipo</label>
                            <p class="mt-1 text-base font-medium text-slate-700"><?= htmlspecialchars($tipos_auto[$factura['tipo_factura']] ?? $factura['tipo_factura']) ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-tight">Serie / Folio</label>
                            <p class="mt-1 text-base font-medium text-slate-700"><?= htmlspecialchars($factura['serie'] ?? '-') ?> <?= $factura['folio'] ? ' / ' . htmlspecialchars($factura['folio']) : '' ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-tight">Fecha de Emisión</label>
                            <p class="mt-1 text-base font-medium text-slate-700"><?= $factura['fecha_emision'] ? date('d/m/Y', strtotime($factura['fecha_emision'])) : '-' ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-tight">RFC Emisor</label>
                            <p class="mt-1 text-base font-mono text-slate-600"><?= htmlspecialchars($factura['rfc_emisor'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-tight">RFC Receptor</label>
                            <p class="mt-1 text-base font-mono text-slate-600"><?= htmlspecialchars($factura['rfc_receptor'] ?? '-') ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 rounded-xl p-5 border border-slate-100 flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Total Facturado</label>
                        <?php if ($factura['total']): ?>
                        <p class="text-3xl font-black text-slate-900 mt-1">$<?= number_format($factura['total'], 2) ?></p>
                        <?php else: ?>
                        <p class="text-3xl font-black text-slate-400 mt-1">-</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($factura['total']): ?>
                    <div class="text-right hidden sm:block">
                        <span class="text-xs font-bold text-slate-400 uppercase block mb-1">Moneda</span>
                        <span class="px-2 py-1 bg-white border border-slate-200 rounded text-xs font-bold text-slate-600">MXN</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Column 2: Datos BBj & Archivos -->
            <div class="space-y-8 lg:border-l lg:pl-8 border-slate-100">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-400 mb-6 flex items-center">
                        <span class="w-8 h-px bg-slate-200 mr-3"></span>
                        Datos de Sistema (BBj)
                    </h3>
                    <div class="grid grid-cols-2 gap-y-4 gap-x-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase">Sucursal</label>
                            <p class="text-base font-medium text-slate-700">
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
                            <label class="block text-xs font-bold text-slate-400 uppercase">Vendedor</label>
                            <p class="text-base font-medium text-slate-700"><?= htmlspecialchars($factura['id_vendedor'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase">Inventario</label>
                            <p class="text-base font-medium text-slate-700"><?= htmlspecialchars($factura['inventario'] ?? '-') ?></p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase">Fecha FAC</label>
                            <p class="text-base font-medium text-slate-700"><?= $factura['fecfac'] ? date('d/m/Y', strtotime($factura['fecfac'])) : '-' ?></p>
                        </div>
                    </div>
                </div>
                <div class="pt-2">
                    <h3 class="text-sm font-bold uppercase tracking-widest text-slate-400 mb-4">Archivos Disponibles</h3>
                    <div class="grid grid-cols-1 gap-3">
                        <?php if ($factura['archivo_xml']): ?>
                        <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/xml"
                            class="download-btn group flex items-center p-3 bg-white border border-blue-100 rounded-lg hover:bg-blue-50 transition-all shadow-sm"
                            data-tipo="xml"
                            data-id-vendedor="<?= htmlspecialchars($factura['id_vendedor'] ?? '') ?>"
                            data-rfc-receptor="<?= htmlspecialchars($factura['rfc_receptor'] ?? '') ?>">
                            <div class="w-10 h-10 rounded bg-blue-100 text-blue-600 flex items-center justify-center mr-3 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-base font-bold text-slate-700">Factura XML</p>
                                <p class="text-xs text-slate-400 uppercase">Descargar comprobante</p>
                            </div>
                        </a>
                        <?php endif; ?>

                        <?php if ($factura['archivo_pdf']): ?>
                        <a href="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/descargar/pdf"
                            class="download-btn group flex items-center p-3 bg-white border border-red-100 rounded-lg hover:bg-red-50 transition-all shadow-sm"
                            data-tipo="pdf"
                            data-id-vendedor="<?= htmlspecialchars($factura['id_vendedor'] ?? '') ?>"
                            data-rfc-receptor="<?= htmlspecialchars($factura['rfc_receptor'] ?? '') ?>">
                            <div class="w-10 h-10 rounded bg-red-100 text-red-600 flex items-center justify-center mr-3 group-hover:bg-red-600 group-hover:text-white transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-base font-bold text-slate-700">Factura PDF</p>
                                <p class="text-xs text-slate-400 uppercase">Vista de impresión</p>
                            </div>
                        </a>
                        <?php endif; ?>
                        
                        <?php if (!$factura['archivo_xml'] && !$factura['archivo_pdf']): ?>
                        <p class="text-sm text-gray-500 italic">No hay archivos disponibles</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <!-- Card Footer Section: Upload Info -->
        <div class="p-6 bg-slate-50 border-t border-slate-200">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Información de Subida</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">Usuario</label>
                    <p class="text-base font-medium text-slate-700"><?= htmlspecialchars($factura['usuario_nombre'] ?? 'Desconocido') ?></p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase">Fecha de Subida</label>
                    <p class="text-base font-medium text-slate-700"><?= $factura['fecha_subida'] ? date('d/m/Y H:i', strtotime($factura['fecha_subida'])) : '-' ?></p>
                </div>
            </div>
        </div>
    </section>
    <!-- END: InvoiceDetailsCard -->

    <?php if (!empty($enviosEmail)): ?>
    <!-- BEGIN: EmailHistory -->
    <section class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" data-purpose="email-history-section">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
            <div>
                <h3 class="text-sm font-bold uppercase tracking-widest text-slate-600">Historial de Envíos por Email</h3>
                <p class="text-sm text-slate-400 mt-1">Registro detallado de notificaciones enviadas a clientes</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Destinatario</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Fecha y Hora</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-xs font-bold text-slate-400 uppercase tracking-wider text-right">Detalle / ID</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($enviosEmail as $envio): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($envio['email_destino']) ?></span>
                                <span class="text-xs text-slate-400">Asunto: <?= htmlspecialchars($envio['asunto']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-base text-slate-600"><?= date('d/m/Y', strtotime($envio['enviado_en'])) ?></span>
                                <span class="text-xs text-slate-400"><?= date('h:i A', strtotime($envio['enviado_en'])) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php
                            $badgeClasses = [
                                'enviado'   => 'bg-green-100 text-green-700',
                                'bloqueado' => 'bg-yellow-100 text-yellow-700',
                            ];
                            $badgeClass = $badgeClasses[$envio['resultado']] ?? 'bg-red-100 text-red-700';
                            $dotClasses = [
                                'enviado'   => 'bg-green-500',
                                'bloqueado' => 'bg-yellow-500',
                            ];
                            $dotClass = $dotClasses[$envio['resultado']] ?? 'bg-red-500';
                            ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-sm font-bold uppercase tracking-tighter <?= $badgeClass ?>">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 <?= $dotClass ?>"></span>
                                <?= ucfirst($envio['resultado']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <?php if ($envio['id_operacion_api']): ?>
                            <span class="font-mono text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded border border-slate-100" title="<?= htmlspecialchars($envio['id_operacion_api']) ?>">
                                <?= htmlspecialchars(substr($envio['id_operacion_api'], 0, 8) . '...') ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($envio['detalle']): ?>
                            <span class="text-xs text-slate-400 block mt-1" title="<?= htmlspecialchars($envio['detalle']) ?>">
                                <?= htmlspecialchars((strlen($envio['detalle']) > 30) ? substr($envio['detalle'], 0, 30) . '...' : $envio['detalle']) ?>
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <!-- END: EmailHistory -->
    <?php endif; ?>

</div>

<script>
// Configuración de permisos desde PHP
const canDownloadAll = <?= $canDownloadAll ? 'true' : 'false' ?>;
const canDownloadVendedor = <?= $canDownloadVendedor ? 'true' : 'false' ?>;
const canDownloadNR = <?= $canDownloadNR ? 'true' : 'false' ?>;
const userVendedor = <?= json_encode($userVendedor) ?>;
const rfcNR = <?= json_encode($rfcNR ?? null) ?>;

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
            
            if (userVendedor && facturaVendedor === userVendedor) {
                return true;
            }
            
            // Si NO tiene permiso de NR, mostrar error aquí
            if (!canDownloadNR) {
                e.preventDefault();
                if (!userVendedor) {
                    mostrarError('No tienes un vendedor asignado. Contacta al administrador.');
                } else {
                    mostrarError('No tienes permiso para descargar facturas de este vendedor.<br><br><strong>Vendedor de la factura:</strong> ' + facturaVendedor + '<br><strong>Tu vendedor asignado:</strong> ' + userVendedor);
                }
                return false;
            }
        }

        // Si tiene permiso de NR, verificar el RFC receptor
        if (canDownloadNR && rfcNR) {
            const facturaRfc = this.getAttribute('data-rfc-receptor');
            
            if (facturaRfc === rfcNR) {
                return true;
            }
            
            // Si ya pasó por vendedor y falló, o si solo tiene NR
            e.preventDefault();
            mostrarError('No tienes permiso para descargar esta factura.<br><br>Solo puedes descargar facturas de <strong>NR Finance</strong> o de tu vendedor asignado.');
            return false;
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
    mensajeDiv.className = 'mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-300 shadow-sm';
    mensajeDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="h-6 w-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <p class="font-bold text-sm">Error de permiso</p>
                <p class="text-xs mt-1">${mensaje}</p>
            </div>
        </div>
    `;
    
    // Insertar el mensaje después del contenedor principal
    const mainContainer = document.querySelector('.max-w-6xl');
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

<?php if ($canSendEmail): ?>
<!-- =====================================================
     MODAL: Enviar factura por email
     ===================================================== -->
<div id="modal-email" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200">
        <div class="px-6 py-4 bg-indigo-600 text-white flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold flex items-center">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Enviar Factura por Email
                </h3>
                <p class="text-xs text-indigo-200 mt-0.5">Se adjuntarán el XML y el PDF de la factura</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-email').classList.add('hidden')" class="text-white/70 hover:text-white transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form action="<?= BASE_URL ?>facturas/<?= $factura['id'] ?>/email" method="POST" class="px-6 py-5 space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

            <div>
                <label class="block text-sm font-bold text-slate-500 uppercase tracking-wide mb-1">Correo destinatario <span class="text-red-500">*</span></label>
                <input type="email" name="email_destino" required
                       placeholder="cliente@empresa.com"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                <p class="text-xs text-slate-400 mt-1">Solo correos en la whitelist autorizada pueden recibir facturas.</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-500 uppercase tracking-wide mb-1">Asunto</label>
                <input type="text" name="asunto"
                       value="Factura <?= htmlspecialchars(($factura['serie'] ?? '') . '-' . ($factura['folio'] ?? '')) ?>"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-500 uppercase tracking-wide mb-1">Mensaje (opcional)</label>
                <textarea name="mensaje_cuerpo" rows="3"
                          placeholder="Se adjuntan los archivos de la factura."
                          class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow resize-none"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-email').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                    Cancelar
                </button>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Enviar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
