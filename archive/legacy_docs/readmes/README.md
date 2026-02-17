# APS Dream Home - Real Estate Website

## परिचय

APS Dream Home एक व्यापक रियल एस्टेट वेबसाइट है जो प्रॉपर्टी खरीदने, बेचने और किराए पर लेने के लिए एक ऑनलाइन प्लेटफॉर्म प्रदान करती है। यह वेबसाइट प्रॉपर्टी लिस्टिंग, एजेंट प्रोफाइल, प्रॉपर्टी मैनेजमेंट और अन्य रियल एस्टेट सेवाओं के लिए एक केंद्रीय हब के रूप में कार्य करती है।

## विशेषताएं

- **प्रॉपर्टी लिस्टिंग**: बिक्री और किराए के लिए प्रॉपर्टी की विस्तृत सूची
- **प्रॉपर्टी सर्च**: उन्नत फिल्टरिंग और सर्च विकल्पों के साथ
- **एजेंट प्रोफाइल**: रियल एस्टेट एजेंटों के लिए प्रोफाइल पेज
- **यूजर अकाउंट**: रजिस्ट्रेशन और लॉगिन सिस्टम
- **एडमिन पैनल**: वेबसाइट प्रबंधन के लिए व्यापक एडमिन पैनल
- **प्रॉपर्टी मैनेजमेंट**: प्रॉपर्टी मैनेजमेंट सेवाएं
- **लीगल सर्विसेज**: रियल एस्टेट से संबंधित कानूनी सेवाएं

## 🌟 Key Features

### 🏘️ Property Management
- Advanced property listings with filters
- High-quality image galleries
- Virtual tours and 360° views (Coming Soon)
- Interactive property maps
- Detailed property analytics

### 👥 User Experience
- Role-based access control (Admin, Agent, User)
- Favorites and saved searches
- Property comparison tool
- Multi-language support (Hindi/English)

### 💰 Financial Tools
- EMI calculator
- Loan eligibility checker
- Investment return calculator
- Payment gateway integration
- Transaction history

### 🛠️ Admin Dashboard
- Comprehensive analytics
- User management
- Content management system
- Report generation
- MLM Commission Tracking

---

## 📚 विस्तृत दस्तावेज़ीकरण (Documentation)

प्रोजेक्ट के सभी पहलुओं को विस्तार से समझने के लिए नीचे दिए गए लिंक्स देखें:

- **[Documentation Home](./docs/README.md)** - सभी गाइड्स का मुख्य इंडेक्स।
- **[Installation Guide](./docs/guides/SETUP_GUIDE.md)** - प्रोजेक्ट को सेटअप करने का तरीका।
- **[Admin User Guide](./docs/guides/ADMIN_USER_GUIDE.md)** - एडमिन पैनल का उपयोग कैसे करें।
- **[API Documentation](./docs/guides/developer/API_DOCUMENTATION.md)** - डेवलपर्स के लिए API जानकारी।
- **[Master Plan](./docs/planning/MASTER_PLAN.md)** - प्रोजेक्ट का विजन और भविष्य की योजनाएं।

---

### **उन्नत विशेषताएं:**
- 🔐 **मल्टी-रोल प्रमाणीकरण** - विभिन्न उपयोगकर्ता प्रकारों के लिए अलग-अलग लॉगिन सिस्टम
- 📱 **मोबाइल रिस्पॉन्सिव** - स्मार्टफोन और टैबलेट के लिए अनुकूलित
- 🚀 **उच्च प्रदर्शन** - अनुकूलित डेटाबेस क्वेरी और कैशिंग
- 🔒 **एंटरप्राइज़ सुरक्षा** - बैंक-ग्रेड सुरक्षा उपाय
- 📊 **एनालिटिक्स रेडी** - अंतर्निहित प्रदर्शन निगरानी
- 🎨 **आधुनिक UI/UX** - एनिमेशन के साथ पेशेवर, स्वच्छ डिज़ाइन

### **सुरक्षा विशेषताएं:**
- ✅ **CSRF सुरक्षा** - टोकन-आधारित फॉर्म सत्यापन
- ✅ **इनपुट सैनिटाइजेशन** - XSS रोकथाम
- ✅ **SQL इंजेक्शन सुरक्षा** - तैयार किए गए स्टेटमेंट
- ✅ **पासवर्ड सुरक्षा** - Bcrypt हैशिंग
- ✅ **सेशन सुरक्षा** - सुरक्षित सेशन प्रबंधन
- ✅ **फ़ाइल अपलोड सुरक्षा** - फ़ाइल प्रकार सत्यापन

