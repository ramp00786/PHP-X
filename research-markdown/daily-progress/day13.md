# Day 13 — Configuration System (Platform Readiness Step)

Ab PHP-X “code-only framework” se nikal kar real application platform banega.
Aaj ka focus: central configuration layer.

---

## Session Output

- Global config system added
- Dev vs Prod mode centralized
- Port/debug/environment flags centralized
- Platform readiness improved (desktop/mobile)

---

## 🎯 Goals

- Global config system
- Dev vs Prod mode control
- Port, debug, and environment flags centralize karna
- Future desktop/mobile builds ke liye base banana

**Non-goals (Today)**

- ❌ No lifecycle changes
- ❌ No native changes
- ❌ No new runtime APIs

---

## 🧠 WHY configuration system ab?

**Ab tak**

- Debug flag `ErrorHandler` me hard-coded
- Server port hard-coded
- Future settings scattered ho jaayengi

**Industry rule**

Scattered settings = unmaintainable platform

---

## ✅ Step 1 — Config class

**File**

- `src/Config.php`

**Code**

```php
<?php

class Config
{
    private static array $data = [];

    public static function load(array $config): void
    {
        self::$data = $config;
    }

    public static function get(string $key, $default = null)
    {
        return self::$data[$key] ?? $default;
    }
}
```

**Why static store?**

- Platform-level settings global hote hain
- Dependency injection overkill at this stage
- Simplicity > architecture purity

**Rejected**

- .env parser ❌ (later)
- JSON config ❌ (overhead)
- YAML ❌ (dependency)

---

## ✅ Step 2 — Config file (user-facing)

**File**

- `config/app.php`

**Code**

```php
<?php

return [
    'app.env' => 'dev',      // dev | prod
    'app.debug' => true,
    'server.port' => 8080,
];
```

---

## ✅ Step 3 — Load config in CLI

**File**

- `bin/phpx`

**Add**

```php
require_once __DIR__ . '/../src/Config.php';

Config::load(require __DIR__ . '/../config/app.php');
```

---

## ✅ Step 4 — Connect config to ErrorHandler

**File**

- `src/ErrorHandler.php`

**Replace**

```php
public static bool $debug = true;
```

**With**

```php
private static function debug(): bool
{
    return Config::get('app.debug', false);
}
```

Then update:

```php
if (self::debug()) {
```

---

## ✅ Step 5 — Use config in Server

**File**

- `examples/server.xphp`

**Replace**

```php
Server::start(8080);
```

**With**

```php
Server::start(Config::get('server.port', 8080));
```

---

## 🧠 What we achieved

- ✔ Central settings
- ✔ Debug mode controlled
- ✔ Server port configurable
- ✔ Future mobile/desktop builds ready
- ✔ No architecture break

---

## 🧪 Quick test

Change:

```php
'app.debug' => false,
```

**Errors now generic.**

Change:

```php
'server.port' => 9000,
```

**Server runs on**

```text
http://127.0.0.1:9000
```

---

## 🧠 Why this step matters long-term

- Desktop builds need platform config
- Mobile builds need runtime flags
- Native layer will read config too

---

## 📝 Update PROJECT_JOURNEY.md

Add:

```markdown
## Day 13 — Configuration System

- Introduced central Config class
- Added config/app.php file
- Connected ErrorHandler and Server to config
- Enables environment-aware platform behavior
```
