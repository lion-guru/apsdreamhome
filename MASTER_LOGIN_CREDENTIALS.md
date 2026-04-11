# APS Dream Home - All Login Credentials
**Document:** MASTER LOGIN CREDENTIALS  
**Date:** April 11, 2026  
**Status:** Development/Test Environment

---

## 🎯 QUICK ACCESS GUIDE

| Role | URL | Username | Password |
|------|-----|----------|----------|
| **Super Admin** | `/admin/login` | superadmin@aps.com | admin123 |
| **Admin** | `/admin/login` | admin@aps.com | admin123 |
| **Customer** | `/login` | customer@example.com | password123 |
| **Associate** | `/associate/login` | associate@example.com | password123 |
| **Agent** | `/agent/login` | agent@example.com | password123 |
| **Employee** | `/employee/login` | employee@aps.com | employee123 |

---

## 👑 SUPER ADMIN / GOD MODE

### Primary Super Admin
- **URL:** `http://localhost/apsdreamhome/admin/login`
- **Email:** `superadmin@aps.com`
- **Password:** `admin123`
- **Access:** Full system control + God Mode features

### Backup Admin Account
- **Email:** `admin@aps.com`
- **Password:** `admin123`
- **Access:** All admin features except God Mode

### God Mode Access
Once logged in as Super Admin:
- **God Mode Dashboard:** `/admin/godmode`
- **Features:**
  - User Impersonation (login as any user)
  - Role Switching (experience system as different roles)
  - System Commands (cache clear, DB optimize)
  - System Health Monitor

---

## 👤 CUSTOMER ACCOUNTS

### Customer 1
- **URL:** `http://localhost/apsdreamhome/login`
- **Email:** `customer@example.com`
- **Password:** `password123`
- **Phone:** `9876543210`

### Customer 2
- **Email:** `rajesh@example.com`
- **Password:** `password123`
- **Phone:** `9876543211`

### Customer 3
- **Email:** `priya@example.com`
- **Password:** `password123`
- **Phone:** `9876543212`

### Customer Dashboard Access
- **Dashboard:** `/user/dashboard`
- **My Properties:** `/user/properties`
- **My Inquiries:** `/user/inquiries`
- **Profile:** `/user/profile`
- **Bank Details:** `/user/bank-details`

---

## 🤝 ASSOCIATE (MLM) ACCOUNTS

### Associate 1
- **URL:** `http://localhost/apsdreamhome/associate/login`
- **Email:** `associate@example.com`
- **Password:** `password123`
- **Referral Code:** `APS001`

### Associate 2
- **Email:** `networker@example.com`
- **Password:** `password123`
- **Referral Code:** `APS002`

### Associate 3
- **Email:** `mlmleader@example.com`
- **Password:** `password123`
- **Referral Code:** `APS003`

### Associate Dashboard Access
- **Dashboard:** `/associate/dashboard`
- **My Network:** `/associate/genealogy` or `/associate/network`
- **Commissions:** `/associate/commissions`
- **Leads:** `/associate/leads`
- **Properties:** `/associate/properties`
- **Profile:** `/associate/profile`

---

## 🏢 AGENT ACCOUNTS

### Agent 1
- **URL:** `http://localhost/apsdreamhome/agent/login`
- **Email:** `agent@example.com`
- **Password:** `password123`
- **License:** `UP-12345`

### Agent 2
- **Email:** `propertydealer@example.com`
- **Password:** `password123`
- **License:** `UP-12346`

### Agent Dashboard Access
- **Dashboard:** `/agent/dashboard`
- **My Leads:** `/agent/leads`
- **Properties:** `/agent/properties`
- **Commissions:** `/agent/commissions`

---

## 💼 EMPLOYEE ACCOUNTS

### Employee 1
- **URL:** `http://localhost/apsdreamhome/employee/login`
- **Email:** `employee@aps.com`
- **Password:** `employee123`
- **Employee ID:** `EMP001`
- **Department:** Sales

### Employee 2
- **Email:** `manager@aps.com`
- **Password:** `employee123`
- **Employee ID:** `EMP002`
- **Department:** Marketing

### Employee 3
- **Email:** `receptionist@aps.com`
- **Password:** `employee123`
- **Employee ID:** `EMP003`
- **Department:** Front Desk

### Employee Dashboard Access
- **Dashboard:** `/employee/dashboard`
- **Tasks:** `/employee/tasks`
- **Attendance:** `/employee/attendance`
- **Performance:** `/employee/performance-page`
- **Profile:** `/employee/profile`

---

## 🔧 TESTING CREDENTIALS (For Automation)

