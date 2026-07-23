# APS Dream Home - Master Project Map
## Complete Architecture & Organization Guide
### Generated: April 10, 2026

---

## 📁 PROJECT ROOT STRUCTURE

```
apsdreamhome/
├── 📄 .mcp.json              → MCP Server Configuration
├── 📄 AGENTS.md              → Project Documentation
├── 📄 PROJECT_MAP.md         → This File
├── 📁 app/                   → Core Application (MVC)
├── 📁 config/                → Configuration Files
├── 📁 database/              → Migrations & Seeds
├── 📁 public/                → Web Root (index.php)
├── 📁 routes/                → Route Definitions
├── 📁 testing/               → Test Suites
├── 📁 storage/               → Logs, Cache, Uploads
├── 📁 vendor/                → Composer Dependencies
└── 📁 node_modules/          → NPM Packages
```

---

## 🏗️ APP FOLDER BREAKDOWN (Core MVC)

### 1️⃣ app/Core/ - Framework Heart
**Purpose:** Custom MVC Framework Core

```
Core/
├── 📁 Auth/                  → Authentication System
├── 📁 Autonomous/            → Self-running Systems
├── 📁 Bootstrap/             → App Initialization
├── 📁 Cache/                 → Caching System
├── 📁 Console/               → CLI Commands
├── 📁 Container/             → Dependency Injection
├── 📁 Database/              → Database Layer
│   └── Database.php          → PDO Connection (Port 3307)
├── 📁 ErrorHandler.php       → Error Management
├── 📁 Http/                  → HTTP Layer
│   ├── Request.php           → Request Handling
│   └── Response.php          → Response Handling
├── 📁 Legacy/                → Legacy Code Support
├── 📁 Middleware/            → HTTP Middleware
├── 📁 Routing/               → Router Logic
└── 📁 Session/               → Session Management
```

**Key Files:**
- `Database.php` → Singleton PDO connection (127.0.0.1:3307)
- `Controller.php` → Base controller with auth, db, session
- `Bootstrap/base.php` → System initialization

---

### 2️⃣ app/Http/Controllers/ - 210 Controllers
**Purpose:** Handle HTTP Requests

```
Controllers/
├── 📄 BaseController.php       → All controllers extend this
├── 📄 Controller.php           → Core Controller (alternate)
├── 📁 Admin/                   → Admin Panel Controllers (30+)
│   ├── AdminController.php   → Main admin dashboard
│   ├── UserController.php    → User management
│   ├── PropertyManagementController.php
│   ├── LeadController.php    → CRM/Leads
│   └── ... (many more)
├── 📁 Auth/                    → Authentication (5 controllers)
│   ├── CustomerAuthController.php    → Customer login/register
│   ├── AgentAuthController.php       → Agent login
│   ├── AssociateAuthController.php   → Associate login
│   ├── AdminAuthController.php       → Admin login
│   └── GoogleAuthController.php      → OAuth
├── 📁 Front/                   → Public Pages (10+ controllers)
│   ├── PageController.php      → Home, About, Contact
│   ├── UserController.php      → Customer dashboard/properties
│   ├── AIBotController.php     → AI Chatbot
│   └── SupportController.php   → Support tickets
├── 📁 Employee/                → Employee Portal (1 controller)
│   └── EmployeeController.php  → Attendance, tasks, salary
├── 📁 MLM/                     → Network Marketing (1 controller)
│   └── MLMDashboardController.php
├── 📁 AI/                      → AI Features (3+ controllers)
│   ├── PropertyValuationController.php
│   ├── AIWebController.php
│   └── ChatbotAPIController.php
├── 📁 Api/                     → API Endpoints (5+ controllers)
│   ├── GeminiApiController.php
│   ├── LocationController.php
│   └── NewsletterController.php
└── 📁 Property/                → Property Specific (1 controller)
    └── CompareController.php
```

