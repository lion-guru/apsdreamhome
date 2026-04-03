# APS DREAM HOME - DEPENDENCY & ROUTE ANALYSIS

## DUPLICATE ROUTES FOUND ❌

| Route | Duplicate Count | Fix |
|-------|---------------|-----|
| /faqs | 2 times | Remove one |

---

## CROSS-REFERENCE CHECK

### Views -> Controllers
This script checks if views are calling correct controller methods.

### Key Files to Check:
1. `mlm-dashboard.php` vs `mlm_dashboard.php` - TWO FILES!
2. Views calling `$this->render('pages/xxx')` 
3. Controllers calling models

---

## DUPLICATE FILES FOUND

| File 1 | File 2 | Status |
|--------|--------|--------|
| mlm-dashboard.php | mlm_dashboard.php | NEED TO MERGE |
| ai-dashboard.php | ??? | Check |
| ai-assistant.php | ??? | Check |

---

## ROUTE -> VIEW -> CONTROLLER FLOW

### Example: MLM Dashboard
```
Route: /mlm-dashboard
  → Points to: Front\PageController@mlmDashboard
    → Renders: pages/mlm-dashboard.php
      → But FULL MLM data in: mlm_dashboard.php (552 lines)
```

**Problem:** mlmDashboard() in PageController just renders empty mlm-dashboard.php
**Fix:** Either:
1. Route should point to MLMController@dashboard (renders mlm_dashboard.php)
2. OR PageController@mlmDashboard should render mlm_dashboard.php

---

## NEEDS ATTENTION

### 1. MLM Dashboard Files
- `pages/mlm-dashboard.php` - Small/Empty
- `pages/mlm_dashboard.php` - Full (552 lines)
- Controller: `MLMController.php` exists

### 2. AI Dashboard Files  
- `pages/ai-dashboard.php` - Check size
- Controller: Need route

### 3. AI Assistant Files
- `pages/ai-assistant.php` - Check size

---

## ACTIONS NEEDED

### HIGH PRIORITY
1. Remove duplicate /faqs route
2. Decide: Which MLM dashboard file to keep?
3. Add routes for AI pages

### MEDIUM PRIORITY
4. Check all view render paths
5. Verify controller methods match views

### LOW PRIORITY
6. Clean up unused/duplicate files
