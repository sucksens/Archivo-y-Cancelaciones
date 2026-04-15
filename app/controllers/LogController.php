<?php
/**
 * LogController - Gestión de logs del sistema
 * Sistema de Tickets de Cancelación
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Log;
use App\Models\User;

class LogController extends BaseController
{
    private Log $logModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->requirePermission('reports.logs');
        $this->logModel = new Log();
        $this->userModel = new User();
    }

    /**
     * Mostrar listado de logs
     */
    public function index(): void
    {
        $page = (int) $this->input('page', 1);
        $filters = [
            'tipo_log' => $this->input('tipo_log'),
            'modulo' => $this->input('modulo'),
            'usuario_id' => $this->input('usuario_id'),
            'fecha_desde' => $this->input('fecha_desde'),
            'fecha_hasta' => $this->input('fecha_hasta'),
            'search' => $this->input('search')
        ];

        $logs = $this->logModel->getAll($filters, $page);
        $modules = $this->logModel->getModules();
        $users = $this->userModel->getAll(['activo' => 1], 1, 1000)['data'];

        $this->view('admin.logs', [
            'title' => 'Logs del Sistema',
            'logs' => $logs['data'],
            'pagination' => [
                'total' => $logs['total'],
                'page' => $logs['page'],
                'pages' => $logs['pages'],
                'limit' => $logs['limit']
            ],
            'filters' => $filters,
            'modules' => $modules,
            'users' => $users
        ]);
    }
}
