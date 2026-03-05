---
description: MVC Structure Rules - Complete Framework Guidelines
auto_execution_mode: 3
---

# MVC STRUCTURE RULES

## 🏗️ APS DREAM HOME MVC ARCHITECTURE

### 📁 DIRECTORY STRUCTURE (IMMUTABLE)

```
apsdreamhome/
├── app/
│   ├── Core/                 # Core framework classes
│   │   ├── App.php         # Application bootstrap
│   │   ├── Controller.php   # Base controller
│   │   ├── View/View.php    # View system
│   │   └── Database/        # Database classes
│   ├── Http/
│   │   ├── Controllers/     # Web controllers
│   │   └── Middleware/      # HTTP middleware
│   ├── Models/             # Data models
│   ├── Services/           # Business logic
│   └── views/              # View files (.php only)
├── routes/
│   ├── web.php            # Web routes
│   ├── api.php            # API routes
│   └── index.php          # Route dispatcher
├── public/
│   ├── index.php          # Entry point
│   └── assets/            # Static files
└── config/                # Configuration files
```

## 🎯 MVC COMPONENT RULES

### 1. 📋 CONTROLLER RULES

#### **Location & Naming:**

```bash
# ✅ CORRECT:
app/Http/Controllers/HomeController.php
app/Http/Controllers/UserController.php
app/Http/Controllers/Property/PropertyController.php

# ❌ WRONG:
app/Controllers/Home.php
app/controller/home.php
resources/Controllers/HomeController.php
```

#### **Controller Structure:**

```php
<?php
namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        // Business logic here
        $data = $this->getData();

        $this->render('pages/home', [
            'page_title' => 'Home - APS Dream Home',
            'page_description' => 'Welcome to APS Dream Home',
            'data' => $data
        ]);
    }

    private function getData()
    {
        // Data fetching logic
        return [];
    }
}
```

#### **Controller Rules:**

- ✅ Always extend `BaseController`
- ✅ Use `App\Http\Controllers` namespace
- ✅ Use `$this->render()` for views
- ✅ Keep methods focused and single-purpose
- ❌ Never use Blade syntax
- ❌ Never mix business logic with presentation

### 2. 🗄️ MODEL RULES

#### **Location & Naming:**

```bash
# ✅ CORRECT:
app/Models/User.php
app/Models/Property.php
app/Models/MLM/NetworkModel.php

# ❌ WRONG:
app/model/user.php
app/Entities/User.php
resources/Models/User.php
```

#### **Model Structure:**

```php
<?php
namespace App\Models;

use App\Core\Database\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        return $stmt->execute([$data['name'], $data['email']]);
    }
}
```

#### **Model Rules:**

- ✅ Use `App\Models` namespace
- ✅ Handle database operations only
- ✅ Use prepared statements for security
- ✅ Return data, not HTML
- ❌ Never output directly
- ❌ Never include business logic

### 3. 🎨 VIEW RULES

#### **Location & Naming:**

```bash
# ✅ CORRECT:
app/views/pages/home.php
app/views/layouts/base.php
app/views/admin/dashboard.php

# ❌ WRONG:
resources/views/home.blade.php
app/views/home.blade.php
views/home.php
```

#### **View Structure:**

```php
<?php
$page_title = 'Page Title';
$page_description = 'Description';
include __DIR__ . '/../layouts/base.php';
?>

<div class="container">
    <h1><?php echo $title ?? 'Default Title'; ?></h1>
    <p><?php echo $content ?? 'Default Content'; ?></p>
</div>

<style>
/* View-specific styles */
.container { margin: 20px; }
</style>

<script>
// View-specific scripts
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded');
});
</script>
```

#### **View Rules:**

- ✅ Use `.php` extension only
- ✅ Use `include __DIR__ . '/../layouts/base.php'`
- ✅ Define `$page_title` and `$page_description`
- ✅ Keep presentation logic only
- ❌ Never use Blade syntax
- ❌ Never include business logic

### 4. 🛣️ ROUTE RULES

#### **Route Definitions:**

```php
// routes/web.php
<?php
// Web Routes
$router->get('/', 'HomeController@index');
$router->get('/about', 'PageController@about');
$router->get('/properties/{id}', 'PropertyController@show');
$router->post('/contact', 'ContactController@submit');

// Protected routes
$router->get('/dashboard', 'DashboardController@index');
$router->get('/admin', 'AdminController@index');
```

