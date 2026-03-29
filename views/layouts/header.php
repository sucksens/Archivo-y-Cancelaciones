<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= htmlspecialchars($title ?? 'Sistema de Cancelaciones') ?> - <?= APP_NAME ?></title>
    
    <!-- Google Fonts - Inter (Motormexa Identity) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        /* ===== MOTORMEXA IDENTITY COLORS ===== */
                        primary: {
                            50: '#e8eef7',
                            100: '#d1ddef',
                            200: '#a3bbe0',
                            300: '#7599d0',
                            400: '#4777c1',
                            500: '#224580',  /* primary-blue */
                            600: '#1a3564',
                            700: '#162745',
                            800: '#0f1a2e',
                            900: '#080d17',
                        },
                        /* Rojo institucional */
                        red: {
                            50: '#fef2f2',
                            100: '#f8e6e5',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#dd1815',  /* primary-red */
                            600: '#c41411',
                            700: '#a11310',
                            800: '#7f1d1d',
                            900: '#450a0a',
                        },
                        /* Oro acento / Warning */
                        gold: {
                            50: '#fdf6e8',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f5ac3d',  /* accent-gold */
                            600: '#e69b35',
                            700: '#d4932a',
                            800: '#b45309',
                            900: '#78350f',
                        },
                        /* Edit Blue */
                        edit: {
                            50: '#dbeafe',
                            100: '#bfdbfe',
                            200: '#93c5fd',
                            300: '#60a5fa',
                            400: '#3b82f6',  /* edit-blue */
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        /* Update Green / Success */
                        success: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',  /* update-green */
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        /* Warning (usando gold) */
                        warning: {
                            50: '#fdf6e8',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        },
                        /* Danger */
                        danger: {
                            50: '#fef2f2',
                            100: '#fee2e2',
                            200: '#fecaca',
                            300: '#fca5a5',
                            400: '#f87171',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                        },
                        /* Info */
                        info: {
                            50: '#cffafe',
                            100: '#a5f3fc',
                            200: '#67e8f9',
                            300: '#22d3ee',
                            400: '#06b6d4',
                            500: '#06b6d4',
                            600: '#0891b2',
                            700: '#0e7490',
                            800: '#155e75',
                            900: '#164e63',
                        },
                        /* Brand Extended */
                        brand: {
                            orange: '#ff6b35',
                            purple: '#8b5cf6',
                            teal: '#14b8a6',
                        },
                        /* Neutrales oscuros */
                        dark: {
                            primary: '#202022',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
                :root {
            /* ===== PALETA ORIGINAL COMPLETA ===== */
            --primary-red: #dd1815;
            --primary-blue: #224580;
            --accent-gold: #f5ac3d;
            --dark-primary: #202022;
            --white: #ffffff;
            --black: #000000;
            /* Variaciones originales */
            --red-hover: #c41411;
            --red-light: #f8e6e5;
            --red-dark: #a11310;
            --blue-hover: #1a3564;
            --blue-light: #e8eef7;
            --blue-dark: #162745;
            --gold-hover: #e69b35;
            --gold-light: #fdf6e8;
            --gold-dark: #d4932a;
            /* ===== NUEVOS COLORES DEL AVANCE ===== */
            --edit-blue: #3b82f6;
            --edit-blue-hover: #2563eb;
            --edit-blue-light: #dbeafe;
            --edit-blue-dark: #1d4ed8;
            --update-green: #10b981;
            --update-green-hover: #059669;
            --update-green-light: #ecfdf5;
            --update-green-dark: #047857;
            /* ===== PALETA EXPANDIDA ===== */
            /* Colores de estado adicionales */
            --success: #22c55e;
            --success-hover: #16a34a;
            --success-light: #dcfce7;
            --success-dark: #15803d;
            --warning: #f59e0b;
            --warning-hover: #d97706;
            --warning-light: #fef3c7;
            --warning-dark: #b45309;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --danger-light: #fecaca;
            --danger-dark: #b91c1c;
            --info: #06b6d4;
            --info-hover: #0891b2;
            --info-light: #cffafe;
            --info-dark: #0e7490;
            /* Colores neutrales expandidos */
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            /* Colores de fondo y superficie */
            --surface-50: #fafafa;
            --surface-100: #f4f4f5;
            --surface-200: #e4e4e7;
            --surface-300: #d4d4d8;
            --surface-400: #a1a1aa;
            --surface-500: #71717a;
            --surface-600: #52525b;
            --surface-700: #3f3f46;
            --surface-800: #27272a;
            --surface-900: #18181b;
            /* Colores de marca extendidos */
            --brand-orange: #ff6b35;
            --brand-orange-hover: #ea580c;
            --brand-orange-light: #fed7aa;
            --brand-orange-dark: #c2410c;
            --brand-purple: #8b5cf6;
            --brand-purple-hover: #7c3aed;
            --brand-purple-light: #ddd6fe;
            --brand-purple-dark: #6d28d9;
            --brand-teal: #14b8a6;
            --brand-teal-hover: #0f766e;
            --brand-teal-light: #ccfbf1;
            --brand-teal-dark: #0d9488;
            /* Gradientes */
            --gradient-primary: linear-gradient(135deg, var(--primary-blue) 0%, var(--blue-dark) 100%);
            --gradient-red: linear-gradient(135deg, var(--primary-red) 0%, var(--red-dark) 100%);
            --gradient-gold: linear-gradient(135deg, var(--accent-gold) 0%, var(--gold-dark) 100%);
            --gradient-success: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
            --gradient-warning: linear-gradient(135deg, var(--warning) 0%, var(--warning-dark) 100%);
            --gradient-danger: linear-gradient(135deg, var(--danger) 0%, var(--danger-dark) 100%);
            /* Sombras mejoradas */
            --shadow-xs: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            /* Espaciados */
            --space-xs: 0.5rem;
            --space-sm: 0.75rem;
            --space-md: 1rem;
            --space-lg: 1.5rem;
            --space-xl: 2rem;
            --space-2xl: 3rem;
            --space-3xl: 4rem;
            /* Radios */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            --radius-full: 9999px;
        }
        /* ===== BUTTONS SYSTEM ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            padding: var(--space-sm) var(--space-lg);
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            justify-content: center;
        }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        /* Botones principales con todos los colores */
        .btn-primary { background: var(--primary-blue); color: white; }
        .btn-primary:hover:not(:disabled) { background: var(--blue-hover); transform: translateY(-1px); box-shadow: var(--shadow-lg); }
        .btn-edit { background: var(--edit-blue); color: white; }
        .btn-edit:hover:not(:disabled) { background: var(--edit-blue-hover); transform: translateY(-1px); box-shadow: var(--shadow-lg); }
        .btn-update { background: var(--update-green); color: white; }
        .btn-update:hover:not(:disabled) { background: var(--update-green-hover); transform: translateY(-1px); box-shadow: var(--shadow-lg); }
        .btn-success { background: var(--success); color: white; }
        .btn-success:hover:not(:disabled) { background: var(--success-hover); }
        .btn-warning { background: var(--warning); color: white; }
        .btn-warning:hover:not(:disabled) { background: var(--warning-hover); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover:not(:disabled) { background: var(--danger-hover); }
        .btn-secondary { background: var(--gray-200); color: var(--gray-700); border-color: var(--gray-300); }
        .btn-secondary:hover:not(:disabled) { background: var(--gray-300); }
        /* Tamaños de botones */
        .btn-sm { padding: var(--space-xs) var(--space-sm); font-size: 0.75rem; }
        .btn-lg { padding: var(--space-md) var(--space-xl); font-size: 1rem; }
        /* Botones outline */
        .btn-outline-primary { color: var(--primary-blue); border-color: var(--primary-blue); background: transparent; }
        .btn-outline-primary:hover { background: var(--primary-blue); color: white; }
        .btn-outline-edit { color: var(--edit-blue); border-color: var(--edit-blue); background: transparent; }
        .btn-outline-edit:hover { background: var(--edit-blue); color: white; }
    </script>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📄</text></svg>">
    
    <!-- Heroicons -->
    
    <!-- Estilos personalizados -->
    <style>
        [x-cloak] { display: none !important; }
        
        .sidebar-link {
            @apply flex items-center px-4 py-3 text-gray-600 transition-colors rounded-lg hover:bg-gray-100 hover:text-gray-900;
        }
        
        .sidebar-link.active {
            @apply bg-primary-50 text-primary-700 font-medium;
        }
        
        .card {
            @apply bg-white rounded-xl shadow-sm border border-gray-100 transition-all duration-200;
        }
        
        .card:hover {
            @apply shadow-md;
        }

        /* Card Accents */
        .card-accent-top { @apply border-t-4 border-gold-500; }
        .card-accent-left { @apply border-l-4 border-gold-500; }
        .card-accent-right { @apply border-r-4 border-gold-500; }
        .card-accent-bottom { @apply border-b-4 border-gold-500; }
        
        .card-accent-blue { @apply border-primary-500; }
        .card-accent-green { @apply border-success-500; }
        .card-accent-red { @apply border-danger-500; }
        .card-accent-purple { @apply border-brand-purple; }

        
        .btn {
            @apply inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2;
        }
        
        .btn-primary {
            @apply bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-500;
        }
        
        .btn-secondary {
            @apply bg-gray-100 text-gray-700 hover:bg-gray-200 focus:ring-gray-500 border border-gray-300;
        }
        
        .btn-danger {
            @apply bg-danger-500 text-white hover:bg-danger-600 focus:ring-danger-500;
        }
        
        .btn-success {
            @apply bg-success-500 text-white hover:bg-success-600 focus:ring-success-500;
        }
        
        .btn-edit {
            @apply bg-edit-500 text-white hover:bg-edit-600 focus:ring-edit-500;
        }
        
        .btn-update {
            @apply bg-success-500 text-white hover:bg-success-600 focus:ring-success-500;
        }
        
        .btn-warning {
            @apply bg-warning-500 text-white hover:bg-warning-600 focus:ring-warning-500;
        }
        
        .btn-info {
            @apply bg-info-500 text-white hover:bg-info-600 focus:ring-info-500;
        }
        
        /* Botones outline */
        .btn-outline-primary {
            @apply border border-primary-500 text-primary-500 bg-transparent hover:bg-primary-500 hover:text-white focus:ring-primary-500;
        }
        
        .btn-outline-edit {
            @apply border border-edit-500 text-edit-500 bg-transparent hover:bg-edit-500 hover:text-white focus:ring-edit-500;
        }
        
        /* Tamaños de botones */
        .btn-sm {
            @apply px-3 py-1.5 text-xs;
        }
        
        .btn-lg {
            @apply px-6 py-3 text-base;
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
        
        .toast-success { @apply bg-success-500 text-white; }
        .toast-error { @apply bg-danger-500 text-white; }
        .toast-warning { @apply bg-warning-500 text-white; }
        .toast-info { @apply bg-info-500 text-white; }
        
        /* Estado badges - Motormexa Identity */
        .badge { @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium; }
        .badge-yellow { @apply bg-gold-50 text-gold-700; }
        .badge-gold { @apply bg-gold-500 text-white; }
        .badge-blue { @apply bg-primary-50 text-primary-500; }
        .badge-orange { @apply bg-warning-100 text-warning-700; }
        .badge-red { @apply bg-red-100 text-red-500; }
        .badge-gray { @apply bg-gray-100 text-gray-700; }
        .badge-green { @apply bg-success-50 text-success-600; }
        .badge-new { @apply bg-success-50 text-success-600; }
        .badge-used { @apply bg-gray-200 text-gray-700; }
        .badge-offer { @apply bg-gold-500 text-white; }
        
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
<body class="bg-gray-50 min-h-screen font-sans">
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
