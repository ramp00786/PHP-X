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
    public static function dispatch(Request $req): Response
    {
        $method = $req->method();
        $path   = $req->path();

        if (isset(self::$routes[$method][$path])) {
            return call_user_func(self::$routes[$method][$path], $req);
        }

        return Response::html("<h1>404 – Not Found</h1>")->status(404);
    }


    private static function notFound()
    {
        return "<h1>404 - Route not found</h1>";
    }
}
