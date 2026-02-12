# Day 12 — Error Handling & Exception Flow (Option A)

Aaj hum PHP-X ko production-grade stability ki taraf le jaayenge.
Goal: errors ko random crash se nikaal kar controlled, observable, and debuggable behavior banana — without breaking the frozen lifecycle.

---

## Session Output

- Centralized error handling added
- Exception → HTTP response mapping
- Middleware-safe exception flow
- Dev-friendly error pages + prod-safe behavior

---

## 🎯 Objectives (Clear & Limited)

- Centralized error handling
- Exception → HTTP response mapping
- Middleware-safe exception flow
- Developer-friendly error pages (dev mode)
- Silent & safe behavior (prod mode)

**Non-goals (Today)**

- ❌ Koi naya feature
- ❌ Lifecycle change
- ❌ Native code

---

## 🧠 Big Picture (WHY)

**Ab tak**

- Agar controller / middleware me exception aayi → process crash ho sakta hai
- Error ka ownership unclear tha (Server? Router? Middleware?)

**Industry rule**

Errors are part of the request lifecycle, not accidents.

**Isliye flow**

```text
Request
 → Middleware
   → Router / Controller
     → Exception
   ← Error Handler
 ← Response
```

---

## ✅ Step 1 — Global Error Handler class

**File**

- `src/ErrorHandler.php`

**Code**

```php
<?php

class ErrorHandler
{
    public static bool $debug = true;

    public static function handle(\Throwable $e): Response
    {
        if (self::$debug) {
            return Response::html(
                "<h1>PHP-X Error</h1>" .
                "<pre>" . htmlspecialchars((string)$e) . "</pre>"
            )->status(500);
        }

        return Response::html(
            "<h1>Internal Server Error</h1>"
        )->status(500);
    }
}
```

**Design decisions (WHY)**

- Throwable → catches Exception + Error
- Debug flag → dev vs prod
- HTML response → browser-friendly
- No echo / die → lifecycle respected

**Alternatives rejected**

- set_error_handler() ❌ (global side effects)
- try/catch everywhere ❌ (boilerplate)

---

## ✅ Step 2 — Middleware layer me exception catch

**File**

- `src/Middleware.php`

**Action (wrap `handle()` method)**

```php
public static function handle(Request $req, callable $core): Response
{
    try {
        $dispatcher = array_reduce(
            array_reverse(self::$stack),
            function ($next, $middleware) {
                return function (Request $req) use ($middleware, $next): Response {
                    $res = $middleware($req, $next);

                    if (!$res instanceof Response) {
                        throw new \LogicException(
                            "Middleware must return instance of Response"
                        );
                    }

                    return $res;
                };
            },
            $core
        );

        return $dispatcher($req);

    } catch (\Throwable $e) {
        return ErrorHandler::handle($e);
    }
}
```

**WHY middleware catches errors?**

- Middleware is request boundary
- Router / Controller errors should not kill server
- One place = predictable behavior

**Rejected**

- Catch in Server ❌ (too low-level)
- Catch in Router ❌ (misses middleware errors)

---

## ✅ Step 3 — Router safe-guard (logic errors)

**File**

- `src/Router.php`

**Inside `dispatch()` add defensive guard**

```php
$result = call_user_func(self::$routes[$method][$path], $req);

if (!$result instanceof Response) {
    throw new \LogicException(
        "Controller must return instance of Response"
    );
}

return $result;
```

**WHY this matters**

- Forces contract discipline
- Prevents silent bugs
- Native runtime will rely on this guarantee

---

## ✅ Step 4 — Load ErrorHandler

**File**

- `bin/phpx`

**Add require (order matters)**

```php
require_once __DIR__ . '/../src/ErrorHandler.php';
```

**Correct order snippet**

- Request
- Response
- ErrorHandler
- Middleware
- Router
- Server

---

## ✅ Step 5 — Test scenarios (VERY IMPORTANT)

1) **Controller throws exception**

```php
Router::get('/boom', function () {
    throw new Exception("Something exploded");
});
```

**Browser**

```text
http://127.0.0.1:8080/boom
```

**Expected (debug=true)**

- 500 status
- Stack trace visible

2) **Middleware throws exception**

```php
Middleware::add(function (Request $req, callable $next) {
    if ($req->path() === '/fail') {
        throw new RuntimeException("Middleware failed");
    }
    return $next($req);
});
```

**Expected**

- Same controlled error response
- Server keeps running

3) **Production mode test**

In `src/ErrorHandler.php`:

```php
ErrorHandler::$debug = false;
```

**Expected**

- Generic error page
- No stack trace leak

---

## 🧠 What Day-12 unlocked (BIG)

- ✔ Server no longer crashes
- ✔ Errors are lifecycle-aware
- ✔ Middleware + Router contracts enforced
- ✔ Dev vs Prod behavior possible
- ✔ Native runtime compatibility improved

---

## 🔒 What remains frozen

- Lifecycle ✔
- Middleware contract ✔
- Event loop API ✔
- Native boundary ✔

Day-12 does not violate Day-10 freeze.

---

## 📝 Update PROJECT_JOURNEY.md

Add:

```markdown
## Day 12 — Centralized Error Handling

- Introduced ErrorHandler for all uncaught exceptions
- Middleware layer catches Throwable
- Controllers and middleware must return Response
- Debug and production modes supported
- Prevents server crashes and undefined behavior
```

---

## ⏸️ Stop point (recommended)

This is a mentally heavy but clean milestone.
Aaj yahin rukna best hai.
