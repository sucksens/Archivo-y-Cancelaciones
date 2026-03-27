<?php
/**
 * Clase BaseController - Controlador base
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
 * @version 1.0
 */

namespace App\Core;

use App\Helpers\SessionHelper;
use App\Helpers\AuthHelper;

abstract class BaseController
{
    protected SessionHelper $session;
    protected ?array $user = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->session = new SessionHelper();
        
        // Cargar usuario autenticado si existe
        if ($this->session->has('user_id')) {
            $this->user = $this->session->get('user');
        }
    }

    /**
     * Renderizar una vista
     * 
     * @param string $view Nombre de la vista
     * @param array $data Datos para la vista
     * @param string|null $layout Layout a usar
     */
    protected function view(string $view, array $data = [], ?string $layout = 'main'): void
    {
        // Extraer datos para la vista
        extract($data);
        
        // Agregar datos comunes
        $user = $this->user;
        $session = $this->session;
        $csrfToken = AuthHelper::generateCsrfToken();

        // Ruta de la vista
        $viewPath = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \Exception("Vista no encontrada: {$view}");
        }

        if ($layout) {
            // Capturar contenido de la vista
            ob_start();
            include $viewPath;
            $content = ob_get_clean();

            // Incluir layout
            include VIEWS_PATH . '/layouts/header.php';
            echo $content;
            include VIEWS_PATH . '/layouts/footer.php';
        } else {
            include $viewPath;
        }
    }

    /**
     * Redirigir a otra URL
     * 
     * @param string $url URL destino
     * @param array $flash Mensaje flash opcional
     */
    protected function redirect(string $url, array $flash = []): void
    {
        if (!empty($flash)) {
            foreach ($flash as $type => $message) {
                $this->session->flash($type, $message);
            }
        }

        Router::redirect($url);
    }

    /**
     * Responder con JSON
     * 
     * @param mixed $data Datos a enviar
     * @param int $status Código HTTP
     */
    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Validar token CSRF
     * 
     * @throws \Exception Si el token es inválido
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!AuthHelper::validateCsrfToken($token)) {
            if ($this->isAjax()) {
                $this->json(['error' => 'Token CSRF inválido'], 403);
            }
            throw new \Exception('Token CSRF inválido');
        }
        
        return true;
    }

    /**
     * Verificar si la solicitud es AJAX
     * 
     * @return bool
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Obtener datos del request (POST o GET)
     * 
     * @param string|null $key Clave específica
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    protected function input(?string $key = null, $default = null)
    {
        $data = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $data;
        }
        
        return $data[$key] ?? $default;
    }

    /**
     * Obtener datos sanitizados del request
     * 
     * @param string $key Clave
     * @param int $filter Filtro a aplicar
     * @return mixed
     */
    protected function sanitize(string $key, int $filter = FILTER_SANITIZE_SPECIAL_CHARS)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? null;
        if ($value === null) {
            return null;
        }
        return filter_var($value, $filter);
    }

    /**
     * Verificar si el usuario tiene un permiso
     * 
     * @param string $permission Código del permiso
     * @return bool
     */
    protected function hasPermission(string $permission): bool
    {
        if (!$this->user) {
            return false;
        }
        
        $permissions = $this->session->get('permissions', []);
        return in_array($permission, $permissions);
    }

    /**
     * Requerir un permiso o redirigir a 403
     * 
     * @param string $permission Código del permiso
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            if ($this->isAjax()) {
                $this->json(['error' => 'No tienes permisos para esta acción'], 403);
            }
            
            http_response_code(403);
            include VIEWS_PATH . '/errors/403.php';
            exit;
        }
    }

    /**
     * Requerir autenticación
     */
    protected function requireAuth(): void
    {
        if (!$this->user) {
            if ($this->isAjax()) {
                $this->json(['error' => 'No autenticado'], 401);
            }
            
            $this->session->flash('warning', 'Debes iniciar sesión para acceder');
            $this->redirect('/login');
        }
    }

    /**
     * Obtener el ID del usuario actual
     * 
     * @return int|null
     */
    protected function userId(): ?int
    {
        return $this->user['id'] ?? null;
    }

    /**
     * Registrar una acción en el log
     * 
     * @param string $accion Acción realizada
     * @param string $modulo Módulo
     * @param string|null $detalles Detalles adicionales
     */
    protected function log(string $accion, string $modulo, ?string $detalles = null): void
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "INSERT INTO logs_sistema (usuario_id, tipo_log, modulo, accion, detalles, ip_address, user_agent) 
                 VALUES (?, 'accion', ?, ?, ?, ?, ?)",
                [
                    $this->userId(),
                    $modulo,
                    $accion,
                    $detalles,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $_SERVER['HTTP_USER_AGENT'] ?? null
                ]
            );
        } catch (\Exception $e) {
            // Silenciar errores de log para no interrumpir flujo
            error_log("Error al registrar log: " . $e->getMessage());
        }
    }
}
