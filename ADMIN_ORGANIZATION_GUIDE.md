# APS Dream Home - Complete Resource Organization Guide

## 📊 Current Statistics

| Category | Count |
|----------|-------|
| Models | 144 |
| Services | 318 |
| Controllers | 166 |
| Total Files | 628 |

---

## 🏷️ ROLE-BASED ACCESS CONTROL (RBAC)

### Available Roles
```php
const ROLE_SUPER_ADMIN = 'super_admin';    // Level 5 - Full Access
const ROLE_ADMIN = 'admin';               // Level 4 - Admin Access
const ROLE_MANAGER = 'manager';           // Level 3 - Manager Access
const ROLE_USER = 'user';                // Level 2 - User Access
const ROLE_ASSOCOCIATE = 'associate';     // Level 1 - Associate Access
const ROLE_GUEST = 'guest';             // Level 0 - Guest Access
```

### Role Permissions Matrix

| Permission | Super Admin | Admin | Manager | User | Associate | Guest |
|------------|-------------|-------|---------|------|-----------|-------|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| User Management | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Property Management | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| System Settings | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Reports | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Banking | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Communication | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ |

---

## 📁 MODELS ORGANIZATION (144 Files)

### Category 1: Core Models (15)
```
app/Models/
├── Model.php                      # Base Model Class
├── User.php                       # User Model
├── Admin.php                      # Admin Model
├── Auth.php                       # Authentication Model
├── UserManager.php                # User Manager Model
├── ConsolidatedUser.php           # Consolidated User Model
├── ConsolidatedProperty.php       # Consolidated Property Model
├── CoreFunctions.php              # Core Functions Model
├── Component.php                  # Component Model
├── Exception.php                  # Exception Model
├── Service.php                    # Service Model
├── Document.php                   # Document Model
├── Download.php                   # Download Model
├── Page.php                       # Page Model
└── SiteSetting.php                # Site Settings Model
```

### Category 2: Property Management (18)
```
app/Models/Property/
├── Property.php                   # Main Property Model
├── Listing.php                    # Property Listing
├── Plot.php                       # Plot Model
├── Project.php                    # Project Model
├── PropertyType.php              # Property Type Model
├── Favorite.php                   # Favorites
├── Inquiry.php                    # Property Inquiry
├── Comparison.php                # Property Comparison
├── Recommendation.php            # Property Recommendation
├── Viewing.php                   # Property Viewing
└── Visit.php                     # Site Visit Model

app/Models/
├── PropertyReview.php             # Property Review
├── ResellProperty.php            # Resell Property
├── ResellPropertyImage.php       # Resell Property Images
├── ProjectEnquiry.php           # Project Enquiry
├── VirtualTour.php              # Virtual Tour
├── VirtualTourAsset.php         # Virtual Tour Assets
└── Portfolio.php                 # Portfolio
```

### Category 3: Lead & CRM (21)
```
app/Models/Lead/
├── Lead.php                      # Main Lead Model
├── CRMLead.php                   # CRM Lead
├── LeadActivity.php             # Lead Activity
├── LeadAssignmentHistory.php    # Assignment History
├── LeadCustomField.php         # Custom Fields
├── LeadCustomFieldValue.php    # Custom Field Values
├── LeadDeal.php                 # Lead Deal
├── LeadFile.php                 # Lead Files
├── LeadNote.php                 # Lead Notes
├── LeadScoring.php              # Lead Scoring
├── LeadSource.php               # Lead Source
├── LeadStatus.php               # Lead Status
├── LeadStatusHistory.php        # Status History
├── LeadTag.php                  # Lead Tags
└── Inquiry.php                  # Inquiry Model

app/Models/
├── MarketingLead.php           # Marketing Lead
├── Feedback.php                # Feedback Model
├── SupportTicket.php           # Support Ticket
├── TicketReply.php             # Ticket Reply
└── NewsletterSubscriber.php    # Newsletter Subscriber
```

