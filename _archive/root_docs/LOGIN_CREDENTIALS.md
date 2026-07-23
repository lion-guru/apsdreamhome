# APS Dream Home - Login Credentials

## Test Accounts

### Admin Panel (`/admin/login`)
| Username | Email | Password | Role |
|----------|-------|----------|------|
| `superadmin` | superadmin@apsdreamhome.com | `admin123` | Super Admin |
| `admin` | admin@apsdreamhome.com | `admin123` | Admin |
| `testadmin` | testadmin@example.com | `admin123` | Admin |

**Test bypass**: `/admin/login?test_login=1` (auto-login as testadmin)

### Customer Portal (`/login`)
| Email | Password | Name |
|-------|----------|------|
| testuser@example.com | `admin123` | Test User |

### Associate Portal (`/associate/login`)
| Email | Password |
|-------|----------|
| *(register new account via `/become-associate`)* |

### Employee Portal (`/employee/login`)
| Email | Password |
|-------|----------|
| *(login via employee credentials configured in DB)* |

---

## Quick Links
- **Homepage**: http://localhost/apsdreamhome/
- **Admin Login**: http://localhost/apsdreamhome/admin/login
- **Customer Login**: http://localhost/apsdreamhome/login
- **Customer Register**: http://localhost/apsdreamhome/register
- **Associate Login**: http://localhost/apsdreamhome/associate/login
- **Employee Login**: http://localhost/apsdreamhome/employee/login

## Test Flow
1. Visit `/admin/login` → login as `testadmin` / `admin123`
2. Visit `/login` → login as `testuser@example.com` / `admin123`
3. Visit `/admin/user-properties` → approve/reject test properties
