# Day 16 — Dynamic Routing with Parameters

> Aaj ka feature: Routing with Parameters (jaise `/user/42`, `/post/hello-world`).  
> `Router::get('/user/{id}', ...)` kaam kare aur `$req->param('id')` se value mile.

---

## 🎯 Day-16 Goal

- `{param}` pattern support in routes
- `$req->param('key')` API
- Regex-based route matching
- 404 for unmatched routes

**Boundaries:**

- ❌ No lifecycle change
- ❌ No middleware change
- ❌ No native change

---

## ✅ STEP 1 — Update Router Dispatch

📄 **File:** `src/Router.php`

Replace the existing `dispatch()` function with:

```php
public static function dispatch(Request $req): Response
{
    $method = $req->method();
    $path   = $req->path();

    if (!isset(self::$routes[$method])) {
        return Response::html("<h1>404</h1>")->status(404);
    }

    foreach (self::$routes[$method] as $route => $handler) {

        // Convert /user/{id} → regex
        $pattern = preg_replace('#\{([^}]+)\}#', '([^/]+)', $route);
        $pattern = "#^" . $pattern . "$#";

        if (preg_match($pattern, $path, $matches)) {

            array_shift($matches);

            $params = [];
            if (preg_match_all('#\{([^}]+)\}#', $route, $keys)) {
                foreach ($keys[1] as $i => $key) {
                    $params[$key] = $matches[$i] ?? null;
                }
            }

            $req->setParams($params);

            $result = $handler($req);

            if (!$result instanceof Response) {
                throw new \LogicException("Route must return Response");
            }

            return $result;
        }
    }

    return Response::html("<h1>404</h1>")->status(404);
}
```

---

## ✅ STEP 2 — Update Request Class

📄 **File:** `src/Request.php`

Add property:

```php
private array $params = [];
```

Add methods:

```php
public function setParams(array $params): void
{
    $this->params = $params;
}

public function param(string $key, $default = null)
{
    return $this->params[$key] ?? $default;
}
```

---

## ✅ STEP 3 — Add Test Route

📄 **File:** `examples/server.xphp`

Routes section me add:

```php
Router::get('/user/{id}', function (Request $req) {
    return Response::text("User ID: " . $req->param('id'));
});
```

---

## ✅ STEP 4 — Run & Test

```bash
php bin/phpx examples/server.xphp
```

**Browser:** `http://127.0.0.1:8080/user/55`

**Expected Output:**

```
User ID: 55
```

---

## 🧠 How It Works

Route pattern:

```
/user/{id}
```

Internally converted to regex:

```
^/user/([^/]+)$
```

So `/user/55` matches → captured value `55` → mapped to `id = 55` → stored in Request object.

---

## 🧪 Extra Tests

| URL | Output |
|-----|--------|
| `/user/55` | `User ID: 55` |
| `/user/abc` | `User ID: abc` |
| `/user/` | `404` |

---

## 🧱 Architecture Status After Day 16

| Feature | Status |
|---------|--------|
| CLI runtime | ✔ |
| HTTP server | ✔ |
| Middleware | ✔ |
| Request parser | ✔ |
| Logger | ✔ |
| Config | ✔ |
| Native bridge | ✔ |
| Dynamic routing | ✔ |

> Ab PHP-X **real framework territory** me aa chuka hai.

---

## 📝 PROJECT_JOURNEY.md Entry

```markdown
## Day 16 — Dynamic Routing

- Added route parameter support
- Implemented regex route matching
- Added Request::param()
- Enables dynamic endpoints like /user/{id}
```

---

**✅ Day 16 complete — dynamic routing active**