### Category 4: User & Employee (25)
```
app/Models/User/
├── User.php                     # Main User Model
├── AgentDetail.php             # Agent Details
├── Customer.php                # Customer Model
├── Profile.php                 # User Profile
├── PublicCustomer.php          # Public Customer
└── ResellUser.php             # Resell User

app/Models/
├── Employee.php                # Employee Model
├── EmployeeAttendance.php      # Attendance
├── EmployeeLeave.php           # Leave Management
├── Associate.php               # Associate Model
├── AssociateMLM.php            # Associate MLM Profile
├── AgentReview.php            # Agent Review
├── BuilderDetail.php         # Builder Details
├── InvestorDetail.php        # Investor Details
├── BankAccount.php            # Bank Account
├── Referral.php               # Referral Model
├── MobileDevice.php          # Mobile Device
├── UserPermission.php        # User Permissions
└── EmailVerification.php      # Email Verification
├── PasswordResetToken.php     # Password Reset
```

### Category 5: Financial & Payment (12)
```
app/Models/Payment/
├── Payment.php                 # Payment Model
├── Invoice.php                # Invoice Model
└── Payout.php                 # Payout Model

app/Models/
├── Booking.php               # Booking Model
├── EMI.php                   # EMI Model
├── Payroll.php               # Payroll Model
├── Budget.php                # Budget Model
├── Expense.php               # Expense Model
├── Tax.php                   # Tax Model
├── FinancialReports.php      # Financial Reports
├── Sale.php                  # Sale Model
├── MortgageInquiry.php       # Mortgage Inquiry
└── Gamification.php          # Gamification (points/rewards)
```

### Category 6: Property & Land (8)
```
app/Models/
├── LandProject.php           # Land Project
├── LandPurchase.php         # Land Purchase
├── AreaAmenity.php          # Area Amenities
├── District.php             # District Model
├── State.php               # State Model
├── Pipeline.php            # Pipeline Model
└── LayoutTemplate.php      # Layout Template
```

### Category 7: HR & Team (10)
```
app/Models/
├── TeamMember.php           # Team Member
├── Shift.php                # Work Shift
├── Leave.php               # Leave Model
├── Training.php            # Training Model
├── Career.php              # Career Model
├── CareerApplication.php   # Job Application
├── JobApplication.php     # Job Application (alt)
├── LegalDocument.php       # Legal Document
├── Feedback.php            # Feedback
└── Feedback_tickets.php   # Feedback Tickets
```

### Category 8: Media & Content (10)
```
app/Models/
├── Gallery.php             # Gallery Model
├── Media.php               # Media Model
├── MediaLibrary.php        # Media Library
├── News.php                # News Model
├── Faq.php                 # FAQ Model
├── About.php               # About Page Model
├── Event.php               # Event Model
├── SeoMetadata.php         # SEO Metadata
├── CustomFeature.php       # Custom Features
└── SavedSearch.php         # Saved Search
```

### Category 9: Marketing & Campaign (5)
```
app/Models/
├── Campaign.php            # Marketing Campaign
├── PushNotification.php    # Push Notification
├── Notification.php        # Notification Model
├── Messaging.php          # Messaging Model
└── TrafficStat.php        # Traffic Statistics
```

### Category 10: AI & Analytics (12)
```
app/Models/
├── AIChatbot.php          # AI Chatbot
├── AIWorkflow.php         # AI Workflow
├── OCR.php               # OCR Model
├── SystemAnalytics.php   # System Analytics
├── Performance.php       # Performance Model
├── PerformanceCache.php  # Performance Cache
├── PerformanceDashboard.php # Performance Dashboard
├── PredictiveAnalytics.php # Predictive Analytics
├── MLMAdvancedAnalytics.php # MLM Analytics
├── ModelIntegration.php  # Model Integration
├── FieldVisit.php       # Field Visit
└── CropRecommendation.php # (if exists)
```

### Category 11: System & Admin (8)
```
app/Models/System/
├── Admin.php              # Admin Model
├── AuditLog.php          # Audit Log
└── SystemAlert.php       # System Alert

app/Models/
├── SystemAnalytics.php   # System Analytics
└── ApiAnalytics.php     # (if exists)
```

---

## 🔧 SERVICES ORGANIZATION (318 Files)

