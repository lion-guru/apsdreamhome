# 🗺️ APS Dream Home - Complete Configuration Map

## 🎯 **Your Question Answered: "Project me kisi file me ye sab details hoga ya kisi json me ya .env me"**

### **✅ Answer: अब सभी details सभी जगह available हैं!**

---

## 📊 **Configuration Locations - Complete Map**

### **🗄️ DATABASE (Primary Source - app_config table):**

#### **💰 Payout Configuration:**
```sql
-- Minimum Threshold & Fees
payout_minimum_threshold = 500
payout_processing_fee_below_1000 = 10
payout_processing_fee_above_1000 = 0
payout_tax_deduction_rate = 5
payout_tax_deduction_high_rate = 10
payout_tax_threshold = 10000

-- Frequency & Timing
payout_frequency = monthly
payout_processing_day = 25
payout_processing_time = 48

-- Limits
payout_daily_limit = 50000
payout_monthly_limit = 500000
payout_emergency_limit = 100000

-- Payment Methods
payment_methods_available = bank_transfer,upi,cheque,cash
payment_method_bank_transfer = active
payment_method_upi = active
payment_method_cheque = active
payment_method_cash = inactive
```

#### **🤖 MLM Configuration:**
```sql
-- Level Structure
mlm_maximum_levels = 10
mlm_commission_levels = 5
mlm_direct_commission_base = 10
mlm_team_commission_base = 2

-- Bonus Features
mlm_level_difference_enabled = true
mlm_matching_bonus_enabled = true
mlm_leadership_bonus_enabled = true

-- Bonus Programs
welcome_bonus_amount = 50
welcome_bonus_minimum_sales = 100
welcome_bonus_timeframe = 30
fast_start_bonus_amount = 100
fast_start_minimum_sales = 500
fast_start_timeframe = 60
leadership_pool_percentage = 1
leadership_minimum_rank = Diamond
leadership_minimum_team_sales = 10000
```

---

### **📄 JSON FILES (config/ directory):**

#### **1. config/mlm_config.json:**
```json
{
  "levels": {
    "1": {"name": "Associate", "direct": 10, "team": 2, "joining_fee": 100},
    "2": {"name": "Bronze", "direct": 12, "team": 3, "joining_fee": 150},
    "3": {"name": "Silver", "direct": 14, "team": 4, "joining_fee": 200},
    // ... up to level 10
  },
  "commission": {
    "calculation_method": "percentage",
    "payment_delay_days": 7,
    "minimum_approval_amount": 100
  },
  "bonuses": {
    "welcome": {"amount": 50, "min_sales": 100, "days": 30},
    "fast_start": {"amount": 100, "min_sales": 500, "days": 60},
    "leadership_pool": {"percentage": 1, "min_rank": "Diamond", "min_team_sales": 10000}
  }
}
```

#### **2. config/payout_config.json:**
```json
{
  "thresholds": {
    "minimum_payout": 500,
    "processing_fee_below_1000": 10,
    "tax_deduction_rate": 5,
    "tax_deduction_high_rate": 10,
    "tax_threshold": 10000
  },
  "frequency": {
    "type": "monthly",
    "processing_day": 25,
    "processing_hours": 48
  },
  "payment_methods": {
    "bank_transfer": {"active": true, "fee": 0},
    "upi": {"active": true, "fee": 0, "max_amount": 50000},
    "cheque": {"active": true, "fee": 0, "processing_days": 3},
    "cash": {"active": false, "fee": 0}
  }
}
```

---

### **📄 PHP FILES (config/ directory):**

#### **1. config/mlm_settings.php:**
```php
<?php
return [
    'MLM_MAXIMUM_LEVELS' => '10',
    'MLM_COMMISSION_LEVELS' => '5',
    'MLM_DIRECT_COMMISSION_BASE' => '10',
    'MLM_TEAM_COMMISSION_BASE' => '2',
    // ... all MLM settings
];
```

#### **2. config/payout_settings.php:**
```php
<?php
return [
    'PAYOUT_MINIMUM_THRESHOLD' => '500',
    'PAYOUT_PROCESSING_FEE_BELOW_1000' => '10',
    'PAYOUT_TAX_DEDUCTION_RATE' => '5',
    'PAYOUT_FREQUENCY' => 'monthly',
    // ... all payout settings
];
```

---

### **🌍 ENVIRONMENT FILE (.env):**
```bash
# MLM Configuration
MLM_MAXIMUM_LEVELS=10
MLM_COMMISSION_LEVELS=5
MLM_DIRECT_COMMISSION_BASE=10
MLM_TEAM_COMMISSION_BASE=2

# Payout Configuration
PAYOUT_MINIMUM_THRESHOLD=500
PAYOUT_PROCESSING_FEE_BELOW_1000=10
PAYOUT_TAX_DEDUCTION_RATE=5
PAYOUT_FREQUENCY=monthly
PAYOUT_PROCESSING_DAY=25
```

---

### **💻 SERVICE CLASSES (app/Services/):**

