<?php
use App\Helpers\PermissionHelper;

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>

<!-- Mobile overlay -->
<div id="sidebarOverlay" class="hidden fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out">
    
    <!-- Logo -->
    <div class="flex items-center h-16 px-6 border-b border-gray-200">
        <a href="<?= BASE_URL ?>dashboard" class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-700 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="text-lg font-bold text-gray-800">Archivo y Cancelaciones</span>
        </a>
    </div>
    
    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        
        <!-- Dashboard -->
        <a href="<?= BASE_URL ?>dashboard" class="sidebar-link <?= $currentPath === '/dashboard' || $currentPath === '/' ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>
        
        <!-- Tickets Section -->
        <?php if (PermissionHelper::hasPermission('tickets.create')): ?>
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tickets</p>
        </div>
        
        <a href="<?= BASE_URL ?>tickets/crear" class="sidebar-link <?= strpos($currentPath, '/tickets/crear') !== false ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo Ticket
        </a>
        <?php endif; ?>
        
        <?php if (PermissionHelper::hasPermission('tickets.view.own')): ?>
        <?php if (PermissionHelper::isConsulta()): ?>
        <a href="<?= BASE_URL ?>solicitudes" class="sidebar-link <?= strpos($currentPath, '/solicitudes') !== false ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Solicitudes
        </a>
        <?php else: ?>
        <a href="<?= BASE_URL ?>mis-solicitudes" class="sidebar-link <?= strpos($currentPath, '/mis-solicitudes') !== false ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Mis Solicitudes
        </a>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php if (PermissionHelper::hasPermission('tickets.view.all')): ?>
        <a href="<?= BASE_URL ?>tickets" class="sidebar-link <?= $currentPath === '/tickets' ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            Todos los Tickets
        </a>
        <?php endif; ?>

        <!-- Facturas Section -->
        <?php if (PermissionHelper::hasAnyPermission(['facturas.view.own', 'facturas.view.empresa', 'facturas.view.all', 'facturas.upload'])): ?>
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Facturas</p>
        </div>

        <?php if (PermissionHelper::hasPermission('facturas.upload')): ?>
        <a href="<?= BASE_URL ?>facturas/subir" class="sidebar-link <?= strpos($currentPath, '/facturas/subir') !== false ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Subir Factura
        </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>facturas" class="sidebar-link <?= strpos($currentPath, '/facturas') !== false && strpos($currentPath, '/facturas/subir') === false ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Facturas
        </a>
        <?php endif; ?>

        <!-- Administración Section -->
        <?php if (PermissionHelper::isAdmin()): ?>
        <div class="pt-6">
            <p class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administración</p>
        </div>
        
        <a href="<?= BASE_URL ?>usuarios" class="sidebar-link <?= strpos($currentPath, '/usuarios') !== false ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            Usuarios
        </a>
        
        <a href="<?= BASE_URL ?>admin/roles" class="sidebar-link <?= strpos($currentPath, '/admin/roles') !== false ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Roles y Permisos
        </a>
        
        <?php if (PermissionHelper::hasPermission('reports.logs')): ?>
        <a href="<?= BASE_URL ?>admin/logs" class="sidebar-link <?= strpos($currentPath, '/admin/logs') !== false ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Logs del Sistema
        </a>
        <?php endif; ?>

        <?php if (PermissionHelper::hasPermission('facturas.email.manage_whitelist')): ?>
        <a href="<?= BASE_URL ?>admin/email-config" class="sidebar-link <?= strpos($currentPath, '/admin/email-config') !== false ? 'active' : '' ?>">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Config. Email
        </a>
        <?php endif; ?>
        <?php endif; ?>
        
    </nav>
    
    <!-- User info at bottom -->
    <div class="p-4 border-t border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold">
                    <?= strtoupper(substr($user['nombre_completo'] ?? 'U', 0, 1)) ?>
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">
                    <?= htmlspecialchars($user['nombre_completo'] ?? 'Usuario') ?>
                </p>
                <p class="text-xs text-gray-500 truncate">
                    <?= EMPRESAS[$user['empresa'] ?? 'grupo_motormexa'] ?? 'Empresa' ?>
                </p>
            </div>
        </div>
    </div>
</aside>
