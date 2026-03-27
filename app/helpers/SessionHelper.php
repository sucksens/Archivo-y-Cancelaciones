<?php
/**
 * SessionHelper - Manejo de sesiones PHP
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
 * @version 1.0
 */

namespace App\Helpers;

class SessionHelper
{
    private bool $started = false;

    /**
     * Constructor - Iniciar sesión
     */
    public function __construct()
    {
        $this->start();
    }

    /**
     * Iniciar sesión con configuración segura
     */
    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }

        // Configurar sesión antes de iniciar
        $sessionPath = BASE_PATH . '/sessions';
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0777, true);
        }
        ini_set('session.save_path', $sessionPath);
        
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_httponly', 1);
        
        session_name(SESSION_NAME);
        
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => SESSION_PATH,
            'secure' => SESSION_SECURE,
            'httponly' => SESSION_HTTPONLY,
            'samesite' => 'Lax'
        ]);

        session_start();
        $this->started = true;

        // Verificar expiración por inactividad
        $this->checkExpiry();

        // Actualizar última actividad
        $_SESSION['_last_activity'] = time();
    }

    /**
     * Verificar si la sesión ha expirado por inactividad
     */
    private function checkExpiry(): void
    {
        if (isset($_SESSION['_last_activity'])) {
            $inactive = time() - $_SESSION['_last_activity'];
            
            if ($inactive > SESSION_LIFETIME) {
                $this->destroy();
                header('Location: ' . BASE_URL . 'login?expired=1');
                exit;
            }
        }
    }

    /**
     * Regenerar ID de sesión (seguridad)
     */
    public function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Establecer un valor en sesión
     * 
     * @param string $key Clave
     * @param mixed $value Valor
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Obtener un valor de sesión
     * 
     * @param string $key Clave
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verificar si existe una clave en sesión
     * 
     * @param string $key Clave
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Eliminar una clave de sesión
     * 
     * @param string $key Clave
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Establecer un mensaje flash (disponible solo en siguiente request)
     * 
     * @param string $type Tipo de mensaje (success, error, warning, info)
     * @param string $message Mensaje
     */
    public function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type][] = $message;
    }

    /**
     * Obtener mensajes flash y eliminarlos
     * 
     * @param string|null $type Tipo específico o todos
     * @return array
     */
    public function getFlash(?string $type = null): array
    {
        $flash = $_SESSION['_flash'] ?? [];
        
        if ($type !== null) {
            $messages = $flash[$type] ?? [];
            unset($_SESSION['_flash'][$type]);
            return $messages;
        }
        
        unset($_SESSION['_flash']);
        return $flash;
    }

    /**
     * Verificar si hay mensajes flash
     * 
     * @param string|null $type Tipo específico
     * @return bool
     */
    public function hasFlash(?string $type = null): bool
    {
        if ($type !== null) {
            return !empty($_SESSION['_flash'][$type]);
        }
        return !empty($_SESSION['_flash']);
    }

    /**
     * Destruir la sesión completamente
     */
    public function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->started = false;
    }

    /**
     * Obtener todos los datos de sesión
     * 
     * @return array
     */
    public function all(): array
    {
        return $_SESSION;
    }

    /**
     * Limpiar sesión excepto ciertas claves
     * 
     * @param array $except Claves a mantener
     */
    public function clear(array $except = []): void
    {
        $keep = [];
        foreach ($except as $key) {
            if (isset($_SESSION[$key])) {
                $keep[$key] = $_SESSION[$key];
            }
        }
        
        $_SESSION = $keep;
    }
}
