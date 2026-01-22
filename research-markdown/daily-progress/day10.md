# Day 10 (Continue) — Lifecycle Freeze

Aaj hum koi naya feature nahi banayenge. Sirf ye ensure karenge ki jo decisions Day-10 me liye gaye the, wo codebase aur documentation dono me **enforce** ho jaayen.

## Session Output

- Lifecycle technically frozen
- Event-loop API spec-only (no accidental drift)
- Native boundaries explicit & enforceable
- PROJECT_JOURNEY.md updated as a spec

---

## 🎯 Exact Tasks

### Task A — Lifecycle Freeze Guards (Code-level)

#### A1) Request ko immutable enforce karna

**Problem**

Abhi Request me properties private hain (good), par future me koi setter add kar sakta hai (bad).

**Decision (freeze)**

- Request is read-only by design.
- No setters. Ever.

**Action**

[src/Request.php](../../src/Request.php) me ye comment + pattern add karo (no setters already, bas guard):

```php
/**
 * Request is IMMUTABLE by design.
 * Do not add setters. Create a new Request if needed.
 */
class Request
{
    // existing code

    final public function __set($name, $value)
    {
        throw new \LogicException("Request is immutable");
    }
}
```

**Why this approach?**

- PHP me `final + __set` = accidental mutation block
- Alternative: cloning ❌ (confusing)
- Alternative: `public readonly` (PHP 8.1+) ❌ (version coupling)

✅ Chosen option is version-safe and explicit.

---

### Task B — Middleware Contract Freeze (Enforcement)

#### B1) Middleware return type enforcement

**Decision (freeze)**

- Every middleware must return a `Response`.

**Action**

[src/Middleware.php](../../src/Middleware.php) me type-hint harden karo:

```php
public static function handle(Request $req, callable $core): Response
{
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
}
```

**Why this matters**

- Native runtime expects `Response`
- Silent bugs ❌
- Fail-fast ✔

---

### Task C — Event Loop API = SPEC ONLY (No Feature Drift)

#### C1) Core.php ko “spec-only” clearly mark karo

**Decision (freeze)**

- API names are final
- Implementation is temporary
- Native layer will replace internals

**Action**

[src/Core.php](../../src/Core.php) ke top pe spec banner add karo:

```php
/**
 * EVENT LOOP SPEC (REFERENCE IMPLEMENTATION)
 *
 * API in this class is FROZEN.
 * Internal implementation WILL be replaced by native code.
 *
 * Do NOT add new async primitives here.
 */
class Core
{
    // existing methods
}
```

**Why comment matters?**

- Contributors ko boundary dikhegi
- Future-you ko yaad rahega: “yahan feature mat ghusaana”

---

### Task D — Native Boundary Markers (Code Tags)

#### D1) Native-bound code ko tag karo

Har jagah jahan future native replacement planned hai, ye tag add karo:

```text
// @native-boundary: event-loop
```

**Examples**

- `Core::setInterval`
- `Core::run`
- (future) IO methods

**Why comments, not folders?**

- Comments are searchable
- No premature directory split
- IDE-friendly

---

### Task E — PROJECT_JOURNEY.md (Day 10 Spec Entry)

Ab documentation ko research-paper grade pe lock karte hain.

**Action**

Is section ko end me add karo:

---

## Day 10 — Lifecycle Freeze & Native Boundaries

### Decisions Frozen

- Request lifecycle is immutable
- Middleware contract is fixed: `function (Request, callable $next): Response`
- Middleware dispatch uses `array_reduce`
- Server acts only as transport
- Router, Middleware, Request, Response APIs are stable

### Event Loop Specification

The following APIs are considered final on the PHP side:

- `Core::setTimeout(callable, int)`
- `Core::setInterval(callable, int)`
- `Core::clearTimer(int)`
- `Core::run()`

Current PHP implementation is a reference only. Native implementations may replace internals without API changes.

### Native Boundaries Defined

**Will move to native (C/C++/Rust):**

- Event loop internals
- Timers and IO polling
- OS-level integrations

**Will remain in PHP:**

- Request / Response
- Middleware
- Router
- Controller logic
- Application code

### Explicit Non-Goals (for now)

- Performance optimization
- Multithreading
- Promise/Fiber abstractions
- OS kernel development

These are postponed by design to protect architectural stability.

---

### Task F — Freeze Checklist (Self-verification)

Aaj ke end pe khud se ye checklist tick karo:

- [ ] Request mutation impossible
- [ ] Middleware return enforced
- [ ] Event loop APIs documented as frozen
- [ ] Native boundaries tagged
- [ ] PROJECT_JOURNEY.md updated

Agar sab ✔ hai → **Day 10 DONE**

---

## 🧠 Important Mentor Note

Freezing architecture is harder than adding features. Aaj aapne temptation resist ki — ye maturity ka sign hai.

Kal ke baad:

- Native work safe hoga
- Desktop embedding predictable hoga
- Research paper credible hoga