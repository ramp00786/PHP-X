<?php

class ErrorHandler
{
    private static function debug(): bool
    {
        return Config::get('app.debug', false);
    }

    public static function handle(\Throwable $e): Response
    {
        if (self::debug()) {
            return Response::html(
                "<h1>PHP-X Error</h1>" .
                "<pre>" . htmlspecialchars((string)$e) . "</pre>"
            )->status(500);
        }

        return Response::html(
            "<h1>Internal Server Error</h1>"
        )->status(500);
    }
}
