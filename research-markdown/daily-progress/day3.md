# Day 3 — DOM Abstraction Layer

### Objective
Enable PHP to control UI elements without JavaScript.  
Establish a DOM manipulation API that works with HTML and prepares for future WebView integration.

### Files Created / Modified
- `src/DOM.php` — DOM abstraction layer (new)
- `bin/phpx` — Added DOM class loading (modified)
- `examples/index.html` — Sample HTML template (new)
- `examples/hello.xphp` — DOM manipulation demonstration (modified)

### Core Concept
Traditional web applications use JavaScript to manipulate the DOM. PHP-X introduces a DOM abstraction layer that allows PHP code to control UI elements directly using CSS selectors, eliminating the need for client-side JavaScript and establishing a unified API that will work across desktop (WebView), mobile, and web platforms.

### Work Done

#### 1. DOM Abstraction Implementation (src/DOM.php)
Created a UI control layer with HTML loading and element manipulation:
```php
<?php

class DOM
{
    private static string $html = '';

    /**
     * Load HTML file into memory
     */
    public static function load(string $file)
    {
        if (!file_exists($file)) {
            echo "[DOM] HTML file not found: $file\n";
            return;
        }

        self::$html = file_get_contents($file);
        echo "[DOM] Loaded $file\n";
    }

    /**
     * Set text content of an element (simulation)
     */
    public static function setText(string $selector, string $text)
    {
        // Currently simulating DOM operations
        // Real DOM parsing will be implemented in Day 4
        echo "[DOM] Set text of $selector => $text\n";
    }
}
```

**Key components:**
- `$html` static property: Stores loaded HTML in memory
- `load()`: Reads HTML file from disk into runtime memory
- `setText()`: Simulates element text modification using CSS selectors
- Logging output: Provides visibility into DOM operations during development

**Design philosophy:**
- Static methods: DOM represents a global UI context (single UI tree per application)
- Selector-based API: Familiar pattern from jQuery, React, and modern frameworks
- Simulation first: Establish API contract before implementing complex parsing

#### 2. Runtime Integration (bin/phpx)
Added DOM class to the runtime loader:
```php
#!/usr/bin/env php
<?php

require_once __DIR__ . '/../src/Core.php';
require_once __DIR__ . '/../src/DOM.php';

// ... rest of launcher code
```

**Why manual require:**
- No composer dependency yet
- Explicit loading order visible to developers
- Simple, predictable initialization

#### 3. HTML Template (examples/index.html)
Created a minimal HTML structure for UI demonstrations:
```html
<!DOCTYPE html>
<html>
<head>
    <title>PHP-X App</title>
</head>
<body>
    <h1 id="title">Loading...</h1>
</body>
</html>
```

**Design choices:**
- Standard HTML5 structure
- No JavaScript dependencies
- CSS selector-friendly elements (id attributes)
- WebView-compatible markup

#### 4. Updated Example (examples/hello.xphp)
Demonstrated PHP-controlled UI updates:
```php
<?php

DOM::load(__DIR__ . "/index.html");

Core::setInterval(function () {
    DOM::setText("#title", "Hello from PHP-X at " . date("H:i:s"));
}, 2000);
```

**What this demonstrates:**
- Loading HTML templates from PHP
- Updating UI elements from PHP code
- Combining event loop (Day 2) with UI control (Day 3)
- Time-based UI updates without JavaScript

#### 5. Execution Verification
Successfully ran PHP-controlled UI updates:

**Linux/Mac:**
```bash
./bin/phpx examples/hello.xphp
```

**Windows:**
```bash
php bin/phpx examples/hello.xphp
```

**Expected Output (repeating every 2 seconds):**
```
[DOM] Loaded examples/index.html
[DOM] Set text of #title => Hello from PHP-X at 12:30:01
[DOM] Set text of #title => Hello from PHP-X at 12:30:03
[DOM] Set text of #title => Hello from PHP-X at 12:30:05
...
```

**Stop execution:** Ctrl+C

### Why This Was Done
- **JavaScript elimination**: Enable full-stack PHP development without client-side scripting
- **Unified API**: Same DOM API will work across web, desktop, and mobile platforms
- **WebView preparation**: Establish API contract before implementing native rendering
- **Developer experience**: Familiar selector-based syntax reduces learning curve
- **Architecture validation**: Prove that PHP can control UI declaratively

### Problems Solved
1. **Language barrier**: Developers no longer need JavaScript knowledge for UI manipulation
2. **API consistency**: Single API across all platforms (unlike PHP + JavaScript hybrid apps)
3. **Complexity reduction**: Eliminate client-server communication for UI updates
4. **Mental model**: PHP as a complete application language, not just server-side

### Technical Decisions

#### Static Class Design
Chose static methods over instance-based design because:
- DOM represents a global singleton (one UI tree per application)
- No need for multiple DOM instances
- Simpler API: `DOM::setText()` vs `$dom->setText()`
- Matches global nature of UI in desktop applications

#### CSS Selector API
Selected CSS selectors (`#id`, `.class`, `tag`) as the element targeting mechanism:
- **Familiarity**: Developers already know CSS selectors from web development
- **Flexibility**: Can target elements by ID, class, attribute, or hierarchy
- **Future-proof**: Easy to implement with DOMDocument, JavaScript, or native code
- **Standard**: CSS selectors are a W3C standard, not a proprietary syntax

