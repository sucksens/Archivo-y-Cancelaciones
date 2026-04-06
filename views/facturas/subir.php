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
                <p id="xml_loading" class="hidden flex items-center text-primary-600 font-medium mt-1">
                    <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Procesando XML...
                </p>
                <p id="xml_status" class="mt-1 text-sm hidden"></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div>
                    <label for="archivo_xml" class="block text-sm font-medium text-gray-700 mb-1">
                        Archivo XML <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="archivo_xml" id="archivo_xml" 
                           accept=".xml,application/xml"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700 transition-all cursor-pointer"
                           required>
                    <p class="mt-1 text-xs text-gray-500">Solo archivos XML (máx. 10MB)</p>
                    <p id="xml_file_name" class="text-sm text-green-600 font-medium hidden mt-1"></p>
                </div>

                <div>
                    <label for="archivo_pdf" class="block text-sm font-medium text-gray-700 mb-1">
                        Archivo PDF <span class="text-gray-400">(opcional)</span>
                    </label>
                    <input type="file" name="archivo_pdf" id="archivo_pdf" 
                           accept=".pdf,application/pdf"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700 transition-all cursor-pointer">
                    <p class="mt-1 text-xs text-gray-500">Solo archivos PDF (máx. 10MB)</p>
                    <p id="pdf_file_name" class="text-sm text-green-600 font-medium hidden mt-1"></p>
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
    document.addEventListener('DOMContentLoaded', function() {
        const archivoXml = document.getElementById('archivo_xml');
        const xmlLoading = document.getElementById('xml_loading');
        const xmlStatus = document.getElementById('xml_status');

        if (archivoXml) {
            archivoXml.addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file) return;

                xmlLoading.classList.remove('hidden');
                xmlStatus.classList.add('hidden');
                archivoXml.disabled = true;

                const formData = new FormData();
                formData.append('xml_file', file);

                try {
                    const response = await fetch('<?= BASE_URL ?>facturas/parse-xml', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    if (result.exito && result.datos && (
                        Array.isArray(result.datos) ? result.datos.length > 0 : Object.keys(result.datos).length > 0
                    )) {
                        fillUuidFromXml(result.datos);
                        showXmlStatus('¡UUID cargado correctamente!', 'text-green-600');
                    } else if (result.error) {
                        showXmlStatus(result.error, 'text-red-600');
                    } else {
                        showXmlStatus('No se pudieron extraer datos del XML', 'text-red-600');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showXmlStatus('Error de conexión con el servidor', 'text-red-600');
                } finally {
                    xmlLoading.classList.add('hidden');
                    archivoXml.disabled = false;
                }
            });
        }

        function showXmlStatus(message, colorClass) {
            xmlStatus.textContent = message;
            xmlStatus.className = `mt-1 text-sm flex items-center ${colorClass}`;
            xmlStatus.classList.remove('hidden');
        }

        function fillUuidFromXml(datos) {
            const cfdi = datos.cfdi40 || datos.cfdi33 || datos.cfdi32;
            if (!cfdi) return;

            const tfd = datos.tfd11 ? datos.tfd11[0] : (datos.tfd10 ? datos.tfd10[0] : null);
            if (tfd && tfd.uuid) {
                const uuidField = document.getElementById('uuid_factura');
                uuidField.value = tfd.uuid.toLowerCase();
                uuidField.dispatchEvent(new Event('input'));
            }
        }

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

        const uuidField = document.getElementById('uuid_factura');
        if (uuidField) {
            uuidField.addEventListener('input', function(e) {
                this.value = this.value.toLowerCase();
            });
        }

        document.querySelector('form').addEventListener('submit', function(e) {
            var xmlFile = document.getElementById('archivo_xml').files[0];
            if (!xmlFile) {
                e.preventDefault();
                alert('El archivo XML es obligatorio');
                return false;
            }
            return true;
        });
    });
</script>
