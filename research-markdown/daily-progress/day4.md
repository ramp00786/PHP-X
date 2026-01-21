# Day 4 — Built-in HTTP Server

### Objective
Implement a TCP-based HTTP server within PHP-X runtime.  
Enable browser-to-PHP communication without JavaScript using server-side form handling.

### Files Created / Modified
- `src/Server.php` — TCP HTTP server implementation (new)
- `bin/phpx` — Added Server class loading (modified)
- `examples/server.xphp` — HTTP server demonstration (new)

### Core Concept
Traditional PHP runs behind external web servers (Apache, Nginx). PHP-X introduces an embedded HTTP server that runs within the PHP-X process itself, enabling complete control over the HTTP request/response lifecycle. This architecture mirrors ASP.NET, Django, and Node.js Express, where the runtime owns the server rather than delegating to external processes.

### Work Done

#### 1. HTTP Server Implementation (src/Server.php)
Created a TCP-based HTTP server using PHP streams:
```php
<?php

class Server
{
    public static function start(int $port = 8080)
    {
        // Create TCP server socket
        $server = stream_socket_server("tcp://127.0.0.1:$port", $errno, $errstr);

        if (!$server) {
            echo "Server error: $errstr ($errno)\n";
            exit(1);
        }

        echo "[Server] Running at http://127.0.0.1:$port\n";

        while (true) {
            // Accept client connections
            $client = stream_socket_accept($server);

            if (!$client) continue;

            $request = fread($client, 1024);

            // Handle button click (POST request)
            if (str_contains($request, "POST /click")) {
                echo "👉 Button clicked from browser\n";
                $responseBody = "<h1>Button clicked ✔</h1>";
            } else {
                // Serve default page with form
                $responseBody = ''
                    <h1>PHP-X Day 4</h1>
                    <form method="POST" action="/click">
                        <button type="submit">Click Me</button>
                    </form>
                '';
            }

            $response =
                "HTTP/1.1 200 OK\r\n" .
                "Content-Type: text/html\r\n" .
                "Content-Length: " . strlen($responseBody) . "\r\n\r\n" .
                $responseBody;

            fwrite($client, $response);
            fclose($client);
        }
    }
}
```

**Key components:**
- `stream_socket_server()`: Creates TCP socket bound to localhost:8080
- `stream_socket_accept()`: Blocks until client connection arrives
- `fread()`: Reads HTTP request from client
- String matching: Routes requests based on method and path
- Manual HTTP response: Constructs valid HTTP/1.1 response with headers and body
- `while(true)`: Infinite loop keeps server alive (leverages Day 2 event loop concept)

**How it works:**
1. Server binds to TCP port 8080
2. Infinite loop accepts incoming connections
3. Reads raw HTTP request data
4. Parses request to detect POST /click (form submission)
5. Generates appropriate HTML response
6. Sends HTTP response with proper headers
7. Closes connection and waits for next request

#### 2. Runtime Integration (bin/phpx)
Added Server class to runtime loader:
```php
#!/usr/bin/env php
<?php

require_once __DIR__ . ''/../src/Core.php'';
require_once __DIR__ . ''/../src/DOM.php'';
require_once __DIR__ . ''/../src/Server.php'';

// ... rest of launcher code
```

**Loading order significance:**
- Core (event loop foundation)
- DOM (UI abstraction)
- Server (HTTP handling)

#### 3. Server Example (examples/server.xphp)
Created minimal server application:
```php
<?php

Server::start(8080);
```

**Simplicity by design:**
- Single line starts complete HTTP server
- No configuration files required
- Runtime handles all server lifecycle

#### 4. Execution Verification
Successfully ran embedded HTTP server:

**Start server:**
```bash
./bin/phpx examples/server.xphp
```

**Expected console output:**
```
[Server] Running at http://127.0.0.1:8080
```

**Browser interaction:**
1. Navigate to `http://127.0.0.1:8080`
2. Page displays "PHP-X Day 4" and a button
3. Click button
4. Browser shows "Button clicked ✔"
5. Console prints "👉 Button clicked from browser"

**Result:**
Real browser interaction handled entirely by PHP-X without JavaScript.