#### Simulation Approach
Implemented logging simulation before real DOM parsing:
- **Rapid prototyping**: Validate API design without complex implementation
- **API-first thinking**: Design the interface developers will use, then implement it
- **Incremental development**: Each day builds on previous foundation
- **Debugging clarity**: Log output makes operations visible during development

#### HTML File Loading
Used `file_get_contents()` for HTML loading:
- **Simplicity**: Single function call, minimal code
- **Sufficient**: Adequate for current stage (no streaming needed)
- **Memory-efficient**: Modern PHP handles string memory well
- **Easy to upgrade**: Can add caching, watching, or streaming later

### Alternatives Considered

#### Option 1: Direct PHP template rendering (echo, include)
**Rejected because:**
- No separation between PHP logic and HTML structure
- Difficult to manipulate rendered HTML programmatically
- Can't update UI in real-time (after initial render)
- Doesn't prepare for WebView where HTML is rendered externally
- Traditional approach, doesn't advance the platform

#### Option 2: JavaScript + WebSocket bridge
**Rejected because:**
- Requires JavaScript knowledge
- Complex bidirectional communication protocol
- Latency between PHP command and UI update
- Two languages to maintain and debug
- Contradicts goal of PHP-only development

#### Option 3: Browser automation (Playwright, Puppeteer)
**Rejected because:**
- Heavy external dependency (requires Node.js)
- High memory overhead (full browser instance)
- Slow initialization and execution
- Complex installation and setup
- Over-engineered for basic UI control

#### Option 4: XML/HTML parsing with DOMDocument
**Initially rejected because:**
- Premature complexity for Day 3
- API design should come before implementation
- Parsing details distract from architecture
- Can be added incrementally in future days
- Note: This will be implemented in future iterations

#### Option 5: Template engine (Twig, Blade, Smarty)
**Rejected because:**
- External dependency
- Focused on generating HTML, not manipulating existing DOM
- Doesn't address real-time UI updates
- Adds unnecessary abstraction layer
- Can be integrated later if template features are needed

### Reason for Final Choice
The DOM abstraction layer provides:
- **Clear API contract**: Developers know what to expect before implementation is complete
- **Platform independence**: Same API will work with WebView, GTK, Qt, or mobile
- **Incremental complexity**: Start simple (logging), evolve to real implementation
- **Familiar patterns**: CSS selectors and method names match industry standards
- **Future compatibility**: Easy to swap simulation with real DOM parser, WebView bridge, or native UI toolkit

### Key Insights
1. **API before implementation**: Designing the interface first ensures usability drives development
2. **Simulation validates design**: Logging shows that the API makes sense before investing in complex parsing
3. **Selectors are universal**: CSS selectors work everywhere (web, desktop, mobile)
4. **Static context for global state**: UI is inherently global in single-window applications
5. **Separation of concerns**: HTML defines structure, PHP controls behavior and state

### What This Enables
With Day 3 complete, PHP-X can now:
- Load HTML templates from PHP
- Define UI structure separately from logic
- Control UI elements using CSS selectors
- Update UI in real-time from PHP event loops
- Build toward WebView integration (Day 4)
- Prepare for desktop application development
- Establish patterns for mobile UI control

**This is the foundation for GUI applications, not web applications.**

### Current Implementation Status
**What works now:**
- HTML file loading
- DOM operation logging
- API pattern established
- Integration with event loop

**What's simulated:**
- Actual DOM parsing
- Real element selection
- HTML modification

**What's next (Day 4):**
- Implement real DOM parsing with DOMDocument
- Add more DOM methods (getAttribute, addClass, etc.)
- Integrate WebView for actual rendering
- Handle bidirectional communication (UI events → PHP)

### Future Enhancements Possible
- **More DOM methods**: `getAttribute()`, `setAttribute()`, `addClass()`, `removeClass()`
- **Element creation**: `createElement()`, `appendChild()`
- **Event handling**: `on('click', ...)`, `addEventListener()`
- **Real-time updates**: WebSocket bridge to live WebView
- **CSS manipulation**: `setStyle()`, `getStyle()`
- **HTML parsing**: DOMDocument integration for actual element selection
- **XPath support**: Alternative to CSS selectors for complex queries
- **Virtual DOM**: Diff-based updates like React

### Comparison to Other Runtimes
- **Electron**: Uses JavaScript + Node.js for UI control, PHP-X uses pure PHP
- **Qt/GTK**: Native UI widgets, PHP-X uses HTML for cross-platform consistency
- **React Native**: JavaScript bridge to native, PHP-X uses same concept but with PHP
- **Flutter**: Dart + custom renderer, PHP-X leverages existing web standards (HTML/CSS)

**PHP-X's unique position:**
Combines web standards (HTML/CSS) with backend language (PHP) for full-stack control without JavaScript, while preparing for native performance through WebView integration.

This approach offers:
- Lower learning curve (no JavaScript required)
- Code reuse (same PHP for server and client)
- Web technology familiarity (HTML/CSS)
- Native performance potential (via WebView)

### Architectural Significance
Day 3 marks the transition from **runtime infrastructure** (Days 1-2) to **application platform** (Days 3+). The event loop now has something to control—a UI layer. This is the moment PHP-X becomes capable of building desktop applications, not just servers or CLI tools.

The DOM abstraction is not just a convenience layer; it's the **platform API** that defines how PHP-X applications interact with user interfaces across all target platforms (web, desktop, mobile).