## तकनीकी विवरण (Tech Stack)

### Frontend
- **HTML5 & CSS3** - Modern semantic markup and styling
- **JavaScript (ES6+)** - Dynamic UI interactions
- **Bootstrap 5** - Responsive design framework
- **FontAwesome** - Professional icons
- **jQuery** - Simplified DOM manipulation

### Backend
- **PHP 8.0+** - Core application logic
- **MySQL 8.0+** - Relational database management
- **Custom MVC Architecture** - Structured and maintainable code
- **Composer** - Dependency management

### Features & Security
- **Bcrypt Hashing** - Secure password storage
- **CSRF Protection** - Token-based form security
- **PDO prepared statements** - SQL injection prevention
- **Session Security Manager** - Advanced session handling

---

## इंस्टॉलेशन

1. XAMPP या समान वेब सर्वर सॉफ्टवेयर इंस्टॉल करें
2. MySQL डेटाबेस बनाएं: `apsdreamhome`
3. `database/migrations` फोल्डर में उपलब्ध SQL फाइलों को इम्पोर्ट करें
4. प्रोजेक्ट फाइलों को `htdocs` फोल्डर में कॉपी करें
5. `includes/config/.env` फाइल में डेटाबेस कनेक्शन सेटिंग्स कॉन्फिगर करें
6. वेब ब्राउज़र में `http://localhost/apsdreamhome` पर जाएं

## प्रोजेक्ट स्ट्रक्चर

