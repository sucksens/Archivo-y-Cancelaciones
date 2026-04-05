<?php
/**
 * Vista: Subir Factura
 * Sistema de Tickets de Cancelación
 */

use App\Helpers\PermissionHelper;
use App\Helpers\AuthHelper;

$title = $title ?? 'Subir Factura';
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
$user = AuthHelper::getUser();
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="<?= BASE_URL ?>facturas" class="text-primary-600 hover:text-primary-800 inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Volver a Facturas
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h1 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($title) ?></h1>
            <p class="text-sm text-gray-600">Sube los archivos XML y PDF de la factura</p>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
        <div class="mx-6 mt-4 p-4 rounded-lg <?= $_SESSION['flash_type'] ?? 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($_SESSION['flash']) ?>
            <?php unset($_SESSION['flash'], $_SESSION['flash_type']); ?>
        </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>facturas" method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Información</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>Tu empresa: <strong><?= htmlspecialchars($empresas[$user['empresa']] ?? $user['empresa']) ?></strong></p>
                            <p class="mt-1">Solo puedes subir facturas de tu empresa. El UUID será validado contra el sistema administrativo.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="empresa" class="block text-sm font-medium text-gray-700 mb-1">Empresa <span class="text-red-500">*</span></label>
                    <select name="empresa" id="empresa" class="form-select rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500" required>
                        <?php foreach ($empresas as $key => $name): ?>
                        <option value="<?= $key ?>" <?= ($oldInput['empresa'] ?? $user['empresa']) === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="tipo_factura" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Factura <span class="text-red-500">*</span></label>
                    <select name="tipo_factura" id="tipo_factura" class="form-select rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500" required>
                        <?php foreach ($tipos_auto as $key => $name): ?>
                        <option value="<?= $key ?>" <?= ($oldInput['tipo_factura'] ?? 'autos_nuevos') === $key ? 'selected' : '' ?>>
                            <?= htmlspecialchars($name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-6">
                <label for="uuid_factura" class="block text-sm font-medium text-gray-700 mb-1">UUID de Factura <span class="text-red-500">*</span></label>
                <input type="text" name="uuid_factura" id="uuid_factura" 
                       value="<?= htmlspecialchars($oldInput['uuid_factura'] ?? '') ?>"
                       placeholder="XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
                       class="form-input rounded-lg border-gray-300 focus:ring-primary-500 focus:border-primary-500 font-mono" required>
                <p class="mt-1 text-xs text-gray-500">UUID de 36 caracteres (con guisos)</p>
            </div>

            <div class="mt-6">
                <label for="archivo_xml" class="block text-sm font-medium text-gray-700 mb-1">
                    Archivo XML <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="archivo_xml" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none">
                                <span>Subir archivo XML</span>
                                <input id="archivo_xml" name="archivo_xml" type="file" accept=".xml,application/xml" class="sr-only" required>
                            </label>
                            <p class="pl-1">o arrastrar y soltar</p>
                        </div>
                        <p class="text-xs text-gray-500">Solo archivos XML (máx. 10MB)</p>
                        <p id="xml_file_name" class="text-sm text-green-600 font-medium hidden"></p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <label for="archivo_pdf" class="block text-sm font-medium text-gray-700 mb-1">
                    Archivo PDF <span class="text-gray-400">(opcional)</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="archivo_pdf" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none">
                                <span>Subir archivo PDF</span>
                                <input id="archivo_pdf" name="archivo_pdf" type="file" accept=".pdf,application/pdf" class="sr-only">
                            </label>
                            <p class="pl-1">o arrastrar y soltar</p>
                        </div>
                        <p class="text-xs text-gray-500">Solo archivos PDF (máx. 10MB)</p>
                        <p id="pdf_file_name" class="text-sm text-green-600 font-medium hidden"></p>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <a href="<?= BASE_URL ?>facturas" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-lg shadow mr-3 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition-colors">
                    <i class="fas fa-upload mr-2"></i>Subir Factura
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('archivo_xml').addEventListener('change', function(e) {
        var fileName = e.target.files[0]?.name;
        var displayEl = document.getElementById('xml_file_name');
        if (fileName) {
            displayEl.textContent = 'Seleccionado: ' + fileName;
            displayEl.classList.remove('hidden');
        } else {
            displayEl.classList.add('hidden');
        }
    });

    document.getElementById('archivo_pdf').addEventListener('change', function(e) {
        var fileName = e.target.files[0]?.name;
        var displayEl = document.getElementById('pdf_file_name');
        if (fileName) {
            displayEl.textContent = 'Seleccionado: ' + fileName;
            displayEl.classList.remove('hidden');
        } else {
            displayEl.classList.add('hidden');
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        var xmlFile = document.getElementById('archivo_xml').files[0];
        if (!xmlFile) {
            e.preventDefault();
            alert('El archivo XML es obligatorio');
            return false;
        }
        return true;
    });
</script>
