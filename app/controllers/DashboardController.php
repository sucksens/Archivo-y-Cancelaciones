<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\FacturaArchivo;
use App\Models\Log;
use App\Helpers\PermissionHelper;

class DashboardController extends BaseController
{
    private FacturaArchivo $facturaModel;
    private Log $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
        $this->facturaModel = new FacturaArchivo();
        $this->logModel = new Log();
    }

    public function index(): void
    {
        $this->requirePermission('reports.dashboard');

        $stats = $this->facturaModel->getStats();

        $recentLogs = [];
        if (PermissionHelper::hasPermission('reports.logs')) {
            $recentLogs = $this->logModel->getRecent(10);
        }

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentLogs' => $recentLogs
        ]);
    }

    public function getStats(): void
    {
        $this->requirePermission('reports.dashboard');

        $stats = $this->facturaModel->getStats();

        $this->json($stats);
    }
}
