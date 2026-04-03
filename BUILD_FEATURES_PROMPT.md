# PROMPT FOR AI AGENT - APS DREAM HOME FEATURES

## CONTEXT
APS Dream Home is a Real Estate CRM project in PHP MVC. Database already has tables created for planned features, but pages/controllers are missing. You need to BUILD complete functionality.

**Project Location:** `C:\xampp\htdocs\apsdreamhome`
**Database:** MySQL on `127.0.0.1:3307`, database `apsdreamhome`
**Base URL:** `http://localhost/apsdreamhome`

---

## TASK 1: PROPERTY COMPARISON SYSTEM

### Database Tables Ready:
- `property_comparisons` - Comparison records
- `property_comparison_sessions` - User comparison sessions
- `properties` - Already has 71 properties

### Build:
1. **Route:** `/compare` - Comparison page
2. **Controller:** Create `CompareController` or add to `PropertyController`
3. **Views:** `app/views/properties/compare.php`

### Functionality:
- Select 2-4 properties to compare
- Show side-by-side comparison of:
  - Price
  - Location
  - Area/size
  - Bedrooms/bathrooms
  - Amenities
  - RERA status
  - Status (available/sold)
- Save comparison session for logged-in users
- Share comparison link

### Files to Create/Modify:
- `app/Http/Controllers/Property/CompareController.php`
- `app/views/properties/compare.php`
- `routes/web.php` - Add route
- `app/Models/PropertyComparison.php` (optional)

---

## TASK 2: AI PROPERTY VALUATION

### Database Tables Ready:
- `property_valuations` - Valuation records
- `properties` - Property data

### Build:
1. **Route:** `/ai-valuation` or `/property-valuation`
2. **Controller:** `AIController@propertyValuation` or create `ValuationController`
3. **Views:** `app/views/pages/ai-valuation.php` (partially exists)

### Functionality:
- Enter property details (location, area, type, bedrooms)
- OR select from existing properties
- Show estimated market value
- Show price per sq ft
- Show similar property prices
- Show price trends
- Generate valuation report
- Save valuation to `property_valuations` table

### Files to Create/Modify:
- Update `app/views/pages/ai-valuation.php`
- `app/Http/Controllers/AIController.php` - Add `propertyValuation` method
- `routes/web.php` - Add route
- `app/Services/AI/PropertyValuationEngine.php` - Already exists, use it

---

## TASK 3: LEAD SCORING SYSTEM

### Database Tables Ready:
- `leads` - 99 leads with data
- `lead_scoring` - Scoring rules
- `lead_scoring_history` - Score history
- `lead_scoring_models` - Different scoring models
- `lead_scoring_rules` - Individual rules
- `lead_engagement_metrics` - Engagement data

### Build:
1. **Route:** `/admin/leads/scoring` - Scoring dashboard
2. **Controller:** `Admin\LeadScoringController` or add to `Admin\LeadController`
3. **Views:** `app/views/admin/leads/scoring.php`

### Functionality:
- View all leads with scores (0-100)
- Score breakdown:
  - Budget match (30%)
  - Location preference (20%)
  - Property type match (20%)
  - Engagement level (15%)
  - Source quality (15%)
- Color coding: Green (>70), Yellow (40-70), Red (<40)
- Filter by score range
- Auto-calculate scores for new leads
- Score history chart
- Export leads by score

### Files to Create/Modify:
- `app/Http/Controllers/Admin/LeadScoringController.php`
- `app/views/admin/leads/scoring.php`
- `app/Services/Lead/LeadScoringService.php`
- `routes/web.php` - Add route

---

## TASK 4: SITE VISIT TRACKING

### Database Tables Ready:
- `property_visits` - Visit records
- `lead_visits` - Leads visits
- `leads` - Lead data
- `properties` - Property data

### Build:
1. **Route:** `/admin/visits` - Visit management
2. **Controller:** `Admin\VisitController` (may already exist, check)
3. **Views:** `app/views/admin/visits/` folder

### Functionality:
- Schedule site visits
  - Select lead
  - Select property
  - Date/time
  - Assigned agent
  - Notes
- View visit calendar
- Visit status: Scheduled, Completed, Cancelled, No-show
- Visit outcomes:
  - Interested
  - Not interested
  - Need more time
- Link to WhatsApp for confirmation
- Visit history per lead
- Visit statistics

### Files to Create/Modify:
- `app/Http/Controllers/Admin/VisitController.php`
- `app/views/admin/visits/index.php`
- `app/views/admin/visits/create.php`
- `app/views/admin/visits/calendar.php`
- `routes/web.php` - Add route if new controller

---

## TASK 5: LEAD DOCUMENTS/FILES

### Database Tables Ready:
- `lead_files` - File attachments

### Build:
1. **Route:** `/admin/leads/{id}/files`
2. **Controller:** Add to `Admin\LeadController`
3. **Views:** Inline in lead detail view

### Functionality:
- Upload documents for leads:
  - ID proof
  - Address proof
  - Income proof
  - Property documents
- File types: PDF, JPG, PNG (max 5MB)
- Store in `storage/app/lead_files/`
- View/download files
- Delete files
- Track upload date and uploader

### Files to Create/Modify:
- Update `app/Http/Controllers/Admin/LeadController.php`
- Update `app/views/admin/leads/show.php`

---

## TASK 6: DEAL TRACKING

### Database Tables Ready:
- `lead_deals` - Deal records
- `leads` - Lead data
- `properties` - Property data

### Build:
1. **Route:** `/admin/deals`
2. **Controller:** `Admin\DealController`
3. **Views:** `app/views/admin/deals/`

