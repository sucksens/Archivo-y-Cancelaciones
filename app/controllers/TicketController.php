<?php
/**
 * TicketController - Controlador de tickets de cancelación
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Ticket;
use App\Models\FacturaOperacion;
use App\Helpers\ValidationHelper;
use App\Helpers\FileUploadHelper;
use App\Helpers\PermissionHelper;
use App\BBj\FacturasBridge;

class TicketController extends BaseController
{
    private Ticket $ticketModel;
    private FacturaOperacion $operacionModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->ticketModel = new Ticket();
        $this->operacionModel = new FacturaOperacion();
    }

    /**
     * Listar todos los tickets
     */
    public function index(): void
    {
        $this->requirePermission('tickets.view.all');

        $page = (int) ($this->input('page') ?? 1);
        $filters = [
            'empresa' => $this->input('empresa'),
            'estado' => $this->input('estado'),
            'tipo_cancelacion' => $this->input('tipo'),
            'tipo_factura' => $this->input('tipo_factura'),
            'fecha_desde' => $this->input('fecha_desde'),
            'fecha_hasta' => $this->input('fecha_hasta'),
            'search' => $this->input('search')
        ];

        $result = $this->ticketModel->getAll($filters, $page);

        $this->view('tickets/listar', [
            'title' => 'Tickets de Cancelación',
            'tickets' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'estados' => TICKET_ESTADOS,
            'empresas' => EMPRESAS,
            'tipos' => TIPOS_CANCELACION,
            'tipos_auto' => TIPOS_AUTO
        ]);
    }

    /**
     * Mis solicitudes (tickets del usuario)
     */
    public function misSolicitudes(): void
    {
        $this->requirePermission('tickets.view.own');

        $page = (int) ($this->input('page') ?? 1);
        $filters = [
            'estado' => $this->input('estado'),
            'tipo_cancelacion' => $this->input('tipo'),
            'tipo_factura' => $this->input('tipo_factura'),
            'fecha_desde' => $this->input('fecha_desde'),
            'fecha_hasta' => $this->input('fecha_hasta'),
            'search' => $this->input('search')
        ];

        $result = $this->ticketModel->getByUser($this->userId(), $page, ITEMS_PER_PAGE, $filters);

        $this->view('tickets/mis-solicitudes', [
            'title' => 'Mis Solicitudes',
            'tickets' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'estados' => TICKET_ESTADOS,
            'tipos' => TIPOS_CANCELACION,
            'tipos_auto' => TIPOS_AUTO
        ]);
    }

    /**
     * Solicitudes (tickets de la empresa - solo rol Consulta)
     */
    public function solicitudes(): void
    {
        $this->requirePermission('tickets.view.empresa');

        $empresa = PermissionHelper::getUserCompany();
        
        if (!$empresa) {
            $this->session->flash('error', 'No tienes una empresa asignada');
            $this->redirect('/dashboard');
        }

        $page = (int) ($this->input('page') ?? 1);
        $filters = [
            'estado' => $this->input('estado'),
            'tipo_cancelacion' => $this->input('tipo'),
            'tipo_factura' => $this->input('tipo_factura'),
            'fecha_desde' => $this->input('fecha_desde'),
            'fecha_hasta' => $this->input('fecha_hasta'),
            'search' => $this->input('search')
        ];

        // Aplicar filtro automático para rol Consulta con especialidad
        if (PermissionHelper::isConsulta()) {
            $especialidadFilter = PermissionHelper::getConsultaTipoFacturaFilter();
            if ($especialidadFilter) {
                $filters['tipo_factura'] = $especialidadFilter;
            }
        }

        $result = $this->ticketModel->getByEmpresa($empresa, $page, ITEMS_PER_PAGE, $filters);

        $canVerifySat = PermissionHelper::hasPermission('tickets.verify_sat');

        $this->view('tickets/solicitudes', [
            'title' => 'Solicitudes',
            'tickets' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'estados' => TICKET_ESTADOS,
            'tipos' => TIPOS_CANCELACION,
            'tipos_auto' => TIPOS_AUTO,
            'canVerifySat' => $canVerifySat,
            'isConsultaWithEspecialidad' => PermissionHelper::isConsultaWithEspecialidad(),
            'userEspecialidadLabel' => PermissionHelper::getUserEspecialidad() ? 
                (ESPECIALIDADES_USUARIO[PermissionHelper::getUserEspecialidad()]['label'] ?? '') : ''
        ]);
    }

    /**
     * Mostrar formulario de creación
     */
    public function create(): void
    {
        $this->requirePermission('tickets.create');

        $this->view('tickets/crear', [
            'title' => 'Nuevo Ticket de Cancelación',
            'empresas' => EMPRESAS,
            'tipos_cancelacion' => TIPOS_CANCELACION,
            'tipos_operacion' => TIPOS_OPERACION,
            'tipos_auto' => TIPOS_AUTO
        ]);
    }

    /**
     * Guardar nuevo ticket
     */
    /**
     * Guardar nuevo ticket
     */
    public function store(): void
    {
        $this->requirePermission('tickets.create');

        $db = \App\Core\Database::getInstance();
        $ticketId = null;

        try {
            $this->validateCsrf();

            // Validar datos básicos del formulario
            $validator = new ValidationHelper($_POST);
            $validator
                ->required('empresa_solicitante', 'La empresa es requerida')
                ->in('empresa_solicitante', array_keys(EMPRESAS))
                ->required('tipo_factura', 'El tipo de factura es requerido')
                ->in('tipo_factura', array_keys(TIPOS_AUTO))
                ->required('uuid_factura', 'El UUID de factura es requerido')
                ->uuid('uuid_factura')
                ->required('serie', 'La serie es requerida')
                ->maxLength('serie', 20)
                ->required('folio', 'El folio es requerido')
                ->maxLength('folio', 20)
                ->required('nombre_cliente', 'El nombre del cliente es requerido')
                ->maxLength('nombre_cliente', 200)
                ->required('total_factura', 'El total de factura es requerido')
                ->numeric('total_factura')
                ->min('total_factura', 0.01)
                ->required('rfc_receptor', 'El RFC del receptor es requerido')
                ->rfc('rfc_receptor')
                ->required('tipo_cancelacion', 'El tipo de cancelación es requerido')
                ->in('tipo_cancelacion', array_keys(TIPOS_CANCELACION))
                ->required('motivo', 'El motivo es requerido')
                ->minLength('motivo', 10, 'El motivo debe tener al menos 10 caracteres');

            if ($validator->hasErrors()) {
                $this->session->flash('error', $validator->getFirstError());
                $this->session->set('old_input', $_POST);
                $this->redirect('/tickets/crear');
            }

            // Validar y subir archivo
            $uploader = new FileUploadHelper();
            $fileData = $_FILES['archivo_autorizacion'] ?? [];
            
            if (empty($fileData) || $fileData['error'] === UPLOAD_ERR_NO_FILE) {
                $this->session->flash('error', 'El archivo de autorización es requerido');
                $this->session->set('old_input', $_POST);
                $this->redirect('/tickets/crear');
            }
            
            $filePath = $uploader->upload($fileData);

            if (!$filePath) {
                $errorMsg = $uploader->getFirstError() ?: 'Error al subir el archivo de autorización';
                $this->session->flash('error', $errorMsg);
                $this->session->set('old_input', $_POST);
                $this->redirect('/tickets/crear');
            }

            // --- CONSULTA A BBJ PARA VALIDAR FACTURA ---
            $empresaSolicitante = $_POST['empresa_solicitante'];
            $tipoFactura = $_POST['tipo_factura'];
            
            // Determinar la base de datos según empresa y tipo de factura
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

            //Revision de si la factura ya tiene un ticket
            $ticket = $this->ticketModel->findByFacturaUuid(ValidationHelper::cleanUuid($_POST['uuid_factura']));
            
            if ($ticket) {
                $this->session->flash('error', "La factura con UUID {$_POST['uuid_factura']} ya tiene un ticket.");
                $this->session->set('old_input', $_POST);
                $this->redirect('/tickets/crear');
            }
           
            //Cargar la base de datos correcta para la empresa y tipo de factura
            $DbName = $dbMapping[$empresaSolicitante][$tipoFactura] ?? '01AN_AUTOSNUEVOS';
            
            $facturaBridge = new FacturasBridge($DbName);
            $uuidLimpio = ValidationHelper::cleanUuid($_POST['uuid_factura']);
            $factura = $facturaBridge->getFactura($uuidLimpio);

            if (!$factura) {
                // Eliminar archivo subido ya que no se creará el ticket
                $uploader->delete($filePath);
                $this->session->flash('error', "La factura con UUID {$uuidLimpio} no fue encontrada en el sistema administrativo ({$empresaSolicitante}).");
                $this->session->set('old_input', $_POST);
                $this->redirect('/tickets/crear');
            }

            // --- INICIO DE TRANSACCIÓN ---
            $db->beginTransaction();

            // Crear ticket con datos del formulario + datos de BBj
            $ticketId = $this->ticketModel->create([
                'usuario_id' => $this->userId(),
                'empresa_solicitante' => $empresaSolicitante,
                'tipo_factura' => $tipoFactura,
                'uuid_factura' => $uuidLimpio,
                'serie' => ValidationHelper::sanitize($_POST['serie']),
                'folio' => ValidationHelper::sanitize($_POST['folio']),
                'inventario' => $factura['INVENTARIO'] ?? ValidationHelper::sanitize($_POST['inventario'] ?? ''),
                'nombre_cliente' => ValidationHelper::sanitize($_POST['nombre_cliente']),
                'total_factura' => floatval($_POST['total_factura']),
                'rfc_receptor' => ValidationHelper::cleanRfc($_POST['rfc_receptor']),
                'tipo_cancelacion' => $_POST['tipo_cancelacion'],
                'motivo' => ValidationHelper::sanitize($_POST['motivo']),
                'archivo_autorizacion' => $filePath,
                'fecfac' => isset($factura['FECFAC']) ? ValidationHelper::BbjDateToMysqlDate($factura['FECFAC']) : null,
                'id_pedido' => $factura['ID_PEDIDO'] ?? null,
                'id_vendedor' => $factura['ID_VENDEDOR'] ?? null,
                'id_suc' => $factura['ID_SUC'] ?? null
            ]);

            // Guardar operaciones relacionadas (Recibos y Doctos Relacionados)
            if (!empty($factura['ID_VENDEDOR']) && !empty($factura['ID_PEDIDO'])) {
                
                // Operaciones de recibos de caja
                $recibos = $facturaBridge->getRecibosCaja($factura['ID_VENDEDOR'], $factura['ID_PEDIDO']);
                foreach ($recibos as $operacion) {
                    $this->operacionModel->create([
                        'ticket_id' => $ticketId,
                        'tipo_operacion' => $operacion['ID_CONCEPTO'],
                        'serie' => $operacion['SERIECP'],
                        'id_compago' => $operacion['ID_COMPAGO'],
                        'uuid_operacion' => $operacion['FOLIOFISCAL'],
                        'monto' => $operacion['IMPORTE'],
                        'requiere_cancelacion' => 1,
                    ]);
                }

                // Operaciones de doctos relacionados
                $doctos = $facturaBridge->getDoctosRelacionados($factura['ID_VENDEDOR'], $factura['ID_PEDIDO']);
                foreach ($doctos as $operacion) {
                    $this->operacionModel->create([
                        'ticket_id' => $ticketId,
                        'tipo_operacion' => 'NAA',
                        'serie' => $operacion['SERIENC'],
                        'id_compago' => $operacion['ID_NOTA'],
                        'uuid_operacion' => $operacion['UUID'],
                        'monto' => $operacion['TOTAL'],
                        'requiere_cancelacion' => 1,
                    ]);
                }
            }

            // Registrar auditoría
            $this->ticketModel->audit($ticketId, $this->userId(), 'Ticket creado');

            // Confirmar cambios
            $db->commit();

            $this->log('Ticket creado', 'tickets', "ID: {$ticketId}");
            $this->session->remove('old_input');
            $this->session->flash('success', 'Ticket creado correctamente');
            $this->redirect('/tickets/' . $ticketId);

        } catch (\Exception $e) {
            if ($db->getConnection()->inTransaction()) {
                $db->rollBack();
            }

            // Log detallado para administración
            $errorDetails = [
                'mensaje' => $e->getMessage(),
                'clase' => get_class($e),
                'request' => $_POST,
                'user_id' => $this->userId()
            ];
            $this->log('Error al crear ticket: ' . $e->getMessage(), 'tickets', json_encode($errorDetails));

            $this->session->flash('error', 'Ocurrió un error al procesar la solicitud. Por favor, intente de nuevo o contacte a soporte.');
            $this->redirect('/tickets/crear');
        }
    }

    /**
     * Ver detalle de ticket
     * 
     * @param int $id ID del ticket
     */
    public function show(int $id): void
    {
        $ticket = $this->ticketModel->getWithOperaciones($id);

        if (!$ticket) {
            $this->session->flash('error', 'Ticket no encontrado');
            $this->redirect('/tickets');
        }

        // Verificar permisos
        $canView = $this->hasPermission('tickets.view.all') 
                || ($this->hasPermission('tickets.view.own') && $ticket['usuario_id'] === $this->userId())
                || ($this->hasPermission('tickets.view.empresa') && $ticket['empresa_solicitante'] === PermissionHelper::getUserCompany());


        if (!$canView) {
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }

        $this->view('tickets/detalle', [
            'title' => 'Ticket #' . $ticket['id'],
            'ticket' => $ticket,
            'estados' => TICKET_ESTADOS,
            'tipos_operacion' => TIPOS_OPERACION,
            'tipos_auto' => TIPOS_AUTO,
            'canEdit' => $this->hasPermission('tickets.edit'),
            'canChangeStatus' => $this->hasPermission('tickets.status'),
            'canProcess' => $this->hasPermission('tickets.process')
        ]);
    }

    /**
     * Actualizar estado del ticket
     * 
     * @param int $id ID del ticket
     */
    public function updateStatus(int $id): void
    {
        $this->requirePermission('tickets.status');

        try {
            $this->validateCsrf();

            $ticket = $this->ticketModel->find($id);
            if (!$ticket) {
                $this->json(['error' => 'Ticket no encontrado'], 404);
            }

            $nuevoEstado = $this->input('estado');
            if (!array_key_exists($nuevoEstado, TICKET_ESTADOS)) {
                $this->json(['error' => 'Estado no válido'], 400);
            }

            $estadoAnterior = $ticket['estado'];
            $this->ticketModel->updateStatus($id, $nuevoEstado, $this->userId());
            
            // Registrar auditoría
            $this->ticketModel->audit(
                $id, 
                $this->userId(), 
                'Cambio de estado',
                'estado',
                $estadoAnterior,
                $nuevoEstado
            );

            $this->log('Cambio de estado de ticket', 'tickets', "ID: {$id}, {$estadoAnterior} -> {$nuevoEstado}");

            if ($this->isAjax()) {
                $this->json([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente',
                    'estado' => $nuevoEstado,
                    'estado_info' => TICKET_ESTADOS[$nuevoEstado]
                ]);
            }

            $this->session->flash('success', 'Estado actualizado correctamente');
            $this->redirect('/tickets/' . $id);

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Error al actualizar estado'], 500);
            }
            $this->session->flash('error', 'Error al actualizar estado');
            $this->redirect('/tickets/' . $id);
        }
    }

    /**
     * Eliminar ticket
     * 
     * @param int $id ID del ticket
     */
    public function destroy(int $id): void
    {
        $this->requirePermission('tickets.delete');

        try {
            $this->validateCsrf();

            $ticket = $this->ticketModel->find($id);
            if (!$ticket) {
                $this->json(['error' => 'Ticket no encontrado'], 404);
            }

            // Solo eliminar si está pendiente
            if ($ticket['estado'] !== 'pendiente') {
                $this->json(['error' => 'Solo se pueden eliminar tickets pendientes'], 400);
            }

            // Eliminar archivo
            if ($ticket['archivo_autorizacion']) {
                $uploader = new FileUploadHelper();
                $uploader->delete($ticket['archivo_autorizacion']);
            }

            $this->ticketModel->delete($id);

            $this->log('Ticket eliminado', 'tickets', "ID: {$id}");

            if ($this->isAjax()) {
                $this->json(['success' => true, 'message' => 'Ticket eliminado']);
            }

            $this->session->flash('success', 'Ticket eliminado correctamente');
            $this->redirect('/tickets');

        } catch (\Exception $e) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Error al eliminar ticket'], 500);
            }
            $this->session->flash('error', 'Error al eliminar ticket');
            $this->redirect('/tickets');
        }
    }

    /**
     * Descargar archivo de autorización
     * 
     * @param int $id ID del ticket
     */
    public function downloadFile(int $id): void
    {
        $ticket = $this->ticketModel->find($id);

        if (!$ticket) {
            http_response_code(404);
            exit('Archivo no encontrado');
        }

        // Verificar permisos
        $canView = $this->hasPermission('tickets.view.all') 
                || ($this->hasPermission('tickets.view.own') && $ticket['usuario_id'] === $this->userId());

        if (!$canView) {
            http_response_code(403);
            exit('Acceso denegado');
        }

        $filePath = UPLOADS_PATH . '/' . $ticket['archivo_autorizacion'];
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit('Archivo no encontrado');
        }

        $filename = basename($ticket['archivo_autorizacion']);
        $mimeType = mime_content_type($filePath);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    }

    /**
     * Alternar bandera de una operación (AJAX)
     * 
     * @param int $id ID de la operación
     */
    public function toggleOperacionFlag(int $id): void
    {
        $this->requirePermission('tickets.status');

        try {
            $this->validateCsrf();

            $operacion = $this->operacionModel->find($id);
            if (!$operacion) {
                $this->json(['error' => 'Operación no encontrada'], 404);
            }

            $flag = $this->input('flag');
            $allowedFlags = ['cancelada', 'cancelado_sistema', 'cancelado_sat'];

            if (!in_array($flag, $allowedFlags)) {
                $this->json(['error' => 'Bandera no válida'], 400);
            }

            $nuevoValor = $operacion[$flag] ? 0 : 1;
            $updateData = [$flag => $nuevoValor];

            // Si se marca como cancelado, guardamos la fecha
            if ($nuevoValor === 1) {
                if ($flag === 'cancelada' || $flag === 'cancelado_sistema') {
                    $updateData['fecha_cancelacion'] = date('Y-m-d H:i:s');
                } elseif ($flag === 'cancelado_sat') {
                    $updateData['fecha_cancelacion_sat'] = date('Y-m-d H:i:s');
                }
            } else {
                // Si se desmarca, podríamos limpiar la fecha, pero mejor dejarla como histórico o limpiarla según regla de negocio
                if ($flag === 'cancelada' || $flag === 'cancelado_sistema') {
                    $updateData['fecha_cancelacion'] = null;
                } elseif ($flag === 'cancelado_sat') {
                    $updateData['fecha_cancelacion_sat'] = null;
                }
            }

            $this->operacionModel->update($id, $updateData);

            // Registrar auditoría en el ticket relacionado
            $this->ticketModel->audit(
                $operacion['ticket_id'],
                $this->userId(),
                "Cambio de bandera '{$flag}' en operación #{$id}",
                $flag,
                $operacion[$flag] ? 'Sí' : 'No',
                $nuevoValor ? 'Sí' : 'No'
            );

            $this->json([
                'success' => true,
                'message' => 'Operación actualizada correctamente',
                'nuevo_valor' => $nuevoValor,
                'flag' => $flag
            ]);

        } catch (\Exception $e) {
            $this->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Validar estatus de factura ante el SAT
     * 
     * @param int $id ID del ticket
     */
    public function validateSatStatus(int $id): void
    {
        $ticket = $this->ticketModel->find($id);

        if (!$ticket) {
            $this->json(['error' => 'Ticket no encontrado'], 404);
        }

        // Definir RFC Emisor según la empresa
        $rfcEmisor = '';
        if ($ticket['empresa_solicitante'] === 'grupo_motormexa') {
            $rfcEmisor = 'GMG090821RT0';
        } else if ($ticket['empresa_solicitante'] === 'automotriz_motormexa') {
            $rfcEmisor = 'AMO021114AG5';
        }

        if (empty($rfcEmisor)) {
            $this->json(['error' => 'RFC emisor no configurado para esta empresa'], 400);
        }

        // Preparar datos para el API
        $data = [
            'rfc_emisor' => $rfcEmisor,
            'rfc_receptor' => $ticket['rfc_receptor'],
            'total' => (float)$ticket['total_factura'],
            'uuid' => $ticket['uuid_factura']
        ];

        // Realizar petición al API SAT externo
        $apiUrl = 'http://200.1.1.245:5000/validar_factura/';
        
        try {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                $this->json(['error' => 'Error al conectar con el servidor de validación: ' . $error], 500);
            }

            $decodedResponse = json_decode($response, true);
            
            if ($httpCode >= 400) {
                $this->json([
                    'error' => 'El servidor de validación respondió con un error',
                    'detail' => $decodedResponse['detail'] ?? $response
                ], $httpCode);
            }

            $this->log("Consultó estatus SAT para ticket #{$id}", 'tickets');
            $this->json($decodedResponse);

        } catch (\Exception $e) {
            $this->json(['error' => 'Ocurrió un error inesperado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Parsear archivo XML para extraer datos (AJAX)
     */
    public function parseXml(): void
    {
        $this->requirePermission('tickets.create');

        try {
            $fileData = $_FILES['xml_file'] ?? null;

            if (!$fileData || $fileData['error'] !== UPLOAD_ERR_OK) {
                $this->json(['error' => 'No se recibió un archivo válido'], 400);
            }

            // Validar que sea XML
            $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) !== 'xml') {
                $this->json(['error' => 'El archivo debe ser un XML'], 400);
            }

            // Preparar el archivo para enviarlo por cURL
            $filePath = $fileData['tmp_name'];
            $mimeType = $fileData['type'] ?: 'text/xml';
            $cfile = curl_file_create($filePath, $mimeType, $fileData['name']);

            $data = ['file' => $cfile];

            // Realizar petición al API de parseo
            $apiUrl = 'http://200.1.1.245:5000/parsear_xml/';
            
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                $this->json(['error' => 'Error al conectar con el servidor de parseo: ' . $error], 500);
            }

            $decodedResponse = json_decode($response, true);
            
            if ($httpCode >= 400) {
                $this->json([
                    'error' => 'El servidor de parseo respondió con un error',
                    'detail' => $decodedResponse['error'] ?? $decodedResponse['detail'] ?? $response
                ], $httpCode);
            }

            $this->json($decodedResponse);

        } catch (\Exception $e) {
            $this->json(['error' => 'Ocurrió un error inesperado: ' . $e->getMessage()], 500);
        }
    }
}
