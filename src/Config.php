<?php

class Config
{
    private static array $data = [];

    public static function load(array $config): void
    {
        self::$data = $config;
    }

    public static function get(string $key, $default = null)
    {
        return self::$data[$key] ?? $default;
    }
}
