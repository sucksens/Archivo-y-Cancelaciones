<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?= APP_NAME ?></title>
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📄</text></svg>">
    
    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
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
                        gold: {
                            50: '#fdf6e8',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f5ac3d',  /* accent-gold */
                            600: '#e69b35',
                            700: '#d4932a',
                        },
                        success: {
                            50: '#ecfdf5',
                            500: '#10b981',
                            600: '#059669',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .brand-gradient {
            background: linear-gradient(135deg, #224580 0%, #080d17 100%);
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 font-sans relative overflow-hidden">
    
    <!-- Background Decoration -->
    <div class="absolute inset-0 z-0">
        <div class="absolute top-0 left-0 w-full h-1/2 brand-gradient"></div>
        <div class="absolute bottom-0 left-0 w-full h-1/2 bg-gray-50"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo y título -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-xl mb-4 p-4">
                <!-- Placeholder Logo - Replace with actual logo if available, utilizing primary color -->
                <svg class="w-full h-full text-primary-500" fill="currentColor" viewBox="0 0 24 24">
                   <path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white"><?= APP_NAME ?></h1>
            <p class="text-primary-100 mt-2 font-light">Gestión de Tickets de Cancelación</p>
        </div>
        
        <!-- Card de login -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 border-t-4 border-gold-500">
            
            <div class="mb-6 text-center">
                <h2 class="text-xl font-semibold text-gray-800">Bienvenido</h2>
                <p class="text-sm text-gray-500">Ingresa tus credenciales para continuar</p>
            </div>

            <!-- Flash messages -->
            <?php 
            $session = new \App\Helpers\SessionHelper();
            if ($session->hasFlash()): 
            ?>
            <div class="mb-6">
                <?php foreach ($session->getFlash() as $type => $messages): ?>
                    <?php foreach ($messages as $message): ?>
                    <div class="flex items-center p-4 rounded-lg <?= $type === 'error' ? 'bg-red-50 text-red-700 border border-red-100' : ($type === 'success' ? 'bg-success-50 text-success-600 border border-success-100' : 'bg-gold-50 text-gold-700 border border-gold-100') ?>">
                        <svg class="w-5 h-5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <?php if ($type === 'error'): ?>
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            <?php else: ?>
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            <?php endif; ?>
                        </svg>
                        <span class="text-sm font-medium"><?= htmlspecialchars($message) ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['expired'])): ?>
            <div class="mb-6 p-4 bg-gold-50 text-gold-700 border border-gold-100 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium">Tu sesión ha expirado. Por favor, inicia sesión nuevamente.</span>
            </div>
            <?php endif; ?>
            
            <form action="<?= BASE_URL ?>login" method="POST" class="space-y-6">
                <?= \App\Helpers\AuthHelper::getCsrfField() ?>
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Usuario o Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            autofocus
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-500 transition-all outline-none"
                            placeholder="Ingresa tu usuario"
                        >
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-200 focus:border-primary-500 transition-all outline-none"
                            placeholder="Ingresa tu contraseña"
                        >
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center outline-none">
                            <svg id="eyeIcon" class="w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full py-3 px-4 bg-primary-500 text-white font-semibold rounded-lg hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all shadow-md hover:shadow-lg"
                >
                    Iniciar Sesión
                </button>
            </form>
        </div>
        
        <!-- Footer -->
        <p class="text-center text-gray-400 text-sm mt-8">
            <?= APP_NAME ?> v<?= APP_VERSION ?> &copy; <?= date('Y') ?>
        </p>
    </div>
    
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'text') {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        });
    </script>
</body>
</html>
