# Day 14 — Logging System (Platform Observability Layer)

> PHP-X ko visible & diagnosable banana hai. Ab tak logs sirf `echo` the — jo scalable nahi hote.  
> **Logging = production survival tool**

---

## 🎯 Day-14 Goals

- Central logging class
- Log levels (`info`, `warning`, `error`)
- File logging
- Console + file dual output
- Debug-mode aware logging

**Boundaries:**

- ❌ No lifecycle change
- ❌ No middleware change
- ❌ No native change

---

## 🧠 Why Logging Now?

Ab PHP-X me:

- Errors structured hain (Day 12)
- Config system hai (Day 13)

**Next logical step:** System ko observe karna

Without logs:

- Bugs invisible
- Native issues track nahi honge
- Desktop/mobile debugging impossible

---

## ✅ STEP 1 — Logger Class

📄 **File:** `src/Logger.php`

```php
<?php

class Logger
{
    private static string $file;

    public static function init(?string $file): void
    {
        if (!$file) {
            $file = __DIR__ . '/../storage/logs/app.log';
        }
        self::$file = $file;
    }

    public static function info(string $msg): void
    {
        self::write('INFO', $msg);
    }

    public static function warning(string $msg): void
    {
        self::write('WARN', $msg);
    }

    public static function error(string $msg): void
    {
        self::write('ERROR', $msg);
    }

    private static function write(string $level, string $msg): void
    {
        $line = "[" . date('Y-m-d H:i:s') . "] [$level] $msg" . PHP_EOL;

        // Always write to file
        file_put_contents(self::$file, $line, FILE_APPEND);

        // Console output only in debug
        if (Config::get('app.debug', false)) {
            echo $line;
        }
    }
}
```

### 🧠 Design Decisions

| Choice | Reason |
|--------|--------|
| Static class | Platform-level utility |
| File logging | Persistent history |
| Console output | Dev convenience |
| Level separation | Filtering in future |

**Rejected alternatives:**

- PSR logger ❌ — too early
- Syslog ❌ — OS dependency
- JSON logs ❌ — later stage

---

## ✅ STEP 2 — Config Update

📄 **File:** `config/app.php`

Add:

```php
'log.file' => dirname(__DIR__) . '/storage/logs/app.log',
```

> ⚠️ Use `dirname(__DIR__)` instead of `__DIR__ . '/..'` for bulletproof path resolution on Windows.

---

## ✅ STEP 3 — Storage Folder

Create directory:

```
storage/logs/
```

Ensure it is writable.

---

## ✅ STEP 4 — Initialize Logger

📄 **File:** `bin/phpx`

Add (after `Config::load()`):

```php
require_once __DIR__ . '/../src/Logger.php';

Logger::init(Config::get('log.file'));
```

> ⚠️ `Logger::init()` **must** come after `Config::load()` — otherwise `Config::get('log.file')` returns `NULL`.

---

## ✅ STEP 5 — Integrate Logging

### Request Logging Middleware

📄 **File:** `examples/server.xphp` (before routes)

```php
Middleware::add(function (Request $req, callable $next) {
    Logger::info("Request: {$req->method()} {$req->path()}");
    return $next($req);
});
```

### Error Handler Integration

📄 **File:** `src/ErrorHandler.php`

Inside `handle()` add:

```php
Logger::error($e->getMessage());
```

### Native Timing Log (Optional)

```php
Middleware::add(function (Request $req, callable $next) {
    $t1 = Native::nowMs();
    $res = $next($req);
    $t2 = Native::nowMs();

    Logger::info("Native timer: " . ($t2 - $t1) . "ms");
    return $res;
});
```

---

## 🧪 Test

Run server and hit `/` route.

**Console (debug mode):**

```
[2026-01-29 18:10:02] [INFO] Request: GET /
[2026-01-29 18:10:02] [INFO] Native timer: 1ms
```

**File:** `storage/logs/app.log` — will contain same entries.

---

## 🧠 echo vs Logger

| `echo` | `Logger` |
|--------|----------|
| Temporary | Persistent |
| No level | info / warn / error |
| No file | File logging |
| Dev only | Prod usable |

---

## 🐛 Known Issue & Fix

### Error Encountered

```
Logger::init(): Argument #1 ($file) must be of type string, null given
```

### Root Cause

| Problem | Why |
|---------|-----|
| Logger got `NULL` | `Config::get('log.file')` returned `NULL` |
| Config path relative | Windows path resolution edge case |
| Logger strict type | No fallback for missing config |

### Fix Applied

1. **Config path** — changed to `dirname(__DIR__) . '/storage/logs/app.log'`
2. **Logger init** — accepts `?string` with fallback default
3. **Load order** — ensured `Config::load()` runs before `Logger::init()`

---

## 🔒 Architecture Status

- Lifecycle untouched ✔
- Middleware contract same ✔
- Config used ✔
- Native boundary untouched ✔

---

## 🧠 What Day-14 Unlocked

- ✔ Persistent logs
- ✔ Debug vs prod behavior
- ✔ Error tracking
- ✔ Native integration debugging
- ✔ Desktop/mobile readiness

> Logging makes a platform **operable**.

---

## 📝 PROJECT_JOURNEY.md Entry

```markdown
## Day 14 — Logging System

- Introduced central Logger class
- File + console logging
- Log levels: info, warning, error
- Integrated with ErrorHandler and middleware
- Enables observability for platform and native layers
```

---

**✅ Day 14 complete — logging active**