### Functionality:
- Create deals from leads
  - Select lead
  - Select property
  - Deal value
  - Expected close date
  - Stage: Lead → Qualified → Proposal → Negotiation → Won/Lost
- Deal pipeline kanban view
- Deal value by stage
- Win/loss ratio
- Average deal cycle time
- Link to commission calculation

### Files to Create/Modify:
- `app/Http/Controllers/Admin/DealController.php`
- `app/views/admin/deals/index.php`
- `app/views/admin/deals/kanban.php`
- `routes/web.php` - Add route

---

## TASK 7: USER ACHIEVEMENT SYSTEM (Optional - Lower Priority)

### Database Tables Ready:
- `user_achievements` - Achievement definitions
- `user_badges` - Badge definitions
- `user_points` - Points tracking

### Build:
1. **Route:** `/dashboard/achievements`
2. **Controller:** Add to `DashboardController`
3. **Views:** `app/views/dashboard/achievements.php`

### Functionality:
- Points for actions:
  - Registration: 100 points
  - First property view: 50 points
  - Enquiry: 25 points
  - Booking: 500 points
  - Referral signup: 200 points
- Badges:
  - New Member
  - Property Explorer
  - Serious Buyer
  - Deal Closer
- Leaderboard
- Points redemption

---

## CODING STANDARDS

### 1. Database Connection
```php
$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getConnection();

// Or use PDO directly
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
```

### 2. Controller Pattern
```php
<?php
namespace App\Http\Controllers\Admin;

class DealController extends AdminBaseController
{
    public function index()
    {
        $deals = $this->db->fetchAll("SELECT * FROM lead_deals ORDER BY created_at DESC");
        $this->render('admin/deals/index', ['deals' => $deals]);
    }
    
    public function store()
    {
        // Handle POST
        $data = [
            'lead_id' => $_POST['lead_id'] ?? 0,
            'property_id' => $_POST['property_id'] ?? 0,
            'deal_value' => $_POST['deal_value'] ?? 0,
            'stage' => $_POST['stage'] ?? 'lead',
            'expected_close_date' => $_POST['expected_close_date'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('lead_deals', $data);
        header('Location: /admin/deals');
        exit;
    }
}
```

### 3. View Pattern
```php
<?php
// app/views/admin/deals/index.php
$page_title = 'Deal Management';
include __DIR__ . '/../layouts/header.php';
?>

<div class="container-fluid">
    <h1>Deals</h1>
    
    <div class="row">
        <?php foreach (['lead', 'qualified', 'proposal', 'negotiation', 'won', 'lost'] as $stage): ?>
        <div class="col-md-2">
            <div class="card">
                <div class="card-header"><?= ucfirst($stage) ?></div>
                <div class="card-body">
                    <!-- Deals in this stage -->
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
```

### 4. Route Pattern
```php
// routes/web.php
$router->get('/admin/deals', 'Admin\\DealController@index');
$router->get('/admin/deals/create', 'Admin\\DealController@create');
$router->post('/admin/deals/store', 'Admin\\DealController@store');
$router->get('/admin/deals/{id}', 'Admin\\DealController@show');
$router->post('/admin/deals/{id}/update', 'Admin\\DealController@update');
$router->post('/admin/deals/{id}/delete', 'Admin\\DealController@delete');
```

---

## IMPORTANT NOTES

1. **DO NOT DELETE ANY EXISTING TABLES** - Tables are for these features
2. **DO NOT MODIFY EXISTING WORKING PAGES** - Only add new functionality
3. **CHECK IF CONTROLLER EXISTS BEFORE CREATING** - Some may already exist
4. **USE EXISTING DATABASE TABLES** - Don't create new tables
5. **TEST EACH FEATURE AFTER BUILDING** - Verify it works
6. **USE EXISTING LAYOUTS** - Don't create new layouts, use existing header/footer
7. **ADD ROUTES** - Don't forget to add routes in web.php

---

## EXISTING STRUCTURE REFERENCE

### Layouts Location
- `app/views/layouts/header.php`
- `app/views/layouts/footer.php`
- `app/views/layouts/base.php`

### Existing Admin Layouts
- `app/views/admin/layouts/default.php`
- `app/views/admin/layouts/superadmin.php`

### Existing Controllers
- `app/Http/Controllers/Admin/LeadController.php`
- `app/Http/Controllers/Admin/PropertyController.php`
- `app/Http/Controllers/Admin/VisitController.php` (check if exists)

### Existing Views
- `app/views/admin/leads/index.php`
- `app/views/admin/properties/index.php`

---

## VERIFICATION CHECKLIST

After building each feature, verify:
- [ ] Page loads without error (200 status)
- [ ] Data saves to database correctly
- [ ] Forms submit properly
- [ ] Lists display data
- [ ] Edit/delete functions work
- [ ] No broken links
- [ ] No console errors

---

## DATABASE TABLE STRUCTURES

### property_comparisons
```sql
id, user_id, session_id, created_at
```

### property_comparison_sessions
```sql
id, user_id, name, created_at, expires_at
```

### lead_scoring
```sql
id, lead_id, score, breakdown_json, calculated_at
```

### property_visits
```sql
id, property_id, visitor_name, visitor_phone, visitor_email, visit_date, visit_time, status, notes, created_at
```

### lead_visits
```sql
id, lead_id, property_id, visit_date, visit_time, status, outcome, agent_id, notes, created_at
```

### lead_files
```sql
id, lead_id, file_name, file_path, file_type, uploaded_by, created_at
```

### lead_deals
```sql
id, lead_id, property_id, deal_value, stage, expected_close_date, actual_close_date, status, notes, created_at, updated_at
```

---

## END OF PROMPT

Build all features with complete functionality. Test each feature. Report back what was built and what works.
