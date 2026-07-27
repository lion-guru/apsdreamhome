# PROJECT_RULES.md — APS Dream Home Coding Standards & Architecture

> **Purpose:** Single source of truth for coding conventions, architecture decisions, and development rules.
> Every agent and developer MUST follow these rules.

---

## 1. PHP Coding Style

### Formatting

- **Indentation:** 4 spaces (NO tabs)
- **Braces:** Allman-style for class/method declarations, K&R for control structures
- **Line length:** Max 120 characters preferred
- **Trailing comma:** Allowed in arrays, not required

### Naming Conventions

| Element     | Convention        | Example                                          |
| ----------- | ----------------- | ------------------------------------------------ |
| Classes     | PascalCase        | `ApiKeyService`, `CRMController`                 |
| Methods     | camelCase         | `getLeads()`, `create()`, `skipCsrfProtection()` |
| Variables   | camelCase         | `$recentLeads`, `$pageData`                      |
| DB columns  | snake_case        | `created_at`, `is_active`, `key_value`           |
| DB tables   | snake_case plural | `api_keys`, `leads`, `mlm_commission_ledger`     |
| Constants   | UPPER_SNAKE_CASE  | `BASE_URL`, `APP_ROOT`                           |
| CSS classes | kebab-case        | `alert-danger`, `shadow-sm`                      |

**Exception:** View metadata uses `$page_title`, `$page_description` (snake*case with `$page*` prefix).

### Namespaces (PSR-4 under `App\`)

```
App\Http\Controllers\Admin    → Admin controllers
App\Http\Controllers\Front    → Public controllers
App\Http\Controllers\Auth     → Auth controllers
App\Http\Controllers\Api      → API controllers
App\Services                  → Business logic
App\Models                    → Data models
App\Core                      → Framework internals
```

---

## 2. Controller Rules

### Inheritance Chain

```
BaseController
  └── AdminController ($this->layout = 'layouts/admin')
        └── All admin controllers
  └── Front\PageController ($this->layout = 'layouts/base')
        └── All public controllers
```

**ALL admin controllers MUST extend `AdminController`** (not `BaseController` directly).

### Constructor Pattern

```php
class ApiKeyController extends AdminController
{
    public function __construct() { parent::__construct(); }
}
```

### Data Passing Pattern

```php
$this->data = array_merge($this->data, [
    'page_title' => 'Page Title',
    'items'      => $service->list(),
]);
return $this->render('admin/features/view_name', $this->data);
```

### CSRF Protection

- `BaseController::__construct()` auto-validates CSRF on POST
- Public auth endpoints MUST override: `skipCsrfProtection(): bool { return true; }`
- Router has SEPARATE CSRF check with `$excludedPaths` list (uses `strpos === 0`)
- **New auth endpoints MUST be added to BOTH** controller AND router exclusion lists

### Auth Guards

```php
$this->requireAdmin();   // checks $_SESSION['admin_id'] + role
$this->requireLogin();   // checks isLoggedIn()
```

### Flash Messages

```php
$_SESSION['flash_success'] = 'Saved!';
$_SESSION['flash_error'] = 'Failed!';
```

---

## 3. Service Rules

### Constructor Pattern

```php
class MyService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }
}
```

### Instantiation in Controllers

```php
$svc = new ApiKeyService($this->db);
$result = $svc->list();
```

### DB Access Pattern

- Use prepared statements with positional `?` or named `:param` placeholders
- Wrap in try/catch returning empty defaults on failure
- NO raw string concatenation in SQL

```php
public function getItems(): array
{
    try {
        $st = $this->db->prepare("SELECT * FROM items WHERE status = ?");
        $st->execute(['active']);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {
        return [];
    }
}
```

---

## 4. Model Rules

### Two Model Layers

1. **Simple Static Model** (`App\Models\Model`) — Most models use this
   - `Model::find($id)`, `Model::create($data)`, `Model::getAll()`
2. **Active Record Model** (`App\Core\Database\Model`) — Few models use this
   - `$model->save()`, `$model->delete()`, `::query()->where()`

