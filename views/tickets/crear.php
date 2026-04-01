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
                    
                    <!-- Inventario -->
                    <div>
                        <label for="inventario" class="form-label">Inventario</label>
                        <input type="text" name="inventario" id="inventario" class="form-input"
                               maxlength="50"
                               value="<?= htmlspecialchars($oldInput['inventario'] ?? '') ?>">
                    </div>
                    
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
    // Campos que requieren eliminar espacios
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
