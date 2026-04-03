# Vistas de Error HTTP

Este directorio contiene las páginas de error del sistema. Cada vista tiene un diseño consistente con la identidad visual de la aplicación.

## Vistas Disponibles

| Código | Archivo | Descripción |
|--------|---------|-------------|
| **401** | `401.php` | No Autorizado - El usuario no ha iniciado sesión |
| **403** | `403.php` | Prohibido - El usuario no tiene permisos |
| **404** | `404.php` | No Encontrado - La página no existe |
| **405** | `405.php` | Método No Permitido - El método HTTP no está permitido |
| **408** | `408.php` | Tiempo de Espera Agotado - Request timeout |
| **419** | `419.php` | Sesión Expirada - Token CSRF expirado |
| **429** | `429.php` | Demasiadas Solicitudes - Rate limit exceeded |
| **500** | `500.php` | Error del Servidor - Error interno del servidor |

## Cómo Usar las Vistas de Error

### Método 1: Usando el Helper `ErrorHelper`

```php
use App\Helpers\ErrorHelper;

// 401 - No Autorizado
ErrorHelper::unauthorized();

// 403 - Prohibido
ErrorHelper::forbidden();

// 404 - No Encontrado
ErrorHelper::notFound();

// 405 - Método No Permitido
ErrorHelper::methodNotAllowed();

// 408 - Tiempo de Espera Agotado
ErrorHelper::requestTimeout();

// 419 - Sesión Expirada
ErrorHelper::pageExpired();

// 429 - Demasiadas Solicitudes
ErrorHelper::tooManyRequests();

// 500 - Error del Servidor
ErrorHelper::serverError();

// Error personalizado
ErrorHelper::custom(503, 'Servicio No Disponible', 'El sistema está en mantenimiento');
```

### Método 2: Incluyendo directamente la vista

```php
http_response_code(404);
include VIEWS_PATH . '/errors/404.php';
exit;
```

### Método 3: Usando el Router (solo para 404)

El Router ya incluye el manejo automático de 404:

```php
// Si una ruta no existe, el Router muestra automáticamente /errors/404.php
```

### Método 4: Usando el BaseController

```php
// En cualquier controlador
$this->requirePermission('tickets.create'); // Muestra 403 si no tiene permiso
```

## Ejemplos de Uso

### En un Controlador

```php
public function show(int $id): void
{
    $ticket = $this->ticketModel->find($id);
    
    if (!$ticket) {
        ErrorHelper::notFound('El ticket solicitado no existe');
    }
    
    // Continuar con la lógica...
}
```

### En Middleware

```php
$authMiddleware = function() {
    if (!AuthHelper::isAuthenticated()) {
        // El sistema redirige a /login, pero puedes forzar error 401:
        ErrorHelper::unauthorized();
        return false;
    }
    return true;
};
```

### Verificación de Token CSRF

```php
try {
    $this->validateCsrf();
} catch (\Exception $e) {
    ErrorHelper::pageExpired('El token de seguridad ha expirado. Por favor, recarga la página.');
}
```

### Manejo de Excepciones

```php
set_exception_handler(function($exception) {
    if (APP_ENV === 'production') {
        ErrorHelper::serverError();
    } else {
        // En desarrollo, mostrar el error completo
        echo "<pre>" . $exception . "</pre>";
    }
});
```

## Características de las Vistas

- **Diseño Responsive**: Se adaptan a dispositivos móviles y de escritorio
- **Tailwind CSS**: Usan Tailwind para estilos consistentes
- **Iconos**: Cada error tiene un icono representativo
- **Botones de Acción**: Permiten navegar al dashboard o volver atrás
- **Colores Semánticos**: Cada código usa un color diferente:
  - 401: Amarillo (warning)
  - 403: Rojo (danger)
  - 404: Naranja (warning)
  - 405: Índigo (info)
  - 408: Rosa (danger)
  - 419: Ámbar (warning)
  - 429: Púrpura (info)
  - 500: Rojo (danger)

## Personalización

Para modificar una vista, simplemente edita el archivo correspondiente en `views/errors/`. 

### Agregar un nuevo código de error

1. Crea el archivo `views/errors/{codigo}.php`
2. Usa la estructura base de las otras vistas
3. Agrega un método en `ErrorHelper` (opcional pero recomendado):

```php
public static function nuevoError(string $message = ''): void
{
    http_response_code(503);
    if (file_exists(VIEWS_PATH . '/errors/503.php')) {
        include VIEWS_PATH . '/errors/503.php';
    } else {
        echo "<h1>503 - " . ($message ?: 'Servicio No Disponible') . "</h1>";
    }
    exit;
}
```

## Configuración Adicional

### Agregar manejo global de errores en `public/index.php`

```php
// Agregar al final de public/index.php antes de $router->dispatch()

// Manejo de errores fatales
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (file_exists(VIEWS_PATH . '/errors/500.php')) {
            include VIEWS_PATH . '/errors/500.php';
        }
    }
});

// Manejo de excepciones no capturadas
set_exception_handler(function($exception) {
    error_log("Exception no capturada: " . $exception->getMessage());
    if (file_exists(VIEWS_PATH . '/errors/500.php')) {
        include VIEWS_PATH . '/errors/500.php';
    } else {
        echo "<h1>500 - Error del Servidor</h1>";
    }
    exit;
});
```

## Notas Importantes

1. **Seguridad**: En producción, nunca muestres detalles de errores sensibles
2. **Logs**: Asegúrate de registrar los errores en los logs del sistema
3. **Monitoreo**: Considera agregar monitoreo para los errores 500
4. **Mantenimiento**: Puedes usar el código 503 para mostrar una página de mantenimiento
