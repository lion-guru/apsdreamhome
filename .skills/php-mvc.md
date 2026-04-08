# PHP MVC Framework Skills

## Project Structure
- Controllers: `app/Http/Controllers/`
- Models: `app/Models/`
- Views: `app/Views/`
- Routes: `routes/web.php`

## Common Patterns

### Controller Pattern
```php
namespace App\Http\Controllers;

class PageController extends Controller {
    public function home() {
        $data = ['title' => 'Home'];
        return view('pages.home', $data);
    }
}
```

### Route Pattern
```php
$router->get('/', 'PageController@home');
$router->post('/submit', 'FormController@submit');
```

### Model Pattern
```php
namespace App\Models;

class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email'];
}
```

## Database
```php
$db = Database::getInstance();
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
```

## View Helper
```php
// In views
<?php echo BASE_URL; ?>
<?php include __DIR__ . '/layouts/header.php'; ?>
```

## Testing Commands
- Syntax check: `php -l file.php`
- Route list: Check routes/web.php
- DB test: http://localhost/apsdreamhome/admin/db-test
