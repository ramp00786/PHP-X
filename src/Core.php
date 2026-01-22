<?php

/**
 * EVENT LOOP SPEC (REFERENCE IMPLEMENTATION)
 *
 * API in this class is FROZEN.
 * Internal implementation WILL be replaced by native code.
 *
 * Do NOT add new async primitives here.
 */


/**
 * Core class for managing asynchronous timers and event loop
 * Provides functionality similar to JavaScript's setInterval
 */
class Core
{
    /**
     * Array of registered timer callbacks
     * @var array
     */
    private static array $timers = [];

    /**
     * Registers a callback to be executed at specified intervals
     * @param callable $callback Function to execute repeatedly
     * @param int $ms Interval in milliseconds
     */
    // @native-boundary: event-loop
    public static function setInterval(callable $callback, int $ms)
    {
        self::$timers[] = [
            'callback' => $callback,
            'interval' => $ms / 1000,
            'lastRun'  => microtime(true)
        ];
    }

    /**
     * Starts the event loop to execute registered timers
     * Runs indefinitely until interrupted
     */
    // @native-boundary: event-loop
    public static function run()
    {
        // Infinite loop to process timers
        while (true) {
            $now = microtime(true);

            // Check each timer and execute if interval has elapsed
            foreach (self::$timers as &$timer) {
                if (($now - $timer['lastRun']) >= $timer['interval']) {
                    $timer['callback']();
                    $timer['lastRun'] = $now;
                }
            }

            // Small sleep to prevent CPU overload (1ms)
            usleep(1000);
        }
    }
}
