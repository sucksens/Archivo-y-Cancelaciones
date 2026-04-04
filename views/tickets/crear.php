<?php
$oldInput = $session->get('old_input', []);
$session->remove('old_input');
?>

<div class="max-w-4xl mx-auto">
    <div class="card card-accent-top card-accent-blue">
        <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Nuevo Ticket de Cancelación</h2>
            <p class="text-sm text-gray-500 mt-1">Complete todos los campos requeridos para crear el ticket</p>
        </div>
        
        <form action="<?= BASE_URL ?>tickets" method="POST" enctype="multipart/form-data" class="card-body space-y-6" id="ticketForm">
            <?= \App\Helpers\AuthHelper::getCsrfField() ?>
            
            <!-- Carga Automática por XML -->
            <div class="bg-primary-50 rounded-xl p-6 border border-primary-100 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-primary-900">Carga Automática</h3>
                        <p class="text-sm text-primary-700">Suba el archivo XML de la factura para autocompletar los campos.</p>
                    </div>
                    <div class="bg-white p-2 rounded-lg shadow-sm">
                        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="w-full">
                        <label for="xml_upload" class="sr-only">Subir XML</label>
                        <input type="file" id="xml_upload" accept=".xml" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700 transition-all cursor-pointer">
                    </div>
                    <div id="xml_loading" class="hidden flex items-center text-primary-600 font-medium">
                        <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando...
                    </div>
                </div>
                <div id="xml_status" class="mt-3 text-sm hidden"></div>
            </div>

            <!-- Información de la Factura -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información de la Factura</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Empresa Solicitante -->
                    <div>
                        <label for="empresa_solicitante" class="form-label">Empresa Solicitante <span class="text-red-500">*</span></label>
                        <select name="empresa_solicitante" id="empresa_solicitante" class="form-select" required>
                            <option value="">Seleccionar empresa...</option>
                            <?php foreach ($empresas as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($oldInput['empresa_solicitante'] ?? $user['empresa']) === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Tipo de Factura -->
                    <div>
                        <label for="tipo_factura" class="form-label">Tipo de Factura <span class="text-red-500">*</span></label>
                        <select name="tipo_factura" id="tipo_factura" class="form-select" required>
                            <?php foreach ($tipos_auto as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($oldInput['tipo_factura'] ?? 'autos_nuevos') === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- UUID Factura -->
                    <div>
                        <label for="uuid_factura" class="form-label">UUID de Factura <span class="text-red-500">*</span></label>
                        <input type="text" name="uuid_factura" id="uuid_factura" class="form-input" 
                               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                               value="<?= htmlspecialchars($oldInput['uuid_factura'] ?? '') ?>"
                               pattern="[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}"
                               required>
                    </div>
                    
                    <!-- Serie -->
                    <div>
                        <label for="serie" class="form-label">Serie <span class="text-red-500">*</span></label>
                        <input type="text" name="serie" id="serie" class="form-input" 
                               maxlength="20"
                               value="<?= htmlspecialchars($oldInput['serie'] ?? '') ?>"
                               required>
                    </div>
                    
                    <!-- Folio -->
                    <div>
                        <label for="folio" class="form-label">Folio <span class="text-red-500">*</span></label>
                        <input type="text" name="folio" id="folio" class="form-input" 
                               maxlength="20"
                               value="<?= htmlspecialchars($oldInput['folio'] ?? '') ?>"
                               required>
                    </div>
                    
                    <!-- Inventario 
                    <div>
                        <label for="inventario" class="form-label">Inventario</label>
                        <input type="text" name="inventario" id="inventario" class="form-input"
                               maxlength="50"
                               value="<?= htmlspecialchars($oldInput['inventario'] ?? '') ?>">
                    </div>
                    -->
                    
    <!-- Total Factura -->
                    <div>
                        <label for="total_factura" class="form-label">Total de Factura <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span id="currency-icon" class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 transition-opacity duration-200">$</span>
                            <input type="number" name="total_factura" id="total_factura" class="form-input pl-8" 
                                   step="0.01" min="0.01"
                                   value="<?= htmlspecialchars($oldInput['total_factura'] ?? '') ?>"
                                   required>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Información del Cliente -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Cliente</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre Cliente -->
                    <div class="md:col-span-2">
                        <label for="nombre_cliente" class="form-label">Nombre del Cliente <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre_cliente" id="nombre_cliente" class="form-input" 
                               maxlength="200"
                               value="<?= htmlspecialchars($oldInput['nombre_cliente'] ?? '') ?>"
                               required>
                    </div>
                    
                    <!-- RFC Receptor -->
                    <div>
                        <label for="rfc_receptor" class="form-label">RFC del Receptor <span class="text-red-500">*</span></label>
                        <input type="text" name="rfc_receptor" id="rfc_receptor" class="form-input uppercase" 
                               maxlength="13" minlength="12"
                               value="<?= htmlspecialchars($oldInput['rfc_receptor'] ?? '') ?>"
                               required>
                    </div>
                </div>
            </div>
            
            <!-- Detalles de Cancelación -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Detalles de Cancelación</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tipo de Cancelación -->
                    <div>
                        <label for="tipo_cancelacion" class="form-label">Tipo de Cancelación <span class="text-red-500">*</span></label>
                        <select name="tipo_cancelacion" id="tipo_cancelacion" class="form-select" required>
                            <option value="">Seleccionar tipo...</option>
                            <?php foreach ($tipos_cancelacion as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($oldInput['tipo_cancelacion'] ?? '') === $key ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Archivo de Autorización -->
                    <div>
                        <label for="archivo_autorizacion" class="form-label">Archivo de Autorización <span class="text-red-500">*</span></label>
                        <input type="file" name="archivo_autorizacion" id="archivo_autorizacion" 
                               accept=".pdf,.xml"
                               class="form-input file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Formatos permitidos: PDF, XML. Máximo 5MB</p>
                    </div>
                    
                    <!-- Motivo -->
                    <div class="md:col-span-2">
                        <label for="motivo" class="form-label">Motivo de Cancelación <span class="text-red-500">*</span></label>
                        <textarea name="motivo" id="motivo" rows="4" class="form-input resize-none" 
                                  minlength="10"
                                  placeholder="Describa detalladamente el motivo de la cancelación..."
                                  required><?= htmlspecialchars($oldInput['motivo'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= BASE_URL ?>dashboard" class="btn btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-update">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Crear Ticket
                </button>
            </div>
        </form>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- LÓGICA DE CARGA AUTOMÁTICA POR XML ---
    const xmlUpload = document.getElementById('xml_upload');
    const xmlLoading = document.getElementById('xml_loading');
    const xmlStatus = document.getElementById('xml_status');

    if (xmlUpload) {
        xmlUpload.addEventListener('change', async function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Mostrar estado de carga
            xmlLoading.classList.remove('hidden');
            xmlStatus.classList.add('hidden');
            xmlUpload.disabled = true;

            const formData = new FormData();
            formData.append('xml_file', file);

            try {
                const response = await fetch('<?= BASE_URL ?>tickets/parse-xml', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.exito && result.datos) {
                    fillFormFromXml(result.datos);
                    showXmlStatus('¡Datos cargados correctamente!', 'text-green-600');
                } else {
                    showXmlStatus(result.error || 'Error al procesar el XML', 'text-red-600');
                }
            } catch (error) {
                console.error('Error:', error);
                showXmlStatus('Error de conexión con el servidor', 'text-red-600');
            } finally {
                xmlLoading.classList.add('hidden');
                xmlUpload.disabled = false;
                xmlUpload.value = ''; // Limpiar para permitir volver a subir el mismo si es necesario
            }
        });
    }

    function showXmlStatus(message, colorClass) {
        xmlStatus.textContent = message;
        xmlStatus.className = `mt-3 text-sm flex items-center ${colorClass}`;
        xmlStatus.classList.remove('hidden');
    }

    function fillFormFromXml(datos) {
        const cfdi = datos.cfdi40 || datos.cfdi33 || datos.cfdi32;
        if (!cfdi) return;

        // 1. UUID (está en tfd11 o tfd10)
        const tfd = datos.tfd11 ? datos.tfd11[0] : (datos.tfd10 ? datos.tfd10[0] : null);
        if (tfd && tfd.uuid) {
            const uuidField = document.getElementById('uuid_factura');
            uuidField.value = tfd.uuid.toLowerCase();
            uuidField.dispatchEvent(new Event('input'));
        }

        // 2. Serie y Folio
        if (cfdi.serie) document.getElementById('serie').value = cfdi.serie;
        if (cfdi.folio) document.getElementById('folio').value = cfdi.folio;

        // 3. Total
        if (cfdi.total) {
            const totalField = document.getElementById('total_factura');
            totalField.value = cfdi.total;
            totalField.dispatchEvent(new Event('input'));
        }

        // 4. Cliente y RFC
        if (cfdi.receptor) {
            if (cfdi.receptor.nombre) document.getElementById('nombre_cliente').value = cfdi.receptor.nombre;
            if (cfdi.receptor.rfc) {
                const rfcField = document.getElementById('rfc_receptor');
                rfcField.value = cfdi.receptor.rfc.toUpperCase();
                rfcField.dispatchEvent(new Event('input'));
            }
        }

        // 5. Empresa (Basado en RFC Emisor)
        if (cfdi.emisor && cfdi.emisor.rfc) {
            const rfcEmisor = cfdi.emisor.rfc.toUpperCase();
            const empresaSelect = document.getElementById('empresa_solicitante');
            
            // Mapeo manual de RFCs conocidos
            const rfcToEmpresa = {
                'GMG090821RT0': 'grupo_motormexa',
                'AMO021114AG5': 'automotriz_motormexa'
            };

            if (rfcToEmpresa[rfcEmisor]) {
                empresaSelect.value = rfcToEmpresa[rfcEmisor];
            }
        }
    }

    // --- LÓGICA EXISTENTE DE LIMPIEZA DE CAMPOS ---
    const cleanFields = [
        'uuid_factura',
        'serie',
        'folio',
        'inventario',
        'rfc_receptor'
    ];

    cleanFields.forEach(id => {
        const field = document.getElementById(id);
        if (field) {
            field.addEventListener('input', function(e) {
                // Eliminar todos los espacios
                const start = this.selectionStart;
                const end = this.selectionEnd;
                const originalValue = this.value;
                const cleanedValue = originalValue.replace(/\s+/g, '');
                
                if (originalValue !== cleanedValue) {
                    this.value = cleanedValue;
                    // Mantener posición del cursor si es posible
                    const shift = originalValue.length - cleanedValue.length;
                    this.setSelectionRange(start - shift, end - shift);
                }

                // Transformaciones adicionales
                if (id === 'rfc_receptor') {
                    this.value = this.value.toUpperCase();
                } else if (id === 'uuid_factura') {
                    this.value = this.value.toLowerCase();
                }
            });
        }
    });

    // Control del icono de moneda en Total Factura
    const totalInput = document.getElementById('total_factura');
    const currencyIcon = document.getElementById('currency-icon');

    if (totalInput && currencyIcon) {
        const toggleIcon = () => {
            if (totalInput.value.length > 0) {
                currencyIcon.classList.add('opacity-0');
                totalInput.classList.remove('pl-8');
            } else {
                currencyIcon.classList.remove('opacity-0');
                totalInput.classList.add('pl-8');
            }
        };

        totalInput.addEventListener('input', toggleIcon);
        // Ejecutar al cargar por si hay old_input
        toggleIcon();
    }
});
</script>
