# Fast File Read Tools

## Quick Read Commands

### Read PHP Syntax Check
```bash
php -l file.php
```

### Read First N Lines
```bash
head -n 50 file.php
```

### Read Last N Lines
```bash
tail -n 20 file.php
```

### Search Pattern
```bash
grep -n "pattern" file.php
```

### Find Files
```bash
find . -name "*.php" -type f
```

---

## Fast Context Tools

### 1. Quick Grep
```bash
grep -r "function name" app/
grep -n "class MyClass" app/
```

### 2. Line Range Read
```bash
sed -n '100,150p' file.php
```

### 3. File Summary
```bash
wc -l file.php  # line count
head -1 file.php  # first line
tail -1 file.php  # last line
```

### 4. Structure Analysis
```bash
grep -E "^class |^function |^namespace " file.php
```

---

## MCP Tools Fast Usage

### Filesystem MCP
```
grep: pattern="function", path="app/Controllers", include="*.php"
read: filePath="file.php", offset=1, limit=50
glob: pattern="**/*.php", path="app"
```

### MySQL MCP
```
query: SHOW TABLES
query: DESCRIBE table_name
query: SELECT COUNT(*) FROM table
```

### Git MCP
```
log: --oneline -10
diff: HEAD~5 HEAD
status: --short
```

---

## Speed Reading Patterns

### 1. Controller Pattern
```php
// Search for class definition
grep -n "^class " app/Http/Controllers/*.php

// Read specific method
sed -n '/public function index/,/^}/p' controller.php
```

### 2. Route Pattern
```php
// Find route
grep -n "/route" routes/web.php
```

### 3. View Pattern
```php
// Find view file
ls app/Views/pages/
```

---

## Context Compression

### Use Abbreviations
- `CI` = Controller Index
- `M` = Model
- `V` = View
- `R` = Route

### Skip Defaults
- Don't explain standard PHP syntax
- Don't describe Bootstrap classes
- Skip obvious comments

### Focus on Changes
- Only read changed sections
- Use diff for modifications
- Compare with production branch
