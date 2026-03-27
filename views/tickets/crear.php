<?php
$oldInput = $session->get('old_input', []);
$session->remove('old_input');
?>

<div class="max-w-4xl mx-auto">
    <div class="card">
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
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
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
            
            <!-- Operaciones Relacionadas -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Operaciones Relacionadas</h3>
                        <p class="text-sm text-gray-500">Agregue complementos de pago, notas, anticipos u otros documentos relacionados</p>
                    </div>
                    <button type="button" id="addOperacion" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar
                    </button>
                </div>
                
                <div id="operacionesContainer" class="space-y-4">
                    <!-- Las operaciones se agregan dinámicamente -->
                </div>
                
                <div id="noOperaciones" class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>No hay operaciones relacionadas</p>
                    <p class="text-xs">Click en "Agregar" para añadir una operación</p>
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="<?= BASE_URL ?>dashboard" class="btn btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Crear Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Template para operaciones -->
<template id="operacionTemplate">
    <div class="operacion-item bg-gray-50 rounded-lg p-4 relative">
        <button type="button" class="remove-operacion absolute top-2 right-2 p-1 text-gray-400 hover:text-red-500 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="form-label text-sm">Tipo de Operación</label>
                <select name="operaciones[INDEX][tipo_operacion]" class="form-select text-sm">
                    <?php foreach ($tipos_operacion as $key => $label): ?>
                    <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label class="form-label text-sm">UUID de Operación</label>
                <input type="text" name="operaciones[INDEX][uuid_operacion]" class="form-input text-sm"
                       pattern="[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}"
                       placeholder="UUID">
            </div>
            
            <div>
                <label class="form-label text-sm">Monto</label>
                <input type="number" name="operaciones[INDEX][monto]" class="form-input text-sm" step="0.01" placeholder="0.00">
            </div>
            
            <div class="md:col-span-2">
                <label class="form-label text-sm">Descripción</label>
                <input type="text" name="operaciones[INDEX][descripcion]" class="form-input text-sm" placeholder="Descripción opcional">
            </div>
            
            <div class="flex items-end">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="operaciones[INDEX][requiere_cancelacion]" value="1" class="w-4 h-4 text-primary-600 rounded focus:ring-primary-500">
                    <span class="ml-2 text-sm text-gray-700">Requiere cancelación</span>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('operacionesContainer');
    const noOperaciones = document.getElementById('noOperaciones');
    const template = document.getElementById('operacionTemplate');
    const addBtn = document.getElementById('addOperacion');
    let operacionIndex = 0;
    
    function updateVisibility() {
        const items = container.querySelectorAll('.operacion-item');
        noOperaciones.style.display = items.length === 0 ? 'block' : 'none';
    }
    
    addBtn.addEventListener('click', function() {
        const clone = template.content.cloneNode(true);
        const html = clone.querySelector('.operacion-item').outerHTML.replace(/INDEX/g, operacionIndex);
        container.insertAdjacentHTML('beforeend', html);
        operacionIndex++;
        updateVisibility();
        
        // Attach remove event
        const newItem = container.lastElementChild;
        newItem.querySelector('.remove-operacion').addEventListener('click', function() {
            newItem.remove();
            updateVisibility();
        });
    });
    
    // RFC to uppercase
    document.getElementById('rfc_receptor').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
    
    // UUID to lowercase
    document.getElementById('uuid_factura').addEventListener('input', function(e) {
        e.target.value = e.target.value.toLowerCase();
    });
});
</script>
