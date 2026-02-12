# Day 11 — Native Bridge (PHP ↔ Native) via FFI (ZERO Risk)

Aaj hum PHP-X me **first time native boundary cross** karenge — lekin **safe, reversible, spec-compliant** tareeke se.

Goal ye nahi ki event-loop ko native me rewrite karein.
Goal ye hai ki **bridge pattern** prove ho jaaye: PHP → native call → value return → PHP lifecycle intact.

> PHP FFI = native rehearsal (not final implementation).

---

## Session Output

- Native boundary cross proof done
- Bridge isolated and reversible
- Middleware lifecycle intact
- Event-loop spec untouched

---

## 🎯 Objectives (Tight & Focused)

- Native boundary ko code me cross karna (first time)
- PHP ↔ native call prove karna
- Middleware + request lifecycle intact validate karna
- Event-loop spec untouched (Day-10 freeze respected)

---

## 🧠 Strategy

Hum PHP **FFI** use karenge, kyunki:

- No PHP extension writing
- Quick feedback loop
- Future C extension / Rust bridge ka shape same rahega

Important: **Architecture change nahi**. Sirf ek isolated bridge class.

---

## ✅ Step 1 — Native demo function (C)

**File**

- `native/timer.c`

**Purpose**

- Sirf ek proof function: `current_time_ms()`
- Event-loop ka replacement ❌
- Bas “PHP native se baat kar sakta hai” ka demo ✔

**Build (Linux)**

```bash
gcc -shared -fPIC native/timer.c -o native/libtimer.so
```

**Build (macOS)**

```bash
gcc -shared -fPIC native/timer.c -o native/libtimer.dylib
```

**Build (Windows, optional)**

```powershell
# Requires a C toolchain (MinGW/clang). Output DLL name is important.
gcc -shared -o native\libtimer.dll native\timer.c
```

> Agar aap build skip karna chahte ho: koi problem nahi. Bridge class safe fallback use karegi.

---

## ✅ Step 2 — PHP-side wrapper (safe layer)

**File**

- `src/Native.php`

**Design Intent**

- Native calls ek hi jagah isolate
- Core runtime untouched
- Native missing/FFI disabled ho to crash nahi (safe fallback)
- Future me C-extension / Rust bridge me swap possible

---

## ✅ Step 3 — Middleware lifecycle validation

**Update**

- `examples/server.xphp`

**Add test middleware**

```php
Middleware::add(function (Request $req, callable $next) {
    $t1 = Native::nowMs();
    $res = $next($req);
    $t2 = Native::nowMs();

    echo "[NATIVE TIMER] " . ($t2 - $t1) . "ms\n";
    return $res;
});
```

**What we proved**

- ✔ PHP can call native (when lib present)
- ✔ Native returns numeric data
- ✔ Middleware lifecycle intact
- ✔ Event-loop spec unaffected
- ✔ Day-10 freeze respected

---

## 🧪 Run

```bash
./bin/phpx examples/server.xphp
```

**Expected output (example)**

```text
[Server] Running at http://127.0.0.1:8080
[NATIVE TIMER] 1ms
```

---

## 🩺 Debug Notes ("[NATIVE TIMER]" nahi dikha?)

Important clarity: **FFI disabled ya native lib missing ho to bhi** `[NATIVE TIMER]` print ho sakta hai (fallback timer via `microtime()`), lekin **true “native proof”** tabhi maana jayega jab FFI enabled ho aur `native/libtimer.*` load ho.

1) **Middleware add block commented / not loaded**
- Confirm `examples/server.xphp` me middleware block active ho.

2) **`Native` class load nahi ho rahi**
- Ensure runtime entrypoint (`bin/phpx`) `src/Native.php` require karta ho.

3) **FFI disabled**
- CLI php.ini me `ffi.enable=1` (or `true`) hona chahiye.

4) **Native library missing / wrong name**
- `native/libtimer.so` (Linux), `native/libtimer.dylib` (macOS), `native/libtimer.dll` (Windows)

**Note**

- Agar native library available nahi hai, PHP-X should still run (safe fallback) — but native proof mode nahi hoga.

---

## End State

Day-11 ke end par:

- Native boundary cross proof ✔
- Architecture freeze respected ✔
- Native bridge isolated ✔
