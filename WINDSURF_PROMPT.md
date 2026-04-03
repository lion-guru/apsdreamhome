# APS Dream Home - Implementation Prompt for WindSurf

## Project Overview
APS Dream Home is a Real Estate CRM platform built with PHP MVC pattern. Database already has 637 tables with full schema ready.

## Current Status
- ✅ Core pages working (Home, About, Register, Login, Admin, AI Valuation)
- ✅ Database fully structured (637 tables)
- ⚠️ Many features planned but NOT IMPLEMENTED

---

## FEATURES TO IMPLEMENT

### 1. MLM (Multi-Level Marketing) Dashboard
**Tables Ready:** mlm_associates, mlm_commissions, mlm_payouts, mlm_network_tree, mlm_levels, mlm_plans, etc.

**Required:**
- Dashboard showing network tree
- Commission tracking and calculation
- Payout requests and processing
- Associate registration with referral system
- Level/_rank system visualization
- Reference: `app/views/pages/mlm_dashboard.php` (has "coming soon" message at line 30)

### 2. AI Dashboard & Assistant
**Tables Ready:** ai_chat_history, ai_chatbot_config, ai_conversation_states, ai_knowledge_base, ai_learning_progress, etc.

**Required:**
- AI chatbot integration
- Knowledge base management
- User learning progress tracking
- AI-powered property recommendations
- Reference: `app/views/pages/ai-dashboard.php` and `app/views/pages/ai-assistant.php` (both show "coming soon")

### 3. Virtual Tour Feature
**Table Ready:** ar_vr_tours

**Required:**
- 360° property tours
- AR/VR integration
- Property walkthrough
- Reference: `app/views/pages/virtual_tour.php` (TODO at line 3)

### 4. WhatsApp Integration
**Tables Ready:** whatsapp_automation_config, whatsapp_campaigns, whatsapp_messages, whatsapp_templates

**Required:**
- WhatsApp message templates
- Campaign automation
- Auto-responses
- Template management
- Reference: `app/views/pages/whatsapp-templates.php` (TODO at line 3)

### 5. Email System Implementation
**Tables Ready:** email_config, email_logs, email_queue, email_tracking, notification_templates

**Required:**
- Email verification on registration
- Password reset emails
- Notification emails
- Email queue processing
- Reference: `app/Http/Controllers/AuthController.php` (TODO at lines 350, 361)

### 6. Two-Factor Authentication (2FA)
**Tables Ready:** two_factor_tokens, otp_verifications

**Required:**
- 2FA setup during registration/login
- OTP generation and verification
- 2FA disable option
- Reference: `app/Http/Controllers/AuthController.php` (TODO at lines 375, 389)

### 7. System Logs & Monitoring
**Tables Ready:** system_logs, audit_log, error_logs, security_logs

**Required:**
- Admin activity logging
- Error tracking dashboard
- Security audit trail
- System health monitoring
- Reference: `app/views/layouts/admin_footer.php` (line 186: "System Logs feature coming soon!")

### 8. Meeting Scheduler
**Tables Ready:** associate_appointments, employee_shifts

**Required:**
- Schedule meetings with leads/customers
- Calendar view
- Reminder notifications
- Reference: `app/views/dashboard/cm_dashboard.php` (line 405: "Meeting scheduler coming soon!")

### 9. Bank/Payment Integration
**Tables Ready:** banking_details, bank_transactions, payment_gateway_config

**Required:**
- Bank account linking
- Payment gateway integration
- Transaction history
- Auto-payment processing for EMI

### 10. Analytics Dashboard
**Tables Ready:** analytics_dashboards, analytics_reports, analytics_events, analytics_summary

**Required:**
- Real-time analytics
- Custom dashboard widgets
- Performance metrics
- Export reports
- Reference: `app/views/pages/analytics.php` (TODO at line 3)

### 11. Careers/Job Portal
**Tables Ready:** careers, job_applications

**Required:**
- Job listings
- Application form
- Application tracking
- Reference: `app/views/pages/careers.php` (TODO at line 3)

### 12. Property Booking & Contact
**Tables Ready:** bookings, property_bookings, contact_requests

**Required:**
- Complete booking flow
- Contact form submission
- Booking confirmation emails
- Reference: `app/views/pages/properties/book_plot.php` and `app/views/pages/properties/list.php`

### 13. Associate/Affiliate Management
**Tables Ready:** associates, associate_details, associate_achievements, associate_training_modules

**Required:**
- Associate registration
- Training module progress
- Achievement badges
- Performance tracking

---

## EXISTING WORKING FEATURES (DO NOT BREAK)
- User registration (Customer, Associate, Agent)
- User login with role-based access
- Admin dashboard
- Property listing and details
- AI Property Valuation
- Customer dashboard
- EMI calculator

---

## DATABASE CONNECTION
```php
Host: 127.0.0.1
Port: 3307
Database: apsdreamhome
User: root
Password: (empty)
```

## IMPORTANT NOTES
1. All tables are already created in database - just implement the functionality
2. Use existing MVC pattern and coding style
3. Follow security best practices
4. Mobile responsive design
5. Error handling required in all new files

## START WITH
1. MLM Dashboard (most important for business)
2. Then AI features
3. Then supporting features

---

## EXAMPLE WORKFLOW - MLM Dashboard
```
1. Create Controller: app/Http/Controllers/MLM/MLMDashboardController.php
2. Create Views: app/views/mlm/dashboard.php, network_tree.php, commissions.php, payouts.php
3. Add Routes in routes/web.php
4. Test with existing mlm_associates data
5. Implement commission calculation
6. Implement payout request flow
```

---

## OUTPUT FILES TO CREATE
1. Controllers in `app/Http/Controllers/`
2. Views in `app/views/`
3. Models in `app/Models/` (if needed)
4. Routes in `routes/web.php`
5. Update navigation/layouts

Good luck! 🚀
