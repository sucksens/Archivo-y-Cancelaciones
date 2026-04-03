<?php
/**
 * ErrorHelper - Manejo centralizado de errores HTTP
 * Sistema de Tickets de Cancelación
 * 
 * @author José Ernesto Ruiz Valdivia
 * @version 1.0
 */

namespace App\Helpers;

class ErrorHelper
{
    /**
     * Mostrar página de error 401 No Autorizado
     * 
     * @param string $message Mensaje personalizado
     */
    public static function unauthorized(string $message = ''): void
    {
        http_response_code(401);
        if (file_exists(VIEWS_PATH . '/errors/401.php')) {
            include VIEWS_PATH . '/errors/401.php';
        } else {
            echo "<h1>401 - " . ($message ?: 'No Autorizado') . "</h1>";
        }
        exit;
    }

    /**
     * Mostrar página de error 403 Prohibido
     * 
     * @param string $message Mensaje personalizado
     */
    public static function forbidden(string $message = ''): void
    {
        http_response_code(403);
        if (file_exists(VIEWS_PATH . '/errors/403.php')) {
            include VIEWS_PATH . '/errors/403.php';
        } else {
            echo "<h1>403 - " . ($message ?: 'Acceso Denegado') . "</h1>";
        }
        exit;
    }

    /**
     * Mostrar página de error 404 No Encontrado
     * 
     * @param string $message Mensaje personalizado
     */
    public static function notFound(string $message = ''): void
    {
        http_response_code(404);
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            include VIEWS_PATH . '/errors/404.php';
        } else {
            echo "<h1>404 - " . ($message ?: 'Página No Encontrada') . "</h1>";
        }
        exit;
    }

    /**
     * Mostrar página de error 405 Método No Permitido
     * 
     * @param string $message Mensaje personalizado
     */
    public static function methodNotAllowed(string $message = ''): void
    {
        http_response_code(405);
        if (file_exists(VIEWS_PATH . '/errors/405.php')) {
            include VIEWS_PATH . '/errors/405.php';
        } else {
            echo "<h1>405 - " . ($message ?: 'Método No Permitido') . "</h1>";
        }
        exit;
    }

    /**
     * Mostrar página de error 408 Tiempo de Espera Agotado
     * 
     * @param string $message Mensaje personalizado
     */
    public static function requestTimeout(string $message = ''): void
    {
        http_response_code(408);
        if (file_exists(VIEWS_PATH . '/errors/408.php')) {
            include VIEWS_PATH . '/errors/408.php';
        } else {
            echo "<h1>408 - " . ($message ?: 'Tiempo de Espera Agotado') . "</h1>";
        }
        exit;
    }

    /**
     * Mostrar página de error 419 CSRF Token Expirado
     * 
     * @param string $message Mensaje personalizado
     */
    public static function pageExpired(string $message = ''): void
    {
        http_response_code(419);
        if (file_exists(VIEWS_PATH . '/errors/419.php')) {
            include VIEWS_PATH . '/errors/419.php';
        } else {
            echo "<h1>419 - " . ($message ?: 'Sesión Expirada') . "</h1>";
        }
        exit;
    }

    /**
     * Mostrar página de error 429 Demasiadas Solicitudes
     * 
     * @param string $message Mensaje personalizado
     */
    public static function tooManyRequests(string $message = ''): void
    {
        http_response_code(429);
        if (file_exists(VIEWS_PATH . '/errors/429.php')) {
            include VIEWS_PATH . '/errors/429.php';
        } else {
            echo "<h1>429 - " . ($message ?: 'Demasiadas Solicitudes') . "</h1>";
        }
        exit;
    }

    /**
     * Mostrar página de error 500 Error del Servidor
     * 
     * @param string $message Mensaje personalizado
     */
    public static function serverError(string $message = ''): void
    {
        http_response_code(500);
        if (file_exists(VIEWS_PATH . '/errors/500.php')) {
            include VIEWS_PATH . '/errors/500.php';
        } else {
            echo "<h1>500 - " . ($message ?: 'Error del Servidor') . "</h1>";
        }
        exit;
    }

    /**
     * Mostrar error genérico con código personalizado
     * 
     * @param int $code Código HTTP
     * @param string $title Título del error
     * @param string $message Mensaje del error
     */
    public static function custom(int $code, string $title, string $message): void
    {
        http_response_code($code);
        
        $viewPath = VIEWS_PATH . "/errors/{$code}.php";
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "<h1>{$code} - {$title}</h1>";
            echo "<p>{$message}</p>";
        }
        exit;
    }
}
