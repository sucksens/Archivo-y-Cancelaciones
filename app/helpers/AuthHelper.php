<?php
/**
 * AuthHelper - Funciones de autenticación
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
 * @version 1.0
 */

namespace App\Helpers;

class AuthHelper
{
    /**
     * Generar hash de contraseña seguro
     * 
     * @param string $password Contraseña en texto plano
     * @return string Hash de la contraseña
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }

    /**
     * Verificar contraseña contra hash
     * 
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash almacenado
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generar token CSRF
     * 
     * @return string Token generado
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Validar token CSRF
     * 
     * @param string $token Token a validar
     * @return bool
     */
    public static function validateCsrfToken(string $token): bool
    {
        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Regenerar token CSRF (después de uso)
     */
    public static function regenerateCsrfToken(): void
    {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    /**
     * Verificar si hay un usuario autenticado
     * 
     * @return bool
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Obtener ID del usuario autenticado
     * 
     * @return int|null
     */
    public static function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtener datos del usuario autenticado
     * 
     * @return array|null
     */
    public static function getUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /**
     * Generar token de recuperación de contraseña
     * 
     * @return string Token único
     */
    public static function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generar UUID v4
     * 
     * @return string UUID
     */
    public static function generateUuid(): string
    {
        $data = random_bytes(16);
        
        // Establecer versión 4
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Establecer variante
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Verificar intentos de login (protección brute force básica)
     * 
     * @param string $username Usuario
     * @return bool True si debe bloquear
     */
    public static function checkLoginAttempts(string $username): bool
    {
        $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
        $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => time()];
        
        // Resetear después de 15 minutos
        if (time() - $attempts['time'] > 900) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
            return false;
        }
        
        // Bloquear después de 5 intentos
        return $attempts['count'] >= 5;
    }

    /**
     * Registrar un intento de login fallido
     * 
     * @param string $username Usuario
     */
    public static function recordFailedLogin(string $username): void
    {
        $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
        $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => time()];
        
        $attempts['count']++;
        $attempts['time'] = time();
        
        $_SESSION[$key] = $attempts;
    }

    /**
     * Limpiar intentos de login después de éxito
     * 
     * @param string $username Usuario
     */
    public static function clearLoginAttempts(string $username): void
    {
        $key = 'login_attempts_' . md5($username . $_SERVER['REMOTE_ADDR']);
        unset($_SESSION[$key]);
    }

    /**
     * Obtener campo oculto CSRF para formularios
     * 
     * @return string HTML del input hidden
     */
    public static function getCsrfField(): string
    {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}
