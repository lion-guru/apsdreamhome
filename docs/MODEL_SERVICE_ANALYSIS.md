# Model & Service Analysis Report
## APS Dream Home - Codebase Architecture Analysis

---

## 1. EXISTING MODELS (73 Total)

### 📦 Property & Real Estate Models
- `Property\Property.php` - Main property model with search, filters, admin methods
- `Property\Project.php` - Project/colony model
- `Property\Plot.php` - Plot model
- `Property\Listing.php` - Property listings
- `Property\PropertyType.php` - Property types
- `Property\Favorite.php` - User favorites
- `Property\Inquiry.php` - Property inquiries
- `Property\Comparison.php` - Property comparisons
- `Property\Visit.php` - Property visits
- `Property\Viewing.php` - Property viewings
- `Property\Recommendation.php` - Property recommendations
- `ConsolidatedProperty.php` - Consolidated property data

### 📅 Booking & Transaction Models
- `Booking.php` - Bookings with admin filters, pagination
- `Payment\Payment.php` - Payment model
- `Payment\Invoice.php` - Invoice model
- `Payment\Payout.php` - Payout model
- `EMI.php` - EMI calculations
- `Sale.php` - Sales model
- `FieldVisit.php` - Field visits
- `Visit.php` - General visits

### 👥 User & Customer Models
- `User.php` - Main user model (extends UnifiedModel)
- `User\User.php` - User model (extends Model)
- `User\Customer.php` - Customer model
- `User\PublicCustomer.php` - Public customer
- `User\ResellUser.php` - Resell user
- `User\Profile.php` - Customer profile
- `User\AgentDetail.php` - Agent details
- `ConsolidatedUser.php` - Consolidated user data
- `Associate.php` - Associate model
- `AssociateMLM.php` - MLM associate model
- `Admin.php` - Admin model
- `System\Admin.php` - System admin
- `Employee.php` - Employee model
- `BuilderDetail.php` - Builder details
- `InvestorDetail.php` - Investor details
- `AgentReview.php` - Agent reviews

### 🎯 Lead Management Models
- `Lead\Lead.php` - Main lead model
- `Lead\CRMLead.php` - CRM lead
- `Lead\Inquiry.php` - Lead inquiries
- `Lead\LeadActivity.php` - Lead activities
- `Lead\LeadAssignmentHistory.php` - Assignment history
- `Lead\LeadCustomField.php` - Custom fields
- `Lead\LeadCustomFieldValue.php` - Custom field values
- `Lead\LeadDeal.php` - Lead deals
- `Lead\LeadFile.php` - Lead files
- `Lead\LeadNote.php` - Lead notes
- `Lead\LeadScoring.php` - Lead scoring
- `Lead\LeadSource.php` - Lead sources
- `Lead\LeadStatus.php` - Lead statuses
- `Lead\LeadStatusHistory.php` - Status history
- `Lead\LeadTag.php` - Lead tags
- `MarketingLead.php` - Marketing leads
- `ProjectEnquiry.php` - Project enquiries
- `MortgageInquiry.php` - Mortgage enquiries

### 📍 Location Models
- `State.php` - States
- `District.php` - Districts
- `AreaAmenity.php` - Area amenities
- `LandProject.php` - Land projects
- `LandPurchase.php` - Land purchases

### 📊 Dashboard & Analytics Models
- `AdminDashboard.php` - Admin dashboard data
- `PerformanceDashboard.php` - Performance dashboard
- `Performance.php` - Performance metrics
- `PerformanceCache.php` - Performance cache
- `FinancialReports.php` - Financial reports
- `SystemAnalytics.php` - System analytics
- `TrafficStat.php` - Traffic statistics
- `MLMAdvancedAnalytics.php` - MLM analytics
- `PredictiveAnalytics.php` - Predictive analytics

### 💰 Financial Models
- `Budget.php` - Budget model
- `Expense.php` - Expense tracking
- `BankAccount.php` - Bank accounts
- `Tax.php` - Tax model
- `Payroll.php` - Payroll model
- `Payout.php` - Payout model

### 🏢 HR & Employee Models
- `Employee.php` - Employee model
- `EmployeeAttendance.php` - Attendance
- `EmployeeLeave.php` - Leave management
- `Shift.php` - Shift management
- `TeamMember.php` - Team members
- `Career.php` - Careers
- `CareerApplication.php` - Career applications
- `JobApplication.php` - Job applications

