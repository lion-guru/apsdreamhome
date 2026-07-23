# APS Dream Home - Development Rules

## Token Bachao Rules (Priority: HIGH)

### Jab User Kahe:
- "token bacho"
- "token bachao"  
- "free tool use karo"
- "pc tool use karo"
- "local tool use karo"

### To Ye Tools Use Karo (PC Level - Har Project Mein):

| Task | Local Tool | Command |
|------|------------|---------|
| **PHP Syntax Check** | `php` | `php -l file.php` |
| **PHP Code Analysis** | PHPStan | `phpstan analyse app` |
| **PHP Formatter** | PHP-CS-Fixer | `php-cs-fixer fix app` |
| **JS/React Linting** | ESLint | `eslint file.js` |
| **JS/React Formatter** | Prettier | `prettier file.js` |
| **File Search** | ripgrep (rg) | `rg "pattern" app/` |
| **File Find** | fd | `fd ".php" app/` |
| **File View (color)** | bat | `bat file.php` |
| **Directory Tree** | tree | `tree app/` |
| **JSON Processing** | jq | `cat file.json \| jq '.key'` |
| **API Testing** | httpie | `http GET localhost/api` |
| **MySQL with autocomplete** | mycli | `mycli apsdreamhome` |
| **Man Pages (simple)** | tldr | `tldr command` |
| **Shell Script Lint** | ShellCheck | `shellcheck script.sh` |
| **Database** | Adminer | Open `adminer.php` in browser |
| **Git Operations** | git | `git status`, `git log` |
| **GitHub Operations** | gh | `gh repo status` |
| **File Read** | read tool | Already local tool |
| **Server Test** | webfetch | Already local tool |

---

## Installed PC Level Tools

### PATH Locations
```
C:\xampp\php                           # PHP
C:\xampp                               # Composer
C:\Users\abhay\AppData\Roaming\Composer\vendor\bin  # PHP tools
C:\Users\abhay\AppData\Local\Python\pythoncore-3.14-64\Scripts  # Python tools
C:\Program Files\GitHub CLI           # GitHub CLI
```

### Development Tools
| Tool | Version | Command | Purpose |
|------|---------|---------|---------|
| ripgrep | 15.1.0 | `rg` | Super fast grep/search |
| fd | 10.4.2 | `fd` | Fast file finder |
| bat | 0.26.1 | `bat` | Syntax highlighted cat |
| tree | - | `tree` | Directory structure |
| jq | 1.8.1 | `jq` | JSON processing |
| httpie | 3.2.4 | `http` | API testing (better curl) |
| http-prompt | 2.1.0 | `http-prompt` | Interactive API client |
| shellcheck | 0.11.0 | `shellcheck` | Shell script linting |
| tldr | 3.4.4 | `tldr` | Simplified man pages |

### PHP Tools
| Tool | Version | Command | Purpose |
|------|---------|---------|---------|
| PHP | 8.2.12 | `php` | PHP runtime |
| Composer | 2.9.5 | `composer` | PHP package manager |
| PHPStan | 2.1.46 | `phpstan` | PHP static analysis |
| PHP-CS-Fixer | 3.94.2 | `php-cs-fixer` | PHP code formatter |

### JavaScript Tools
| Tool | Version | Command | Purpose |
|------|---------|---------|---------|
| Node.js | 24.14.1 | `node` | JS runtime |
| npm | 11.11.0 | `npm` | JS package manager |
| Prettier | 3.8.1 | `prettier` | Code formatter |
| ESLint | 10.1.0 | `eslint` | JS/React linting |

### Database Tools
| Tool | Version | Command | Purpose |
|------|---------|---------|---------|
| mycli | 1.67.1 | `mycli` | MySQL with autocomplete |
| Adminer | 4.8.1 | Browser | Database manager |

### Git Tools
| Tool | Version | Command | Purpose |
|------|---------|---------|---------|
| Git | - | `git` | Version control |
| GitHub CLI | - | `gh` | GitHub operations |

### MCP Servers (Project Level)
| Server | Purpose |
|--------|---------|
| filesystem | File system access |
| memory | Memory/context |
| sequential-thinking | Step-by-step reasoning |
| fetch | API/web calls |
| sqlite | Local queries |

---

## AI Sirf Tab Use Karo Jab:
- Complex logic likhna ho
- New feature design karna ho
- Bug fix ka logic sochna ho
- Architecture decision lena ho
- Code explanation chahiye ho

### Never Use AI For:
- Syntax checking (PHPStan/ESLint available hai)
- Finding files (rg/fd available hai)
- Reading files (read tool available hai)
- Git status/log (git command available hai)
- Database queries (MySQL/Adminer/mycli available hai)
- JSON processing (jq available hai)
- API testing (httpie available hai)
- Command help (tldr available hai)

---

## Quick Reference

```bash
# PHP Tools (Token FREE)
php -l file.php                        # Syntax check
phpstan analyse app                     # Static analysis
php-cs-fixer fix app --dry-run        # Format check

# JS Tools (Token FREE)
eslint file.js                         # Lint
prettier --check app/                  # Format check

# File Search (Token FREE)
rg "function" app/                     # Search
fd ".php" app/                         # Find files
bat file.php                           # View with colors
tree app/                             # Directory tree

# JSON/API (Token FREE)
cat data.json | jq '.key'              # JSON parse
http GET localhost/api/users           # API test
tldr http                             # Quick help

# Database (Token FREE)
mycli apsdreamhome                     # MySQL CLI
# Open: http://localhost/apsdreamhome/adminer.php

# Git (Token FREE)
git status                            # Status
git log --oneline -10                 # History
gh repo view                          # GitHub
```

## Session Start Protocol
1. Read PROJECT_NOTES.md for context
2. Use local tools first
3. Only call AI for complex tasks