### Bulk Test Users
```
Role       | Email                    | Password      | Phone
-----------|--------------------------|---------------|------------
Customer   | test1@test.com          | test123       | 9000000001
Customer   | test2@test.com          | test123       | 9000000002
Associate  | assoc1@test.com         | test123       | 9000000003
Associate  | assoc2@test.com         | test123       | 9000000004
Agent      | agent1@test.com         | test123       | 9000000005
Employee   | emp1@test.com           | test123       | 9000000006
```

---

## 🗄️ DATABASE DIRECT ACCESS (Emergency)

### Direct Database Connection
```
Host: localhost
Port: 3307
Database: apsdreamhome
Username: root
Password: (blank)
```

### Adminer Access
- **URL:** `http://localhost/apsdreamhome/adminer.php`
- **Server:** `localhost:3307`
- **Username:** `root`
- **Password:** (leave blank)
- **Database:** `apsdreamhome`

---

## 🧪 API TESTING ENDPOINTS

### Authentication APIs
```bash
# Customer Login
POST http://localhost/apsdreamhome/login
Content-Type: application/x-www-form-urlencoded
identity=customer@example.com&password=password123

# Admin Login
POST http://localhost/apsdreamhome/admin/login
Content-Type: application/x-www-form-urlencoded
email=superadmin@aps.com&password=admin123
```

### API Keys (For Testing)
```
Email:      test_api_key_12345
SMS:        msg91_test_key_12345
Payment:    razorpay_test_key_12345
```

---

## 🔒 PASSWORD RESET

### If You Forget Password

**Option 1: Direct Database Reset**
```sql
-- Reset customer password
UPDATE users SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE email = 'customer@example.com';
-- New password: password123
```

**Option 2: Admin Reset**
1. Login as Super Admin
2. Go to `/admin/users`
3. Find user → Edit → Reset Password

---

## 🎯 FEATURES ACCESS MATRIX

| Feature | Super Admin | Admin | Customer | Associate | Agent | Employee |
|---------|-------------|-------|----------|-----------|-------|------------|
| **God Mode** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **User Impersonation** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Role Switching** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **System Commands** | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **All Dashboards** | ✅ | ✅ | Own | Own | Own | Own |
| **Property Management** | ✅ | ✅ | View | CRUD | CRUD | View |
| **Lead Management** | ✅ | ✅ | Own | Own | CRUD | View |
| **Commission View** | ✅ | ✅ | ❌ | Own | Own | ❌ |
| **MLM Network** | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ |
| **Settings** | ✅ | ⚠️ | ❌ | ❌ | ❌ | ❌ |

**Legend:** ✅ Full Access | ⚠️ Limited | ❌ No Access | Own = Own Data Only

---

## 🚀 QUICK TEST WORKFLOWS

### 1. Customer Journey Test
1. Login as `customer@example.com`
2. Visit `/properties` - Browse properties
3. Visit `/user/dashboard` - Check dashboard
4. Visit `/user/properties` - View my properties
5. Visit `/user/inquiries` - Check inquiries

### 2. MLM/Associate Test
1. Login as `associate@example.com`
2. Visit `/associate/genealogy` - View MLM tree
3. Visit `/associate/commissions` - Check commissions
4. Visit `/associate/dashboard` - Dashboard view

### 3. Admin God Mode Test
1. Login as `superadmin@aps.com`
2. Visit `/admin/godmode` - God Mode Dashboard
3. Click "Impersonate" on any user
4. Verify you see their dashboard
5. Click "Exit Impersonation"
6. Try "Switch Role" to customer/associate

### 4. Employee Portal Test
1. Login as `employee@aps.com`
2. Check In at `/employee/attendance`
3. View tasks at `/employee/tasks`
4. View performance at `/employee/performance-page`

---

## 📞 SUPPORT & EMERGENCY ACCESS

### Master Admin Account (Emergency)
- **Email:** `emergency@aps.com`
- **Password:** `emergency2026!`
- **Access:** Full system + password reset capability

### Database Admin (Root Access)
- **System:** XAMPP MySQL
- **Port:** 3307
- **Root:** root / (no password)
- **phpMyAdmin:** `http://localhost:8080/phpmyadmin` (if configured)

---

## 📝 NOTES

1. **All passwords are for DEVELOPMENT only**
2. **Change passwords before production deployment**
3. **God Mode is logged - all actions are audited**
4. **Impersonation sessions expire after 2 hours**
5. **Role switching is temporary - can be restored**

---

## 🔐 SECURITY REMINDERS

```
⚠️ NEVER use default passwords in production
⚠️ ALWAYS change database credentials
⚠️ ENABLE SSL/HTTPS for production
⚠️ SET UP fail2ban for brute force protection
⚠️ ENABLE audit logging
⚠️ CHANGE API keys before going live
```

---

**Document Version:** 1.0  
**Last Updated:** April 11, 2026  
**Created By:** Development Team

---

*For production deployment, use `.env` file and secure credential management.*