### Configuration

```php
class Lead extends Model
{
    protected static $table = 'leads';
    // primaryKey defaults to 'id'
}
```

---

## 5. View Rules

### Rendering Pipeline

1. Controller calls `render('view/path', $data)`
2. `render()` extracts `$data` to local variables via `extract()`
3. View file included (PHP + HTML mixed)
4. `$content` captured via `ob_get_clean()`
5. Layout file included, echoes `$content`

### View Layout Assignment

- Admin views: `require_once APP_PATH . '/views/layouts/admin.php'`
- **NEVER reference archived `admin/layouts/admin.php`** — use `views/layouts/admin.php`

### View Conventions

- Views are PHP+HTML templates (NOT Blade, NOT Twig)
- Always `htmlspecialchars()` on user output
- Use `<?= $variable ?>` short echo tags
- CSRF tokens: `$_SESSION['csrf_token']`
- Flash messages: `$_SESSION['flash_success']`, `$_SESSION['flash_error']`

---

## 6. Database Rules

### Column Naming

- **snake_case universally**
- Boolean columns: `is_` prefix (`is_active`, `is_featured`)
- Status columns: lowercase strings, NOT ENUM (`'active'`, `'cancelled'`)

### Primary Keys

- **Always `id`** (never `table_name_id`)

### Timestamps

- `created_at` and `updated_at` on most tables
- Use MySQL `NOW()` for inserts/updates

### Table Naming

- **Plural snake_case**: `api_keys`, `leads`, `users`
- Junction tables append parent: `lead_notes`, `lead_deals`

### SQL Reserved Words

- **NEVER use reserved words as column aliases** without backticks
- Known traps: `delayed`, `active`, `order`, `group`, `key`, `status`, `position`
- Always wrap aliases in backticks if they might be reserved: `` SELECT `delayed` AS `delayed_count` ``

---

## 7. Route Rules

### Definition Format

```php
$router->get('/path', 'Namespace\\Controller@method');
$router->post('/path', 'Namespace\\Controller@method');
```

### Route Duplication

- Later route definitions override earlier ones
- Always add new routes near the END of the relevant section
- Document any route conflicts with comments

### CSRF Exclusion (Router)

New auth endpoints MUST be added to `$excludedPaths` in `routes/router.php:106-143`.
Pattern: `strpos($uri, $path) === 0` — `/login` matches `/login` but NOT `/farmer/login`.

---

## 8. Error Handling Rules

### Standard Pattern

```php
try {
    // DB operation
} catch (\Throwable $e) {
    error_log("ServiceName::methodName error: " . $e->getMessage());
    return [];  // or false, or null — match the return type
}
```

### Exception Types

- Use `catch (\Throwable $e)` for new code (catches both Exception and Error)
- `catch (\Exception $e)` acceptable in legacy code

### Logging

- `error_log()` for PHP native logging
- Never suppress errors with `@` unless intentional (e.g., `@error_log()`)

---

## 9. Frontend Rules

### CSS Framework

- **Bootstrap 5.3.3** via CDN
- **Font Awesome 6.5.1** for icons
- Custom CSS files loaded via layout (NOT inline)

### JavaScript

- **jQuery 3.7.1** for admin DataTables/modals
- **Bootstrap 5.3.3 JS bundle**
- CSP nonce required on ALL inline scripts: `<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">`
- CSRF token auto-attached by `admin-form-enhancer.js`

### Asset Paths

```php
<?= BASE_URL ?>/assets/admin/css/admin.css
<?= BASE_URL ?>/assets/js/notification-system.js
```

---

## 10. File Organization

### Folder Structure