#### **API Routes:**

```php
// routes/api.php
<?php
// API Routes
$router->get('/api/properties', 'ApiController@properties');
$router->post('/api/contact', 'ApiController@contact');
$router->get('/api/users', 'UserController@apiIndex');
```

#### **Route Rules:**

- ✅ Use descriptive route names
- ✅ Group related routes
- ✅ Use RESTful patterns for APIs
- ✅ Protect sensitive routes
- ❌ Never put business logic in routes

## 🔄 MVC DATA FLOW

### **Request Flow:**

```
1. User Request → public/index.php
2. Router → routes/web.php or routes/api.php
3. Controller → app/Http/Controllers/
4. Model → app/Models/ (if needed)
5. View → app/views/pages/
6. Response → User Browser
```

### **Data Flow Rules:**

- ✅ Controller handles request/response
- ✅ Model handles data operations
- ✅ View handles presentation only
- ✅ Services handle business logic
- ❌ Never skip MVC layers
- ❌ Never mix responsibilities

## 🎯 SPECIFIC PATTERNS

### **CRUD Operations:**

```php
// Controller
public function index() { /* List */ }
public function show($id) { /* Single item */ }
public function create() { /* Create form */ }
public function store() { /* Save new */ }
public function edit($id) { /* Edit form */ }
public function update($id) { /* Update existing */ }
public function destroy($id) { /* Delete */ }
```

### **API Responses:**

```php
// Controller API method
public function apiIndex()
{
    header('Content-Type: application/json');

    try {
        $data = $this->model->getAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
```

### **Authentication:**

```php
// Controller
public function dashboard()
{
    $this->requireLogin(); // From BaseController

    $userId = $_SESSION['user_id'];
    $data = $this->model->getUserData($userId);

    $this->render('pages/dashboard', [
        'page_title' => 'Dashboard',
        'user_data' => $data
    ]);
}
```

## 🚨 COMMON MISTAKES TO AVOID

### **❌ Anti-Patterns:**

```php
// WRONG - Business logic in view
<?php
$users = $db->query("SELECT * FROM users"); // ❌
foreach ($users as $user) {
    echo "<li>" . htmlspecialchars($user['name']) . "</li>";
}
?>

// WRONG - Direct database in controller
public function index()
{
    $stmt = $this->db->query("SELECT * FROM users"); // ❌
    include 'views/home.php'; // ❌
}

// WRONG - HTML in model
class User {
    public function displayUsers() {
        echo "<ul>"; // ❌
    }
}
```

### **✅ Correct Patterns:**

```php
// RIGHT - Controller orchestrates
public function index()
{
    $users = $this->userModel->getAll(); // ✅
    $this->render('pages/home', ['users' => $users]); // ✅
}

// RIGHT - View presents data
<?php foreach ($users as $user): ?>
    <li><?php echo htmlspecialchars($user['name']); ?></li>
<?php endforeach; ?>

// RIGHT - Model handles data
class User {
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(); // ✅
    }
}
```

## 📋 MVC CHECKLIST

### **Before Creating Controller:**

- [ ] Namespace: `App\Http\Controllers`
- [ ] Extends: `BaseController`
- [ ] Location: `app/Http/Controllers/`
- [ ] Naming: `NameController.php`

### **Before Creating Model:**

- [ ] Namespace: `App\Models`
- [ ] Location: `app/Models/`
- [ ] Database: Use Database class
- [ ] Security: Prepared statements

### **Before Creating View:**

- [ ] Extension: `.php` only
- [ ] Location: `app/views/`
- [ ] Include: `base.php` layout
- [ ] Variables: `$page_title`, `$page_description`

### **Before Adding Route:**

- [ ] File: `routes/web.php` or `routes/api.php`
- [ ] Pattern: RESTful where applicable
- [ ] Controller: Exists and correct
- [ ] Method: Exists in controller

---

**⚠️ WARNING: Never break MVC separation!**
**🔒 This MVC structure is permanent!**
**📋 Follow patterns every time!**

**Created:** 2026-03-04  
**Status:** PERMANENT MVC RULES  
**Authority:** IMMUTABLE
