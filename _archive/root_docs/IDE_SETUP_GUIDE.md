# VS Code IDE Setup Guide for APS Dream Home
## Complete Development Environment Configuration

---

## 🎯 Purpose
Bade project (210 controllers, 146 models, 492 views) mein efficiently kaam karne ke liye proper IDE setup zaroori hai. Isse:
- Fast code navigation
- Auto-complete support
- Better debugging
- Error detection

---

## 📦 Required Extensions

### 1️⃣ PHP Intelephense (Must Have)
**Extension:** `bmewburn.vscode-intelephense-client`

**Features:**
- PHP code auto-completion
- Go to definition (Ctrl+Click)
- Find all references
- Symbol search (Ctrl+Shift+O)
- Code folding
- Formatting

**Install:**
```
1. VS Code mein jaayein: Ctrl+Shift+X
2. Search: "PHP Intelephense"
3. Click "Install"
```

**Configuration (settings.json):**
```json
{
  "intelephense.files.include": [
    "**/*.php"
  ],
  "intelephense.files.exclude": [
    "**/vendor/**",
    "**/node_modules/**"
  ],
  "intelephense.stubs": [
    "apache",
    "bcmath",
    "Core",
    "curl",
    "date",
    "dom",
    "fileinfo",
    "filter",
    "gd",
    "hash",
    "iconv",
    "json",
    "libxml",
    "mbstring",
    "mysqli",
    "mysqlnd",
    "openssl",
    "pcre",
    "PDO",
    "pdo_mysql",
    "pdo_sqlite",
    "Phar",
    "posix",
    "Reflection",
    "session",
    "SimpleXML",
    "soap",
    "sockets",
    "SPL",
    "sqlite3",
    "standard",
    "tokenizer",
    "xml",
    "xmlreader",
    "xmlwriter",
    "zip",
    "zlib"
  ]
}
```

---

### 2️⃣ PHP Debug (Xdebug)
**Extension:** `felixfbecker.php-debug`

**Features:**
- Breakpoint debugging
- Variable inspection
- Step through code
- Watch expressions

**Install:**
```
1. VS Code: Ctrl+Shift+X
2. Search: "PHP Debug"
3. Install
```

**Configuration (launch.json):**
```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for XDebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html/apsdreamhome": "${workspaceFolder}"
      }
    }
  ]
}
```

---

### 3️⃣ Database Client (MySQL)
**Extension:** `cweijan.vscode-database-client2`

**Features:**
- SQL query execution
- Table browser
- Query builder
- Export/Import

**Install:**
```
1. VS Code: Ctrl+Shift+X
2. Search: "Database Client"
3. Install
4. Configure connection:
   - Host: 127.0.0.1
   - Port: 3307
   - User: root
   - Password: (blank)
   - Database: apsdreamhome
```

---

### 4️⃣ Path Intellisense
**Extension:** `christian-kohler.path-intellisense`

**Features:**
- Auto-complete file paths
- Navigate to views quickly
- Import file suggestions

---

### 5️⃣ Better Comments
**Extension:** `aaron-bond.better-comments`

**Features:**
- Colorful comments
- TODO highlighting
- FIXME alerts
- Important notes

---

### 6️⃣ GitLens (Agar GitKraken nahi use karte)
**Extension:** `eamodio.gitlens`

**Features:**
- Git blame annotations
- Code history
- Diff view

---

## 🔧 VS Code Settings Configuration

### settings.json (Workspace Settings)
```json
{
  "php.validate.enable": true,
  "php.validate.executablePath": "C:/xampp/php/php.exe",
  "php.validate.run": "onType",
  
  "files.associations": {
    "*.php": "php"
  },
  
  "files.exclude": {
    "**/vendor/**": true,
    "**/node_modules/**": true,
    "**/.git/**": true,
    "**/storage/cache/**": true,
    "**/storage/logs/**": true
  },
  
  "search.exclude": {
    "**/vendor/**": true,
    "**/node_modules/**": true,
    "**/*.min.js": true,
    "**/*.min.css": true
  },
  
  "editor.formatOnSave": true,
  "editor.tabSize": 4,
  "editor.insertSpaces": false,
  
  "emmet.includeLanguages": {
    "php": "html"
  },
  
  "intelephense.format.enable": true
}
```

### File Location:
`.vscode/settings.json`

---

## 🚀 Keyboard Shortcuts (Essential)

| Shortcut | Action | Use Case |
|----------|--------|----------|
| `Ctrl+P` | Quick Open | File search - type "UserController" |
| `Ctrl+Shift+O` | Symbol Search | Find methods/functions |
| `Ctrl+Click` | Go to Definition | Jump to class/method definition |
| `Alt+←` | Go Back | Return to previous location |
| `Ctrl+Shift+F` | Global Search | Search across all files |
| `Ctrl+Shift+H` | Replace All | Mass replace |
| `Ctrl+D` | Multi Cursor | Select next occurrence |
| `Ctrl+Shift+L` | Select All Occurrences | Edit all at once |
| `F12` | Go to Definition | Navigate to definition |
| `Shift+F12` | Find References | Find all usages |

---