**Important Pattern:**
- All controllers extend `BaseController`
- Namespaces follow folder structure: `App\Http\Controllers\Admin\`
- Methods return views via `$this->render('view_name', $data)`

---

### 3️⃣ app/Models/ - 146 Models
**Purpose:** Database Interaction Layer

```
Models/
├── 📄 Model.php                → Base Model Class
├── 📁 Core Models (20+)
│   ├── User.php                → User accounts
│   ├── Customer.php            → Customer profiles
│   ├── Admin.php               → Admin accounts
│   ├── Property.php            → Property listings
│   ├── Lead.php                → CRM Leads
│   ├── Inquiry.php             → Inquiries
│   ├── Site.php                → Real estate sites
│   └── ...
├── 📁 Financial (10+)
│   ├── Invoice.php
│   ├── Tax.php
│   ├── Budget.php
│   └── FinancialReports.php
├── 📁 MLM (10+)
│   ├── Associate.php
│   ├── Commission.php
│   ├── NetworkTree.php
│   └── Payout.php
├── 📁 Training (5+)
│   ├── Training.php            → E-learning
│   ├── Course.php
│   └── Certification.php
└── 📁 Support (10+)
    ├── Ticket.php
    ├── Message.php
    └── Notification.php
```

**Pattern:**
- All models extend `Model` base class
- Table name defined: `protected $table = 'table_name'`
- Methods: `find()`, `all()`, `create()`, `update()`, `delete()`

---

### 4️⃣ app/Services/ - Business Logic Layer
**Purpose:** Complex Business Operations (NOT in Controllers)

```
Services/
├── 📁 AI/                      → Artificial Intelligence
│   ├── AIManager.php           → Main AI Orchestrator
│   ├── GeminiService.php     → Google Gemini API
│   ├── AIBackendService.php    → AI Processing
│   ├── AIEcosystemManager.php  → AI Agent Management
│   └── 📁 modules/             → AI Sub-modules
│       ├── NLPProcessor.php    → Text Analysis
│       ├── DataAnalyst.php     → Data Insights
│       ├── DecisionEngine.php  → Decision Making
│       ├── CodeAssistant.php   → Code Help
│       └── RecommendationEngine.php
├── 📁 Training/                → E-Learning System
│   └── TrainingService.php     → Course Management
├── 📁 Payment/               → Payment Processing
│   ├── PaymentGateway.php
│   └── EMICalculator.php
├── 📁 Localization/            → Multi-language
│   └── LocalizationService.php
├── 📁 Performance/             → Performance Optimization
│   └── PHPOptimizerService.php
├── 📁 DevTools/                → Development Tools
│   ├── DebugRoutingService.php
│   └── TestControllerService.php
├── 📁 Testing/                 → Testing Services
│   ├── AdminDashboardTestService.php
│   ├── HomeSimpleService.php
│   └── IndexTestService.php
├── 📁 Logger/                  → Logging
│   └── LoggerService.php
└── 📁 AI/Agents/               → AI Agents
    ├── WhatsAppAgent.php
    └── PropertyAgent.php
```

**WHY Services?**
- Controllers should be THIN (only HTTP handling)
- Services contain BUSINESS LOGIC
- Reusable across multiple controllers
- Easier to test

---

### 5️⃣ app/Modules/ - Feature Modules
**Purpose:** Self-contained Feature Packages

```
Modules/
├── 📁 Property/                → Property Module
│   ├── property_management.php
│   ├── property_purchase.php
│   └── property_valuation.php
├── 📁 MLM/                     → Network Marketing Module
│   ├── mlm_registration.php
│   ├── commission_calculator.php
│   └── genealogy_viewer.php
└── 📁 Payment/                 → Payment Module
    └── payment_gateway.php
