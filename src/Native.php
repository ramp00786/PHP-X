<?php

class Native
{
    private static $ffi = null;

    public static function load()
    {
        if (self::$ffi !== null) {
            return;
        }

        self::$ffi = FFI::cdef("
            long current_time_ms();
        ", __DIR__ . '/../native/libtimer.so');
    }

    public static function nowMs(): int
    {
        self::load();
        return self::$ffi->current_time_ms();
    }
}