### 📰 Content Models
- `Gallery.php` - Gallery images
- `Media.php` - Media library
- `MediaLibrary.php` - Media library
- `News.php` - News articles
- `Page.php` - CMS pages
- `About.php` - About page
- `Faq.php` - FAQs
- `Blog.php` - Blog posts
- `Document.php` - Documents
- `LegalDocument.php` - Legal documents
- `Download.php` - Downloads

### 🎨 UI & Layout Models
- `LayoutTemplate.php` - Layout templates
- `Component.php` - UI components
- `CustomFeature.php` - Custom features

### 🔔 Notification Models
- `Notification.php` - Notifications
- `PushNotification.php` - Push notifications
- `EmailVerification.php` - Email verification
- `PasswordResetToken.php` - Password reset tokens

### 🎮 Gamification Models
- `Gamification.php` - Gamification features

### 🤖 AI Models
- `AIChatbot.php` - AI chatbot
- `AIWorkflow.php` - AI workflows

### 🛠️ Utility Models
- `Auth.php` - Authentication
- `SiteSetting.php` - Site settings
- `SeoMetadata.php` - SEO metadata
- `SavedSearch.php` - Saved searches
- `Feedback.php` - Feedback
- `SupportTicket.php` - Support tickets
- `TicketReply.php` - Ticket replies
- `Campaign.php` - Campaigns
- `Event.php` - Events
- `Farmer.php` - Farmers
- `FarmerLandHolding.php` - Farmer land holdings
- `VirtualTour.php` - Virtual tours
- `VirtualTourAsset.php` - Virtual tour assets
- `MobileDevice.php` - Mobile devices
- `Messaging.php` - Messaging
- `OCR.php` - OCR processing
- `Exception.php` - Exception handling
- `Portfolio.php` - Portfolio
- `Pipeline.php` - Pipeline
- `Referral.php` - Referrals
- `UserPermission.php` - User permissions
- `System\SystemAlert.php` - System alerts
- `System\AuditLog.php` - Audit logs
- `Service.php` - Services

---

## 2. EXISTING SERVICES (61 Total)

### 🤖 AI Services (40+)
- `AI/AIBackendService.php` - AI backend service
- `AI/AIBackendEnhancedService.php` - Enhanced AI backend
- `AI/AIBackendFixedService.php` - Fixed AI backend
- `AI/AIManager.php` - AI manager
- `AI/AIPropertyEngine.php` - AI property engine
- `AI/AIRecommendationEngine.php` - Recommendation engine
- `AI/AIPropertyRecommendationService.php` - Property recommendations
- `AI/AIPropertyValuationEngine.php` - Property valuation
- `AI/AIMarketAnalyzer.php` - Market analyzer
- `AI/AITelecallingAgent.php` - Telecalling agent
- `AI/AIMarketingAgent.php` - Marketing agent
- `AI/AICallingAgent.php` - Calling agent
- `AI/AdvancedAIBot.php` - Advanced AI bot
- `AI/AssistantService.php` - AI assistant
- `AI/CommunicationManager.php` - Communication manager
- `AI/IntegrationService.php` - Integration service
- `AI/InvestmentManager.php` - Investment manager
- `AI/JobManager.php` - Job manager
- `AI/LearningSystem.php` - Learning system
- `AI/MLIntegrationService.php` - ML integration
- `AI/OllamaClient.php` - Ollama client
- `AI/OpenRouterClient.php` - OpenRouter client
- `AI/PersonalitySystem.php` - Personality system
- `AI/PropertyAI.php` - Property AI
- `AI/WorkflowEngine.php` - Workflow engine
- `AI/AIEcosystemManager.php` - Ecosystem manager
- `AI/AIHealthMonitor.php` - AI health monitor
- `AI/Agents/` - Agent system (BaseAgent, AgentManager, WhatsAppAgent, specialized agents)
- `AI/modules/` - AI modules (CodeAssistant, DataAnalyst, DecisionEngine, KnowledgeGraph, NLPProcessor, RecommendationEngine)

### 👤 User Services
- `UserService.php` - User service
- `User/UserService.php` - User service (user namespace)
- `SocialLoginService.php` - Social login
- `VisitorTrackingService.php` - Visitor tracking
- `AuthenticationService.php` - Authentication (in Auth namespace)

### 📋 Task & Support Services
- `TaskService.php` - Task management
- `SupportTicketService.php` - Support tickets

### 🛠️ Utility Services
- `Utility/FileService.php` - File operations
- `Utility/AlertManagerService.php` - Alert manager
- `Utility/AlertEscalationService.php` - Alert escalation
- `ValidatorService.php` - Validation
- `UniversalServiceWrapper.php` - Universal wrapper
- `SyncService.php` - Sync service
- `SiteVisitService.php` - Site visits

