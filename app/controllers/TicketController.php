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
            if (!empty($_POST['operaciones']) && is_array($_POST['operaciones'])) {
                foreach ($_POST['operaciones'] as $op) {
                    if (!empty($op['uuid_operacion'])) {
                        $this->operacionModel->create([
                            'ticket_id' => $ticketId,
                            'tipo_operacion' => $op['tipo_operacion'] ?? 'documento_relacionado',
                            'uuid_operacion' => ValidationHelper::cleanUuid($op['uuid_operacion']),
                            'descripcion' => ValidationHelper::sanitize($op['descripcion'] ?? ''),
                            'monto' => !empty($op['monto']) ? floatval($op['monto']) : null,
                            'fecha_operacion' => !empty($op['fecha_operacion']) ? $op['fecha_operacion'] : null,
                            'requiere_cancelacion' => isset($op['requiere_cancelacion']) ? 1 : 0,
                            'observaciones' => ValidationHelper::sanitize($op['observaciones'] ?? '')
                        ]);
                    }
                }
            }

            // Registrar auditoría
            $this->ticketModel->audit($ticketId, $this->userId(), 'Ticket creado');

            $this->log('Ticket creado', 'tickets', "ID: {$ticketId}");
            $this->session->remove('old_input');
            $this->session->flash('success', 'Ticket creado correctamente');
            $this->redirect('/tickets/' . $ticketId);

        } catch (\Exception $e) {
            $this->log('Error al crear ticket: ' . $e->getMessage(), 'tickets');
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
