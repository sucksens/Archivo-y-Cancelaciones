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
            $result = $this->getAllFacturas($filters, $page);
        } elseif (PermissionHelper::hasPermission('facturas.view.empresa')) {
            $empresa = PermissionHelper::getUserCompany();
            if (!$empresa) {
                $this->session->flash('error', 'No tienes una empresa asignada');
                $this->redirect('/dashboard');
            }
            $result = $this->facturaModel->getByEmpresa($empresa, $page, ITEMS_PER_PAGE, $filters);
        } else {
            $result = $this->facturaModel->getByUser($this->userId(), $page, ITEMS_PER_PAGE, $filters);
        }

        $this->view('facturas/index', [
            'title' => 'Archivos de Facturas',
            'facturas' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'empresas' => EMPRESAS,
            'tipos_auto' => TIPOS_AUTO
        ]);
    }

    /**
     * Mostrar formulario de subida
     */
    public function create(): void
    {
        $this->requirePermission('facturas.upload');

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
            /*
            $user = AuthHelper::getUser();
            if ($empresa !== $user['empresa']) {
                $uploader->delete($xmlPath);
                if ($pdfPath) $uploader->delete($pdfPath);
                throw new \Exception('No puedes subir facturas de una empresa diferente a la tuya');
            }
            */

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

            $filePath = $_FILES['archivo_xml']['tmp_name'];
            $mimeType = $_FILES['archivo_xml']['type'] ?: 'text/xml';
            $cfile = curl_file_create($filePath, $mimeType, $_FILES['archivo_xml']['name']);

            $apiUrl = 'http://200.1.1.245:5000/parsear_xml/';
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $cfile]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            $parsedData = [];
            if ($response && $httpCode === 200) {
                $decoded = json_decode($response, true);
                if ($decoded && isset($decoded['exito']) && $decoded['exito']) {
                    switch ($decoded['version']) {
                        case '4.0':
                            $cfdi = $decoded['datos']['cfdi40'] ?? [];
                            $timbre = $decoded['datos']['tfd11'] ?? [];
                            break;
                        case '3.3':
                            $cfdi = $decoded['datos']['cfdi33'] ?? [];
                            $timbre = $decoded['datos']['tfd11'] ?? [];
                            break;
                        case '3.2':
                            $cfdi = $decoded['datos']['cfdi32'] ?? [];
                            $timbre = $decoded['datos']['tfd10'] ?? [];
                            break;
                    }
                }
            }

            $facturaId = $this->facturaModel->create([
                'usuario_id' => $this->userId(),
                'empresa' => $empresa,
                'tipo_factura' => $tipoFactura,
                'uuid_factura' => $uuidLimpio,
                'archivo_xml' => $xmlPath,
                'archivo_pdf' => $pdfPath,
                'serie' => $cfdi['serie'],
                'folio' => $cfdi['folio'],
                'total' => isset($cfdi['total']) ? (float) $cfdi['total'] : null,
                'fecha_emision' => $timbre['fecha_timbrado'],
                'rfc_emisor' => $cfdi['emisor']['rfc'],
                'rfc_receptor' => $cfdi['receptor']['rfc'],
                'id_suc' => $facturaBbj['ID_SUC'] ?? null,
                'fecfac' => isset($facturaBbj['FECFAC']) ? ValidationHelper::BbjDateToMysqlDate($facturaBbj['FECFAC']) : null,
                'inventario' => $facturaBbj['INVENTARIO'] ?? null,
                'id_vendedor' => $facturaBbj['ID_VENDEDOR'] ?? null,
                'datos_extra' => json_encode($cfdi)
            ]);

            $this->log('Factura subida', 'facturas', "ID: {$facturaId}, UUID: {$uuidLimpio},".json_encode($cfdi)," ");
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

        $canView = PermissionHelper::hasPermission('facturas.view.all')
                || (PermissionHelper::hasPermission('facturas.view.own') && $factura['usuario_id'] === $this->userId())
                || (PermissionHelper::hasPermission('facturas.view.empresa') && $factura['empresa'] === PermissionHelper::getUserCompany());

        if (!$canView) {
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $canDownload = PermissionHelper::hasPermission('facturas.download');
        $canDelete = PermissionHelper::isAdmin();

        $this->view('facturas/detalle', [
            'title' => 'Factura #' . $id,
            'factura' => $factura,
            'empresas' => EMPRESAS,
            'tipos_auto' => TIPOS_AUTO,
            'canDownload' => $canDownload,
            'canDelete' => $canDelete
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

        $canDownload = PermissionHelper::hasPermission('facturas.download');
        if (!$canDownload) {
            http_response_code(403);
            exit('Acceso denegado');
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

            $this->log('Factura eliminada', 'facturas', "ID: {$id}, UUID: {$factura['uuid_factura']}");

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

    /**
     * Obtener todas las facturas (para administradores)
     *
     * @param array $filters Filtros
     * @param int $page Página
     * @param int $limit Límite
     * @return array
     */
    private function getAllFacturas(array $filters = [], int $page = 1, int $limit = ITEMS_PER_PAGE): array
    {
        $where = ["fa.estado = 'activo'"];
        $params = [];

        if (!empty($filters['empresa'])) {
            $where[] = 'fa.empresa = ?';
            $params[] = $filters['empresa'];
        }

        if (!empty($filters['tipo_factura'])) {
            $where[] = 'fa.tipo_factura = ?';
            $params[] = $filters['tipo_factura'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(fa.uuid_factura LIKE ? OR fa.serie LIKE ? OR fa.folio LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params = array_merge($params, [$search, $search, $search]);
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(*) FROM facturas_archivo fa WHERE {$whereClause}";
        $total = (int) $this->db->fetchColumn($countSql, $params);

        $sql = "SELECT fa.*, u.nombre_completo as usuario_nombre
                FROM facturas_archivo fa
                LEFT JOIN usuarios u ON fa.usuario_id = u.id
                WHERE {$whereClause}
                ORDER BY fa.fecha_subida DESC
                LIMIT {$limit} OFFSET {$offset}";

        $facturas = $this->db->fetchAll($sql, $params);

        return [
            'data' => $facturas,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ];
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
}
