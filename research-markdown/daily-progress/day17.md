# Day 17 — Static File Serving (Real Web Capability)

> Aaj ke baad PHP-X CSS, JS, images aur HTML files serve kar sakega.  
> **Matlab: complete website hosting capable.**

---

## 🎯 Day-17 Goal

Agar browser request kare `/style.css` → server file bhej de `public/style.css`.

**Boundaries:**

- ❌ No lifecycle change
- ❌ No middleware change
- ❌ No router change

---

## 📂 Folder Structure (First Step)

Project root me `public/` folder banao aur test files create karo:

```
public/
├── style.css
├── test.txt
└── index.html
```

**Example content:**

`public/style.css`

```css
body { background: black; color: white; }
```

`public/test.txt`

```
Hello from static file
```

`public/index.html`

```html
<link rel="stylesheet" href="/style.css">
<h1>Hello PHP-X</h1>
```

---

## ✅ STEP 1 — Add Static File Serving in Server

📄 **File:** `src/Server.php`

Find this block:

```php
// Middleware handle karo aur response bhejo
$res = Middleware::handle($req, function (Request $req) {
    return Router::dispatch($req);
});
```

Paste the following code **just above** it:

```php
// Serve static files
$publicPath = dirname(__DIR__) . '/public' . $req->path();

if (is_file($publicPath)) {

    $ext = pathinfo($publicPath, PATHINFO_EXTENSION);

    $types = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'txt'  => 'text/plain',
        'html' => 'text/html',
    ];

    $type = $types[$ext] ?? 'application/octet-stream';

    $content = file_get_contents($publicPath);

    $response =
        "HTTP/1.1 200 OK\r\n" .
        "Content-Type: $type\r\n" .
        "Content-Length: " . strlen($content) . "\r\n\r\n" .
        $content;

    fwrite($client, $response);
    fclose($client);
    continue;
}
```

---

## ✅ STEP 2 — Run Server

```bash
php bin/phpx examples/server.xphp
```

---

## ✅ STEP 3 — Test Static Files

### Text File

**URL:** `http://127.0.0.1:8080/test.txt`

**Expected:**

```
Hello from static file
```

### HTML + CSS

**URL:** `http://127.0.0.1:8080/index.html`

**Expected:** Page with black background and white text heading "Hello PHP-X".

---

## 🧠 How It Works

```
Browser: GET /style.css
        ↓
Server: public/style.css exists?
        ↓
    YES → serve file directly
    NO  → pass to Router
```

---

## 🧠 Why Static Serve BEFORE Router?

```
✅ Correct Order          ❌ Wrong Order
Static files              Router
    ↓                         ↓
  Router                  Static files
```

**Reason:**

- CSS/JS requests should not hit the router
- Better performance
- Cleaner separation

---

## 🔒 Security Note

Abhi simple version hai. Future me add honge:

- Path traversal protection
- Cache headers
- Gzip compression
- Range requests

> Intentionally simple for now.

---

## 🧱 After Day 17, PHP-X Can:

| Capability | Status |
|------------|--------|
| Serve API routes | ✔ |
| Serve HTML | ✔ |
| Serve CSS / JS | ✔ |
| Serve images / assets | ✔ |

> **Complete web server capability unlocked.**

---

## 📝 PROJECT_JOURNEY.md Entry

```markdown
## Day 17 — Static File Serving

- Added public directory support
- Implemented static file detection
- Automatic content-type handling
- Router bypass for assets
```

---

**✅ Day 17 complete — static file serving active**
