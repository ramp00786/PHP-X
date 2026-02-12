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

    // Route dispatcher

    public static function dispatch(Request $req): Response
    {
        $method = $req->method();
        $path   = $req->path();

        if (!isset(self::$routes[$method])) {
            return Response::html("<h1>404</h1>")->status(404);
        }

        foreach (self::$routes[$method] as $route => $handler) {

            // Convert /user/{id} → regex
            $pattern = preg_replace('#\{([^}]+)\}#', '([^/]+)', $route);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $path, $matches)) {

                array_shift($matches);

                $params = [];
                if (preg_match_all('#\{([^}]+)\}#', $route, $keys)) {
                    foreach ($keys[1] as $i => $key) {
                        $params[$key] = $matches[$i] ?? null;
                    }
                }

                $req->setParams($params);

                $result = $handler($req);

                if (!$result instanceof Response) {
                    throw new \LogicException("Route must return Response");
                }

                return $result;
            }
        }

        return Response::html("<h1>404</h1>")->status(404);
    }



    private static function notFound()
    {
        return "<h1>404 - Route not found</h1>";
    }
}