### 🎓 Training Services
- `Training/TrainingService.php` - Training service

### 🧪 Testing Services
- `Testing/TestimonialsPreviewService.php` - Testimonials preview
- `Testing/Public/HomeSimpleService.php` - Home simple service

---

## 3. CONTROLLERS USING DIRECT DATABASE QUERIES (Problem Areas)

### ❌ Admin Controllers with Direct DB Queries:
- `Admin/BookingController.php` - Uses `$this->db->query()` for properties, customers, associates
- `Admin/VisitController.php` - Uses `$this->db->query()` for properties, customers, associates (repeated 3x)
- `Admin/UserPropertyController.php` - Uses `$this->db->query()`
- `Admin/TestimonialsAdminController.php` - Uses `$this->db->query()`
- `Admin/TaskController.php` - Uses `$this->db->query()` for users (repeated 3x)
- `Admin/SupportTicketController.php` - Uses `$this->db->query()` for customers, agents (repeated 2x)
- `Admin/SiteController.php` - Uses `$this->db->query()`
- `Admin/ProjectsAdminController.php` - Uses `Database::getInstance()` directly
- `Admin/PlotsAdminController.php` - Uses `$this->db->query()` extensively
- `Admin/PlotCostController.php` - Uses `Database::getInstance()`
- `Admin/LocationAdminController.php` - Uses `$this->db->query()` for states, districts (repeated 7x)
- `Admin/LegalPagesController.php` - Uses `Database::getInstance()`
- `Admin/LeadScoringController.php` - Uses `Database::getInstance()`
- `Admin/JobsAdminController.php` - Uses `Database::getInstance()` and `$this->db->query()`
- `Admin/DealController.php` - Uses `Database::getInstance()`
- `Admin/EngagementController.php` - Uses `$this->db->query()` (4x)
- `Admin/CFODashboardController.php` - Uses `$this->db->query()` for analytics
- `Admin/BuilderDashboardController.php` - Uses `$this->db->query()` for analytics

### ❌ Other Controllers with Direct DB Queries:
- `WalletController.php` - Uses `Database::getInstance()`
- `VisitorTrackingController.php` - Uses `Database::getInstance()`
- `User/FarmerController.php` - Uses `Database::getInstance()`
- `Tech/IoTController.php` - Uses `Database::getInstance()`
- `Tech/BlockchainController.php` - Uses `Database::getInstance()`
- `TeamManagementController.php` - Uses `Database::getInstance()`
- `RoleBasedDashboardController.php` - Uses `Database::getInstance()`
- `RequestController.php` - Uses `Database::getInstance()` (4x)
- `Property/CompareController.php` - Uses `Database::getInstance()`
- `MLM/MLMDashboardController.php` - Uses `Database::getInstance()`
- `Property/PropertyWorkflowController.php` - Uses `Database::getInstance()`
- `MarketingController.php` - Uses `Database::getInstance()` (4x)
- `LoggingController.php` - Uses `Database::getInstance()` (4x)

---

## 4. COMMON PATTERNS THAT SHOULD USE MODELS/SERVICES

### 🔄 Repetitive Pattern #1: Getting Users by Role
**Current Code (repeated 10+ times):**
```php
$customers = $this->db->query("SELECT id, name, email FROM users WHERE role = 'customer' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
$associates = $this->db->query("SELECT id, name, email FROM users WHERE role = 'associate' AND status = 'active' ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
$agents = $this->db->query("SELECT id, name, email FROM users WHERE role IN ('admin', 'support', 'associate') ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
```

**Should Use:** `UserService` or `User` model methods:
```php
$customers = UserService::getCustomers();
$associates = UserService::getActiveAssociates();
$agents = UserService::getAgents();
```

### 🔄 Repetitive Pattern #2: Getting Properties
**Current Code (repeated 10+ times):**
```php
$properties = $this->db->query("SELECT id, title, location FROM properties ORDER BY title")->fetchAll(\PDO::FETCH_ASSOC);
```

**Should Use:** `Property` model:
```php
$properties = Property::getAll(['id', 'title', 'location']);
```

### 🔄 Repetitive Pattern #3: Getting States/Districts
**Current Code (repeated 7+ times in LocationAdminController):**
```php
$states = $this->db->query("SELECT * FROM states WHERE is_active = 1 ORDER BY name")->fetchAll(\PDO::FETCH_ASSOC);
$districts = $this->db->query("SELECT d.*, s.name as state_name FROM districts d LEFT JOIN states s ON d.state_id = s.id WHERE d.is_active = 1 ORDER BY s.name, d.name")->fetchAll(\PDO::FETCH_ASSOC);
```

