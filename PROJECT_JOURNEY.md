# PHP-X — Project Journey, Design Decisions & Daily Engineering Log

This document is a **living research-style engineering log** for the PHP-X project.

It records, day by day:
- what was built
- which files were created or modified
- why each decision was taken
- which alternatives existed
- and why the chosen solution was preferred

This file will be **continuously updated** as PHP-X evolves.

## System architecture

PHP-X Runtime
│
├── PHP Engine (PHP-CLI/JIT)
│
├── X-Core (Event Loop + Async Engine)
│      ├── Timers
│      ├── Async FS
│      ├── Networking
│      ├── WebSocket
│      ├── Child Processes
│      └── Thread Pool
│
├── X-Bridge (C/C++ FFI Layer)
│      ├── OS APIs
│      ├── System Info
│      ├── Notifications
│      └── Device Access
│
├── X-GUI
│      ├── Qt/GTK bindings
│      └── HTML-based Renderer (Electron-style)
│
├── X-Mobile
│      ├── Android Build Engine
│      ├── iOS Build Engine
│      └── Native Bridge
│
└── PPM – PHP-X Package Manager
       ├── ppm init
       ├── ppm install gui
       ├── ppm install mobile
       └── ppm publish


## PHP-X Repository Structure

php-x/
│
├── src/
│   ├── core/
│   ├── gui/
│   ├── mobile/
│   ├── system/
│   └── utils/
│
├── bin/
│   └── phpx
│
├── ppm/
│   ├── registry/
│   ├── install.php
│   └── publish.php
│
├── examples/
│   ├── hello-world.xphp
│   └── gui-demo.xphp
│
├── docs/
│   └── architecture.md
│
└── tests/


---

## Table of Contents

- Project Intent  
- Core Design Principles  
- Day-by-Day Engineering Log  
  - Day 1 — Project Bootstrap  
  - Day 2 — Custom CLI Runtime  
  - Day 3 — Long-Running Execution  
  - Day 4 — Built-in HTTP Server  
  - Day 5 — DOM & View Abstraction  
  - Day 6 — Router System  
  - Day 7 — Request / Response Design  

---

## Project Intent

PHP-X is an experimental **runtime and application platform** built using PHP
as the primary control language.

The project explores how PHP can evolve beyond traditional
request–response scripting into:

- a long-running runtime
- a platform-controlled HTTP server
- a desktop and mobile application platform
- a system-level application environment (long-term research goal)

PHP-X is **not positioned as a production framework** at this stage.

---

## Core Design Principles

1. Execution model comes before features  
2. APIs are designed before native implementations  
3. Simplicity over premature optimization  
4. Clear separation of concerns  
5. Every abstraction must justify its existence  

---

## Day-by-Day Engineering Log

---

## Day 1 — Project Bootstrap

### Objective
Initialize the PHP-X project with a minimal working runtime.
Build a running system rather than planning a perfect system.

### Files Created
- `README.md` — Project documentation and intent
- `bin/phpx` — Custom CLI launcher
- `examples/hello.xphp` — First example application

### Directory Structure Established
```
php-x/
│
├── README.md
├── bin/
│   └── phpx
└── examples/
    └── hello.xphp
```

### Work Done

#### 1. Project Initialization
- Created root project directory `php-x/`
- Initialized Git repository
- Defined project name and purpose in README

#### 2. Custom CLI Runtime (bin/phpx)
Implemented a minimal PHP CLI launcher:
```php
#!/usr/bin/env php
<?php

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
```

Key features:
- Shebang for direct execution on Unix systems
- Command-line argument validation
- File existence checking
- Direct file execution via `require`

#### 3. File Extension Convention
Introduced `.xphp` as the standard extension for PHP-X applications:
- Distinguishes runtime-specific code from traditional PHP
- Signals that code runs in PHP-X context, not Apache/Nginx

#### 4. First Working Example (examples/hello.xphp)
Created a simple proof-of-concept application:
```php
<?php

echo "Hello from PHP-X!\n";

sleep(1);

echo "PHP-X is running 🚀\n";
```

#### 5. Execution Verification
Successfully ran the first PHP-X application:

**Linux/Mac:**
```bash
chmod +x bin/phpx
./bin/phpx examples/hello.xphp
```

**Windows:**
```bash
php bin/phpx examples/hello.xphp
```

**Expected Output:**
```
Hello from PHP-X!
PHP-X is running 🚀
```

#### 6. Version Control Setup
Initialized Git repository with first commit:
```bash
git init
git add .
git commit -m "init: start PHP-X runtime"
```

