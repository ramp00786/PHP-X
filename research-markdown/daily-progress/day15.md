# Day 15 — Request Body Parsing (JSON + Form Data)

> PHP-X ko real application framework capability dena hai.  
> Ab tak sirf `GET /` path handle ho raha tha — aaj se PHP-X POST data samajhne lagega.

---

## 🎯 Day-15 Goals

- Request body parser
- JSON support
- `application/x-www-form-urlencoded` support
- Clean API: `$req->input('key')`
- No lifecycle changes

**Boundaries:**

- ❌ No native change
- ❌ No middleware change
- ❌ No router change

---

## 🧠 Why This Step Is Important?

Without body parsing:

- Forms useless
- APIs useless
- Mobile/desktop app backend impossible

**This makes PHP-X usable.**

---

## ✅ STEP 1 — Extend Request Class

📄 **File:** `src/Request.php`

Add properties:

```php
private array $data = [];
private array $headers = [];
```

---

## ✅ STEP 2 — Parse Headers

Inside constructor, after `$lines = explode("\n", $rawRequest);`, add:

```php
foreach ($lines as $line) {
    if (strpos($line, ':') !== false) {
        [$k, $v] = explode(':', $line, 2);
        $this->headers[strtolower(trim($k))] = trim($v);
    }
}
```

---

## ✅ STEP 3 — Extract Body Properly

**Replace:**

```php
$this->body = trim(end($lines));
```

**With:**

```php
$parts = explode("\r\n\r\n", $rawRequest, 2);
$this->body = $parts[1] ?? '';
```

---

## ✅ STEP 4 — Body Parsing Logic

Add method:

```php
private function parseBody(): void
{
    $type = $this->headers['content-type'] ?? '';

    if (str_contains($type, 'application/json')) {
        $this->data = json_decode($this->body, true) ?? [];
    } elseif (str_contains($type, 'application/x-www-form-urlencoded')) {
        parse_str($this->body, $this->data);
    }
}
```

Call this at end of constructor:

```php
$this->parseBody();
```

---

## ✅ STEP 5 — Public Input API

Add methods:

```php
public function input(string $key, $default = null)
{
    return $this->data[$key] ?? $default;
}

public function all(): array
{
    return $this->data;
}
```

---

## 🧪 Test Cases

### Form POST

**HTML:**

```html
<form method="POST" action="/submit">
  <input name="name">
  <button>Send</button>
</form>
```

**Route:**

```php
Router::post('/submit', function (Request $req) {
    return Response::text("Hello " . $req->input('name'));
});
```

### JSON POST (API)

**Request:**

```
POST /api
Content-Type: application/json

{"email":"test@test.com"}
```

**Route:**

```php
Router::post('/api', function (Request $req) {
    return Response::json($req->all());
});
```

---

## 🧠 What Day-15 Unlocked

- ✔ API backend capability
- ✔ Form handling
- ✔ Mobile app data exchange
- ✔ Desktop app data exchange
- ✔ Platform usability jump

---

## 🔒 Architecture Status

- Lifecycle ✔
- Middleware contract ✔
- Event loop ✔
- Native boundary ✔

---

## 📝 PROJECT_JOURNEY.md Entry

```markdown
## Day 15 — Request Body Parsing

- Added JSON and form-urlencoded parsing
- Introduced Request::input() and Request::all()
- Enables API and form handling
```

---

**✅ Day 15 complete**
