# Day 1 — Project Bootstrap

### Objective
Initialize the PHP-X project with a minimal working runtime.
Build a running system rather than planning a perfect system.

### Files Created
- `README.md` — Project documentation and intent
- `bin/phpx` — Custom CLI launcher
- `examples/hello.xphp` — First example application

### Directory Structure Established
```
php-x/
│
├── README.md
├── bin/
│   └── phpx
└── examples/
    └── hello.xphp
```

### Work Done

#### 1. Project Initialization
- Created root project directory `php-x/`
- Initialized Git repository
- Defined project name and purpose in README

#### 2. Custom CLI Runtime (bin/phpx)
Implemented a minimal PHP CLI launcher:
```php
#!/usr/bin/env php
<?php

if ($argc < 2) {
    echo "Usage: phpx <file.xphp>\n";
    exit(1);
}

$file = $argv[1];

if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(1);
}

require $file;
```

Key features:
- Shebang for direct execution on Unix systems
- Command-line argument validation
- File existence checking
- Direct file execution via `require`

#### 3. File Extension Convention
Introduced `.xphp` as the standard extension for PHP-X applications:
- Distinguishes runtime-specific code from traditional PHP
- Signals that code runs in PHP-X context, not Apache/Nginx

#### 4. First Working Example (examples/hello.xphp)
Created a simple proof-of-concept application:
```php
<?php

echo "Hello from PHP-X!\n";

sleep(1);

echo "PHP-X is running 🚀\n";
```

#### 5. Execution Verification
Successfully ran the first PHP-X application:

**Linux/Mac:**
```bash
chmod +x bin/phpx
./bin/phpx examples/hello.xphp
```

**Windows:**
```bash
php bin/phpx examples/hello.xphp
```

**Expected Output:**
```
Hello from PHP-X!
PHP-X is running 🚀
```

#### 6. Version Control Setup
Initialized Git repository with first commit:
```bash
git init
git add .
git commit -m "init: start PHP-X runtime"
```

### Why This Was Done
- **Minimal viable start**: Focus on a working system rather than perfect architecture
- **Clear direction**: Establish what PHP-X is (a runtime) rather than what it might become
- **Executable proof**: Create something that runs immediately, not just documentation
- **Foundation for iteration**: Build incrementally from a solid base

### Problems Solved
1. **Execution model clarity**: Traditional PHP exits after script completion. PHP-X needs lifecycle control.
2. **Platform independence**: CLI approach works across all operating systems.
3. **Development workflow**: Developers can immediately run and test code.

### Alternatives Considered

#### Option 1: Use Composer as the launcher
**Rejected because:**
- Adds unnecessary dependency
- Obscures the runtime concept
- Less control over execution lifecycle

#### Option 2: Start with web server
**Rejected because:**
- Premature complexity
- Can't verify basic execution model first
- Harder to debug initial issues

#### Option 3: Compile to binary immediately
**Rejected because:**
- Over-engineering for Day 1
- Blocks rapid iteration
- Adds build complexity too early

### Reason for Final Choice
The chosen approach provides:
- **Immediate feedback loop**: Write code, run code, see results
- **Zero external dependencies**: Pure PHP, no installation requirements
- **Clear mental model**: Simple file execution, easy to understand
- **Room to grow**: Can evolve into complex runtime without rewriting foundation

### Key Insights
1. **Running code beats perfect planning**: A working prototype validates assumptions faster than architecture documents.
2. **Custom launcher is essential**: Runtime control starts with controlling process initialization.
3. **File conventions matter**: `.xphp` extension communicates intent and separates concerns.

### What This Enables
With Day 1 complete, PHP-X can now:
- Execute PHP code outside traditional web server context
- Control when and how PHP scripts are invoked
- Build toward long-running processes
- Expand into event loops, servers, and GUI applications

This foundation makes all future development possible.