### Why This Was Done
- **Platform control**: PHP-X owns the server, not external processes
- **HTTP foundation**: Essential for web applications, APIs, and services
- **ASP.NET model**: Follows proven architecture of runtime-embedded servers
- **JavaScript elimination**: Server-side forms provide interactivity without client scripting
- **Request/response lifecycle**: Foundation for routing, middleware, and session management
- **Long-running capability**: Demonstrates Day 2''s event loop in practical application

### Problems Solved
1. **External dependency**: No need for Apache, Nginx, or `php -S`
2. **Deployment complexity**: Single executable runs complete application
3. **Control limitations**: Traditional PHP can''t intercept request lifecycle
4. **JavaScript requirement**: HTML forms provide interactivity server-side
5. **Process management**: Runtime manages server lifecycle automatically

### Technical Decisions

#### TCP Socket Implementation
Used `stream_socket_server()` for low-level control:
- **Direct access**: Full control over socket options and behavior
- **Blocking I/O**: Simplifies implementation (async can be added later)
- **Standard streams**: PHP''s built-in networking, no extensions required
- **Raw HTTP**: Parse and generate HTTP manually for educational clarity

#### Request Parsing Strategy
Implemented simple string matching instead of full HTTP parser:
- **MVP approach**: Sufficient for Day 4 demonstration
- **Readable**: Easy to understand what''s happening
- **Extensible**: Can be replaced with proper parser later
- **Educational**: Shows HTTP is just text over TCP

#### Form-Based Interaction
Chose HTML forms over AJAX/WebSocket:
- **Zero JavaScript**: Pure server-side processing
- **Proven model**: ASP.NET WebForms, Django, classic PHP all started here
- **Simpler debugging**: Full page reload makes state changes visible
- **Progressive enhancement**: JavaScript can be added later as optional enhancement

#### Synchronous Architecture
Used blocking I/O instead of async:
- **Clear execution flow**: Easy to trace and debug
- **Sufficient for now**: Handles single connection at a time adequately
- **Foundation**: Can evolve to async without breaking API
- **Educational value**: Shows HTTP fundamentals clearly

### Alternatives Considered

#### Option 1: Use PHP built-in server (php -S)
**Rejected because:**
- External process, not runtime-controlled
- Can''t customize request handling at low level
- Doesn''t demonstrate platform capability
- Limited to development environments
- No programmatic control over lifecycle

#### Option 2: Require Apache/Nginx
**Rejected because:**
- External dependency contradicts self-contained runtime goal
- Complex deployment and configuration
- Can''t control server behavior from PHP
- Traditional PHP model, not advancing the platform
- Requires system-level installation

#### Option 3: Use ReactPHP HTTP server
**Rejected because:**
- External dependency
- Hides implementation details
- Async complexity premature at this stage
- Can be integrated later if needed
- Educational value lost

#### Option 4: WebSocket server
**Rejected because:**
- More complex protocol than HTTP
- Requires JavaScript on client side
- Doesn''t demonstrate form-based interaction
- Can be built after HTTP foundation is solid
- Day 4 too early for bidirectional streaming

#### Option 5: FastCGI implementation
**Rejected because:**
- Requires external web server anyway
- Complex binary protocol
- Doesn''t solve external dependency issue
- Over-engineered for current stage
- Obscures HTTP fundamentals

### Reason for Final Choice
The TCP socket HTTP server provides:
- **Complete control**: Full ownership of server lifecycle and behavior
- **Zero dependencies**: Pure PHP, works anywhere with PHP CLI
- **Educational clarity**: Shows exactly how HTTP works at TCP level
- **Foundation for everything**: Routing, middleware, sessions, APIs all build on this
- **Platform credibility**: Proves PHP-X is a runtime, not just a script executor
- **Familiar pattern**: Matches Node.js, ASP.NET, Django server-in-runtime model

### Key Insights
1. **HTTP is just text**: Manual parsing shows protocol simplicity
2. **Server ownership matters**: Runtime-controlled server enables platform features
3. **Forms are powerful**: JavaScript-free interaction is viable for many use cases
4. **Blocking I/O is fine**: Async isn''t always necessary, especially for learning
5. **TCP is accessible**: PHP streams make low-level networking approachable