- **admin/**: एडमिन पैनल और प्रबंधन फंक्शंस
- **api/**: API एंडपॉइंट्स
- **assets/**: CSS, JavaScript, इमेज और अन्य स्टैटिक फाइलें
- **database/**: डेटाबेस माइग्रेशन और सीड फाइलें
- **includes/**: हेल्पर फंक्शंस, कॉन्फिगरेशन और कॉमन कोड
- **templates/**: HTML टेम्पलेट्स
- **uploads/**: यूजर अपलोड की गई फाइलें

## Comprehensive A-to-Z Project Map (Developer/Owner View)

यह project एक single-framework नहीं है; इसमें **multiple layers** साथ-साथ चल रहे हैं। इस README का goal है कि आप किसी भी folder/page को देखकर तुरंत समझ सकें कि वह किस system में आता है और कहाँ use होता है।

### 1) Main Entry Points (कौन-सा URL किस engine से serve होता है)

- **Root Public Site (Legacy Pages)**
  - `index.php` (homepage logic + legacy HTML injection)
  - `login.php`, `register.php`, `logout.php`
  - `properties.php`, `projects.php`, `contact.php`, `about.php`, etc.

- **Modern Front Controller (MVC pipeline)**
  - `public/index.php` (front controller)
  - `public/.htaccess` (rewrite -> `public/index.php`)
  - Root `.htaccess` भी non-file requests को `public/index.php` पर route करता है

- **Admin Panel**
  - `admin/index.php` (admin login)
  - `admin/process_login.php` -> `admin/admin_login_handler.php` (session + role redirect)

### 2) Routing / Dispatch (Routing कहाँ define होती है)

इस project में routing के **3 parallel approaches** मिले:

1. **Apache rewrite (primary web entry)**
   - Root `.htaccess`: non-existing paths => `public/index.php`
   - `public/.htaccess`: non-existing paths => `public/index.php`

2. **MVC Router (App core)**
   - `public/index.php` -> `app/core/App.php`
   - `app/core/App.php` loads:
     - `routes/modern.php`
     - `routes/web.php` (legacy fallback)
     - `routes/api.php`
   - NOTE: routing files में legacy-style calls दिखते हैं; इसलिए कुछ routes “intended” हैं पर runtime में verify करना जरूरी है।

3. **Alternate Dispatcher + Static Route definitions (MLM focused)**
   - `includes/dispatcher.php` + `app/core/routes.php`
   - `app/core/routes.php` में `Route::get(...)` जैसी definitions हैं (MLM/admin-MLM endpoints)

### 3) Template/Layout System (Header/Footer/Sidebar)

Frontend में templates के **multiple variants** हैं:

- **Legacy Static Header**
  - `header.php` (top navbar + dropdowns)
  - कई legacy pages इसी को include करते हैं

- **Unified Header/Footer (restored templates)**
  - `includes/unified_header.php`
  - `includes/unified_footer.php`

- **Dynamic Template System (DB-driven header/footer)**
  - Integration helper: `includes/dynamic_templates.php`
  - OO templates: `templates/dynamic_header.php`, `templates/dynamic_footer.php`
  - Practical include templates: `includes/templates/dynamic_header.php`, `includes/templates/dynamic_footer.php`
  - Admin configuration UI: `admin/dynamic_content_manager.php`
  - DB tables: `dynamic_headers`, `dynamic_footers`, `site_settings`, `site_content`, etc. (detail: `DYNAMIC_TEMPLATE_SYSTEM.md`)

Admin panel में templates/menus भी multiple हैं:

- **Classic Admin Wrapper**
  - `admin/header.php` (includes sidebar markup भी)

- **Updated Admin Wrapper**
  - `admin/updated-admin-wrapper.php` (wraps a content file)
  - `admin/updated-admin-header.php`
  - `admin/updated-admin-sidebar.php`
  - `admin/updated-admin-footer.php`

### 4) Folder-by-Folder Map (कौन सा folder क्यों है)

- **`admin/`**
  - Full admin ERP panel (458+ PHP files documented)
  - Role-wise dashboards (`*_dashboard.php`) + modules: property, CRM, finance, analytics, MLM, etc.
  - Docs:
    - `ADMIN_FUNCTIONALITY_MAPPING.md`
    - `ADMIN_USER_GUIDE.md`
    - `admin/README_ROLE_DASHBOARDS.txt`

- **`app/`**
  - Modern MVC-like codebase
  - `app/controllers/` (Auth, MLM analytics/network, payout controllers)
  - `app/models/`, `app/services/`, `app/views/` (templates)
  - `app/pages/` (page-style PHP templates/content pages)
  - `app/core/` (framework-ish router, request/response, helpers)

- **`routes/`**
  - MVC route registration files
  - `web.php`, `api.php`, `modern.php`

- **`includes/`**
  - Shared utilities + security + DB + template helpers
  - बहुत सारे “system components” इसी folder में हैं: Auth/RBAC/Cache/Email/WhatsApp/MLM managers etc.

- **`public/`**
  - Public web root for MVC pipeline
  - `public/index.php` front controller
  - `public/assets/`, `public/css/`, `public/js/`

- **`assets/`**
  - Legacy/static frontend assets (CSS/JS/images/fonts)

- **`api/`**
  - PHP API endpoints (legacy style)

- **`database/`**
  - SQL/migrations/schema/check tools

- **`cron/` / `scripts/`**
  - automation jobs, utilities

- **`docs/`**
  - project documentation exports

- **`backup/`, `archive/`, `archives/`, `*_archive/`**
  - older versions, cleanup outputs, recovery snapshots
  - (इनमें duplicated/legacy dashboards और files मिलेंगे)

### 5) Dashboard Entry & Role Routing (सब dashboards कहाँ से connect होते हैं)

**Primary entry:** `dashboard.php`

यह file login के बाद “router” की तरह काम करता है:

- **If `$_SESSION['user_logged_in'] === true`**
  - `associates` table में user exists -> `mlm_dashboard.php`
  - else -> `index.php`

- **If `$_SESSION['admin_logged_in'] === true`**
  - `$_SESSION['admin_role']` के हिसाब से redirect -> `admin/<role>_dashboard.php`
  - examples: `admin/superadmin_dashboard.php`, `admin/employee_dashboard.php`, `admin/finance_dashboard.php`, `admin/agent_dashboard.php`, etc.

- **Else if `$_SESSION['user_id']` set**
  - Redirect -> `BASE_URL . 'dashboard/'` (MVC route intended)

- **Else**
  - Redirect -> Home (`BASE_URL`)

### 6) Role-wise Sitemap (High-level)

- **Public Visitor**
  - Home: `index.php`
  - Properties listing/search: `properties.php`, `properties_advanced.php`, `property-details.php` etc.
  - Projects: `projects.php`, `project-detail.php`
  - Static pages: `about.php`, `contact.php`, `services.php`, `career.php`, etc.

- **Customer/User (frontend dashboards)**
  - `user_dashboard.php` (menu config: `includes/config/menu_config.php`)
  - Profile: `profile.php`
  - Property actions: `property-listings.php`, `post-property.php`, etc.

- **Associate / MLM user**
  - `mlm_dashboard.php` (simple MLM dashboard)
  - `associate_dashboard.php` (enterprise associate dashboard; commissions/team/EMI)

- **Agent**
  - `agent_dashboard.php` (agent session based)

- **Employee (frontend style)**
  - `employee_dashboard.php`

- **Admin/Official (admin panel)**
  - Login: `admin/index.php`
  - Main admin dashboard: `admin/dashboard.php`
  - Role dashboards: `admin/*_dashboard.php` (large set)
  - MLM admin suite: `admin/mlm_dashboard.php`, `admin/mlm_commissions.php`, `admin/mlm_payouts.php`, etc.

### 7) What’s “Done” vs “To-Do” (Practical Planning)

**Already present / working blocks (as per codebase + docs):**
- Customer-facing pages + property/catalog flows
- Admin panel with many modules + role dashboards
- MLM core tables/routes/controllers (network tree, payouts, analytics endpoints)
- Dynamic template system (DB-driven header/footer) + admin UI

**Next Steps (recommended sequencing):**
1. **Single Source of Truth for routing**
   - Decide primary path: legacy PHP pages vs `public/index.php` MVC.
   - Align routes: `routes/*.php` vs `app/core/routes.php` vs legacy direct pages.
2. **Session/Role unification**
   - Currently multiple session keys exist (admin/user/associate/customer). Standardize.
3. **Template unification**
   - Choose default: `includes/templates/dynamic_*` (DB-driven) vs `header.php` vs `includes/unified_*`.
4. **Admin sidebar standardization**
   - Choose one: `admin/header.php` inline sidebar vs `updated-admin-*` wrapper.
5. **Security hardening pass**
   - Follow `ADMIN_FUNCTIONALITY_MAPPING.md` security section.
   - Remove debug outputs, review sensitive endpoints.

### 8) “Where to read next” (existing deep docs)

- `PROJECT_MAPPING.md` (page-by-page mapping)
- `SYSTEM_FLOW.md` (flows/diagrams)
- `ROLE_WISE_SITEMAP.md` (role-wise dashboards + menus + connected pages)
- `ADMIN_FUNCTIONALITY_MAPPING.md` (admin modules + risks)
- `DYNAMIC_TEMPLATE_SYSTEM.md` (dynamic header/footer)
- `DEEP_PROJECT_ANALYSIS.md` (architecture overview)
- `FUTURE_PLAN.md` (development roadmap + priorities + what's next)
- `SESSION_AUDIT_REPORT.md` (session key usage audit + migration plan)
- `SESSION_MIGRATION_PROGRESS.md` (migration progress tracking)
- `DASHBOARD_MIGRATION_GUIDE.md` (dashboard migration patterns + examples)

## डेवलपमेंट रोडमैप

1. **फेज 1 (वर्तमान)**: कोर फंक्शनैलिटी और बेसिक UI
2. **फेज 2**: उन्नत सर्च और फिल्टरिंग, यूजर प्रोफाइल एनहांसमेंट
3. **फेज 3**: मोबाइल ऐप इंटीग्रेशन, AI-आधारित प्रॉपर्टी रेकमेंडेशन
4. **फेज 4**: वर्चुअल टूर, AR/VR इंटीग्रेशन

## योगदान

प्रोजेक्ट में योगदान करने के लिए, कृपया निम्नलिखित प्रक्रिया का पालन करें:

1. प्रोजेक्ट को फोर्क करें
2. अपनी फीचर ब्रांच बनाएं (`git checkout -b feature/amazing-feature`)
3. अपने परिवर्तनों को कमिट करें (`git commit -m 'Add some amazing feature'`)
4. ब्रांच को पुश करें (`git push origin feature/amazing-feature`)
5. पुल रिक्वेस्ट खोलें

## संपर्थ

प्रोजेक्ट से संबंधित प्रश्नों के लिए, कृपया संपर्त करें: support@apsdreamhome.com
