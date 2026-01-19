<?php

class View
{
    public static function render(string $file)
    {
        if (!file_exists($file)) {
            return "<h1>View not found</h1>";
        }

        return file_get_contents($file);
    }

    public static function text(string $text)
    {
        return "<h1>" . htmlspecialchars($text) . "</h1><a href='/'><- Back</a>";
    }
}
