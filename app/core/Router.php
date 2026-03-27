<?php namespace App\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        $this->basePath = $basePath;
    }

    public function get(string $path, $handler, array $middlewares = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, $handler, array $middlewares = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, $handler, array $middlewares): self
    {
        $path = $this->basePath . $path;
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
        return $this;
    }

    public function middleware(callable $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        foreach ($this->middlewares as $middleware) {
            $result = $middleware();
            if ($result === false) {
                return;
            }
        }

        if (isset($this->routes[$method][$uri])) {
            $this->executeRoute($this->routes[$method][$uri]);
            return;
        }

        foreach ($this->routes[$method] ?? [] as $path => $route) {
            $pattern = $this->convertToRegex($path);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->executeRoute($route, $matches);
                return;
            }
        }

        $this->notFound();
    }

    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = str_replace('/index.php', '', $uri);
        $uri = '/' . trim($uri, '/');
        
        return $uri;
    }

    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function executeRoute(array $route, array $params = []): void
    {
        foreach ($route['middlewares'] as $middleware) {
            if (is_callable($middleware)) {
                $result = $middleware();
            } elseif (is_string($middleware)) {
                $result = $this->callMiddleware($middleware);
            }
            
            if (isset($result) && $result === false) {
                return;
            }
        }

        $handler = $route['handler'];

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controller, $method] = explode('@', $handler);
            $this->callController($controller, $method, $params);
            return;
        }

        if (is_array($handler)) {
            [$controller, $method] = $handler;
            $this->callController($controller, $method, $params);
            return;
        }

        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        }
    }

    private function callController(string $controller, string $method, array $params): void
    {
        if (strpos($controller, '\\') === false) {
            $controller = "App\\Controllers\\{$controller}";
        }

        if (!class_exists($controller)) {
            $this->notFound("Controlador no encontrado: {$controller}");
            return;
        }

        $instance = new $controller();

        if (!method_exists($instance, $method)) {
            $this->notFound("Método no encontrado: {$method}");
            return;
        }

        call_user_func_array([$instance, $method], $params);
    }

    private function callMiddleware(string $middleware)
    {
        $middlewareClass = "App\\Middlewares\\{$middleware}";
        
        if (class_exists($middlewareClass)) {
            $instance = new $middlewareClass();
            if (method_exists($instance, 'handle')) {
                return $instance->handle();
            }
        }
        
        return true;
    }

    private function notFound(string $message = 'Página no encontrada'): void
    {
        http_response_code(404);
        
        if (file_exists(VIEWS_PATH . '/errors/404.php')) {
            include VIEWS_PATH . '/errors/404.php';
        } else {
            echo "<h1>404 - {$message}</h1>";
        }
    }

    public static function redirect(string $url, int $code = 302): void
    {
        if (strpos($url, 'http') !== 0) {
            $url = BASE_URL . ltrim($url, '/');
        }
        
        header("Location: {$url}", true, $code);
        exit;
    }
}
