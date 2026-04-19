<?php
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/views');
define('UPLOADS_PATH', PUBLIC_PATH . '/assets/uploads');

require_once APP_PATH . '/config/constants.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    $file = str_replace(['/Core/', '/Controllers/', '/Models/', '/Helpers/'], ['/core/', '/controllers/', '/models/', '/helpers/'], $file);
    if (file_exists($file)) require $file;
});

use App\Core\Router;
use App\Helpers\SessionHelper;
use App\Helpers\AuthHelper;

$session = new SessionHelper();
$router = new Router();

$authMiddleware = function() {
    if (!AuthHelper::isAuthenticated()) {
        Router::redirect('/login');
        return false;
    }
    return true;
};

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/registro', 'AuthController@showRegister');
$router->post('/registro', 'AuthController@register');
$router->post('/logout', 'AuthController@logout', [$authMiddleware]);

$router->get('/', 'DashboardController@index', [$authMiddleware]);
$router->get('/dashboard', 'DashboardController@index', [$authMiddleware]);
$router->get('/api/dashboard/stats', 'DashboardController@getStats', [$authMiddleware]);

$router->get('/tickets', 'TicketController@index', [$authMiddleware]);
$router->get('/tickets/crear', 'TicketController@create', [$authMiddleware]);
$router->post('/tickets', 'TicketController@store', [$authMiddleware]);
$router->get('/tickets/{id}', 'TicketController@show', [$authMiddleware]);
$router->post('/tickets/{id}/estado', 'TicketController@updateStatus', [$authMiddleware]);
$router->post('/tickets/{id}/corregir-error', 'TicketController@correctRejectionError', [$authMiddleware]);
$router->post('/tickets/{id}/eliminar', 'TicketController@destroy', [$authMiddleware]);
$router->post('/tickets/{id}/uuid-nueva', 'TicketController@updateUuidFacturaNueva', [$authMiddleware]);
$router->post('/tickets/operacion/{id}/toggle', 'TicketController@toggleOperacionFlag', [$authMiddleware]);
$router->post('/tickets/{id}/validar-sat', 'TicketController@validateSatStatus', [$authMiddleware]);
$router->post('/tickets/operacion/{id}/validar-sat', 'TicketController@validateOperacionSatStatus', [$authMiddleware]);
$router->post('/tickets/parse-xml', 'TicketController@parseXml', [$authMiddleware]);
$router->get('/tickets/{id}/archivo', 'TicketController@downloadFile', [$authMiddleware]);
$router->get('/tickets/{id}/comentarios', 'TicketController@getComments', [$authMiddleware]);
$router->post('/tickets/{id}/comentarios', 'TicketController@addComment', [$authMiddleware]);
$router->post('/tickets/{id}/comentarios/{comentarioId}/eliminar', 'TicketController@deleteComment', [$authMiddleware]);

$router->get('/mis-solicitudes', 'TicketController@misSolicitudes', [$authMiddleware]);
$router->get('/solicitudes', 'TicketController@solicitudes', [$authMiddleware]);

$router->get('/perfil', 'AuthController@profile', [$authMiddleware]);
$router->post('/perfil', 'AuthController@updateProfile', [$authMiddleware]);

$router->get('/usuarios', 'UserController@index', [$authMiddleware]);
$router->get('/usuarios/crear', 'UserController@create', [$authMiddleware]);
$router->post('/usuarios', 'UserController@store', [$authMiddleware]);
$router->get('/usuarios/{id}/editar', 'UserController@edit', [$authMiddleware]);
$router->post('/usuarios/{id}', 'UserController@update', [$authMiddleware]);
$router->post('/usuarios/{id}/eliminar', 'UserController@destroy', [$authMiddleware]);
$router->post('/usuarios/{id}/toggle', 'UserController@toggleStatus', [$authMiddleware]);

$router->get('/admin/roles', 'RoleController@index', [$authMiddleware]);
$router->post('/admin/roles', 'RoleController@store', [$authMiddleware]);
$router->get('/admin/roles/{id}', 'RoleController@show', [$authMiddleware]);
$router->post('/admin/roles/{id}', 'RoleController@update', [$authMiddleware]);
$router->post('/admin/roles/{id}/eliminar', 'RoleController@destroy', [$authMiddleware]);
$router->get('/admin/roles/{id}/permisos', 'RoleController@getRolePermissions', [$authMiddleware]);
$router->get('/admin/permisos', 'RoleController@getAllPermissions', [$authMiddleware]);

$router->get('/admin/logs', 'LogController@index', [$authMiddleware]);

$router->get('/facturas', 'FacturaArchivoController@index', [$authMiddleware]);
$router->get('/facturas/subir', 'FacturaArchivoController@create', [$authMiddleware]);
$router->post('/facturas', 'FacturaArchivoController@store', [$authMiddleware]);
$router->get('/facturas/{id}', 'FacturaArchivoController@show', [$authMiddleware]);
$router->get('/facturas/{id}/descargar/{tipo}', 'FacturaArchivoController@download', [$authMiddleware]);
$router->post('/facturas/{id}/eliminar', 'FacturaArchivoController@destroy', [$authMiddleware]);
$router->post('/facturas/parse-xml', 'FacturaArchivoController@parseXml', [$authMiddleware]);
$router->post('/facturas/{id}/email', 'FacturaArchivoController@sendEmail', [$authMiddleware]);

// Configuración de Email (whitelist / blacklist)
$router->get('/admin/email-config', 'EmailConfigController@index', [$authMiddleware]);
$router->post('/admin/email-config/whitelist', 'EmailConfigController@storeWhitelist', [$authMiddleware]);
$router->post('/admin/email-config/whitelist/{id}/toggle', 'EmailConfigController@toggleWhitelist', [$authMiddleware]);
$router->post('/admin/email-config/whitelist/{id}/eliminar', 'EmailConfigController@destroyWhitelist', [$authMiddleware]);
$router->post('/admin/email-config/blacklist', 'EmailConfigController@storeBlacklist', [$authMiddleware]);
$router->post('/admin/email-config/blacklist/{id}/eliminar', 'EmailConfigController@destroyBlacklist', [$authMiddleware]);

$router->dispatch();
