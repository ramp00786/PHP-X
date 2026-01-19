<?php

class Router
{
    private static array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    // GET route register
    public static function get(string $path, callable $handler)
    {
        self::$routes['GET'][$path] = $handler;
    }

    // POST route register
    public static function post(string $path, callable $handler)
    {
        self::$routes['POST'][$path] = $handler;
    }

    // incoming request resolve
    public static function dispatch(string $method, string $path)
    {
        if (isset(self::$routes[$method][$path])) {
            return call_user_func(self::$routes[$method][$path]);
        }

        // route not found
        return self::notFound();
    }

    private static function notFound()
    {
        return "<h1>404 - Route not found</h1>";
    }
}
