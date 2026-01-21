# Day 9 — Advanced Middleware & Request Lifecycle

**Date**: Day 9 of PHP-X development  
**Focus**: Enterprise-grade middleware patterns — blocking, timing, response modification  
**Outcome**: Completed middleware architecture with before/after execution, request blocking, and response transformation capabilities

---

## Objective

Transform the Day 8 middleware foundation from functional proof-of-concept to production-ready pipeline with support for:

1. **Before + After execution** — Single middleware executes code both before and after route handler
2. **Request blocking** — Middleware can terminate request without invoking handler (auth/rate-limiting)
3. **Response modification** — Middleware can transform responses (security headers, CORS, cookies)
4. **Execution timing** — Built-in performance profiling via middleware
5. **Order control** — FIFO registration with predictable execution sequence

This completes the middleware architecture specification and establishes the **frozen API contract** for future native/C++ implementation.

---

## Architecture Evolution

**Before Day 9** (Day 8 implementation):
```php
// Functional composition approach
$dispatcher = array_reduce(
    array_reverse(self::$stack),
    fn($next, $middleware) => fn($req) => $middleware($req, $next),
    $core
);
return $dispatcher($req);
```

**After Day 9** (Recursive dispatcher):
```php
// Recursive queue-based approach
$dispatcher = function ($req) use (&$dispatcher, &$middlewareStack, $core) {
    if (empty($middlewareStack)) return $core($req);
    $middleware = array_shift($middlewareStack);
    return $middleware($req, fn($req) => $dispatcher($req));
};
return $dispatcher($req);
```

**Key Innovation**: Shift from functional composition to recursive dispatcher enables more intuitive mental model matching Express.js/ASP.NET Core patterns while maintaining identical external API.

---

## Request Lifecycle — Final Architecture
Aaj hum middleware ko "toy feature" se nikaal kar real enterprise-grade pipeline banayenge.  
Is day ke baad aap clearly dekh paoge ki auth, timing, rate-limit, security headers kaise naturally plug hote hain.

Main hamesha ki tarah explain karunga:

kya ho raha hai

kyon ho raha hai

alternatives kya the

ye design kyon best hai

🎯 Day-9 Goals (very concrete)

End of Day-9 aap kar paoge:

Multiple middleware with order control

Before + After middleware behavior

Timing middleware (request kitna time laga)

Blocking middleware (auth-style)

Response ko middleware se modify karna

🧠 Big Picture: Request Lifecycle (ab final shape le raha hai)
Raw HTTP
  ↓
Request object
  ↓
Middleware (before)
  ↓
Router / Controller
  ↓
Response object
  ↑
Middleware (after)
  ↑
Server sends HTTP


👉 Ye lifecycle ab freeze hone ke kareeb hai
👉 Ye future C / native runtime ka contract banega

✅ STEP-1: Middleware ko "Before + After" capable banana
📄 File:
src/Middleware.php

🔁 handle() method ko replace karo:
public static function handle(Request $req, callable $core)
{
    $middlewareStack = self::$stack;

    $dispatcher = function (Request $req) use (&$dispatcher, $middlewareStack, $core) {
        if (empty($middlewareStack)) {
            return $core($req);
        }

        $middleware = array_shift($middlewareStack);

        return $middleware($req, function (Request $req) use (&$dispatcher) {
            return $dispatcher($req);
        });
    };

    return $dispatcher($req);
}

🔍 Line-by-line reasoning
array_shift($middlewareStack)

Middleware queue ki tarah behave karti hai

Order preserved rehta hai

👉 Why queue, not stack?

Middleware ko top-down logical order me chalana hota hai

Debugging easy hoti hai

$middleware($req, function (...) {})

Middleware ko next() callback milta hai

Ye next controller ya next middleware ho sakta hai

👉 Exactly Express / ASP.NET Core model

✅ STEP-2: Timing Middleware (Before + After demo)
📄 Add in:
examples/server.xphp

Middleware::add(function (Request $req, callable $next) {
    $start = microtime(true);

    $response = $next($req);

    $time = round((microtime(true) - $start) * 1000, 2);
    echo "[TIME] {$req->method()} {$req->path()} took {$time}ms\n";

    return $response;
});

🧠 Kya seekha yahan?

Middleware request se pehle bhi chal raha

Aur response ke baad bhi

Same middleware me

👉 Ye hi magic hai middleware ka

✅ STEP-3: Auth-style Blocking Middleware
📄 Same file:
examples/server.xphp

Middleware::add(function (Request $req, callable $next) {
    if ($req->path() === '/admin') {
        return Response::html("<h1>403 Forbidden</h1>")->status(403);
    }

    return $next($req);
});

🧠 Important insight
return Response::html(...);


👉 $next() call nahi hua
👉 Request yahin stop ho gayi

Exactly:

auth

rate-limit

IP block
aise hi kaam karte hain

✅ STEP-4: Response-modifying Middleware (headers example)
📄 Add this middleware:
Middleware::add(function (Request $req, callable $next) {
    $res = $next($req);

    // Example: security header
    $res->header('X-Powered-By', 'PHP-X');

    return $res;
});

⚠️ Ek chhota change chahiye Response class me
📄 File:
src/Response.php

➕ Add this method:
public function header(string $key, string $value): self
{
    $this->headers[$key] = $value;
    return $this;
}

🧠 Why this matters

Middleware response ko decorate kar sakta hai

Security headers

CORS

Cookies

👉 Ye enterprise requirement hai

✅ STEP-5: Test Routes
📄 examples/server.xphp
Router::get('/', function () {
    return Response::html("<h1>Home</h1>");
});

Router::get('/admin', function () {
    return Response::html("<h1>Admin</h1>");
});

Server::start(8080);

🚀 RUN & TEST
./bin/phpx examples/server.xphp

Browser:

/ → works

/admin → blocked (403)

Terminal:
[TIME] GET / took 1.23ms

🧠 Day-9 BIG achievements

✔ Full request lifecycle finalized
✔ Before + After middleware
✔ Blocking middleware
✔ Response modification
✔ Native-ready contract

👉 Is point ke baad middleware design rarely change hota hai
👉 Ye almost "framework-complete" area hai