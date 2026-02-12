<?php

class Native
{
    private static $ffi = null;
    private static $loaded = false;
    private static $available = false;

    public static function load()
    {
        if (self::$loaded) {
            return;
        }

        self::$loaded = true;

        if (!class_exists('FFI')) {
            self::$available = false;
            return;
        }

        $libName = match (PHP_OS_FAMILY) {
            'Windows' => 'libtimer.dll',
            'Darwin' => 'libtimer.dylib',
            default => 'libtimer.so',
        };

        $libPath = __DIR__ . '/../native/' . $libName;
        if (!is_file($libPath)) {
            self::$available = false;
            return;
        }

        try {
            self::$ffi = FFI::cdef("\n            long current_time_ms();\n        ", $libPath);
            self::$available = true;
        } catch (\Throwable $e) {
            self::$ffi = null;
            self::$available = false;
        }
    }

    public static function isAvailable(): bool
    {
        self::load();
        return self::$available;
    }

    public static function nowMs(): int
    {
        self::load();

        if (self::$available && self::$ffi !== null) {
            return (int) self::$ffi->current_time_ms();
        }

        return (int) round(microtime(true) * 1000);
    }
}