```
app/
├── Core/                    → Framework internals
├── Http/Controllers/
│   ├── Admin/               → Admin panel controllers
│   ├── Front/               → Public page controllers
│   ├── Auth/                → Login/register controllers
│   ├── Api/                 → API endpoints
│   ├── Employee/            → Employee portal
│   ├── MLM/                 → Network marketing
│   └── AI/                  → AI features
├── Models/                  → Data models
├── Services/                → Business logic
│   ├── Land/                → Colony/pipeline services
│   ├── MLM/                 → Commission engine
│   ├── Communication/       → Email/SMS/WhatsApp
│   ├── Accounting/          → Finance services
│   └── AI/                  → AI services & agents
├── Views/
│   ├── layouts/             → Layout templates (admin.php, base.php, employee.php)
│   ├── admin/               → Admin view files
│   │   ├── features/        → Feature-specific views
│   │   └── layouts/         → rbac_sidebar.php ONLY (others archived)
│   └── pages/               → Public view files
├── Helpers/                 → Utility functions
└── Modules/                 → Feature packages
```

### View-to-Controller Mapping

Views mirror the controller namespace:

```
Admin\ApiKeyController::index()  → admin/features/api_keys.php
Front\PageController::home()     → pages/home.php
```

---

## 11. Architecture Decisions

### ADR: Dual-Write for MLM Trees

- `network_tree` — rich binary tree (D3.js display, views)
- `mlm_network_tree` — simple parent chain (commission engines)
- **Both MUST be written to** during registration. Commission engines ONLY see `mlm_network_tree`.

### ADR: Services Bypass Models for Complex Queries

Most business logic lives in Services, not Models. Services write raw SQL.
Use Models only for simple CRUD operations.

### ADR: No Formal DI Container

Services are manually instantiated: `new ServiceClass($this->db)`.
The `App\Core\App` singleton exists but is only used by framework-level `Controller`.

### ADR: View Rendering is Synchronous

No template engine, no compilation, no caching. Every request re-evaluates PHP views.

### ADR: Commission Plan Snapshot

Every `mlm_commission_ledger` entry captures full plan snapshot as immutable JSON.
Past entries are NEVER affected by plan changes.

### ADR: Two CSRF Layers

- Controller-level (`BaseController::__construct()`)
- Router-level (`routes/router.php:106-143`)
- Both must be satisfied for POST requests to work

---

## 12. Testing Rules

### E2E Tests

- Command: `node testing/visual_tests/E2E_MASTER_TEST.mjs`
- Target: **153/153 PASS** (1 expected GodMode 403)
- Run after ANY change to verify no regressions

### PHP Syntax Check

```bash
php -l <file.php>
```

Run on every modified PHP file before committing.

---

## 13. Git Rules

- **NEVER commit without explicit "commit kar do" from user**
- Use meaningful commit messages
- Don't force push, don't amend without permission

---

## 14. Prohibited Patterns

1. **NEVER delete files without 7-step checklist** — see DELETION_RULE.md
2. **NEVER use reserved words as SQL aliases** without backticks
3. **NEVER reference `admin/layouts/admin.php`** — archived. Use `views/layouts/admin.php`
4. **NEVER write ONLY to `network_tree`** — always dual-write to `mlm_network_tree` too
5. **NEVER use `catch (Exception)` in new code** — use `catch (\Throwable)`
6. **NEVER skip CSRF on non-auth POST endpoints**
7. **NEVER create routes pointing to non-existent controllers**
8. **NEVER hardcode fake data** in production views — query the DB
9. **NEVER assume column names** — always verify DB schema before writing queries
10. **NEVER use `@` to suppress errors** in production code

---

## 15. Quick Reference Commands

```bash
# Start XAMPP server
# http://localhost/apsdreamhome/

# Admin login (test bypass)
http://localhost/apsdreamhome/admin/login?test_login=1

# E2E test
node testing/visual_tests/E2E_MASTER_TEST.mjs

# PHP syntax check
php -l <file.php>

# Flutter APK build
cd mobile/apsdreamhome_app_v2 && flutter build apk --debug

# Commission breakdown doc
docs/COMMISSION_BREAKDOWN_1LAKH.md

# Colony pipeline workflow
docs/COLONY_PIPELINE_WORKFLOW.md
```

---

_Last updated: 2026-07-28 — Session 55_
