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

### Work Done
- Created custom CLI runtime with `.xphp` file extension convention
- Implemented basic file execution via `require`
- Established executable proof of concept
- Initialized Git repository

### Alternatives Considered
- Composer-based launcher
- Starting with web server
- Immediate binary compilation

### Reason for Final Choice
Custom launcher provides immediate feedback loop,
zero external dependencies, and clear mental model
for runtime control.

**See [daily-progress/day1.md](daily-progress/day1.md) for detailed analysis.**

---

## Day 2 — Event Loop & Long-Running Execution

### Objective
Transform PHP from a script-and-exit model into a long-running runtime.
Implement Node.js-style `setInterval()` to keep PHP-X alive indefinitely.

### Files Created / Modified
- `src/Core.php` — Event loop and timer management (new)
- `bin/phpx` — Runtime initialization (modified)
- `examples/hello.xphp` — Event loop demonstration (modified)

### Work Done
- Implemented minimal event loop using `while(true)` with timer callbacks
- Added `Core::setInterval()` for recurring tasks
- Integrated `usleep(1000)` to prevent CPU saturation
- Modified launcher to call `Core::run()` after user code execution

### Alternatives Considered
- pcntl_alarm() for timers
- Forked processes
- ReactPHP/Amp libraries
- Cron-style scheduling
- Event extensions (libevent, libev)

### Reason for Final Choice
Custom loop provides full control, zero dependencies,
educational transparency, and cross-platform portability
while maintaining extensibility.

**See [daily-progress/day2.md](daily-progress/day2.md) for detailed analysis.**

---

## Day 3 — DOM Abstraction Layer

### Objective
Enable PHP to control UI elements without JavaScript.
Establish DOM manipulation API for future WebView integration.

### Files Created / Modified
- `src/DOM.php` — DOM abstraction layer (new)
- `bin/phpx` — Added DOM class loading (modified)
- `examples/index.html` — Sample HTML template (new)
- `examples/hello.xphp` — DOM manipulation demonstration (modified)

### Work Done
- Created DOM class with static methods for UI control
- Implemented `DOM::load()` for HTML file loading
- Added `DOM::setText()` with CSS selector API (simulation)
- Integrated with event loop for real-time UI updates

### Alternatives Considered
- Direct PHP template rendering
- JavaScript + WebSocket bridge
- Browser automation tools
- DOMDocument parsing (deferred)
- Template engines

### Reason for Final Choice
Selector-based API with simulation-first approach
validates design before implementation, maintains
platform independence, and provides familiar patterns.

**See [daily-progress/day3.md](daily-progress/day3.md) for detailed analysis.**

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
Replace brittle `if/else` routing with declarative router system.
Transform PHP-X from server-with-routes to framework-with-router.

### Files Created / Modified
- `src/Router.php` — Route registry and dispatcher (new)
- `src/View.php` — Response formatting abstraction (new)
- `src/Server.php` — Integrated Router dispatch (modified)
- `examples/server.xphp` — Declarative route definitions (modified)
- `bin/phpx` — Added Router and View loading (modified)

### Work Done
- Implemented hash-table-based route registry (O(1) lookup)
- Added `Router::get()` and `Router::post()` registration methods
- Created `Router::dispatch()` for request→handler resolution
- Built `View::render()` and `View::text()` for response formatting
- Integrated router into server request handling loop
- Added 404 fallback handler

### Alternatives Considered
- Regex-based routing (deferred until route parameters needed)
- File-based auto-routing (rejected — too implicit)
- Annotation-based routing (rejected — requires reflection)
- External router libraries (rejected — violates zero-dependency philosophy)

### Reason for Final Choice
Hash table + closures provides O(1) performance,
zero dependencies, educational transparency,
and matches Laravel/Express patterns.
Simple enough for MVP, extensible for future features.

**See [daily-progress/day6.md](daily-progress/day6.md) for detailed analysis.**

---

## Day 7 — Request / Response Design

### Objective
Replace raw string-based HTTP handling with type-safe Request and Response objects.
Complete HTTP abstraction layer and establish API contract for native code integration.

### Files Created / Modified
- `src/Request.php` — Immutable HTTP request object with method/path/body (new)
- `src/Response.php` — Fluent response builder with status codes and headers (new)
- `src/Router.php` — Updated `dispatch()` to accept Request, return Response (modified)
- `src/Server.php` — Simplified to pure I/O transport layer (modified)
- `examples/server.xphp` — Updated handlers to use Request/Response API (modified)
- `bin/phpx` — Added Request and Response class loading (modified)

### Work Done
- Implemented immutable Request object with HTTP parsing in constructor
- Created Response with static factories (`Response::html()`, `Response::text()`)
- Added fluent API for method chaining (`->status(404)`)
- Integrated Request/Response into Router dispatch flow
- Fixed 404 status code bug (was 200, now correctly 404)
- Reduced Server to 30 lines (was 50 lines)
- Established clear API boundary for future C++ native layer

### Alternatives Considered
- PSR-7 interfaces (rejected — too complex for MVP, external dependency)
- Mutable Response objects (rejected — verbose, not fluent)
- Array-based responses (rejected — no type safety or validation)
- Global request/response helpers (rejected — hidden dependencies, not testable)

### Reason for Final Choice
Request/Response objects with static factories and fluent API provide:
(1) Type safety via method signatures
(2) Testability without running server
(3) Clear API contract for native code
(4) Extensibility for JSON/middleware/headers
(5) Industry-standard pattern (Laravel/Symfony/Express)

**See [daily-progress/day7.md](daily-progress/day7.md) for detailed analysis.**

---

## Day 8 — Middleware Pipeline

### Objective
Implement middleware architecture for request/response interception and transformation.
Enable cross-cutting concerns (logging, auth, timing) without modifying routes.

### Files Created / Modified
- `src/Middleware.php` — Middleware registry and pipeline executor (new)
- `src/Server.php` — Wrapped Router dispatch in Middleware pipeline (modified)
- `examples/server.xphp` — Added logging middleware registration (modified)
- `bin/phpx` — Added Middleware class loading (modified)

### Work Done
- Implemented middleware stack with FIFO registration
- Built functional pipeline composition using `array_reduce()`
- Created onion-model execution (request in, response out through same layers)
- Integrated middleware into Server request handling
- Added before/after processing support
- Enabled request blocking via short-circuit returns
- Demonstrated logging middleware as first use case

### Alternatives Considered
- Decorator pattern with classes (rejected — verbose, manual nesting)
- Event-based system (rejected — no control flow or modification)
- Pipeline class with fluent API (deferred — over-engineering for MVP)
- Annotation/attribute-based middleware (rejected — requires reflection)

### Reason for Final Choice
Functional middleware with `array_reduce()` provides:
(1) Simplicity (~40 lines, zero dependencies)
(2) Flexibility (dynamic add/remove)
(3) Transparency (understandable functional composition)
(4) Industry alignment (Express.js, Koa.js patterns)
(5) Pre/post processing support
(6) Easy testability in isolation

**See [daily-progress/day8.md](daily-progress/day8.md) for detailed analysis.**

---

## Current Architectural State

PHP-X currently includes:

- Custom CLI runtime
- Long-running execution model
- Built-in HTTP server
- Router system
- Request/Response abstraction
- Middleware pipeline
- DOM/View layer

This is a **platform foundation**, not a finished product.

---

## Living Document Policy

This file will be updated daily.
Future sections will extend this log without rewriting history.
