<?php

class Middleware
{
    private static array $stack = [];

    /**
     * Register a middleware
     */
    public static function add(callable $middleware): void
    {
        self::$stack[] = $middleware;
    }

    /**
     * Run middleware stack
     */
    
    public static function handle(Request $req, callable $core)
    {
        $dispatcher = array_reduce(
            array_reverse(self::$stack),
            function ($next, $middleware) {
                return function (Request $req) use ($middleware, $next) {
                    return $middleware($req, $next);
                };
            },
            $core
        );

        return $dispatcher($req);
    }

}
