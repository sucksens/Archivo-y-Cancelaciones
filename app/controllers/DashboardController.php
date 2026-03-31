<?php
/**
 * DashboardController - Controlador del dashboard
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Ticket;
use App\Models\Log;
use App\Helpers\PermissionHelper;

class DashboardController extends BaseController
{
    private Ticket $ticketModel;
    private Log $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->ticketModel = new Ticket();
        $this->logModel = new Log();
    }

    /**
     * Mostrar dashboard principal
     */
    public function index(): void
    {
        $this->requirePermission('reports.dashboard');

        // Obtener estadísticas según permisos
        if (PermissionHelper::hasPermission('tickets.view.all')) {
            $stats = $this->ticketModel->getStats();
            $recentTickets = $this->ticketModel->getRecent(5);
        } else {
            $stats = $this->ticketModel->getStats($this->userId());
            $recentTickets = $this->ticketModel->getByUser($this->userId(), 1, 5)['data'];
        }

        // Obtener logs recientes si tiene permisos
        $recentLogs = [];
        if (PermissionHelper::hasPermission('reports.logs')) {
            $recentLogs = $this->logModel->getRecent(10);
        }

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentTickets' => $recentTickets,
            'recentLogs' => $recentLogs,
            'estados' => TICKET_ESTADOS
        ]);
    }

    /**
     * Obtener estadísticas actualizadas (AJAX)
     */
    public function getStats(): void
    {
        $this->requirePermission('reports.dashboard');

        if (PermissionHelper::hasPermission('tickets.view.all')) {
            $stats = $this->ticketModel->getStats();
        } else {
            $stats = $this->ticketModel->getStats($this->userId());
        }

        $this->json($stats);
    }

    /**
     * Obtener tickets recientes (AJAX)
     */
    public function getRecentTickets(): void
    {
        $this->requirePermission('reports.dashboard');

        $limit = (int) ($this->input('limit') ?? 5);

        if (PermissionHelper::hasPermission('tickets.view.all')) {
            $tickets = $this->ticketModel->getRecent($limit);
        } else {
            $tickets = $this->ticketModel->getByUser($this->userId(), 1, $limit)['data'];
        }

        $this->json($tickets);
    }
}
