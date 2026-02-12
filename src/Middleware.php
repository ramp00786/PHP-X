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
    
    // public static function handle(Request $req, callable $core): Response
    // {
    //     $dispatcher = array_reduce(
    //         array_reverse(self::$stack),
    //         function ($next, $middleware) {
    //             return function (Request $req) use ($middleware, $next): Response {
    //                 $res = $middleware($req, $next);

    //                 if (!$res instanceof Response) {
    //                     throw new \LogicException(
    //                         "Middleware must return instance of Response"
    //                     );
    //                 }

    //                 return $res;
    //             };
    //         },
    //         $core
    //     );

    //     return $dispatcher($req);
    // }

    public static function handle(Request $req, callable $core): Response
    {
        try {
            $dispatcher = array_reduce(
                array_reverse(self::$stack),
                function ($next, $middleware) {
                    return function (Request $req) use ($middleware, $next): Response {
                        $res = $middleware($req, $next);

                        if (!$res instanceof Response) {
                            throw new \LogicException(
                                "Middleware must return instance of Response"
                            );
                        }

                        return $res;
                    };
                },
                $core
            );

            return $dispatcher($req);

        } catch (\Throwable $e) {
            return ErrorHandler::handle($e);
        }
    }



}
