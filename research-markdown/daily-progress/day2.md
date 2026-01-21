# Day 2 — Event Loop & Long-Running Execution

### Objective
Transform PHP from a script-and-exit model into a long-running runtime.  
Implement Node.js-style `setInterval()` to keep PHP-X alive indefinitely.

### Files Created / Modified
- `src/Core.php` — Event loop and timer management (new)
- `bin/phpx` — Runtime initialization (modified)
- `examples/hello.xphp` — Event loop demonstration (modified)

### Core Concept
Traditional PHP executes a script and immediately exits. PHP-X introduces a persistent event loop that keeps the process running, enabling continuous execution and scheduled callbacks—essential for servers, background services, and GUI applications.

### Work Done

#### 1. Event Loop Implementation (src/Core.php)
Created a minimal event loop with timer support:
```php
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

            usleep(1000); // Prevent CPU saturation
        }
    }
}
```

**Key components:**
- `$timers` array: Stores all registered interval callbacks
- `setInterval()`: Registers a callback to run at specified intervals (milliseconds)
- `run()`: Infinite loop that checks and executes due callbacks
- `usleep(1000)`: Prevents CPU saturation while maintaining responsiveness (1ms sleep)

**How it works:**
1. User code calls `setInterval()` to register callbacks
2. `Core::run()` starts the infinite event loop
3. Each iteration checks if any timer is due for execution
4. Executes callbacks whose interval has elapsed
5. Updates `lastRun` timestamp after execution
6. Sleeps briefly to reduce CPU usage

#### 2. Updated CLI Launcher (bin/phpx)
Modified to initialize the event loop after loading user code:
```php
#!/usr/bin/env php
<?php

require_once __DIR__ . '/../src/Core.php';

if ($argc < 2) {
    echo "Usage: phpx <file.xphp>\n";
    exit(1);
}

$file = $argv[1];

if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

require $file;

// Start runtime event loop
Core::run();
```

**Critical changes:**
- Added `require_once` for `Core.php` at the top
- Added `Core::run()` at the end to start the event loop
- User code registers timers, then `Core::run()` keeps process alive
- Process now continues indefinitely instead of exiting

#### 3. Updated Example (examples/hello.xphp)
Demonstrated continuous execution:
```php
<?php

Core::setInterval(function () {
    echo "Tick from PHP-X ⏱️\n";
}, 1000);
```

**Behavior:**
- Registers a callback that prints every 1000ms (1 second)
- No explicit loop in user code—handled by runtime
- Clean, declarative syntax similar to JavaScript

#### 4. Execution Verification
Successfully ran persistent PHP-X application:

**Linux/Mac:**
```bash
./bin/phpx examples/hello.xphp
```

**Windows:**
```bash
php bin/phpx examples/hello.xphp
```

**Expected Output (repeating every second):**
```
Tick from PHP-X ⏱️
Tick from PHP-X ⏱️
Tick from PHP-X ⏱️
...
```

**Stop execution:** Ctrl+C

### Why This Was Done
- **Runtime persistence**: Essential foundation for all long-running applications
- **Event-driven architecture**: Enables Node.js-style asynchronous patterns in PHP
- **Server capabilities**: Required for HTTP servers, WebSocket servers, background workers
- **GUI applications**: Desktop apps need event loops to handle user interactions
- **Resource efficiency**: Single process handles multiple concurrent operations without forking

### Problems Solved
1. **Script termination**: PHP's default behavior of exiting after execution
2. **Scheduled execution**: Need for periodic tasks without external cron
3. **Concurrency foundation**: Base for handling multiple operations in single process
4. **Developer experience**: Familiar API for developers coming from Node.js/JavaScript

### Technical Decisions

#### Timer Resolution
Used microsecond precision (`microtime(true)`) for accurate interval timing, enabling sub-second intervals.

#### CPU Management
Added `usleep(1000)` (1ms) to prevent 100% CPU usage while maintaining millisecond responsiveness. This balances performance with resource efficiency.

#### Timer Storage
Stored timers as array with:
- `callback`: The function to execute
- `interval`: Time between executions (in seconds)
- `lastRun`: Timestamp of last execution (for drift calculation)

#### By-Reference Timer Updates
Used `&$timer` in foreach loop to update `lastRun` in place without array reconstruction.

### Alternatives Considered

#### Option 1: Use pcntl_alarm() for timers
**Rejected because:**
- Limited to one timer at a time
- Signal-based approach is complex and fragile
- Not available on Windows
- Harder to manage multiple intervals
- Signals can interfere with other operations

#### Option 2: Forked processes for each timer
**Rejected because:**
- Massive overhead for simple timers
- Complex inter-process communication
- Resource intensive (each fork consumes memory)
- Over-engineering for basic requirement
- Difficult to manage process lifecycle

#### Option 3: Use existing event loop library (ReactPHP, Amp)
**Rejected because:**
- External dependency contradicts self-contained runtime goal
- Hides implementation details needed for learning
- Harder to extend and customize for PHP-X needs
- Can be integrated later if truly needed
- Educational value lost

#### Option 4: Cron-style scheduling (external cron daemon)
**Rejected because:**
- External dependency (system cron)
- Not cross-platform (Windows requires Task Scheduler)
- Can't handle sub-minute intervals
- No programmatic control from PHP code
- Requires system-level permissions

#### Option 5: Event extension (libevent, libev)
**Rejected because:**
- Requires PECL extension installation
- Platform dependency
- Adds complexity too early
- Pure PHP solution is more transparent

### Reason for Final Choice
The custom event loop provides:
- **Full control**: Complete visibility into execution model
- **Zero dependencies**: Pure PHP implementation, works everywhere
- **Educational value**: Demonstrates runtime concepts clearly
- **Extensibility**: Easy to add features (setTimeout, I/O handling, promises)
- **Performance**: Minimal overhead, predictable behavior
- **Portability**: Works on any platform with PHP CLI

### Key Insights
1. **Event loops are conceptually simple**: Core concept is just a `while(true)` loop checking conditions
2. **Timing is critical**: Microsecond precision prevents timer drift over long periods
3. **CPU awareness matters**: Even efficient loops need breathing room (usleep)
4. **Execution model shift**: Code now registers behaviors rather than executing directly
5. **Foundation for everything**: All async operations will build on this loop

### What This Enables
With Day 2 complete, PHP-X can now:
- Run indefinitely without external process managers
- Execute scheduled, recurring tasks
- Build toward HTTP servers with request handling loops
- Create GUI applications with event-driven architecture
- Implement timeout mechanisms (`setTimeout` can be added similarly)
- Handle background processing
- Support multiple concurrent timers

**The event loop is the foundation of everything that follows.**

### Future Enhancements Possible
- `setTimeout()` for one-time delayed execution
- `clearInterval()` to stop specific timers
- Priority-based timer execution
- I/O event handling (file, network, stdin)
- Promise/async/await syntax
- Integration with native event loops (libuv, libevent)

### Comparison to Other Runtimes
- **Node.js**: Similar `setInterval()` API, but Node.js uses libuv (C++) for true async I/O with epoll/kqueue
- **Python asyncio**: More complex with explicit async/await syntax, more powerful but steeper learning curve
- **PHP-X**: Simpler polling-based model, easier to understand and extend, trades some performance for clarity

This implementation prioritizes **clarity and control over raw performance**, which is appropriate for an experimental runtime focused on learning and flexibility.