### Category 1: Authentication & User (25)
```
app/Services/Auth/
├── AuthenticationService.php
├── AuthMiddleware.php
├── AuthManager.php
└── TwoFactorAuth.php

app/Services/
├── UserService.php
├── UserManager.php
├── CustomerService.php
├── SocialLoginService.php
├── GoogleAuthService.php
├── OTPService.php
├── ProgressiveRegistrationService.php
├── KYCService.php
└── UserManager/
```

### Category 2: Property Services (30)
```
app/Services/Property/
├── PropertyService.php
├── PropertySubmissionService.php
├── PropertyComparisonService.php
└── VirtualTourService.php

app/Services/
├── PropertyService.php
├── PropertySubmissionService.php
├── PropertyComparisonService.php
├── VirtualTourService.php
├── PropertyRecommendationService.php
├── PropertyValuationService.php
└── (more property services)
```

### Category 3: Lead & CRM Services (20)
```
app/Services/Lead/
├── LeadService.php
├── LeadManagementService.php
├── CleanLeadService.php
└── LeadScoringService.php

app/Services/CRM/
├── CRMService.php
├── LeadAssignmentService.php
├── CampaignDeliveryService.php
└── EngagementService.php
```

### Category 4: MLM & Commission Services (15)
```
app/Services/MLM/
├── CommissionService.php
├── CommissionCalculator.php
├── CommissionAgreementService.php
├── DifferentialCommissionCalculator.php
├── MLMNetworkService.php
├── MLMIncentiveService.php
└── RankService.php
```

### Category 5: AI Services (50+)
```
app/Services/AI/
├── AIService.php
├── AIBackendService.php
├── AIAdvancedAgent.php
├── AIChatbotService.php
├── AIManager.php
├── AIPropertyEngine.php
├── AIRecommendationEngine.php
├── AIValuationEngine.php
├── AIMarketAnalyzer.php
├── AIMarketingAgent.php
├── AICallingAgent.php
├── AITelecallingAgent.php
├── PropertyRecommendationEngine.php
├── PropertyAI.php
├── OllamaClient.php
├── OpenRouterClient.php
├── GeminiAIService.php
├── GeminiService.php
├── AssistantService.php
├── IntegrationService.php
├── LearningSystem.php
├── PersonalitySystem.php
├── WorkflowEngine.php
├── InvestmentManager.php
├── JobManager.php
├── CommunicationManager.php
├── MLIntegrationService.php
├── AIHealthMonitor.php
├── AIToolsManager.php
├── AIEcosystemManager.php
├── AIPropertyEngine.php

app/Services/AI/Agents/
├── AgentInterface.php
├── AgentManager.php
├── BaseAgent.php
├── WhatsAppAgent.php
└── specialized/
    ├── ContentCreationAgent.php
    ├── DataAnalysisAgent.php
    ├── EMICollectionAgent.php
    ├── LeadGenerationAgent.php
    ├── RecommendationAgent.php
    └── ResearchAgent.php

app/Services/AI/nodes/
├── AINode.php
├── BaseNode.php
├── CalendarNode.php
├── DBNode.php
├── EmailNode.php
├── HTTPNode.php
├── LogicNode.php
├── NotificationNode.php
├── PaymentNode.php
├── SMSNode.php
├── SocialMediaNode.php
└── TelecallingNode.php

app/Services/AI/modules/
├── CodeAssistant.php
├── DataAnalyst.php
├── DecisionEngine.php
├── KnowledgeGraph.php
├── NLPProcessor.php
├── RecommendationEngine.php
└── worker.php
```

### Category 6: Payment & Financial (25)
```
app/Services/Payment/
├── PaymentService.php
├── PaymentProcessor.php
├── PaymentGatewayInterface.php
├── RazorpayGateway.php
├── StripeGateway.php
├── EMIAutomationService.php
└── AutoPayoutService.php

app/Services/Finance/
├── BankingService.php
├── ReportService.php
├── PayrollService.php
└── TaxService.php

app/Services/
├── PayoutService.php
├── Payroll/
├── BankingService.php
└── (more financial services)
```