### What This Enables
With Day 4 complete, PHP-X can now:
- Serve web pages from embedded server
- Handle HTTP requests within PHP runtime
- Process form submissions without JavaScript
- Build toward routing system (Day 5+)
- Prepare for REST APIs and WebSocket servers
- Create self-contained web applications
- Deploy without external web server dependencies

**This is the foundation for server-side web applications.**

### Current Implementation Status
**What works now:**
- TCP socket server on localhost:8080
- HTTP request acceptance
- Basic request routing (string matching)
- HTML form handling (POST requests)
- Manual HTTP response generation

**What''s missing:**
- Proper HTTP parser
- Multiple request methods (GET, POST, PUT, DELETE)
- Request headers parsing
- URL query parameters
- Request body parsing
- Static file serving
- Concurrent connection handling

**What''s next (Day 5+):**
- Router system for clean URL mapping
- Request/Response abstractions
- Middleware pipeline
- Session management
- Static file serving
- Template engine integration

### Future Enhancements Possible
- **Async I/O**: Non-blocking sockets for concurrent connections
- **HTTP/2**: Modern protocol support
- **WebSocket**: Bidirectional real-time communication
- **Static files**: Serve CSS, JS, images automatically
- **Compression**: GZIP response encoding
- **HTTPS**: TLS encryption support
- **Chunked encoding**: Streaming large responses
- **Keep-alive**: Connection reuse
- **Proper parsing**: Full HTTP/1.1 compliance
- **Error handling**: HTTP error codes (404, 500, etc.)

### Comparison to Other Runtimes
- **Node.js Express**: Similar concept, but Node.js uses libuv (C++) for async I/O; PHP-X uses simpler blocking model
- **Python Flask/Django**: Django has embedded server, Flask uses Werkzeug; PHP-X builds equivalent from scratch
- **ASP.NET Kestrel**: High-performance async server; PHP-X starts simpler but can evolve similarly
- **Ruby Rails/Sinatra**: WEBrick/Puma servers; PHP-X follows same runtime-embedded pattern

**PHP-X''s advantage:**
Pure PHP implementation means:
- No C extensions required
- Fully transparent implementation
- Easy to modify and extend
- Cross-platform by default
- Educational value maximized

### Architectural Significance
Day 4 marks PHP-X''s transformation from **CLI runtime** to **web platform**. The embedded HTTP server establishes PHP-X as a complete application platform capable of:
- Handling network I/O
- Managing long-running processes
- Processing user interactions
- Serving web content

This is the architectural foundation for:
- Web frameworks
- REST APIs
- Real-time applications
- Desktop apps with web UI
- Mobile backend services

**The server is not just a feature—it''s proof that PHP-X is a platform, not a script runner.**

### Problems Encountered & Solutions

#### Issue 1: Connection not closing
**Problem**: Browser hangs waiting for connection to close.
**Solution**: Added explicit `fclose($client)` after response sent.

#### Issue 2: Favicon.ico requests
**Problem**: Browsers automatically request `/favicon.ico`, causing unexpected behavior.
**Solution**: Handle with 204 No Content response (can be added in future iterations).

#### Issue 3: HTTP response format
**Problem**: Incorrect line endings caused browser parsing errors.
**Solution**: Used `\r\n` (CRLF) as per HTTP specification, not just `\n`.

#### Issue 4: Content-Length mismatch
**Problem**: Browser rendered incomplete or corrupted HTML.
**Solution**: Calculate exact byte length with `strlen()` for Content-Length header.

### Deployment Considerations
**Current limitations:**
- Single-threaded (one request at a time)
- No graceful shutdown
- Port hardcoded (can be parameterized)
- Localhost only (can bind to 0.0.0.0)

**Production readiness:**
This implementation is intentionally educational, not production-ready. For production:
- Add async I/O (ReactPHP, Amp)
- Implement proper HTTP parser
- Add security measures (input validation, rate limiting)
- Use process manager (systemd, supervisor)
- Consider nginx reverse proxy for static assets

**But the architecture is sound:** Runtime-embedded server is the correct foundation.