# 🤖 TRAE - PROJECT CONTEXT & MEMORY

## 📅 LAST UPDATED: 2026-03-09

## 🚨 CRITICAL STATUS: "Phase 1 - Infrastructure Repair"

### 🏗️ PROJECT STRUCTURE ANALYSIS
- **Architecture:** Custom PHP MVC (No Laravel/Blade)
- **Root Path:** `c:\xampp\htdocs\apsdreamhome`
- **Public URL:** `http://localhost/apsdreamhome/public`
- **Entry Point:** `public/index.php`

### ❌ MISSING CRITICAL COMPONENTS
My deep scan of `routes/web.php` vs `app/Controllers` reveals massive gaps. The following controllers are defined in routes but **DO NOT EXIST**:

#### 1. Admin Module
- `Admin\AdminDashboardController` (Routes: `/admin/dashboard`, `/admin/stats`)
- `Admin\AdminController` (Routes: `/admin/stats`, `/admin/activities`, `/admin/analytics/*`)
- `Admin\PropertyController` (Routes: `/admin/properties/*`)
- `Admin\UserController` (Routes: `/admin/users`)

#### 2. Auth Module
- `Auth\AuthController` (Routes: `/auth/login`, `/auth/register`, `/auth/*`)
- `Auth\AdminAuthController` (Routes: `/login`, `/admin/login`)

#### 3. Core Features
- `Event\EventController` (Routes: `/events/*`)
- `Performance\PerformanceController` (Routes: `/performance/*`)
- `Communication\MediaController` (Routes: `/communication/media`)
- `Communication\SmsController` (Routes: `/communication/sms/*`)
- `Security\SecurityController` (Routes: `/security/*`)
- `Reports\ReportController` (Routes: `/reports/*`)

#### 4. Legacy/General
- `HomeController` (Route: `/`)
- `DashboardController` (Routes: `/dashboard/*`)
- `AgentController` (Routes: `/agent/*`)
- `CustomerController` (Routes: `/customer/*`)

### ✅ EXISTING CONTROLLERS (VERIFIED)
- `Async\AsyncTaskController`
- `Business\AssociateController`
- `HumanResources\CareerController`
- `Land\PlottingController`
- `Marketing\MarketingAutomationController`
- `Media\MediaLibraryController`
- `Utilities\UtilityController`

### 📋 ACTION PLAN (TRAE EXECUTION)
1.  **Database:** Create `config/database.php` (Priority #1).
2.  **Auth:** Create `Auth\AuthController` and `Auth\AdminAuthController`.
3.  **Admin:** Create `Admin\AdminDashboardController`.
4.  **Routing:** Fix duplicate routes in `routes/web.php` (`/careers`, `/marketing/dashboard`).

## 🧠 RULES FOR TRAE
1.  **NEVER** assume a controller exists just because it's in `routes/web.php`.
2.  **ALWAYS** check `app/Controllers` before referencing a class.
3.  **STRICTLY** use Pure PHP for views (No Blade).
4.  **USE** `MASTER_PLAN.md` as the source of truth for immediate tasks.