## 🗂️ Recommended Workspace Settings

### File Explorer Organization
```
VS Code Explorer mein ye structure dikhaye:

apsdreamhome/
├── 📁 .vscode/           → Settings
├── 📁 .windsurf/         → AI rules
├── 📁 app/
│   ├── 📁 Core/          → Framework
│   ├── 📁 Http/
│   │   └── 📁 Controllers/
│   │       ├── 📁 Admin/
│   │       ├── 📁 Auth/
│   │       ├── 📁 Front/
│   │       └── ...
│   ├── 📁 Models/
│   ├── 📁 Services/
│   ├── 📁 Views/
│   └── ...
├── 📁 config/
├── 📁 database/
├── 📁 public/
├── 📁 routes/
├── 📁 testing/
└── 📄 PROJECT_MAP.md      ← Important!
```

---

## 🔍 Efficient Navigation Tips

### 1. Find Controller Fast
```
Ctrl+P → type: "UserController"
→ Select from list
```

### 2. Find View Fast
```
Ctrl+P → type: "user_dashboard.php"
→ Select from list
```

### 3. Find Method in File
```
Ctrl+Shift+O → type: "dashboard"
→ Jump to method
```

### 4. Search All Files
```
Ctrl+Shift+F → type: "class UserController"
→ Find all occurrences
```

### 5. Go to Definition
```
Ctrl+Click on "UserController"
→ Opens file at class definition
```

---

## 🐛 Debugging Setup

### XAMPP Configuration (php.ini)
```ini
[XDebug]
zend_extension = xdebug
xdebug.mode = debug
xdebug.start_with_request = yes
xdebug.client_port = 9003
xdebug.client_host = 127.0.0.1
xdebug.idekey = VSCODE
```

### Debug Workflow:
1. Set breakpoint (F9)
2. Press F5 (Start Debugging)
3. Open browser → trigger code
4. VS Code stops at breakpoint
5. Inspect variables, step through

---

## 📝 Code Snippets (Custom)

### Create `.vscode/php.code-snippets`:
```json
{
  "PHP Controller Method": {
    "prefix": "ctrlmethod",
    "body": [
      "public function ${1:methodName}()",
      "{",
      "    \$data = [",
      "        'page_title' => '${2:Page Title}',",
      "        'page_description' => '${3:Description}'",
      "    ];",
      "",
      "    \$this->render('${4:view_name}', \$data);",
      "}"
    ],
    "description": "Create a new controller method"
  },
  
  "PHP Model Method": {
    "prefix": "modelmethod",
    "body": [
      "public function ${1:getSomething}(\$${2:id})",
      "{",
      "    return \$this->find(\$${2:id});",
      "}"
    ],
    "description": "Create a new model method"
  }
}
```

---

## 🎯 Quick Reference: Project Navigation

### Common Patterns:

**Controller → View:**
```php
// Controller
$this->render('pages/user_dashboard', $data);

// View Location
app/views/pages/user_dashboard.php
```

**Controller → Model:**
```php
// In Controller
$model = new \App\Models\User();
$user = $model->find($id);

// Or via Service
$service = new \App\Services\User\UserService();
```

**Route → Controller:**
```php
// routes/web.php
$router->get('/user/dashboard', 'Front\UserController@dashboard');

// Controller
app/Http/Controllers/Front/UserController.php
```

---

## ⚡ Performance Tips

### 1. Exclude Large Folders from Search
```json
{
  "search.exclude": {
    "**/vendor/**": true,
    "**/node_modules/**": true,
    "**/storage/**": true,
    "**/.git/**": true
  }
}
```

### 2. Disable Unnecessary Extensions
- Sirf zaroori extensions enable karein
- Kaam ke baad disable karein

### 3. Use File Nesting
```json
{
  "explorer.fileNesting.enabled": true,
  "explorer.fileNesting.patterns": {
    "*.php": "${capture}.php, ${capture}Test.php"
  }
}
```

---

## ✅ Installation Checklist

- [ ] PHP Intelephense installed
- [ ] PHP Debug installed
- [ ] Database Client installed
- [ ] Path Intellisense installed
- [ ] Better Comments installed
- [ ] settings.json configured
- [ ] launch.json configured (for debugging)
- [ ] php.code-snippets created
- [ ] XDebug enabled in php.ini

---

## 🆘 Troubleshooting

### Issue: Auto-complete not working
**Solution:**
1. Check if Intelephense is active
2. Reload window: `Ctrl+Shift+P` → "Developer: Reload Window"
3. Check settings.json path

### Issue: Go to definition not working
**Solution:**
1. Ensure Intelephense is indexing
2. Check `intelephense.files.include`
3. Restart VS Code

### Issue: Database connection fails
**Solution:**
1. Check XAMPP MySQL running
2. Verify port 3307
3. Check credentials in Database Client

---

## 📚 Additional Resources

- **PROJECT_MAP.md** - Project architecture
- **AGENTS.md** - Rules & conventions
- **MCP_TOOLS_INSTALLATION_REPORT.md** - MCP tools

---

**Setup Complete hone ke baad, project mein navigation 10x fast ho jayega!** 🚀