```

**Services vs Modules:**
- **Services** = Business logic classes (OOP)
- **Modules** = Feature packages (can include views, controllers)

---

### 6️⃣ app/Views/ - 492 View Files
**Purpose:** HTML/PHP Templates

```
views/
├── 📁 layouts/                 → Page Layouts
│   ├── base.php                → Main layout (header+footer+content)
│   ├── header.php              → Site header
│   └── footer.php              → Site footer
├── 📁 pages/                   → Page Templates (300+ files)
│   ├── home.php                → Homepage
│   ├── about.php               → About page
│   ├── properties.php          → Property listings
│   ├── list_property.php       → Post property form
│   ├── 📁 user_/               → Customer pages
│   │   ├── dashboard.php
│   │   ├── properties.php
│   │   ├── inquiries.php
│   │   └── profile.php
│   ├── 📁 admin_/              → Admin pages
│   │   ├── dashboard.php
│   │   ├── users/
│   │   ├── properties/
│   │   └── leads/
│   └── 📁 properties/          → Property detail pages
├── 📁 auth/                    → Authentication Forms
│   ├── customer_login.php
│   ├── customer_register.php
│   └── forgot_password.php
├── 📁 shared/                  → Shared Components
│   └── profile.php             → Universal profile view
├── 📁 components/              → UI Components
│   ├── alert.php
│   ├── modal.php
│   └── pagination.php
└── 📁 emails/                  → Email Templates
```

---

### 7️⃣ app/Helpers/ - Utility Functions
**Purpose:** Global Helper Functions

```
Helpers/
├── 📄 AuthHelper.php           → Auth utilities
├── 📄 SecurityHelper.php       → Security functions
└── 📄 logger.php               → Logging helpers
```

---

### 8️⃣ app/Reports/ - Reporting System
**Purpose:** Generate Reports

```
Reports/
├── 📄 FinancialReport.php
├── 📄 SalesReport.php
└── 📄 PerformanceReport.php
```

---

## 🗄️ DATABASE STRUCTURE (597 Tables)

### Core Tables (10 Main)
| Table | Purpose | Records |
|-------|---------|---------|
| `users` | All user accounts | ~1000+ |
| `customers` | Customer auth | ~500+ |
| `admin_users` | Admin accounts | ~20+ |
| `user_properties` | Property listings | ~200+ |
| `inquiries` | Lead inquiries | ~1000+ |
| `projects` | Real estate projects | ~50+ |
| `districts` | Districts data | ~75 |
| `states` | States data | ~30 |
| `newsletter_subscribers` | Email subscribers | ~500+ |
| `service_interests` | Service requests | ~200+ |

### Feature Tables
| Category | Tables | Examples |
|----------|--------|----------|
| MLM | ~50 | `associates`, `commissions`, `network_tree` |
| Financial | ~30 | `invoices`, `payments`, `transactions` |
| Training | ~20 | `training_courses`, `modules`, `lessons` |
| Support | ~15 | `tickets`, `messages`, `faqs` |
| AI/ML | ~25 | `ai_suggestions`, `chat_logs`, `valuations` |

---

## 🛣️ ROUTES STRUCTURE (737 Routes)

### routes/web.php Organization
```php
Line 1-195:    PUBLIC PAGES (Home, About, Contact, Properties)
Line 196-277:  AUTHENTICATION (Login, Register, Logout for all user types)
Line 278-520:  ADMIN PANEL (Dashboard, Users, Properties, Leads, etc.)
Line 521-600:  AI FEATURES (Chatbot, Valuation)
Line 601-720:  CUSTOMER ROUTES (Dashboard, Profile)
Line 721-737:  USER ROUTES (⚠️ At end - needs fixing!)
```

### Key Route Patterns
```
Public:     /, /about, /contact, /properties
Customer:   /login, /register, /customer/dashboard
Agent:      /agent/login, /agent/dashboard
Associate:  /associate/login, /associate/dashboard
Employee:   /employee/login, /employee/dashboard
Admin:      /admin/login, /admin, /admin/dashboard
API:        /api/gemini/chat, /api/notifications
```

---

## 🔧 CONFIGURATION FILES

### config/
```
config/
├── 📄 bootstrap.php            → App initialization
├── 📄 database.php             → DB credentials (127.0.0.1:3307)
├── 📄 application.php          → App settings
├── 📄 security.php             → Security config
├── 📄 helpers.php              → Helper functions
└── 📁 environments/            → Environment configs
    ├── development.php
    ├── production.php
    └── testing.php
