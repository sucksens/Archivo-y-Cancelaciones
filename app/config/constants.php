<?php
/**
 * Constantes del Sistema
 * Sistema de Tickets de Cancelación
 * 
 * @author Sistema
 * @version 1.0
 */

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Información del sistema
define('APP_NAME', 'Archivo y Cancelaciones');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // 'development' o 'production'

// Rutas del sistema
define('BASE_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('VIEWS_PATH', BASE_PATH . '/views');
define('UPLOADS_PATH', PUBLIC_PATH . '/assets/uploads');

// URL base (ajustar según configuración del servidor)
define('BASE_URL', '/index.php/');
define('BASE_URL_JS', '/');

// Configuración de sesiones
define('SESSION_NAME', 'cancelaciones_session');
define('SESSION_LIFETIME', 7200); // 2 horas en segundos
define('SESSION_PATH', '/');
define('SESSION_SECURE', false); // Cambiar a true en producción con HTTPS
define('SESSION_HTTPONLY', true);

// Configuración de archivos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['application/pdf', 'text/xml', 'application/xml']);
define('ALLOWED_EXTENSIONS', ['pdf', 'xml']);

// Empresas disponibles
define('EMPRESAS', [
    'grupo_motormexa' => 'Grupo Motormexa',
    'automotriz_motormexa' => 'Automotriz Motormexa'
]);

// Estados de tickets
define('TICKET_ESTADOS', [
    'pendiente' => ['label' => 'Pendiente', 'color' => 'yellow', 'icon' => 'clock'],
    'en_revision' => ['label' => 'En Revisión', 'color' => 'blue', 'icon' => 'eye'],
    'proceso_cancelacion' => ['label' => 'En Proceso', 'color' => 'orange', 'icon' => 'refresh'],
    'cancelado' => ['label' => 'Cancelado', 'color' => 'red', 'icon' => 'x-circle'],
    'rechazado' => ['label' => 'Rechazado', 'color' => 'gray', 'icon' => 'ban'],
    'completado' => ['label' => 'Completado', 'color' => 'green', 'icon' => 'check-circle']
]);

// Tipos de cancelación
define('TIPOS_CANCELACION', [
    'cancelacion_total' => 'Cancelación Total',
    'refacturacion' => 'Refacturación'
]);

// Tipos de operaciones
define('TIPOS_OPERACION', [
    'complemento_pago' => 'Complemento de Pago',
    'nota_aplicacion' => 'Nota de Aplicación',
    'anticipo' => 'Anticipo',
    'documento_relacionado' => 'Documento Relacionado'
]);

// Tipos de factura
define('TIPOS_AUTO', [
    'autos_nuevos' => 'Autos Nuevos',
    'seminuevos' => 'Seminuevos'
]);

// Configuración de paginación
define('ITEMS_PER_PAGE', 15);

// Debug (desactivar en producción)
define('DEBUG_MODE', APP_ENV === 'development');

// Configurar reporte de errores según ambiente
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}
