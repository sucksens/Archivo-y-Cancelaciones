<?php
/**
 * FacturaArchivoController - Controlador de archivos de facturas
 * Sistema de Tickets de Cancelación
 *
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Database;
use App\Models\FacturaArchivo;
use App\Helpers\ValidationHelper;
use App\Helpers\FileUploadHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\AuthHelper;
use App\BBj\FacturasBridge;
use App\Models\EmailConfig;
use App\Helpers\EmailService;

class FacturaArchivoController extends BaseController
{
    private FacturaArchivo $facturaModel;
    private Database $db;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->facturaModel = new FacturaArchivo();
        $this->db = Database::getInstance();
    }

    /**
     * Listar facturas según permisos
     */
    public function index(): void
    {
        $page = (int) ($this->input('page') ?? 1);
        $filters = [
            'empresa' => $this->input('empresa'),
            'tipo_factura' => $this->input('tipo_factura'),
            'search' => $this->input('search')
        ];

        if (PermissionHelper::hasPermission('facturas.view.all')) {
            $result = $this->facturaModel->getAllFacturas($page, ITEMS_PER_PAGE, $filters);
        } elseif (PermissionHelper::hasPermission('facturas.view.empresa')) {
            $empresa = PermissionHelper::getUserCompany();
            if (!$empresa) {
                $this->session->flash('error', 'No tienes una empresa asignada');
                $this->redirect('/dashboard');
            }
            
            // Aplicar filtro automático para rol Consulta con especialidad
            if (PermissionHelper::isConsulta()) {
                $especialidadFilter = PermissionHelper::getConsultaTipoFacturaFilter();
                if ($especialidadFilter) {
                    $filters['tipo_factura'] = $especialidadFilter;
                }
            }
            
            $result = $this->facturaModel->getByEmpresa($empresa, $page, ITEMS_PER_PAGE, $filters);
        } else {
            $result = $this->facturaModel->getByUser($this->userId(), $page, ITEMS_PER_PAGE, $filters);
        }

        // Registrar log de la acción
        $this->log(
            'Listar facturas',
            'facturas',
            'Filtros: ' . json_encode($filters) . ', Página: ' . $page . ', Total: ' . $result['total']
        );

        $this->view('facturas/index', [
            'title' => 'Archivos de Facturas',
            'facturas' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'empresas' => EMPRESAS,
            'tipos_auto' => TIPOS_AUTO,
            'isConsultaWithEspecialidad' => PermissionHelper::isConsultaWithEspecialidad(),
            'userEspecialidadLabel' => PermissionHelper::getUserEspecialidad() ? 
                (ESPECIALIDADES_USUARIO[PermissionHelper::getUserEspecialidad()]['label'] ?? '') : ''
        ]);
    }

    /**
     * Mostrar formulario de subida
     */
    public function create(): void
    {
        $this->requirePermission('facturas.upload');

        // Registrar log de la acción
        $this->log(
            'Acceso a formulario de subida',
            'facturas',
            'Usuario accedió al formulario para subir nueva factura'
        );

        $this->view('facturas/subir', [
            'title' => 'Subir Factura',
            'empresas' => EMPRESAS,
            'tipos_auto' => TIPOS_AUTO
        ]);
    }

    /**
     * Guardar nueva factura
     */
    public function store(): void
    {
        $this->requirePermission('facturas.upload');

        // Validación: Usuarios con empresa='ambas' no pueden subir facturas
        $userCompany = \App\Helpers\PermissionHelper::getUserCompany();
        if ($userCompany === 'ambas') {
            throw new \Exception('Usuarios con acceso a ambas empresas no pueden subir facturas');
        }

        try {
            $this->validateCsrf();

            $empresa = $this->input('empresa');
            $tipoFactura = $this->input('tipo_factura');
            $uuidFactura = $this->input('uuid_factura');

            if (!$empresa || !in_array($empresa, array_keys(EMPRESAS))) {
                throw new \Exception('Empresa no válida');
            }

            if (!$tipoFactura || !in_array($tipoFactura, array_keys(TIPOS_AUTO))) {
                throw new \Exception('Tipo de factura no válido');
            }

            $uuidLimpio = ValidationHelper::cleanUuid($uuidFactura);
            if (empty($uuidLimpio)) {
                throw new \Exception('UUID de factura inválido');
            }

            $xmlFile = $_FILES['archivo_xml'] ?? [];
            if (empty($xmlFile) || $xmlFile['error'] === UPLOAD_ERR_NO_FILE) {
                throw new \Exception('El archivo XML es obligatorio');
            }

            $uploader = new FileUploadHelper();

            $xmlPath = $uploader->upload($xmlFile, 'facturas');
            if (!$xmlPath) {
                throw new \Exception($uploader->getFirstError() ?: 'Error al subir el archivo XML');
            }

            $pdfPath = null;
            $pdfFile = $_FILES['archivo_pdf'] ?? [];
            if (!empty($pdfFile) && $pdfFile['error'] !== UPLOAD_ERR_NO_FILE) {
                $pdfPath = $uploader->upload($pdfFile, 'facturas');
                if (!$pdfPath) {
                    $uploader->delete($xmlPath);
                    throw new \Exception($uploader->getFirstError() ?: 'Error al subir el archivo PDF');
                }
            }

            $existingFactura = $this->facturaModel->findByUuid($uuidLimpio);
            if ($existingFactura) {
                $uploader->delete($xmlPath);
                if ($pdfPath) $uploader->delete($pdfPath);
                throw new \Exception("La factura con UUID {$uuidLimpio} ya fue subida anteriormente");
            }

            $dbMapping = [
                'grupo_motormexa' => [
                    'autos_nuevos' => '01AN_AUTOSNUEVOS',
                    'seminuevos' => '01AU_SEMINUEVOS'
                ],
                'automotriz_motormexa' => [
                    'autos_nuevos' => '02AN_AUTOSNUEVOS',
                    'seminuevos' => '02AU_SEMINUEVOS'
                ]
            ];

            $dbName = $dbMapping[$empresa][$tipoFactura] ?? '01AN_AUTOSNUEVOS';
            $facturaBridge = new FacturasBridge($dbName);
            $facturaBbj = $facturaBridge->getFactura($uuidLimpio);

            if (!$facturaBbj) {
                $uploader->delete($xmlPath);
                if ($pdfPath) $uploader->delete($pdfPath);
                throw new \Exception("La factura con UUID {$uuidLimpio} no fue encontrada en el sistema administrativo ({$empresa})");
            }

            $filePath = UPLOADS_PATH . '/' . $xmlPath;
            $mimeType = $_FILES['archivo_xml']['type'] ?: 'text/xml';
            $cfile = curl_file_create($filePath, $mimeType, $_FILES['archivo_xml']['name']);

            $apiUrl = 'http://200.1.1.245:5000/parsear_xml/';
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $cfile]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);

            error_log("=== Parse XML DEBUG ===");
            error_log("Archivo: " . $_FILES['archivo_xml']['name']);
            error_log("Tamaño: " . $_FILES['archivo_xml']['size'] . " bytes");
            error_log("HTTP Code: " . $httpCode);
            error_log("cURL Error: " . ($curlError ?: 'None'));
            error_log("cURL Errno: " . $curlErrno);
            error_log("Response (first 500 chars): " . ($response ? substr($response, 0, 500) : 'empty'));

            if ($curlError) {
                throw new \Exception('Error al conectar con el servidor de parsing: ' . $curlError);
            }

            $cfdi = [];
            $timbre = [];
            $parsedData = [];

            if ($response && $httpCode === 200) {
                $decoded = json_decode($response, true);
                if ($decoded && isset($decoded['exito']) && $decoded['exito']) {
                    switch ($decoded['version']) {
                        case '4.0':
                            $cfdi = $decoded['datos']['cfdi40'] ?? [];
                            $timbre = $decoded['datos']['tfd11'][0] ?? [];
                            break;
                        case '3.3':
                            $cfdi = $decoded['datos']['cfdi33'] ?? [];
                            $timbre = $decoded['datos']['tfd11'][0] ?? [];
                            break;
                        case '3.2':
                            $cfdi = $decoded['datos']['cfdi32'] ?? [];
                            $timbre = $decoded['datos']['tfd10'][0] ?? [];
                            break;
                    }
                } else {
                    throw new \Exception('El endpoint de parsing no devolvió datos válidos');
                }
            } else {
                $errorMessage = 'No se pudo procesar el XML';
                if ($curlErrno) {
                    $errorMessage .= ': ' . $curlError . ' (Error ' . $curlErrno . ')';
                } else {
                    $errorMessage .= ': HTTP ' . $httpCode;
                }
                if ($response) {
                    $errorMessage .= ' - Respuesta: ' . substr($response, 0, 200);
                }
                throw new \Exception($errorMessage);
            }

            if (empty($cfdi)) {
                throw new \Exception('No se pudieron extraer los datos del CFDI del XML');
            }

            if (!isset($cfdi['serie']) || !isset($cfdi['folio']) || !isset($cfdi['emisor']['rfc']) || !isset($cfdi['receptor']['rfc'])) {
                throw new \Exception('El XML del CFDI no contiene los datos mínimos requeridos (serie, folio, rfc emisor, rfc receptor)');
            }

            $facturaId = $this->facturaModel->create([
                'usuario_id' => $this->userId(),
                'empresa' => $empresa,
                'tipo_factura' => $tipoFactura,
                'uuid_factura' => $uuidLimpio,
                'archivo_xml' => $xmlPath,
                'archivo_pdf' => $pdfPath,
                'serie' => $cfdi['serie'] ?? null,
                'folio' => $cfdi['folio'] ?? null,
                'total' => isset($cfdi['total']) ? (float) $cfdi['total'] : null,
                'fecha_emision' => $timbre['fecha_timbrado'] ?? null,
                'rfc_emisor' => $cfdi['emisor']['rfc'] ?? null,
                'rfc_receptor' => $cfdi['receptor']['rfc'] ?? null,
                'id_suc' => $facturaBbj['ID_SUC'] ?? null,
                'id_pedido' => $facturaBbj['ID_PEDIDO'] ?? null,
                'fecfac' => isset($facturaBbj['FECFAC']) ? ValidationHelper::BbjDateToMysqlDate($facturaBbj['FECFAC']) : null,
                'inventario' => $facturaBbj['INVENTARIO'] ?? null,
                'id_vendedor' => $facturaBbj['ID_VENDEDOR'] ?? null,
                'datos_extra' => json_encode($cfdi)
            ]);

            // Registrar log mejorado de la acción
            $this->log(
                'Factura subida',
                'facturas',
                "ID: {$facturaId}, UUID: {$uuidLimpio}, Empresa: {$empresa}, Tipo: {$tipoFactura}, " .
                "Serie: {$cfdi['serie']}, Folio: {$cfdi['folio']}, Total: {$cfdi['total']}, " .
                "RFC Emisor: {$cfdi['emisor']['rfc']}, RFC Receptor: {$cfdi['receptor']['rfc']}, " .
                "ID Vendedor: {$facturaBbj['ID_VENDEDOR']}, Inventario: {$facturaBbj['INVENTARIO']}"
            );
            $this->session->flash('success', 'Factura subida correctamente');
            $this->redirect('/facturas/' . $facturaId);

        } catch (\Exception $e) {
            $this->session->flash('error', $e->getMessage());
            $this->session->set('old_input', $_POST);
            $this->redirect('/facturas/subir');
        }
    }

    /**
     * Ver detalle de factura
     *
     * @param int $id ID de la factura
     */
    public function show(int $id): void
    {
        $factura = $this->facturaModel->find($id);

        if (!$factura) {
            $this->session->flash('error', 'Factura no encontrada');
            $this->redirect('/facturas');
        }

        $userCompany = PermissionHelper::getUserCompany();
        $canView = PermissionHelper::hasPermission('facturas.view.all')
                || (PermissionHelper::hasPermission('facturas.view.own') && $factura['usuario_id'] === $this->userId())
                || (PermissionHelper::hasPermission('facturas.view.empresa') && 
                    ($factura['empresa'] === $userCompany || $userCompany === 'ambas'));

        if (!$canView) {
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $canDownloadAll = PermissionHelper::hasPermission('facturas.download');
        $canDownloadVendedor = PermissionHelper::hasPermission('facturas.download.vendedor');
        $canDownloadNR = PermissionHelper::canDownloadNR();
        $canDownload = $canDownloadAll || $canDownloadVendedor || $canDownloadNR;
        $canDelete = PermissionHelper::isAdmin();
        $canSendEmail = PermissionHelper::hasPermission('facturas.email.send');

        // Obtener el vendedor del usuario para validación en el cliente
        $userVendedor = PermissionHelper::getUserVendedor();
        
        // Definir el RFC de NR para validación en el cliente
        $rfcNR = PermissionHelper::getRfcNR();

        // Historial de envíos (visible para admin/supervisor)
        $enviosEmail = [];
        if (PermissionHelper::isAdmin() || PermissionHelper::hasPermission('facturas.view.all')) {
            $emailConfigModel = new EmailConfig();
            $enviosEmail = $emailConfigModel->getEnviosByFactura($id);
        }

        // Registrar log de la acción
        $this->log(
            'Ver detalle de factura',
            'facturas',
            "ID: {$id}, UUID: {$factura['uuid_factura']}, Empresa: {$factura['empresa']}, " .
            "Inventario: {$factura['inventario']}, Tipo: {$factura['tipo_factura']}"
        );

        $this->view('facturas/detalle', [
            'title'              => 'Factura #' . $id,
            'factura'            => $factura,
            'empresas'           => EMPRESAS,
            'tipos_auto'         => TIPOS_AUTO,
            'canDownload'        => $canDownload,
            'canDownloadAll'     => $canDownloadAll,
            'canDownloadVendedor'=> $canDownloadVendedor,
            'canDownloadNR'      => $canDownloadNR,
            'userVendedor'       => $userVendedor,
            'rfcNR'              => $rfcNR,
            'canDelete'          => $canDelete,
            'canSendEmail'       => $canSendEmail,
            'enviosEmail'        => $enviosEmail,
        ]);
    }

    /**
     * Descargar archivo XML o PDF
     *
     * @param int $id ID de la factura
     * @param string $tipo Tipo de archivo (xml/pdf)
     */
    public function download(int $id, string $tipo): void
    {
        $factura = $this->facturaModel->find($id);

        if (!$factura) {
            http_response_code(404);
            exit('Factura no encontrada');
        }

        $canDownloadAll = PermissionHelper::hasPermission('facturas.download');
        $canDownloadVendedor = PermissionHelper::hasPermission('facturas.download.vendedor');
        $canDownloadNR = PermissionHelper::canDownloadNR();

        if (!$canDownloadAll && !$canDownloadVendedor && !$canDownloadNR) {
            http_response_code(403);
            exit('Acceso denegado');
        }

        // Si solo tiene permiso de vendedor, verificar que el id_vendedor coincida
        if (!$canDownloadAll && $canDownloadVendedor && !$canDownloadNR) {
            $userVendedor = PermissionHelper::getUserVendedor();
            
            if (!$userVendedor) {
                http_response_code(403);
                exit('No tienes un vendedor asignado');
            }
            
            if ($factura['id_vendedor'] !== $userVendedor) {
                http_response_code(403);
                exit('No tienes permiso para descargar facturas de este vendedor');
            }
        }

        // Si solo tiene permiso de NR, verificar que el RFC receptor sea NFM0307091L9
        if (!$canDownloadAll && !$canDownloadVendedor && $canDownloadNR) {
            $rfcNR = PermissionHelper::getRfcNR();
            
            if ($factura['rfc_receptor'] !== $rfcNR) {
                http_response_code(403);
                exit('No tienes permiso para descargar facturas de este RFC receptor');
            }
        }

        // Si tiene permiso de vendedor y NR, verificar que cumpla con al menos uno
        if (!$canDownloadAll && $canDownloadVendedor && $canDownloadNR) {
            $hasAccess = false;
            
            // Verificar permiso de vendedor
            $userVendedor = PermissionHelper::getUserVendedor();
            if ($userVendedor && $factura['id_vendedor'] === $userVendedor) {
                $hasAccess = true;
            }
            
            // Verificar permiso de NR
            if (PermissionHelper::isFacturaNR($factura)) {
                $hasAccess = true;
            }
            
            if (!$hasAccess) {
                http_response_code(403);
                exit('No tienes permiso para descargar esta factura');
            }
        }

        $filePath = null;
        if ($tipo === 'xml') {
            $filePath = UPLOADS_PATH . '/' . $factura['archivo_xml'];
            $filename = 'factura_' . $factura['uuid_factura'] . '.xml';
        } elseif ($tipo === 'pdf') {
            if (empty($factura['archivo_pdf'])) {
                http_response_code(404);
                exit('Archivo PDF no disponible');
            }
            $filePath = UPLOADS_PATH . '/' . $factura['archivo_pdf'];
            $filename = 'factura_' . $factura['uuid_factura'] . '.pdf';
        } else {
            http_response_code(400);
            exit('Tipo de archivo no válido');
        }

        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('Archivo no encontrado');
        }

        // Registrar log de la acción
        $this->log(
            'Descargar archivo de factura',
            'facturas',
            "ID: {$id}, Tipo: {$tipo}, UUID: {$factura['uuid_factura']}, " .
            "Empresa: {$factura['empresa']}, Inventario: {$factura['inventario']}, " .
            "Archivo: {$filename}"
        );

        $mimeType = mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    }

    /**
     * Eliminar factura (solo administradores)
     *
     * @param int $id ID de la factura
     */
    public function destroy(int $id): void
    {
        if (!PermissionHelper::isAdmin()) {
            http_response_code(403);
            exit('Acceso denegado');
        }

        $factura = $this->facturaModel->find($id);

        if (!$factura) {
            $this->json(['error' => 'Factura no encontrada'], 404);
        }

        try {
            $this->validateCsrf();

            $uploader = new FileUploadHelper();

            if ($factura['archivo_xml']) {
                $uploader->delete($factura['archivo_xml']);
            }
            if ($factura['archivo_pdf']) {
                $uploader->delete($factura['archivo_pdf']);
            }

            $this->facturaModel->delete($id);

            // Registrar log mejorado de la acción
            $this->log(
                'Factura eliminada',
                'facturas',
                "ID: {$id}, UUID: {$factura['uuid_factura']}, Empresa: {$factura['empresa']}, " .
                "Tipo: {$factura['tipo_factura']}, Inventario: {$factura['inventario']}, " .
                "Usuario que subió: {$factura['usuario_nombre']}"
            );

            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Factura eliminada correctamente']);
            }

            $this->session->flash('success', 'Factura eliminada correctamente');
            $this->redirect('/facturas');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Error al eliminar factura'], 500);
            }
            $this->session->flash('error', 'Error al eliminar factura');
            $this->redirect('/facturas/' . $id);
        }
    }


    public function parseXml(): void
    {
        try {
            $this->requirePermission('facturas.upload');

            if (!isset($_FILES['xml_file'])) {
                $this->json(['error' => 'No se proporcionó ningún archivo'], 400);
            }

            $xmlFile = $_FILES['xml_file'];
            if ($xmlFile['error'] !== UPLOAD_ERR_OK) {
                $this->json(['error' => 'Error al subir el archivo: código ' . $xmlFile['error']], 400);
            }

            $filePath = $xmlFile['tmp_name'];
            $mimeType = $xmlFile['type'] ?: 'text/xml';
            $cfile = curl_file_create($filePath, $mimeType, $xmlFile['name']);

            $apiUrl = 'http://200.1.1.245:5000/parsear_xml/';
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $cfile]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            error_log("=== parseXml DEBUG ===");
            error_log("HTTP Code: " . $httpCode);
            error_log("cURL Error: " . ($curlError ?: 'None'));
            error_log("Response (first 500 chars): " . substr($response, 0, 500));

            if ($curlError) {
                error_log("cURL Error details: " . $curlError);
                $this->json(['error' => 'Error de conexión con el servidor de parsing: ' . $curlError], 500);
            }

            if ($httpCode !== 200) {
                error_log("Non-200 response: " . $response);
                $this->json(['error' => 'El servidor de parsing respondió con código ' . $httpCode . ': ' . substr($response, 0, 200)], $httpCode);
            }

            $decoded = json_decode($response, true);
            error_log("JSON decoded: " . ($decoded ? 'Success' : 'FAILED'));
            error_log("Decoded structure: " . print_r($decoded, true));

            if (!$decoded) {
                error_log("json_decode failed. Response is not valid JSON.");
                $this->json(['error' => 'La respuesta del servidor no es JSON válido'], 500);
            }

            if (!isset($decoded['exito'])) {
                error_log("Missing 'exito' key in response");
                $this->json(['error' => 'La respuesta del servidor no tiene el formato esperado (falta exito)'], 500);
            }

            if (!$decoded['exito']) {
                $errorMsg = $decoded['error'] ?? $decoded['message'] ?? 'Error desconocido al procesar el XML';
                error_log("API reported failure: " . $errorMsg);
                $this->json(['error' => $errorMsg], 400);
            }

            $data = $decoded['datos'] ?? [];
            if (empty($data) || (is_array($data) && count($data) === 0)) {
                error_log("API reported success but data is empty");
                $this->json(['error' => 'El XML fue procesado pero no se encontraron datos utilizables'], 400);
            }

            error_log("parseXml success. Data keys: " . implode(', ', array_keys($data)));

            // Registrar log de la acción
            $uuidExtraido = $data['tfd11'][0]['uuid'] ?? ($data['tfd10'][0]['uuid'] ?? 'N/A');
            $this->log(
                'Parsear XML exitoso',
                'facturas',
                "Archivo: {$xmlFile['name']}, Tamaño: {$xmlFile['size']} bytes, " .
                "UUID extraído: {$uuidExtraido}"
            );

            $this->json([
                'exito' => true,
                'datos' => $data
            ]);

        } catch (\Exception $e) {
            error_log("parseXml exception: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Enviar factura por correo electrónico
     *
     * Valida whitelist/blacklist antes de llamar a la API de envío.
     * Registra todos los intentos (enviado, bloqueado, error) en factura_envios_email.
     *
     * @param int $id ID de la factura
     */
    public function sendEmail(int $id): void
    {
        $this->requirePermission('facturas.email.send');

        $factura = $this->facturaModel->find($id);
        if (!$factura) {
            $this->session->flash('error', 'Factura no encontrada.');
            $this->redirect('/facturas');
        }

        // Verificar que el usuario puede ver esta factura
        $userCompany = \App\Helpers\PermissionHelper::getUserCompany();
        $canView = PermissionHelper::hasPermission('facturas.view.all')
                || (PermissionHelper::hasPermission('facturas.view.own') && $factura['usuario_id'] === $this->userId())
                || (PermissionHelper::hasPermission('facturas.view.empresa') &&
                    ($factura['empresa'] === $userCompany || $userCompany === 'ambas'));

        if (!$canView) {
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }

        try {
            $this->validateCsrf();

            $emailDestino = trim($this->input('email_destino') ?? '');
            $asunto       = trim($this->input('asunto') ?? '');
            $cuerpo       = trim($this->input('mensaje_cuerpo') ?? 'Se adjuntan los archivos de la factura.');

            // Validaciones básicas
            if (empty($emailDestino) || !filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Correo electrónico destino inválido.');
            }
            if (empty($asunto)) {
                $asunto = 'Factura ' . ($factura['serie'] ?? '') . '-' . ($factura['folio'] ?? '');
            }

            // Validar whitelist / blacklist
            $emailConfigModel = new EmailConfig();
            $validation = $emailConfigModel->isEmailAllowed($emailDestino);

            if (!$validation['allowed']) {
                // Registrar intento bloqueado
                $emailConfigModel->logEnvio([
                    'factura_id'       => $id,
                    'usuario_id'       => $this->userId(),
                    'email_destino'    => $emailDestino,
                    'asunto'           => $asunto,
                    'resultado'        => 'bloqueado',
                    'detalle'          => $validation['motivo'],
                    'id_operacion_api' => null,
                ]);

                $this->log(
                    'Envío de factura bloqueado',
                    'facturas',
                    "ID Factura: {$id}, Email: {$emailDestino}, Motivo: {$validation['motivo']}"
                );

                $this->session->flash('error', 'Envío bloqueado: ' . $validation['motivo']);
                $this->redirect('/facturas/' . $id);
            }

            // Rutas de archivos
            $xmlPath = UPLOADS_PATH . '/' . $factura['archivo_xml'];
            $pdfPath = !empty($factura['archivo_pdf']) ? UPLOADS_PATH . '/' . $factura['archivo_pdf'] : '';

            if (!file_exists($xmlPath)) {
                throw new \Exception('El archivo XML de la factura no está disponible en el servidor.');
            }

            // Llamar a la API de envío
            $emailService = new EmailService();
            $resultado = $emailService->send($xmlPath, $pdfPath, $emailDestino, $asunto, $cuerpo);

            $logResultado = $resultado['exito'] ? 'enviado' : 'error';
            $logDetalle   = $resultado['exito']
                ? ($resultado['mensaje'] ?? 'Enviado correctamente')
                : ($resultado['error'] ?? 'Error desconocido en la API');

            // Registrar en log de envíos
            $emailConfigModel->logEnvio([
                'factura_id'       => $id,
                'usuario_id'       => $this->userId(),
                'email_destino'    => $emailDestino,
                'asunto'           => $asunto,
                'resultado'        => $logResultado,
                'detalle'          => $logDetalle,
                'id_operacion_api' => $resultado['id_operacion'] ?? null,
            ]);

            // Registrar en logs_sistema
            $this->log(
                'Factura enviada por email',
                'facturas',
                "ID: {$id}, UUID: {$factura['uuid_factura']}, Email: {$emailDestino}, " .
                "Resultado: {$logResultado}, ID Operación: " . ($resultado['id_operacion'] ?? 'N/A')
            );

            if ($resultado['exito']) {
                $this->session->flash('success', "Factura enviada correctamente a {$emailDestino}.");
            } else {
                $this->session->flash('error', 'La API reportó error al enviar: ' . $logDetalle);
            }

        } catch (\Exception $e) {
            // Registrar error en log de envíos si podemos obtener el email
            try {
                $emailDest = trim($this->input('email_destino') ?? '');
                if (!empty($emailDest)) {
                    $emailConfigModel = new EmailConfig();
                    $emailConfigModel->logEnvio([
                        'factura_id'       => $id,
                        'usuario_id'       => $this->userId(),
                        'email_destino'    => $emailDest,
                        'asunto'           => trim($this->input('asunto') ?? '-'),
                        'resultado'        => 'error',
                        'detalle'          => $e->getMessage(),
                        'id_operacion_api' => null,
                    ]);
                }
            } catch (\Exception $logEx) {
                error_log("Error al registrar log de email fallido: " . $logEx->getMessage());
            }

            $this->log('Error al enviar factura por email', 'facturas', "ID: {$id}, Error: " . $e->getMessage());
            $this->session->flash('error', 'Error al enviar: ' . $e->getMessage());
        }

        $this->redirect('/facturas/' . $id);
    }
}