### Category 7: Employee & HR (20)
```
app/Services/HR/
├── HRService.php
├── EmployeeService.php
├── AttendanceService.php
├── LeaveService.php
├── PayrollService.php
└── TrainingService.php

app/Services/
├── EmployeeService.php
├── EmployeeAssignmentService.php
└── (more HR services)
```

### Category 8: Admin & System (30)
```
app/Services/Admin/
├── AdminService.php
├── AdminDashboardService.php
├── AdminDashboardServiceEnhanced.php
└── DashboardService.php

app/Services/
├── SystemLogger.php
├── AlertService.php
├── BackupService.php
├── BackupManager.php
├── ConfigManager.php
├── ConfigurationManager.php
├── FeatureFlagManager.php
├── SiteSettings.php
└── (more admin services)
```

### Category 9: Performance & Caching (20)
```
app/Services/Performance/
├── PerformanceService.php
├── PerformanceConfigService.php
├── ManagerService.php
├── MonitorService.php
├── CacheService.php
├── QueryCacheService.php
├── PHPOptimizerService.php
├── ProfilerService.php
├── AssetOptimizer.php
└── ImageOptimizer.php
```

### Category 10: Marketing & Communication (20)
```
app/Services/Marketing/
├── MarketingService.php
├── MarketingAutomationService.php
├── CampaignService.php
├── EngagementService.php
└── (more marketing services)

app/Services/Communication/
├── EmailService.php
├── SMSService.php
├── NotificationService.php
├── WhatsAppService.php
└── (more communication services)
```

### Category 11: Utility Services (40+)
```
app/Services/Utility/
├── ValidatorService.php
├── FileUploadService.php
├── LocalizationService.php
├── LoggerService.php
├── LoggingService.php
├── RequestService.php
├── RequestMiddlewareService.php
├── TaskService.php
├── SyncService.php
├── DependencyContainer.php
├── UniversalServiceWrapper.php
└── (more utilities)

app/Services/Cleaning/
├── CleanLeadService.php
├── CleanDataService.php
└── (more cleaning services)
```

---

## 🎮 CONTROLLERS ORGANIZATION (166 Files)

### Category 1: Auth Controllers (6)
```
app/Http/Controllers/Auth/
├── AuthController.php
├── AdminAuthController.php
├── CustomerAuthController.php
├── AssociateAuthController.php
├── AgentAuthController.php
└── EmployeeAuthController.php
```

### Category 2: Admin Controllers (40+)
```
app/Http/Controllers/Admin/
├── AdminController.php
├── AdminDashboardController.php
├── AgentDashboardController.php
├── BuilderDashboardController.php
├── CEODashboardController.php
├── CFODashboardController.php
├── CMDashboardController.php
├── AccountingController.php
├── AnalyticsController.php
├── BookingController.php
├── CampaignController.php
├── CareerController.php
├── CommissionController.php
├── CustomerController.php
├── DashboardController.php
├── EMIController.php
├── EngagementController.php
├── LandController.php
├── LayoutController.php
├── LeadController.php
├── LegalPagesController.php
├── MediaController.php
├── NetworkController.php
├── NewsController.php
├── PaymentController.php
├── PayoutController.php
├── PlotController.php
├── PlotManagementController.php
├── ProjectController.php
├── PropertyController.php
├── PropertyManagementController.php
├── SalesController.php
├── SiteController.php
├── SiteSettingsController.php
├── SupportTicketController.php
├── TaskController.php
├── UserController.php
├── VisitController.php
└── AiController.php
```

### Category 3: Property Controllers (8)
```
app/Http/Controllers/Property/
├── PropertyController.php
└── PropertyWorkflowController.php

app/Http/Controllers/
├── PropertyController.php
├── ResellController.php
└── Land/
    └── PlottingController.php
```

### Category 4: Employee Controllers (10)
```
app/Http/Controllers/Employee/
├── EmployeeController.php
├── EmployeeAuthController.php
├── EmployeeDashboardController.php
├── CAController.php
├── HRManagerController.php
├── LandManagerController.php
├── LegalAdvisorController.php
├── TelecallingController.php
├── WorkDistributionController.php
└── (more employee controllers)
```