**Should Use:** `State` and `District` models:
```php
$states = State::getActive();
$districts = District::getActiveWithState();
```

### 🔄 Repetitive Pattern #4: Dashboard Analytics
**Current Code (repeated in multiple dashboard controllers):**
```php
$analytics = $this->db->query("SELECT ...")->fetchAll();
$breakdown = $this->db->query("SELECT ...")->fetchAll();
```

**Should Use:** `DashboardService` or `AnalyticsService`:
```php
$analytics = DashboardService::getAnalytics($type);
$breakdown = DashboardService::getBreakdown($type);
```

### 🔄 Repetitive Pattern #5: Pagination
**Current Code (repeated in many controllers):**
```php
$page = $_GET['page'] ?? 1;
$per_page = $_GET['per_page'] ?? 10;
$offset = ($page - 1) * $per_page;
$limit = "LIMIT $offset, $per_page";
```

**Should Use:** `PaginationService`:
```php
$pagination = PaginationService::getPagination($total, $page, $per_page);
```

### 🔄 Repetitive Pattern #6: Filters
**Current Code (repeated in many controllers):**
```php
$where = [];
$params = [];
if (!empty($filters['search'])) {
    $where[] = "title LIKE :search";
    $params['search'] = '%' . $filters['search'] . '%';
}
```

**Should Use:** `FilterService` or Model scopes:
```php
$query = Property::query();
$query = FilterService::applyFilters($query, $filters);
```

### 🔄 Repetitive Pattern #7: Stats Counting
**Current Code (repeated in many controllers):**
```php
$total = $this->db->query("SELECT COUNT(*) as count FROM bookings")->fetch()['count'];
$confirmed = $this->db->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")->fetch()['count'];
```

**Should Use:** Model methods:
```php
$total = Booking::count();
$confirmed = Booking::where('status', 'confirmed')->count();
```

---

## 5. RECOMMENDATIONS FOR REFACTORING

### 🎯 Priority 1: Create Common Service Classes

#### 1. DashboardDataService
```php
class DashboardDataService
{
    public static function getBookingStats($filters = [])
    public static function getPropertyStats($filters = [])
    public static function getLeadStats($filters = [])
    public static function getRevenueStats($filters = [])
    public static function getTeamPerformanceStats($filters = [])
}
```

#### 2. FilterService
```php
class FilterService
{
    public static function applySearchFilters($query, $searchTerm, $columns)
    public static function applyDateFilters($query, $dateFrom, $dateTo)
    public static function applyStatusFilter($query, $status)
    public static function buildWhereClause($filters)
}
```

#### 3. PaginationService
```php
class PaginationService
{
    public static function getPagination($total, $page, $per_page)
    public static function getOffset($page, $per_page)
    public static function getTotalPages($total, $per_page)
}
```

#### 4. FormSelectDataService
```php
class FormSelectDataService
{
    public static function getCustomers($filters = [])
    public static function getAssociates($filters = [])
    public static function getAgents($filters = [])
    public static function getProperties($filters = [])
    public static function getStates($filters = [])
    public static function getDistricts($filters = [])
}
```

### 🎯 Priority 2: Enhance Existing Models

#### 1. Property Model - Add Missing Methods
```php
class Property extends Model
{
    public static function getForSelect($columns = ['id', 'title'])
    public static function getActive()
    public static function getFeatured()
    public static function getByType($type)
    public static function getByLocation($location)
    public static function getByPriceRange($min, $max)
}
```

#### 2. User Model - Add Missing Methods
```php
class User extends Model
{
    public static function getCustomers($status = 'active')
    public static function getAssociates($status = 'active')
    public static function getAgents($status = 'active')
    public static function getAdmins()
    public static function getEmployees()
    public static function getForSelect($role, $columns = ['id', 'name', 'email'])
}
```

#### 3. State/District Models - Add Missing Methods
```php
class State extends Model
{
    public static function getActive()
    public static function getForSelect()
    public static function getWithDistricts()
}

class District extends Model
{
    public static function getActive()
    public static function getForSelect()
    public static function getByState($stateId)
    public static function getWithStateName()
}
```

### 🎯 Priority 3: Refactor Admin Controllers