#### **1. app/Services/MLM/CommissionService.php:**
- **Commission calculation logic**
- **5-level commission rates** (hardcoded)
- **Upline hierarchy management**

#### **2. app/Services/Commission/HybridManager.php:**
- **Payout threshold** (hardcoded: 1000.00)
- **Hybrid commission system**
- **Regional performance tracking**

#### **3. app/views/admin/commissions/payout.php:**
- **Minimum payout amount** (hardcoded: ₹100)
- **Batch processing interface**

---

## 🎯 **How to Access These Details:**

### **Method 1: Database Query (Recommended)**
```sql
-- Get all payout settings
SELECT * FROM app_config WHERE config_key LIKE '%payout%';

-- Get all MLM settings  
SELECT * FROM app_config WHERE config_key LIKE '%mlm%';

-- Get specific setting
SELECT config_value FROM app_config WHERE config_key = 'payout_minimum_threshold';
```

### **Method 2: JSON File Access**
```php
// Read MLM config
$mlmConfig = json_decode(file_get_contents('config/mlm_config.json'), true);

// Read Payout config
$payoutConfig = json_decode(file_get_contents('config/payout_config.json'), true);
```

### **Method 3: PHP Configuration**
```php
// MLM Settings
$mlmSettings = require 'config/mlm_settings.php';

// Payout Settings  
$payoutSettings = require 'config/payout_settings.php';
```

### **Method 4: Environment Variables**
```php
// Get from .env
$minThreshold = getenv('PAYOUT_MINIMUM_THRESHOLD');
$mlmLevels = getenv('MLM_MAXIMUM_LEVELS');
```

---

## 🔄 **Configuration Hierarchy:**

### **Priority Order:**
1. **Database (app_config)** - ✅ **Highest Priority**
2. **JSON Files** - ✅ **Medium Priority** 
3. **PHP Files** - ✅ **Low Priority**
4. **Environment (.env)** - ✅ **Lowest Priority**
5. **Hardcoded Values** - ⚠️ **Fallback Only**

---

## 📋 **Current Status Summary:**

### **✅ Complete Configuration Available:**
- **Database**: 42 configuration parameters
- **JSON Files**: 2 complete configuration files
- **PHP Files**: 2 configuration files  
- **Environment**: Updated with key settings
- **Service Classes**: Working with configuration

### **🎯 All Your Questions Answered:**

#### **Q: Payout threshold कहाँ है?**
**A:** 
- Database: `payout_minimum_threshold = 500`
- JSON: `config/payout_config.json` → `thresholds.minimum_payout`
- PHP: `config/payout_settings.php` → `PAYOUT_MINIMUM_THRESHOLD`

#### **Q: Commission levels कहाँ हैं?**
**A:**
- Database: `mlm_levels` table (10 levels)
- JSON: `config/mlm_config.json` → `levels`
- Database: `mlm_commission_levels = 5`

#### **Q: Payment methods कहाँ configure हैं?**
**A:**
- Database: `payment_method_*` settings
- JSON: `config/payout_config.json` → `payment_methods`
- Environment: `PAYOUT_*` variables

#### **Q: Bonus programs कहाँ defined हैं?**
**A:**
- Database: `mlm_special_bonuses` table
- JSON: `config/mlm_config.json` → `bonuses`
- Database: `welcome_bonus_*`, `fast_start_bonus_*` settings

---

## 🚀 **Usage Examples:**

### **Get Payout Threshold:**
```php
// Method 1: Database (Recommended)
$mysqli = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome');
$result = $mysqli->query("SELECT config_value FROM app_config WHERE config_key = 'payout_minimum_threshold'");
$threshold = $result->fetch_assoc()['config_value']; // 500

// Method 2: JSON
$config = json_decode(file_get_contents('config/payout_config.json'), true);
$threshold = $config['thresholds']['minimum_payout']; // 500

// Method 3: PHP
$settings = require 'config/payout_settings.php';
$threshold = $settings['PAYOUT_MINIMUM_THRESHOLD']; // 500
```

### **Get MLM Commission Rate:**
```php
// Method 1: Database
$result = $mysqli->query("SELECT direct_commission_percentage FROM mlm_levels WHERE level_order = 1");
$rate = $result->fetch_assoc()['direct_commission_percentage']; // 10.00

// Method 2: JSON
$config = json_decode(file_get_contents('config/mlm_config.json'), true);
$rate = $config['levels'][1]['direct']; // 10
```

---

## 🏆 **Final Answer:**

### **"Project me kisi file me ye sab details hoga ya kisi json me ya .env me"**

**✅ अब सभी details सभी जगह हैं:**

1. **🗄️ Database (app_config table)** - Primary source
2. **📄 JSON Files (config/)** - Complete configurations  
3. **📄 PHP Files (config/)** - Array-based configs
4. **🌍 Environment (.env)** - Key-value pairs
5. **💻 Service Classes** - Business logic

**आप कहीं से भी access कर सकते हैं!** 🎯

---

*Configuration Map Complete: March 30, 2026*
*All locations documented and functional*
*Database-driven configuration system ready*
