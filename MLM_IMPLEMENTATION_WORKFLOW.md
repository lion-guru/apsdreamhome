# 🚀 APS Dream Homes - MLM Referral System Implementation Workflow

## 📋 Systematic Step-by-Step Plan

### **Phase 1: Foundation Setup (Priority: HIGH)**

#### **Step 1.1: Database Schema Creation**
**Files to Create:**
- `database/mlm_schema.sql`
- `database/migration_script.php`

**Commands:**
```sql
-- Run this SQL to create MLM structure
-- Located at: database/mlm_schema.sql
```

**Verification:**
- [ ] Check tables created successfully
- [ ] Verify foreign key relationships
- [ ] Test sample data insertion

#### **Step 1.2: Archive Legacy Systems**
**Files to Archive:**
```bash
# Archive these files to backups/mlm_archive_$(date +%Y%m%d)/
- register.php
- login.php
- agent_registration.php
- builder_registration.php
- associate_dir/associate_registration.php
- All *_old.php files
- All *_backup* files
```

**Archive Command:**
```bash
mkdir -p backups/mlm_archive_$(date +%Y%m%d)
mv register.php login.php agent_registration.php builder_registration.php backups/mlm_archive_$(date +%Y%m%d)/
```

### **Phase 2: Core System Development (Priority: HIGH)**

#### **Step 2.1: Unified Registration System**
**File:** `app/views/auth/register.php`
**Features:**
- Role selection (customer/agent/associate/builder/investor)
- Mandatory referral code
- Dynamic form fields based on role
- Real-time validation

#### **Step 2.2: Referral Code Generator**
**File:** `app/helpers/ReferralCodeGenerator.php`
```php
class ReferralCodeGenerator {
    public static function generate($user_type, $name) {
        // Generate unique codes like: CUST1234, AGENT5678, ASSOC9012
    }
}
```

#### **Step 2.3: Network Tree Builder**
**File:** `app/models/NetworkTree.php`
```php
class NetworkTree {
    public function addUser($user_id, $sponsor_id) {
        // Build network tree up to 5 levels
    }
}
```

### **Phase 3: Commission System (Priority: MEDIUM)**

#### **Step 3.1: Commission Calculator**
**File:** `app/services/CommissionCalculator.php`
```php
class CommissionCalculator {
    public function calculate($user_id, $sale_amount, $levels = 5) {
        // Multi-level commission calculation
    }
}
```

#### **Step 3.2: Commission Tracking**
**File:** `app/models/Commission.php`
- Track all commission types
- Real-time calculation
- Payment status management

### **Phase 4: Dashboard & UI (Priority: MEDIUM)**

#### **Step 4.1: Network Dashboard**
**File:** `app/views/user/network_dashboard.php`
- Network tree visualization
- Commission summary
- Referral links
- Team statistics

#### **Step 4.2: Admin Dashboard**
**File:** `app/views/admin/mlm_dashboard.php`
- Network overview
- Commission management
- User verification
- Payment processing

### **Phase 5: Testing & Deployment (Priority: MEDIUM)**

#### **Step 5.1: Testing Checklist**
- [ ] Test all user type registrations
- [ ] Verify referral code generation
- [ ] Test commission calculations
- [ ] Check network tree building
- [ ] Test edge cases (circular references, etc.)

#### **Step 5.2: Deployment Script**
**File:** `deploy_mlm_system.sh`
```bash
#!/bin/bash
# Automated deployment script
# Usage: ./deploy_mlm_system.sh

# 1. Backup current system
# 2. Apply database changes
# 3. Update file permissions
# 4. Clear cache
# 5. Run tests
```

## 🎯 Daily Work Checklist

### **Day 1: Database & Archive**
- [ ] Run database schema creation
- [ ] Archive legacy files
- [ ] Test database connections

### **Day 2: Registration System**
- [ ] Create unified registration form
- [ ] Implement referral code generator
- [ ] Add role-based dynamic fields

### **Day 3: Commission System**
- [ ] Build commission calculator
- [ ] Create commission tracking
- [ ] Test commission calculations

### **Day 4: Dashboards**
- [ ] Create user network dashboard
- [ ] Create admin MLM dashboard
- [ ] Add network visualization

### **Day 5: Testing & Deployment**
- [ ] Run comprehensive tests
- [ ] Deploy to staging
- [ ] Monitor for issues

## 📁 File Structure After Implementation

```
apsdreamhome/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php (unified registration)
│   │   ├── MLMController.php (network management)
│   │   └── CommissionController.php
│   ├── models/
│   │   ├── User.php (enhanced with MLM)
│   │   ├── NetworkTree.php
│   │   └── Commission.php
│   ├── views/
│   │   ├── auth/
│   │   │   └── register.php (unified form)
│   │   ├── user/
│   │   │   └── network_dashboard.php
│   │   └── admin/
│   │       └── mlm_dashboard.php
│   └── helpers/
│       └── ReferralCodeGenerator.php
├── database/
│   ├── mlm_schema.sql
│   └── migration_script.php
├── backups/
│   └── mlm_archive_20241113/
└── docs/
    └── MLM_SYSTEM_GUIDE.md
```

## 🔧 Quick Commands for Any AI Agent

### **Start Any Phase:**
```bash
# Phase 1: Database
php database/migration_script.php

# Phase 2: Registration
php app/controllers/AuthController.php --test-registration

# Phase 3: Commission Test
php app/services/CommissionCalculator.php --test-calculation
```

### **Status Check:**
```bash
# Check system status
php system_check.php --mlm-status

# Verify database tables
php database_check.php --tables
```

## 📋 Progress Tracking

**Current Status:**
- ✅ Analysis Complete
- ⏳ Phase 1: Database Setup (Next)
- ⏳ Phase 2: Registration System
- ⏳ Phase 3: Commission System
- ⏳ Phase 4: Dashboards
- ⏳ Phase 5: Testing & Deployment

**Next Action:** Start with Phase 1 - Database Schema Creation

## 🔄 Agent Handoff Instructions

**For Future AI Agents:**
1. **Always check**: `MLM_IMPLEMENTATION_WORKFLOW.md` first
2. **Current phase**: Check todo list status
3. **Next step**: Follow the numbered phases in order
4. **Testing**: Use provided test commands
5. **Documentation**: Update this file with progress

**Ready to begin Phase 1?**
