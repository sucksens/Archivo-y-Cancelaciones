<?php
/**
 * AuthController - Controlador de autenticación
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\User;
use App\Models\Log;
use App\Helpers\AuthHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\ValidationHelper;

class AuthController extends BaseController
{
    private User $userModel;
    private Log $logModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->logModel = new Log();
    }

    /**
     * Mostrar formulario de login
     */
    public function showLogin(): void
    {
        // Si ya está autenticado, redirigir al dashboard
        if (AuthHelper::isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login', [
            'title' => 'Iniciar Sesión'
        ], null); // Sin layout principal
    }

    /**
     * Procesar login
     */
    public function login(): void
    {
        try {
            // Debug log
            $this->logModel->log('debug', 'auth', 'Inicio de intento de login', 'POST: ' . json_encode($_POST));

            $this->validateCsrf();

            $username = $this->sanitize('username');
            $password = $_POST['password'] ?? '';

            // Validar campos requeridos
            if (empty($username) || empty($password)) {
                $this->session->flash('error', 'Usuario y contraseña son requeridos');
                $this->redirect('/login');
            }

            // Verificar intentos de login
            if (AuthHelper::checkLoginAttempts($username)) {
                $this->logModel->log('login', 'auth', 'Bloqueo por intentos fallidos', "Usuario: {$username}");
                $this->session->flash('error', 'Demasiados intentos fallidos. Intenta de nuevo en 15 minutos.');
                $this->redirect('/login');
            }

            // Autenticar usuario
            $user = $this->userModel->authenticate($username, $password);

            if (!$user) {
                AuthHelper::recordFailedLogin($username);
                $this->logModel->log('login', 'auth', 'Login fallido', "Usuario: {$username}");
                $this->session->flash('error', 'Credenciales incorrectas');
                $this->redirect('/login');
            }

            // Login exitoso
            $this->session->regenerate();
            AuthHelper::clearLoginAttempts($username);

            // Guardar datos en sesión
            $this->session->set('user_id', $user['id']);
            $this->session->set('user', $user);

            // Cargar permisos
            PermissionHelper::loadUserPermissions($user['id']);

            // Registrar log
            $this->logModel->logLogin($user['id'], true);

            $this->session->flash('success', "¡Bienvenido, {$user['nombre_completo']}!");
            $this->redirect('/dashboard');

        } catch (\Exception $e) {
            $this->logModel->logError('auth', $e->getMessage());
            $this->session->flash('error', 'Error al procesar login');
            $this->redirect('/login');
        }
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void
    {
        if ($userId = $this->userId()) {
            $this->logModel->logLogout($userId);
        }

        PermissionHelper::clearCache();
        $this->session->destroy();

        // Iniciar nueva sesión para flash message
        $newSession = new \App\Helpers\SessionHelper();
        $newSession->flash('success', 'Sesión cerrada correctamente');
        
        $this->redirect('/login');
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegister(): void
    {
        // Verificar si el registro está habilitado
        // Por defecto deshabilitado, solo admin puede crear usuarios
        $this->redirect('/login');
    }

    /**
     * Procesar registro
     */
    public function register(): void
    {
        try {
            $this->validateCsrf();

            $validator = new ValidationHelper($_POST);
            $validator
                ->required('username', 'El usuario es requerido')
                ->required('email', 'El email es requerido')
                ->email('email')
                ->required('nombre_completo', 'El nombre completo es requerido')
                ->required('password', 'La contraseña es requerida')
                ->minLength('password', 8, 'La contraseña debe tener al menos 8 caracteres')
                ->matches('password', 'password_confirm', 'Las contraseñas no coinciden')
                ->required('empresa', 'La empresa es requerida')
                ->in('empresa', array_keys(EMPRESAS));

            if ($validator->hasErrors()) {
                $this->session->flash('error', $validator->getFirstError());
                $this->redirect('/registro');
            }

            // Verificar username único
            if ($this->userModel->usernameExists($_POST['username'])) {
                $this->session->flash('error', 'El nombre de usuario ya existe');
                $this->redirect('/registro');
            }

            // Verificar email único
            if ($this->userModel->emailExists($_POST['email'])) {
                $this->session->flash('error', 'El email ya está registrado');
                $this->redirect('/registro');
            }

            // Crear usuario
            $userId = $this->userModel->create([
                'username' => ValidationHelper::sanitize($_POST['username']),
                'email' => ValidationHelper::sanitize($_POST['email']),
                'password' => $_POST['password'],
                'nombre_completo' => ValidationHelper::sanitize($_POST['nombre_completo']),
                'empresa' => $_POST['empresa'],
                'departamento' => ValidationHelper::sanitize($_POST['departamento'] ?? ''),
                'activo' => 1
            ]);

            // Asignar rol de usuario por defecto
            $this->userModel->assignRole($userId, 3); // 3 = Usuario

            $this->logModel->logAction('auth', 'Registro de usuario', "ID: {$userId}");
            
            $this->session->flash('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
            $this->redirect('/login');

        } catch (\Exception $e) {
            $this->logModel->logError('auth', $e->getMessage());
            $this->session->flash('error', 'Error al procesar registro');
            $this->redirect('/registro');
        }
    }

    /**
     * Mostrar perfil del usuario
     */
    public function profile(): void
    {
        $this->requireAuth();

        $user = $this->userModel->find($this->userId());
        $roles = $this->userModel->getRoles($this->userId());

        $this->view('users/profile', [
            'title' => 'Mi Perfil',
            'userData' => $user,
            'roles' => $roles
        ]);
    }

    /**
     * Actualizar perfil
     */
    public function updateProfile(): void
    {
        $this->requireAuth();
        
        try {
            $this->validateCsrf();

            $validator = new ValidationHelper($_POST);
            $validator
                ->required('nombre_completo', 'El nombre completo es requerido')
                ->required('email', 'El email es requerido')
                ->email('email');

            if ($validator->hasErrors()) {
                $this->session->flash('error', $validator->getFirstError());
                $this->redirect('/perfil');
            }

            // Verificar email único
            if ($this->userModel->emailExists($_POST['email'], $this->userId())) {
                $this->session->flash('error', 'El email ya está en uso');
                $this->redirect('/perfil');
            }

            $updateData = [
                'nombre_completo' => ValidationHelper::sanitize($_POST['nombre_completo']),
                'email' => ValidationHelper::sanitize($_POST['email']),
                'departamento' => ValidationHelper::sanitize($_POST['departamento'] ?? '')
            ];

            // Cambiar contraseña si se proporciona
            if (!empty($_POST['new_password'])) {
                if (strlen($_POST['new_password']) < 8) {
                    $this->session->flash('error', 'La nueva contraseña debe tener al menos 8 caracteres');
                    $this->redirect('/perfil');
                }
                
                if ($_POST['new_password'] !== ($_POST['confirm_password'] ?? '')) {
                    $this->session->flash('error', 'Las contraseñas no coinciden');
                    $this->redirect('/perfil');
                }
                
                $updateData['password'] = $_POST['new_password'];
            }

            $this->userModel->update($this->userId(), $updateData);

            // Actualizar datos en sesión
            $this->session->set('user', $this->userModel->find($this->userId()));

            $this->log('Actualización de perfil', 'auth');
            $this->session->flash('success', 'Perfil actualizado correctamente');
            $this->redirect('/perfil');

        } catch (\Exception $e) {
            $this->logModel->logError('auth', $e->getMessage());
            $this->session->flash('error', 'Error al actualizar perfil');
            $this->redirect('/perfil');
        }
    }
}
