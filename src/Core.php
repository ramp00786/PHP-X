<?php

class Core
{
    private static array $timers = [];

    public static function setInterval(callable $callback, int $ms)
    {
        self::$timers[] = [
            'callback' => $callback,
            'interval' => $ms / 1000,
            'lastRun'  => microtime(true)
        ];
    }

    public static function run()
    {
        while (true) {
            $now = microtime(true);

            foreach (self::$timers as &$timer) {
                if (($now - $timer['lastRun']) >= $timer['interval']) {
                    $timer['callback']();
                    $timer['lastRun'] = $now;
                }
            }

            usleep(1000); // CPU ko rest
        }
    }
}