### Category 5: User & Associate Controllers (8)
```
app/Http/Controllers/User/
├── UserController.php
├── FarmerController.php
├── activity_timeline.php
├── feedback_tickets.php
└── self_service_portal.php

app/Http/Controllers/
├── AssociateController.php
└── Associate/
    └── AssociateController.php
```

### Category 6: API Controllers (25)
```
app/Http/Controllers/Api/
├── MobileApiController.php
├── PropertyController.php
├── ApiLeadController.php
├── AuthController.php
├── ApiEnquiryController.php
├── BankingController.php
├── BaseApiController.php
├── CommunicationController.php
├── FollowupController.php
├── GeminiApiController.php
├── MonitorApiController.php
├── NewsletterController.php
├── NotificationController.php
├── ReferralController.php
├── ReviewController.php
├── SeoController.php
├── SharingController.php
├── SystemController.php
├── TestApiController.php
├── WorkflowController.php
└── api_keys.php
```

### Category 7: Frontend Controllers (15)
```
app/Http/Controllers/Front/
├── PageController.php
└── (public-facing pages)

app/Http/Controllers/
├── HomeController.php
├── BlogController.php
├── CareerController.php
├── FAQController.php
├── GalleryController.php
├── MapController.php
├── MLMController.php
├── RegistrationController.php
├── ResellController.php
├── TestimonialController.php
└── (more frontend controllers)
```

### Category 8: Business Controllers (10)
```
app/Http/Controllers/Business/
└── AssociateController.php

app/Http/Controllers/
├── CampaignController.php
├── EventController.php
├── MarketingController.php
├── Marketing/
│   └── MarketingAutomationController.php
├── NotificationController.php
└── WhatsAppTemplateController.php
```

### Category 9: System Controllers (15)
```
app/Http/Controllers/
├── CoreFunctionsController.php
├── ContainerController.php
├── DashboardController.php
├── LoggingController.php
├── LocalizationController.php
├── PerformanceController.php
├── PerformanceCacheController.php
├── RequestController.php
├── RequestMiddlewareController.php
├── RoleBasedDashboardController.php
├── SecurityController.php
└── System/
    └── CronController.php
```

### Category 10: Reports & Analytics (5)
```
app/Http/Controllers/Reports/
└── ReportController.php

app/Http/Controllers/
├── AnalyticsController.php
├── AIAnalyticsController.php
├── AIDashboardController.php
└── AIValuationController.php
```

### Category 11: Tech & Advanced (15)
```
app/Http/Controllers/Tech/
├── VirtualTourController.php
├── SustainableTechController.php
├── SocialMediaController.php
├── PWAController.php
├── MetaverseController.php
├── IoTController.php
├── EdgeComputingController.php
├── BlockchainController.php
└── AdvancedSecurityController.php

app/Http/Controllers/
├── CustomFeaturesController.php
├── MonitoringController.php
└── TeamManagementController.php
```

### Category 12: Utility Controllers (10)
```
app/Http/Controllers/Utility/
├── TestController.php
├── SystemDiagnosticController.php
├── LanguageController.php
├── ErrorTestController.php
├── DatabaseSeederController.php
├── AdvancedAIController.php
└── AIChatbotController.php

app/Http/Controllers/
├── Async/
│   └── AsyncController.php
├── Backup/
│   └── BackupIntegrityController.php
├── Career/
│   └── CareerController.php
└── Payroll/
    └── SalaryController.php
```

---

## 📋 ROLE-BASED ADMIN MENU STRUCTURE

