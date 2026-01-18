<?php

class DOM
{
    public static string $html = '';
    private static bool $loaded = false;

    /**
     * Loads HTML file
     */
    public static function load(string $file)
    {
        if (self::$loaded) {
            // already loaded, dubara nahi
            return;
        }

        if (!file_exists($file)) {
            echo "[DOM] HTML file not found: $file\n";
            return;
        }

        self::$html = file_get_contents($file);
        self::$loaded = true;

        echo "[DOM] Loaded $file\n";
    }


    /**
     * Changes text of an element (simulation)
     */
    public static function setText(string $selector, string $text)
    {
        // HTML response generate kar rahe hain
        self::$html = '
            <h1 id="title">' . htmlspecialchars($text) . '</h1>
            <a href="/"> <- Back</a>
        ';
    }

}
