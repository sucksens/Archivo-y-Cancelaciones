<?php
/**
 * TicketController - Controlador de tickets de cancelación
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
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
            'tipos' => TIPOS_CANCELACION
        ]);
    }

    /**
     * Mis solicitudes (tickets del usuario)
     */
    public function misSolicitudes(): void
    {
        $this->requirePermission('tickets.view.own');

        $page = (int) ($this->input('page') ?? 1);
        $result = $this->ticketModel->getByUser($this->userId(), $page);

        $this->view('tickets/mis-solicitudes', [
            'title' => 'Mis Solicitudes',
            'tickets' => $result['data'],
            'pagination' => $result,
            'estados' => TICKET_ESTADOS
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
            'tipos_operacion' => TIPOS_OPERACION
        ]);
    }

    /**
     * Guardar nuevo ticket
     */
    public function store(): void
    {
        $this->requirePermission('tickets.create');

        try {
            $this->validateCsrf();

            // Validar datos
            $validator = new ValidationHelper($_POST);
            $validator
                ->required('empresa_solicitante', 'La empresa es requerida')
                ->in('empresa_solicitante', array_keys(EMPRESAS))
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
            
            // DIAGNÓSTICO TEMPORAL: 
            if (empty($_FILES)) {
                die("ERROR CRÍTICO: El servidor no recibió ningún archivo (FILES está vacío). " . 
                    "POST tiene: " . count($_POST) . " campos. " . 
                    "Límite máximo de POST (post_max_size): " . ini_get('post_max_size'));
            }
            
            $filePath = $uploader->upload($fileData);

            if (!$filePath) {
                $errorMsg = $uploader->getFirstError() ?: 'Error al subir el archivo de autorización';
                $this->session->flash('error', $errorMsg);
                $this->session->set('old_input', $_POST);
                $this->redirect('/tickets/crear');
            }

            // Crear ticket
            $ticketId = $this->ticketModel->create([
                'usuario_id' => $this->userId(),
                'empresa_solicitante' => $_POST['empresa_solicitante'],
                'uuid_factura' => ValidationHelper::cleanUuid($_POST['uuid_factura']),
                'serie' => ValidationHelper::sanitize($_POST['serie']),
                'folio' => ValidationHelper::sanitize($_POST['folio']),
                'inventario' => ValidationHelper::sanitize($_POST['inventario'] ?? ''),
                'nombre_cliente' => ValidationHelper::sanitize($_POST['nombre_cliente']),
                'total_factura' => floatval($_POST['total_factura']),
                'rfc_receptor' => ValidationHelper::cleanRfc($_POST['rfc_receptor']),
                'tipo_cancelacion' => $_POST['tipo_cancelacion'],
                'motivo' => ValidationHelper::sanitize($_POST['motivo']),
                'archivo_autorizacion' => $filePath
            ]);

            // Guardar operaciones relacionadas
            // La logica de buscar los datos de la factura en bbj 
            if($_POST['empresa_solicitante'] == 'grupo_motormexa'){
                $DbName  = "01AN_AUTOSNUEVOS";
            }elseif ($_POST['empresa_solicitante'] == 'automotriz_motormexa') {
                $DbName  = "02AN_AUTOSNUEVOS";
            }
            //llamada a un helper de bbj
            $facturaBridge = new FacturasBridge($DbName);
            $factura = $facturaBridge->getFactura(ValidationHelper::cleanUuid($_POST['uuid_factura']));

            //estos son los datos para ya actualizar la factura de bbj
            $actualizado = $this->ticketModel->update($ticketId, [
                'fecfac' => ValidationHelper::BbjDateToMysqlDate($factura['FECFAC']),
                'id_pedido' => $factura['ID_PEDIDO'],
                'id_vendedor' => $factura['ID_VENDEDOR'],
                'id_suc' => $factura['ID_SUC'],
                'inventario' => $factura['INVENTARIO']
            ]);

            // La logica de buscar las operaciones 
            if (!empty($factura['ID_VENDEDOR']) && !empty($factura['ID_PEDIDO'])) {
                
                //operaciones de recibos de caja
                $operaciones = $facturaBridge->getRecibosCaja($factura['ID_VENDEDOR'], $factura['ID_PEDIDO']);
                foreach ($operaciones as $operacion) {
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

                //operaciones de doctos relacionados
                $operaciones = $facturaBridge->getDoctosRelacionados($factura['ID_VENDEDOR'], $factura['ID_PEDIDO']);
                foreach ($operaciones as $operacion) {
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

            $this->log('Ticket creado', 'tickets', "ID: {$ticketId}");
            $this->session->remove('old_input');
            $this->session->flash('success', 'Ticket creado correctamente');
            $this->redirect('/tickets/' . $ticketId);

        } catch (\Exception $e) {
                $errorDetails = [
                'mensaje' => $e->getMessage(),
                'codigo' => $e->getCode(),
                'archivo' => $e->getFile(),
                'linea' => $e->getLine(),
                'clase' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'trace_completo' => $e->getTrace(),
                'request_data' => [
                    'post' => $_POST,
                    'files' => isset($_FILES) ? array_map(fn($f) => [
                        'name' => $f['name'] ?? null,
                        'type' => $f['type'] ?? null,
                        'size' => $f['size'] ?? null,
                        'error' => $f['error'] ?? null,
                    ], $_FILES) : [],
                    'server' => [
                        'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
                        'request_method' => $_SERVER['REQUEST_METHOD'] ?? null,
                        'http_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
                    ],
                ],
                'datos_ticket' => [
                    'empresa_solicitante' => $_POST['empresa_solicitante'] ?? null,
                    'uuid_factura' => $_POST['uuid_factura'] ?? null,
                    'serie' => $_POST['serie'] ?? null,
                    'folio' => $_POST['folio'] ?? null,
                    'nombre_cliente' => $_POST['nombre_cliente'] ?? null,
                    'total_factura' => $_POST['total_factura'] ?? null,
                    'rfc_receptor' => $_POST['rfc_receptor'] ?? null,
                    'tipo_cancelacion' => $_POST['tipo_cancelacion'] ?? null,
                    'motivo' => $_POST['motivo'] ?? null,
                ],
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $this->userId(),
            ];

            $this->log('Error al crear ticket: ' . $e->getMessage(), 'tickets', json_encode($errorDetails,JSON_PRETTTY_PRINT|JSON_UNSCAPED_UNICODE));
            $this->session->flash('error', 'Error al crear el ticket');
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
                || ($this->hasPermission('tickets.view.own') && $ticket['usuario_id'] === $this->userId());

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
}
