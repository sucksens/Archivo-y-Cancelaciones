<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($title ?? 'Sistema de Cancelaciones') ?> - <?= APP_NAME ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/v2.0.18/24/outline/esm/index.js" type="module"></script>
    
    <!-- Estilos personalizados -->
    <style>
        [x-cloak] { display: none !important; }
        
        .sidebar-link {
            @apply flex items-center px-4 py-3 text-gray-600 transition-colors rounded-lg hover:bg-gray-100 hover:text-gray-900;
        }
        
        .sidebar-link.active {
            @apply bg-primary-50 text-primary-700 font-medium;
        }
        
        .btn {
            @apply inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2;
        }
        
        .btn-primary {
            @apply bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500;
        }
        
        .btn-secondary {
            @apply bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-500;
        }
        
        .btn-danger {
            @apply bg-red-600 text-white hover:bg-red-700 focus:ring-red-500;
        }
        
        .btn-success {
            @apply bg-green-600 text-white hover:bg-green-700 focus:ring-green-500;
        }
        
        .form-input {
            @apply w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 focus:outline-none transition-all;
        }
        
        .form-select {
            @apply w-full px-4 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg focus:border-primary-500 focus:ring-2 focus:ring-primary-200 focus:outline-none transition-all;
        }
        
        .form-label {
            @apply block mb-2 text-sm font-medium text-gray-700;
        }
        
        .card {
            @apply bg-white rounded-xl shadow-sm border border-gray-100;
        }
        
        .card-header {
            @apply px-6 py-4 border-b border-gray-100;
        }
        
        .card-body {
            @apply p-6;
        }
        
        /* Toast Styles */
        .toast-container {
            @apply fixed top-4 right-4 z-50 space-y-2;
        }
        
        .toast {
            @apply flex items-center p-4 rounded-lg shadow-lg transform transition-all duration-300;
        }
        
        .toast-success { @apply bg-green-500 text-white; }
        .toast-error { @apply bg-red-500 text-white; }
        .toast-warning { @apply bg-yellow-500 text-white; }
        .toast-info { @apply bg-blue-500 text-white; }
        
        /* Estado badges */
        .badge { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium; }
        .badge-yellow { @apply bg-yellow-100 text-yellow-800; }
        .badge-blue { @apply bg-blue-100 text-blue-800; }
        .badge-orange { @apply bg-orange-100 text-orange-800; }
        .badge-red { @apply bg-red-100 text-red-800; }
        .badge-gray { @apply bg-gray-100 text-gray-800; }
        .badge-green { @apply bg-green-100 text-green-800; }
        
        /* Animations */
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .animate-slide-in { animation: slideIn 0.3s ease-out; }
        .animate-fade-out { animation: fadeOut 0.3s ease-out forwards; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        
        <!-- Sidebar -->
        <?php include VIEWS_PATH . '/layouts/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Navbar -->
            <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <!-- Mobile menu button -->
                        <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <!-- User dropdown -->
                        <div class="relative" id="userDropdown">
                            <button class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                                    <span class="text-primary-700 font-medium text-sm">
                                        <?= strtoupper(substr($user['nombre_completo'] ?? 'U', 0, 1)) ?>
                                    </span>
                                </div>
                                <span class="hidden md:block text-sm font-medium text-gray-700">
                                    <?= htmlspecialchars($user['nombre_completo'] ?? 'Usuario') ?>
                                </span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 border border-gray-100">
                                <a href="<?= BASE_URL ?>perfil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mi Perfil
                                </a>
                                <hr class="my-1 border-gray-100">
                                <form action="<?= BASE_URL ?>logout" method="POST" class="block">
                                    <?= \App\Helpers\AuthHelper::getCsrfField() ?>
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                
                <!-- Flash Messages -->
                <?php if ($session->hasFlash()): ?>
                <div class="toast-container">
                    <?php foreach ($session->getFlash() as $type => $messages): ?>
                        <?php foreach ($messages as $message): ?>
                        <div class="toast toast-<?= $type ?> animate-slide-in">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php if ($type === 'success'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                <?php elseif ($type === 'error'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                <?php elseif ($type === 'warning'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                <?php else: ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                <?php endif; ?>
                            </svg>
                            <span><?= htmlspecialchars($message) ?></span>
                            <button class="ml-4 hover:opacity-75" onclick="this.parentElement.remove()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
