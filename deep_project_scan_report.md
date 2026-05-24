# APS Dream Home - Maximum Level Deep Project Scan Report

**Scan Date:** 2026-05-23 17:44:50
**Project Path:** C:\xampp\htdocs\apsdreamhome\tools/..

## Executive Summary

- **Total Files Scanned:** 46605
- **PHP Files:** 128
- **Database Tables:** 819
- **Critical Issues:** 12
- **High Priority Issues:** 91
- **Medium Priority Issues:** 12
- **Low Priority Issues:** 92

## Php Deprecated Usage

### [MEDIUM] scripts/find_duplicates.php

**Issue:** Deprecated function usage: each(

### [MEDIUM] tools/deep_project_scanner.php

**Issue:** Deprecated function usage: mysql_

### [MEDIUM] tools/deep_project_scanner.php

**Issue:** Deprecated function usage: ereg

### [MEDIUM] tools/deep_project_scanner.php

**Issue:** Deprecated function usage: split(

### [MEDIUM] tools/deep_project_scanner.php

**Issue:** Deprecated function usage: each(

## Php Security Issues

### [HIGH] config/import_mcp_config_handler.php

**Issue:** Direct use of $_GET/$_POST without sanitization

### [CRITICAL] scripts/user_consolidation.php

**Issue:** Potential SQL injection vulnerability

### [CRITICAL] tools/check_all_gallery_tables.php

**Issue:** Potential hardcoded password detected

### [CRITICAL] tools/check_associate_users.php

**Issue:** Potential hardcoded password detected

### [CRITICAL] tools/check_associates.php

**Issue:** Potential hardcoded password detected

### [CRITICAL] tools/check_associates_structure.php

**Issue:** Potential hardcoded password detected

### [CRITICAL] tools/check_bookings_tables.php

**Issue:** Potential hardcoded password detected

### [CRITICAL] tools/check_existing_associates.php

**Issue:** Potential hardcoded password detected

### [CRITICAL] tools/check_gallery_images.php

**Issue:** Potential hardcoded password detected

### [CRITICAL] tools/check_users_table.php

**Issue:** Potential hardcoded password detected

### [CRITICAL] tools/deep_project_scanner.php

**Issue:** Potential SQL injection vulnerability

### [CRITICAL] tools/setup_rbac_permissions.php

**Issue:** Potential SQL injection vulnerability

### [CRITICAL] tools/test_param_routes.php

**Issue:** Potential SQL injection vulnerability

## Database Connection Error

### [MEDIUM] **Issue:** Unknown issue

### [MEDIUM] **Issue:** Unknown issue

## Controller Issues

### [LOW] app/Http/Controllers/Admin/AIAggregatorController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AIAnalyticsController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AICallingController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AIChatbotController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AISettingsController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AccountingController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ActivityLogController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AdManagerController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AdminDashboardController.php

**Issue:** Unconventional parent class: AdminBaseController

### [LOW] app/Http/Controllers/Admin/AdminFileController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AdminLoyaltyController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AdminProfileController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AdminSchedulerController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AdminWorkflowController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AgentDashboardController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AiController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AjaxController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/AnalyticsController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ApiKeyController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/BackupController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/BlogController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/BookingController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/BuilderDashboardController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/CEODashboardController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/CFODashboardController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/CampaignController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/CareerController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ColonyController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/CommissionAdminController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/CommissionController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/CustomerController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/DealController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/DocumentController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/EMIController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/EngagementController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ErpDashboardController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ExpensesController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/GalleryController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/InquiryController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/JobsAdminController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/KhatabookSalesController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/LandController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/LeadFollowUpController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/LeadScoringController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/LegalPagesController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/LoanController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/MLMController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/MLMSettingsController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/MediaController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/MeetingController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/NetworkController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/NewsController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/NewsletterAdminController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/PagesController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/PaymentController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/PayoutController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/PlotCostController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/PlotManagementController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ProjectController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ProjectsAdminController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/PropertyController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/PropertyManagementController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ReferralController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ReportController.php

**Issue:** Unconventional parent class: AdminBaseController

### [LOW] app/Http/Controllers/Admin/SalesController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/ServiceController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/SiteController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/SiteSettingsController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/SocialMediaController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/SupportTicketController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/TaskController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/TeamController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/TestimonialController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/TestimonialsAdminController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/UserController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/UserPropertyController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Admin/VisitController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Analytics/AdminReportsController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Analytics/AdvancedAnalyticsController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Analytics/ReportController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Api/AnalyticsController.php

**Issue:** Unconventional parent class: BaseApiController

### [LOW] app/Http/Controllers/Api/AuthController.php

**Issue:** Unconventional parent class: BaseApiController

### [LOW] app/Http/Controllers/Api/BankingController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Api/CommunicationController.php

**Issue:** Unconventional parent class: BaseApiController

### [LOW] app/Http/Controllers/Api/FollowupController.php

**Issue:** Unconventional parent class: BaseApiController

### [LOW] app/Http/Controllers/Api/KYCController.php

**Issue:** Unconventional parent class: BaseApiController

### [LOW] app/Http/Controllers/Api/ReferralController.php

**Issue:** Unconventional parent class: BaseApiController

### [LOW] app/Http/Controllers/Api/ReviewController.php

**Issue:** Unconventional parent class: BaseApiController

### [LOW] app/Http/Controllers/Api/SeoController.php

**Issue:** Unconventional parent class: BaseApiController

### [LOW] app/Http/Controllers/Api/SharingController.php

**Issue:** Unconventional parent class: BaseApiController

### [LOW] app/Http/Controllers/Utility/DatabaseSeederController.php

**Issue:** Unconventional parent class: AdminController

### [LOW] app/Http/Controllers/Utility/SystemDiagnosticController.php

**Issue:** Unconventional parent class: AdminController

## Configuration Issues

### [MEDIUM] .env

**Issue:** Debug mode may be enabled

### [MEDIUM] .env.example

**Issue:** Debug mode may be enabled

### [MEDIUM] config/app.php

**Issue:** Configuration file missing

## Security Issues

### [MEDIUM] **Issue:** Git repository exposed

**Recommendation:** Ensure .git is in .gitignore and not deployed

### [HIGH] .env

**Issue:** Sensitive file may be accessible

**Recommendation:** Ensure proper .htaccess or web server configuration

### [HIGH] config/database.php

**Issue:** Sensitive file may be accessible

**Recommendation:** Ensure proper .htaccess or web server configuration

### [HIGH] composer.json

**Issue:** Sensitive file may be accessible

**Recommendation:** Ensure proper .htaccess or web server configuration

### [HIGH] app/views/pages/booking_confirmation.php

**Issue:** Potential XSS vulnerability - unescaped variable output

### [HIGH] app/views/pages/booking_pay.php

**Issue:** Potential XSS vulnerability - unescaped variable output

### [HIGH] app/views/pages/colony_plots.php

**Issue:** Potential XSS vulnerability - unescaped variable output

### [HIGH] app/views/pages/interior_design.php

**Issue:** Potential XSS vulnerability - unescaped variable output

### [HIGH] app/views/pages/news.php

**Issue:** Potential XSS vulnerability - unescaped variable output

### [HIGH] app/views/pages/plot_detail.php

**Issue:** Potential XSS vulnerability - unescaped variable output

### [HIGH] app/views/pages/rera_lookup.php

**Issue:** Potential XSS vulnerability - unescaped variable output

### [HIGH] app/views/pages/user_bookings.php

**Issue:** Potential XSS vulnerability - unescaped variable output

### [HIGH] app/views/pages/user_dashboard.php

**Issue:** Potential XSS vulnerability - unescaped variable output

### [HIGH] app/Http/Controllers/AI/AssistantController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/AI/PropertyValuationController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AIChatbotController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AISettingsController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AdManagerController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AdminFileController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AdminLoyaltyController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AdminMenuPermissionController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AdminProfileController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AdminSchedulerController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AdminWorkflowController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AiController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/AjaxController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/ApiKeyController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/CampaignController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/ColonyController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/CommissionController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/DealController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/DocumentController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/GalleryController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/GodModeController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/InquiryController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/JobsAdminController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/LayoutController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/LeadFollowUpController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/LegalPagesController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/LocationAdminController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/MLMRealEstateController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/MLMSettingsController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/PagesController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/PlotCostController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/PlotsAdminController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/ProjectsAdminController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/PropertyImageController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/ServiceController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/TeamController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/TestimonialsAdminController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Admin/UserPropertyController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Agent/AgentDashboardController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Agent/MainController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/ApiLeadController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/BaseApiController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/CommunicationController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/GeminiChatbotController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/KYCController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/MobileApiController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/NewsletterController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/PaymentGatewayController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/SearchController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Api/TestApiController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Associate/AssociateController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Auth/GoogleAuthController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Auth/QuickAuthController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Employee/EmployeeController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Front/AIBotController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Front/PageController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Front/PlotController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Front/UserController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/MLM/MLMDashboardController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Payment/AdvancedPaymentController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Payment/PaymentGatewayController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Property/CompareController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Property/PropertyWorkflowController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Reports/ReportController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Tech/AdvancedSecurityController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Tech/BlockchainController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Tech/EdgeComputingController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Tech/IoTController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Tech/MetaverseController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Tech/PWAController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Tech/SocialMediaController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Tech/SustainableTechController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Tech/VirtualTourController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/User/FarmerController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/User/UserController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Utility/AdvancedAIController.php

**Issue:** POST method without CSRF protection

### [HIGH] app/Http/Controllers/Utility/LanguageController.php

**Issue:** POST method without CSRF protection

## Opencode References

### [MEDIUM] **Issue:** Unknown issue

## Environment Issues

### [HIGH] **Issue:** Required environment variable DB_DATABASE not set