### SUPER ADMIN Menu (Full Access)
```
┌─────────────────────────────────────────────────────────────┐
│ 🏠 Dashboard                                                │
│    ├── Overview                                            │
│    ├── Analytics                                           │
│    ├── Reports                                             │
│    └── Activity Log                                        │
├─────────────────────────────────────────────────────────────┤
│ 👥 User Management                                         │
│    ├── All Users                                           │
│    ├── Admin Users                                         │
│    ├── Employees                                           │
│    ├── Associates                                         │
│    ├── Agents                                             │
│    ├── Customers                                          │
│    ├── Roles & Permissions                                 │
│    └── Login History                                       │
├─────────────────────────────────────────────────────────────┤
│ 🏘️ Properties                                               │
│    ├── All Properties                                      │
│    ├── Projects                                            │
│    ├── Plots / Land                                       │
│    ├── Residential                                        │
│    ├── Commercial                                         │
│    ├── Featured Properties                                 │
│    ├── Property Types                                      │
│    └── Property Enquiries                                  │
├─────────────────────────────────────────────────────────────┤
│ 🎯 Leads & CRM                                              │
│    ├── All Leads                                          │
│    ├── New Leads                                          │
│    ├── Follow-ups                                         │
│    ├── Customers                                          │
│    ├── Campaigns                                          │
│    ├── Lead Sources                                       │
│    └── Lead Scoring                                       │
├─────────────────────────────────────────────────────────────┤
│ 🌐 MLM Network                                             │
│    ├── Network Tree                                       │
│    ├── Associates                                         │
│    ├── Ranks & Tiers                                      │
│    ├── Commissions                                        │
│    ├── Performance                                        │
│    └── Genealogy                                          │
├─────────────────────────────────────────────────────────────┤
│ 💰 Financial                                                │
│    ├── Bookings                                           │
│    ├── Transactions                                       │
│    ├── Commissions                                       │
│    ├── Invoices                                          │
│    ├── Payments                                          │
│    ├── EMI Management                                    │
│    ├── Expenses                                          │
│    └── Financial Reports                                 │
├─────────────────────────────────────────────────────────────┤
│ 👨‍💼 Team & HR                                                │
│    ├── Staff Members                                      │
│    ├── Attendance                                        │
│    ├── Leaves                                            │
│    ├── Payroll                                          │
│    ├── Roles & Access                                    │
│    ├── Training                                          │
│    └── Documents                                        │
├─────────────────────────────────────────────────────────────┤
│ 📢 Marketing                                                │
│    ├── Campaigns                                         │
│    ├── Email Templates                                   │
│    ├── SMS Templates                                     │
│    ├── WhatsApp Templates                                │
│    ├── Notifications                                     │
│    └── Analytics                                        │
├─────────────────────────────────────────────────────────────┤
│ 📊 Content Management                                       │
│    ├── Media Gallery                                     │
│    ├── Blog & News                                      │
│    ├── Testimonials                                     │
│    ├── FAQ                                              │
│    ├── Pages                                            │
│    └── Sliders                                          │
├─────────────────────────────────────────────────────────────┤
│ 🤖 AI Features                                             │
│    ├── AI Dashboard                                      │
│    ├── Property Valuation                                │
│    ├── Lead Scoring                                     │
│    ├── Recommendations                                   │
│    ├── Chatbot Settings                                 │
│    └── AI Analytics                                     │
├─────────────────────────────────────────────────────────────┤
│ 🛠️ System & Settings                                       │
│    ├── General Settings                                  │
│    ├── API Integrations                                  │
│    ├── Backup & Restore                                 │
│    ├── Security                                         │
│    ├── Cache Management                                 │
│    ├── Logs                                             │
│    ├── Cron Jobs                                        │
│    └── System Health                                    │
└─────────────────────────────────────────────────────────────┘
```

### ADMIN Menu (No System Settings)
```
┌─────────────────────────────────────────────────────────────┐
│ 🏠 Dashboard                                                │
├─────────────────────────────────────────────────────────────┤
│ 👥 User Management (Limited)                                │
├─────────────────────────────────────────────────────────────┤
│ 🏘️ Properties                                               │
├─────────────────────────────────────────────────────────────┤
│ 🎯 Leads & CRM                                              │
├─────────────────────────────────────────────────────────────┤
│ 💰 Financial                                                │
├─────────────────────────────────────────────────────────────┤
│ 👨‍💼 Team & HR                                                │
├─────────────────────────────────────────────────────────────┤
│ 📢 Marketing                                                │
├─────────────────────────────────────────────────────────────┤
│ 📊 Content Management                                       │
└─────────────────────────────────────────────────────────────┘
```