### Why This Was Done
- **Minimal viable start**: Focus on a working system rather than perfect architecture
- **Clear direction**: Establish what PHP-X is (a runtime) rather than what it might become
- **Executable proof**: Create something that runs immediately, not just documentation
- **Foundation for iteration**: Build incrementally from a solid base

### Problems Solved
1. **Execution model clarity**: Traditional PHP exits after script completion. PHP-X needs lifecycle control.
2. **Platform independence**: CLI approach works across all operating systems.
3. **Development workflow**: Developers can immediately run and test code.

### Alternatives Considered

#### Option 1: Use Composer as the launcher
**Rejected because:**
- Adds unnecessary dependency
- Obscures the runtime concept
- Less control over execution lifecycle

#### Option 2: Start with web server
**Rejected because:**
- Premature complexity
- Can't verify basic execution model first
- Harder to debug initial issues

#### Option 3: Compile to binary immediately
**Rejected because:**
- Over-engineering for Day 1
- Blocks rapid iteration
- Adds build complexity too early

### Reason for Final Choice
The chosen approach provides:
- **Immediate feedback loop**: Write code, run code, see results
- **Zero external dependencies**: Pure PHP, no installation requirements
- **Clear mental model**: Simple file execution, easy to understand
- **Room to grow**: Can evolve into complex runtime without rewriting foundation

### Key Insights
1. **Running code beats perfect planning**: A working prototype validates assumptions faster than architecture documents.
2. **Custom launcher is essential**: Runtime control starts with controlling process initialization.
3. **File conventions matter**: `.xphp` extension communicates intent and separates concerns.

### What This Enables
With Day 1 complete, PHP-X can now:
- Execute PHP code outside traditional web server context
- Control when and how PHP scripts are invoked
- Build toward long-running processes
- Expand into event loops, servers, and GUI applications

This foundation makes all future development possible.

---

## Day 2 — Event Loop & Long-Running Execution

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

---
## Day 3 — Long-Running Execution

### Objective
Keep PHP running instead of exiting.

### Files Created / Modified
- src/Core.php
- bin/phpx

### Key Concept
```php
Core::setInterval(fn () => ..., 1000);
```

### Work Done
- Implemented a simple event loop using while(true)
- Added timer-based callbacks

### Why This Was Done
Long-running execution is essential for servers,
desktop apps, and background services.

### Alternatives Considered
- Forked processes
- Cron-style execution

### Reason for Final Choice
The loop-based model is simple, transparent,
and easy to evolve into a native event loop.

---

## Day 4 — Built-in HTTP Server

### Objective
Handle HTTP requests inside PHP-X.

### Files Created
- src/Server.php
- examples/server.xphp

### Work Done
- Implemented a TCP-based HTTP server
- Manually parsed HTTP requests
- Returned valid HTTP responses

### Problems Encountered
- HTML escaping issues
- favicon.ico browser requests
- Empty HTTP responses

### Fixes Applied
- Proper HTTP status codes
- 204 No Content for favicon requests

### Reason for Final Choice
Full control over HTTP lifecycle is mandatory
for a runtime-level platform.

---

## Day 5 — DOM & View Abstraction

### Objective
Separate UI generation from server logic.

### Files Created / Modified
- src/DOM.php
- examples/index.html

### Work Done
- Introduced DOM/View abstraction
- Cached HTML to prevent repeated disk reads

### Alternatives Considered
- Inline HTML strings
- Full template engines

### Reason for Final Choice
Minimal abstraction with future WebView compatibility.

---

## Day 6 — Router System

### Objective
Replace server-side if/else routing logic.

### Files Created
- src/Router.php

### Work Done
- Implemented Router::get() and Router::post()
- Centralized route definitions

### Alternatives Considered
- Regex-based routing
- Auto-routing

### Reason for Final Choice
Explicit routing is easier to reason about
and safer for long-term maintenance.

---

## Day 7 — Request / Response Design

### Objective
Formalize communication between server and application logic.

### Files Created
- src/Request.php
- src/Response.php

### Files Modified
- src/Router.php
- src/Server.php

### Work Done
- Wrapped raw HTTP data into Request object
- Generated HTTP output via Response object
- Server reduced to transport layer

### Alternatives Considered
- Passing arrays
- Passing raw strings

### Reason for Final Choice
Request/Response objects define clear API contracts
required for middleware and native layers.

---

## Current Architectural State

PHP-X currently includes:

- Custom CLI runtime
- Long-running execution model
- Built-in HTTP server
- Router system
- Request/Response abstraction
- DOM/View layer

This is a **platform foundation**, not a finished product.

---

## Living Document Policy

This file will be updated daily.
Future sections will extend this log without rewriting history.
