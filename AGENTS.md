# APS Dream Home - Agent Rules & Project Status

## Project Overview
- Custom PHP MVC Framework (NOT Laravel)
- Location: C:\xampp\htdocs\apsdreamhome
- Database: MySQL (port 3307)
- Server: XAMPP Apache (port 80)

## Quick Commands
- **Start server**: http://localhost/apsdreamhome/
- **Admin**: http://localhost/apsdreamhome/admin/login
- **Test page**: http://localhost/apsdreamhome/

## Architecture
- Custom MVC pattern in `app/` folder
- Controllers: `app/Http/Controllers/`
- Models: `app/Models/`
- Views: `app/Views/`
- Routes: `routes/web.php`, `routes/api.php`
- Core: `app/Core/`

---

## Completed Features

### 1. Header System (UPDATED - DYNAMIC)
- **File**: `app/views/layouts/header.php` (ONE consolidated header)
- Shows navigation with dropdowns (Buy, Rent, Projects, Services, Resources, About Us)
- **Dynamic Projects Dropdown** - Loads from `projects` table via JOIN with `districts` and `states` tables
- Groups projects by location (district/city)
- Shows project count badges per location
- Shows login/register buttons for guests (Customer, Associate, Agent options)
- Shows user name and dropdown menu for logged-in users
- Menu items: Dashboard, My Properties, My Inquiries, Profile, Logout
- Premium CSS with gradients, animations, scroll effects
- Mobile responsive with collapsible menu
- Call button (+91 92771 21112) and Admin button

### 2. User Authentication System
- **Files**: 
  - `app/Http/Controllers/Front/UserController.php`
  - `app/views/pages/user_login.php`
  - `app/views/pages/user_register.php`
  - `app/views/pages/user_dashboard.php`
  - `app/views/pages/user_properties.php`
  - `app/views/pages/user_inquiries.php`
  - `app/views/pages/user_profile.php`
- User can register with name, email, phone, password
- User can login with email and password
- Passwords are hashed using PHP password_hash()
- Sessions store user_id, user_name, user_email, user_phone

### 3. User Dashboard
- Shows welcome message with user details
- Shows stats: My Properties, My Inquiries, Property Views
- Quick actions: Post Property, View Properties, Inquiry History, Edit Profile
- Shows recent properties and recent inquiries

### 4. Properties Page
- **File**: `app/views/pages/properties.php`
- **Controller**: `PageController::properties()`
- Filtering by: Property Type, Listing Type (Buy/Rent), Location, Sort
- Pagination support
- Displays properties from database (user_properties table)
- Falls back to sample data if no properties in DB

### 5. Property Posting
- **File**: `app/views/pages/list_property.php`
- User can post: Plot, House, Flat, Shop, Farmhouse
- User can choose: Sell or Rent
- Captures: Name, Phone, Email, Price, Location, Area, Description
- Saves to `user_properties` table with `pending` status
- Admin can approve/reject from admin panel

### 6. Admin Property Management
- **File**: `app/Http/Controllers/Admin/UserPropertyController.php`
- **Views**: `app/views/admin/user-properties/`
- Admin can view all user-submitted properties
- Admin can filter by status (pending, verified, approved, rejected)
- Admin can approve or reject properties
- Routes:
  - `/admin/user-properties` - List all
  - `/admin/user-properties/verify/{id}` - View & Verify
  - `/admin/user-properties/action` - Approve/Reject

### 7. Newsletter Subscription
- **File**: `app/Http/Controllers/Api/NewsletterController.php`
- Saves subscribers to `newsletter_subscribers` table
- Creates table automatically if not exists
- AJAX form submission in footer

### 8. Service Interest Tracking
- **File**: `app/Http/Controllers/Front/PageController.php` (serviceInterest method)
- **Form**: `app/views/pages/services.php`
- Services: Home Loan, Legal, Registry, Mutation, Interior, Rental Agreement, Property Tax
- Saves to `service_interests` table
- Admin can view at `/admin/services`