### MANAGER Menu
```
┌─────────────────────────────────────────────────────────────┐
│ 🏠 Dashboard                                                │
├─────────────────────────────────────────────────────────────┤
│ 🏘️ Properties (Assigned)                                    │
├─────────────────────────────────────────────────────────────┤
│ 🎯 Leads (Team Leads)                                       │
├─────────────────────────────────────────────────────────────┤
│ 👥 Team                                                    │
├─────────────────────────────────────────────────────────────┤
│ 📊 Reports                                                 │
└─────────────────────────────────────────────────────────────┘
```

### ASSOCIATE Menu
```
┌─────────────────────────────────────────────────────────────┐
│ 🏠 My Dashboard                                            │
├─────────────────────────────────────────────────────────────┤
│ 🏘️ My Properties                                           │
├─────────────────────────────────────────────────────────────┤
│ 👥 My Clients                                              │
├─────────────────────────────────────────────────────────────┤
│ 💰 My Commissions                                          │
├─────────────────────────────────────────────────────────────┤
│ 🎯 My Leads                                               │
├─────────────────────────────────────────────────────────────┤
│ 📊 My Performance                                          │
└─────────────────────────────────────────────────────────────┘
```

### EMPLOYEE Menu
```
┌─────────────────────────────────────────────────────────────┐
│ 🏠 My Dashboard                                            │
├─────────────────────────────────────────────────────────────┤
│ 📋 My Tasks                                               │
├─────────────────────────────────────────────────────────────┤
│ ⏰ Attendance                                             │
├─────────────────────────────────────────────────────────────┤
│ 📅 My Leave                                               │
├─────────────────────────────────────────────────────────────┤
│ 👥 My Team                                                │
└─────────────────────────────────────────────────────────────┘
```

---

## 🏗️ PROPOSED FOLDER STRUCTURE

```
app/
├── Http/
│   └── Controllers/
│       └── Admin/
│           ├── AdminController.php          # Base Admin Controller
│           ├── SuperAdminController.php     # Super Admin Only
│           ├── AdminController.php          # Regular Admin
│           ├── ManagerController.php        # Manager
│           ├── AssociateController.php      # Associate
│           └── EmployeeController.php      # Employee
│
├── Models/
│   └── Admin/
│       ├── User.php
│       ├── Property.php
│       ├── Lead.php
│       └── ...
│
├── Services/
│   └── Admin/
│       ├── AdminService.php
│       ├── DashboardService.php
│       └── ...
│
├── views/
│   └── admin/
│       ├── layouts/
│       │   ├── superadmin.php    # Super Admin Layout
│       │   ├── admin.php         # Admin Layout
│       │   ├── manager.php       # Manager Layout
│       │   ├── associate.php     # Associate Layout
│       │   └── employee.php      # Employee Layout
│       │
│       ├── sidebar/
│       │   ├── superadmin_sidebar.php
│       │   ├── admin_sidebar.php
│       │   ├── manager_sidebar.php
│       │   ├── associate_sidebar.php
│       │   └── employee_sidebar.php
│       │
│       ├── header/
│       │   ├── superadmin_header.php
│       │   ├── admin_header.php
│       │   ├── manager_header.php
│       │   ├── associate_header.php
│       │   └── employee_header.php
│       │
│       ├── superadmin/
│       │   ├── dashboard.php
│       │   ├── users/
│       │   ├── properties/
│       │   ├── leads/
│       │   ├── mlm/
│       │   ├── financial/
│       │   ├── hr/
│       │   ├── marketing/
│       │   ├── content/
│       │   ├── ai/
│       │   └── system/
│       │
│       ├── admin/
│       │   ├── dashboard.php
│       │   ├── users/
│       │   ├── properties/
│       │   ├── leads/
│       │   ├── financial/
│       │   ├── hr/
│       │   ├── marketing/
│       │   └── content/
│       │
│       ├── manager/
│       │   ├── dashboard.php
│       │   ├── team/
│       │   ├── properties/
│       │   ├── leads/
│       │   └── reports/
│       │
│       ├── associate/
│       │   ├── dashboard.php
│       │   ├── properties/
│       │   ├── clients/
│       │   ├── commissions/
│       │   └── leads/
│       │
│       └── employee/
│           ├── dashboard.php
│           ├── tasks/
│           ├── attendance/
│           └── leave/
```

