<?php

class Logger
{
    private static string $file;

   public static function init(?string $file): void
    {
        if (!$file) {
            $file = __DIR__ . '/../storage/logs/app.log';
        }

        self::$file = $file;
    }


    public static function info(string $msg): void
    {
        self::write('INFO', $msg);
    }

    public static function warning(string $msg): void
    {
        self::write('WARN', $msg);
    }

    public static function error(string $msg): void
    {
        self::write('ERROR', $msg);
    }

    private static function write(string $level, string $msg): void
    {
        $line = "[" . date('Y-m-d H:i:s') . "] [$level] $msg" . PHP_EOL;

        // Always write to file
        file_put_contents(self::$file, $line, FILE_APPEND);

        // Console output only in debug
        if (Config::get('app.debug', false)) {
            echo $line;
        }
    }
}