#### Controllers to Refactor (High Priority):
1. `Admin/BookingController.php` - Use Booking model + UserService
2. `Admin/VisitController.php` - Use Visit model + UserService + Property model
3. `Admin/TaskController.php` - Use TaskService + UserService
4. `Admin/SupportTicketController.php` - Use SupportTicket model + UserService
5. `Admin/LocationAdminController.php` - Use State/District models
6. `Admin/PlotsAdminController.php` - Use Plot model + State/District models
7. `Admin/ProjectsAdminController.php` - Use Project model
8. `Admin/EngagementController.php` - Use EngagementService

### 🎯 Priority 4: Create Repository Pattern (Optional)

For complex queries, consider creating Repository classes:
```php
class BookingRepository
{
    public function getAdminBookings($filters)
    public function getBookingStats($filters)
    public function getRecentBookings($limit)
}

class PropertyRepository
{
    public function getAdminProperties($filters)
    public function getPropertyStats($filters)
    public function searchProperties($filters)
}
```

---

## 6. WHERE TO USE MODELS vs SERVICES vs UTILITIES

### 📦 Use MODELS for:
- **Database table operations** (CRUD)
- **Table-specific queries**
- **Model relationships**
- **Validation rules specific to model**
- **Model-specific scopes/filters**

**Examples:**
- `Property::find($id)`
- `User::where('role', 'customer')->get()`
- `Booking::create($data)`
- `$property->images()->get()`

### 🔧 Use SERVICES for:
- **Business logic** (complex operations)
- **Cross-model operations**
- **External API integrations**
- **Calculations and transformations**
- **Dashboard data aggregation**
- **Email/SMS notifications**
- **File operations**

**Examples:**
- `BookingService::processBooking($data)` - Complex booking logic
- `DashboardService::getAnalytics($type)` - Data aggregation
- `EmailService::sendBookingConfirmation($booking)` - Email logic
- `FileService::uploadPropertyImage($file)` - File operations
- `PaymentService::processPayment($booking, $amount)` - Payment logic

### 🛠️ Use UTILITIES for:
- **Helper functions**
- **Common formatters**
- **Validation helpers**
- **Date/time helpers**
- **String manipulation**
- **Array operations**

**Examples:**
- `DateHelper::formatDate($date)`
- `StringHelper::slugify($text)`
- `ValidationHelper::validateEmail($email)`
- `ArrayHelper::sortArray($array, $key)`

### 🎨 Use COMPONENTS for:
- **UI widgets**
- **Form components**
- **Display components**
- **Reusable view partials**

**Examples:**
- `SelectComponent::render($options, $selected)`
- `TableComponent::render($data, $columns)`
- `FormComponent::render($fields)`

---

## 7. ACTION PLAN

### Phase 1: Create Common Services (Week 1)
1. Create `DashboardDataService`
2. Create `FormSelectDataService`
3. Create `FilterService`
4. Create `PaginationService`
5. Test services with existing data

### Phase 2: Enhance Existing Models (Week 2)
1. Add common methods to Property model
2. Add common methods to User model
3. Add common methods to State/District models
4. Add common methods to Booking model
5. Test model methods

### Phase 3: Refactor High-Priority Controllers (Week 3-4)
1. Refactor `Admin/BookingController.php`
2. Refactor `Admin/VisitController.php`
3. Refactor `Admin/TaskController.php`
4. Refactor `Admin/SupportTicketController.php`
5. Refactor `Admin/LocationAdminController.php`
6. Test refactored controllers

### Phase 4: Refactor Remaining Controllers (Week 5-6)
1. Refactor remaining admin controllers
2. Refactor other controllers with direct DB queries
3. Create repository classes if needed
4. Test all refactored code

### Phase 5: Documentation & Training (Week 7)
1. Update coding standards document
2. Create model/service usage guide
3. Train team on new patterns
4. Code review guidelines

---

## 8. SUMMARY

**Total Models Available:** 73
**Total Services Available:** 61
**Controllers Using Direct DB Queries:** 25+
**Repetitive Code Patterns Identified:** 7

**Key Findings:**
1. ✅ Rich model ecosystem exists (73 models)
2. ✅ Comprehensive service layer exists (61 services, mostly AI)
3. ❌ Many controllers bypass models and use direct DB queries
4. ❌ Common patterns repeated across 25+ controllers
5. ❌ Missing common services for: Dashboard data, Form selects, Filters, Pagination

**Recommendation:**
- Leverage existing models instead of direct DB queries
- Create common services for repeated patterns
- Refactor controllers to use models/services
- Follow MVC pattern strictly
- Use services for business logic, models for data access