---

## 🔄 HOW TO ORGANIZE

### Step 1: Create Admin Module Structure
```bash
mkdir -p app/Modules/Admin/
mkdir -p app/Modules/Admin/Controllers/
mkdir -p app/Modules/Admin/Models/
mkdir -p app/Modules/Admin/Services/
mkdir -p app/Modules/Admin/views/
```

### Step 2: Create Role-Based Layouts
- Create separate layouts for each role
- Each layout has its own sidebar and header
- Sidebar items are based on role permissions

### Step 3: Update Controllers
- Extend role-specific base controllers
- Use RBACManager for permission checks
- Load role-specific data

### Step 4: Update Routes
```php
// Super Admin Only Routes
$router->group(['middleware' => 'role:super_admin'], function() {
    // System settings routes
    // User management routes
    // Backup routes
});

// Admin Routes
$router->group(['middleware' => 'role:admin'], function() {
    // Content management routes
    // Marketing routes
});

// Manager Routes
$router->group(['middleware' => 'role:manager'], function() {
    // Team management routes
    // Reports routes
});
```

---

## 📝 IMPLEMENTATION CHECKLIST

### Phase 1: Core Structure
- [ ] Create Admin Base Controller
- [ ] Create Role-Based Layouts
- [ ] Create Role-Based Sidebars
- [ ] Create Role-Based Headers

### Phase 2: Controllers
- [ ] Refactor Admin Controllers
- [ ] Add RBAC Middleware
- [ ] Create Role-Specific Controllers

### Phase 3: Views
- [ ] Create Super Admin Views
- [ ] Create Admin Views
- [ ] Create Manager Views
- [ ] Create Associate Views
- [ ] Create Employee Views

### Phase 4: Services
- [ ] Create Admin Services
- [ ] Create Role-Specific Services
- [ ] Update Existing Services

### Phase 5: Testing
- [ ] Test RBAC Permissions
- [ ] Test Role-Based Views
- [ ] Test Admin Functions

---

## 🎯 QUICK REFERENCE: Models to Services to Controllers

| Model | Service | Controller | Role Access |
|-------|---------|------------|-------------|
| User | UserService | Admin/UserController | Super Admin, Admin |
| Property | PropertyService | Admin/PropertyController | All except Guest |
| Lead | LeadService | Admin/LeadController | Admin, Manager, Associate |
| Booking | BookingService | Admin/BookingController | Admin, Finance |
| Commission | CommissionService | Admin/CommissionController | Admin, Associate |
| Employee | EmployeeService | Admin/EmployeeController | Super Admin, HR |
| Campaign | CampaignService | Admin/CampaignController | Admin, Marketing |
| Payment | PaymentService | Admin/PaymentController | Admin, Finance |
| Document | DocumentService | Admin/DocumentController | All |
| Notification | NotificationService | Admin/NotificationController | All |

---

## 🔧 UTILITY CLASSES NEEDED

### 1. AdminMenuHelper
```php
class AdminMenuHelper {
    public static function getMenuByRole($role) { }
    public static function hasAccess($role, $menuItem) { }
    public static function getSidebarItems($role) { }
    public static function getHeaderMenu($role) { }
}
```

### 2. AdminBreadcrumb
```php
class AdminBreadcrumb {
    public static function add($title, $url) { }
    public static function render() { }
    public static function clear() { }
}
```

### 3. AdminWidget
```php
class AdminWidget {
    public static function card($title, $value, $icon, $color) { }
    public static function chart($type, $data) { }
    public static function table($headers, $rows) { }
    public static function stat($label, $value, $change) { }
}
```

---

## 📞 SUMMARY

This document provides a complete organization of:
- 144 Models organized into 11 categories
- 318 Services organized into 11 categories
- 166 Controllers organized into 12 categories
- Role-based menu structure for 5 roles
- Proposed folder structure for clean organization
- Implementation checklist

All resources are now categorized and can be easily accessed based on:
1. User Role (RBAC)
2. Module Type (Property, Lead, User, etc.)
3. Functionality (CRUD, Reports, Analytics, etc.)