### 9. AI Bot
- **File**: `app/Http/Controllers/Front/AIBotController.php`
- Hindi/English chatbot
- Intent detection (buy, sell, rent, loan, legal, contact)
- Auto lead creation
- Integrated via `/api/ai/chatbot`

### 10. Admin Services Management
- **File**: `app/Http/Controllers/Admin/ServiceController.php`
- **Views**: `app/views/admin/services/`
- Lists all service interests
- Shows customer details, service type, status
- Admin can update status

---

## Routes Added

### User Authentication
```
GET  /login
POST /login
GET  /register
POST /register
GET  /user/logout
GET  /user/dashboard
GET  /user/properties
GET  /user/inquiries
GET  /user/profile
POST /user/profile
```

### Property Management
```
GET  /properties
GET  /list-property
POST /list-property/submit
GET  /admin/user-properties
GET  /admin/user-properties/verify/{id}
POST /admin/user-properties/action
```

### Newsletter & Services
```
POST /subscribe
POST /service-interest
```

---

## Database Tables

### customers table
Used for user authentication. Fields: id, name, email, phone, password, status, created_at

### user_properties table
Stores user-posted properties. Fields: id, user_id, name, phone, email, property_type, listing_type, address, area_sqft, price, price_type, description, status, views, inquiries, created_at

### newsletter_subscribers table
Stores newsletter subscribers. Fields: id, email, name, is_active, created_at

### service_interests table
Stores service inquiries. Fields: id, lead_id, service_type, status, notes, created_at

### inquiries table
Stores all inquiries. Fields: id, name, email, phone, message, type, status, priority, created_at

---

## Project Locations (from Database)
- Gorakhpur: Suryoday Heights Phase 1, Raghunath City Center
- Lucknow: Braj Radha Enclave
- Kushinagar: Budh Bihar Colony
- Varanasi: Ganga Nagri

---

## Pending Tasks

1. **Pan-India Locations** - Add API for location search
2. **Email Notifications** - Send email when property is approved/rejected
3. **Property Images** - Allow users to upload property images
4. **Search by Price** - Add price range filter
5. **SMS Notifications** - Send SMS for important events
6. **Test User Flow** - Complete user registration, login, post property, admin approval flow

---

## Issues Fixed

### 1. Duplicate /properties Route (FIXED)
- **Issue**: Properties page showed empty main section
- **Cause**: Two routes for `/properties` in routes/web.php (line 53 and 557)
- **Fix**: Removed duplicate route at line 557 that pointed to PropertyController@index
- **Result**: Properties page now renders correctly with header, filters, and property grid

### 2. BaseController render() Method
- The `render()` method properly captures view content and passes to layout via `$content` variable
- Layout (base.php) uses `<?php echo $content ?? ''; ?>` to render page content

### 3. View Files Fixed
- `app/views/pages/properties.php` - Main properties page with filters
- `app/views/pages/list_property.php` - Hindi property posting form
- `app/views/pages/services.php` - Service interest form with AJAX submission

---

## Testing Results

| Page | Status |
|------|--------|
| Homepage | Working |
| Properties | Working (fixed) |
| List Property | Working |
| Services | Working |
| Contact | Working |
| Login/Register | Working |
| Admin Login | Working |
| Newsletter | Working |
| AI Bot | Working |

---

## Important Rules

### Git Workflow
- Use PowerShell for git commands (not bash)
- Commands: `git add -A`, `git commit -m "message"`, `git push origin production`
- Run PHP syntax check before commit

### Token Optimization
1. Use filesystem tool for file operations
2. Use grep for finding code
3. Read specific lines with offset/limit
4. Be concise in responses

### Code Style
- Use `<?php` opening tag
- Use `BASE_URL` constant for URLs
- Use prepared statements for SQL
- Use Bootstrap 5 for UI
- Use Font Awesome 6 for icons

### Common Issues
- CSS not loading: Check `<link>` tags in `app/views/layouts/base.php`
- JS not loading: Check `<script>` tags in base.php
- Database errors: Check `.env` DB credentials
- Route 404: Check `routes/web.php`

### Database
- Host: 127.0.0.1
- Port: 3307
- Database: apsdreamhome
- User: root
- Password: (empty)