```

---

## 🧪 TESTING STRUCTURE

### testing/
```
testing/
├── 📁 api/                     → API Tests
├── 📁 checks/                  → Health Checks
├── 📁 database/                → DB Tests
├── 📁 integration/             → Integration Tests
├── 📁 unit/                    → Unit Tests
├── 📁 visual_tests/            → Playwright Tests
│   └── MASTER_TEST_RUNNER.js → Main test suite
└── 📁 system/                  → System Tests
```

---

## 🎯 MCP TOOLS CONFIGURED

### .mcp.json
```json
{
  "mysql": "@f4ww4z/mcp-mysql-server",
  "sequential-thinking": "@modelcontextprotocol/server-sequential-thinking",
  "playwright": "@playwright/mcp",
  "filesystem": "@modelcontextprotocol/server-filesystem",
  "memory": "@modelcontextprotocol/server-memory"
}
```

---

## ⚠️ KNOWN ISSUES (Priority Fix)

| Issue | Location | Impact | Fix |
|-------|----------|--------|-----|
| 🔴 Router Bug | web.php:9 | Double Router instance | Remove line 9 |
| 🔴 User Routes | web.php:720 | Routes at end | Move to ~200 |
| 🟡 JS 404 | assets/js/ | Autocomplete broken | Check paths |
| 🟡 Image 404 | assets/images/ | Missing placeholder | Add image |

---

## 🚀 QUICK NAVIGATION GUIDE

### "Mujhe ye feature modify karna hai..."

| Feature | Where to Look |
|---------|---------------|
| Homepage | `app/Controllers/Front/PageController.php::home()` + `views/pages/home.php` |
| Property Listing | `app/Controllers/Front/PageController.php::properties()` + `views/pages/properties.php` |
| Customer Dashboard | `app/Controllers/Front/UserController.php::dashboard()` + `views/pages/user_dashboard.php` |
| Admin Panel | `app/Controllers/Admin/AdminController.php` + `views/admin/` |
| AI Chatbot | `app/Services/AI/AIManager.php` + `app/Controllers/Front/AIBotController.php` |
| Login/Register | `app/Controllers/Auth/CustomerAuthController.php` + `views/auth/` |
| Database | `app/Core/Database/Database.php` |
| Routes | `routes/web.php` |

---

## 📊 PROJECT STATISTICS

| Metric | Count |
|--------|-------|
| Total PHP Files | 1000+ |
| Controllers | 210 |
| Models | 146 |
| Views | 492 |
| Routes | 737 |
| Database Tables | 597 |
| Services | 50+ |
| Lines of Code | ~500,000+ |

---

## 📝 NAMING CONVENTIONS

### Controllers
- Suffix: `Controller` (e.g., `UserController`)
- Namespace: `App\Http\Controllers\{Folder}\`

### Models
- Singular (e.g., `User`, `Property`)
- Namespace: `App\Models\`

### Services
- Suffix: `Service` (e.g., `TrainingService`)
- Namespace: `App\Services\{Folder}\`

### Views
- Lowercase with underscores (e.g., `user_dashboard.php`)
- Folder: `views/pages/` or `views/admin/`

---

## 🔄 DATA FLOW

```
User Request → public/index.php → Router → Controller → Service/Model → View → Response
                                    ↓
                              routes/web.php (737 routes)
```

---

## 💡 BEST PRACTICES FOR THIS PROJECT

1. **Always extend BaseController** - Consistent auth, db, session access
2. **Use Services for complex logic** - Keep controllers thin
3. **Check AGENTS.md** - Project rules and status
4. **Use MCP MySQL tool** - Direct database queries
5. **Test with Playwright MCP** - Visual testing
6. **Follow naming conventions** - Consistency is key

---

**End of Project Map**
*Generated for APS Dream Home Project*
