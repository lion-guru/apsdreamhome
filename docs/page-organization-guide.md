# Page Organization Guide - APS Dream Home

## 📁 Current Page Structure Issues

### Problems Identified:
1. **Duplicate pages** in multiple locations
2. **Inconsistent naming** conventions
3. **Scattered page files** across different folders
4. **No logical organization** system
5. **Mixed public and admin pages**

## 🎯 Recommended Page Organization

### 1. Public Pages (Customer-Facing)
```
app/views/pages/
├── home.php                    # Homepage - main landing page
├── about.php                   # About company/team
├── services.php                # Services offered
├── contact.php                 # Contact form and info
├── testimonials.php            # Customer reviews
├── blog/
│   ├── index.php              # Blog listing
│   ├── post.php               # Individual blog post
│   └── category.php           # Blog category
└── legal/
    ├── privacy.php            # Privacy policy
    ├── terms.php              # Terms of service
    └── disclaimer.php         # Legal disclaimer
```

### 2. Property Pages
```
app/views/properties/
├── index.php                   # All properties listing
├── detail.php                  # Single property page
├── search.php                  # Advanced search form
├── search_results.php          # Search results
├── compare.php                 # Property comparison
├── favorites.php              # Saved properties
└── categories/
    ├── apartments.php         # Apartment listings
    ├── villas.php              # Villa listings
    ├── plots.php               # Plot listings
    └── commercial.php          # Commercial properties
```

### 3. Authentication Pages
```
app/views/auth/
├── login.php                   # User login form
├── register.php                # User registration
├── forgot_password.php         # Password reset request
├── reset_password.php          # Password reset form
├── verify_email.php            # Email verification
└── login_handler.php           # Login processing (no direct access)
```

### 4. Customer Dashboard Pages
```
app/views/customer/
├── dashboard.php               # Customer main dashboard
├── profile.php                 # Profile management
├── saved_properties.php        # Bookmarked properties
├── my_properties.php           # Customer's own properties
├── inquiries.php               # Property inquiries sent
├── messages.php                # Communication history
├── appointments.php            # Viewed/scheduled visits
├── documents.php               # Documents and contracts
└── settings.php                # Account settings
```

### 5. Admin Pages
```
app/views/admin/
├── dashboard.php               # Admin main dashboard
├── login.php                   # Admin login
├── users/
│   ├── index.php              # User management
│   ├── create.php             # Add new user
│   ├── edit.php               # Edit user
│   └── roles.php              # User roles
├── properties/
│   ├── index.php              # Property management
│   ├── create.php             # Add property
│   ├── edit.php               # Edit property
│   ├── images.php             # Property images
│   └── features.php           # Property features
├── inquiries/
│   ├── index.php              # All inquiries
│   ├── details.php            # Inquiry details
│   └── follow_ups.php         # Follow-up management
├── reports/
│   ├── sales.php              # Sales reports
│   ├── analytics.php          # Website analytics
│   ├── properties.php         # Property reports
│   └── customers.php          # Customer reports
└── settings/
    ├── general.php            # General settings
    ├── appearance.php         # Theme/appearance
    ├── seo.php                # SEO settings
    └── backup.php             # Backup management
```

### 6. Component Pages
```
app/views/components/
├── header.php                  # Site header
├── footer.php                  # Site footer
├── navigation.php              # Main navigation menu
├── breadcrumbs.php             # Breadcrumb navigation
├── alerts.php                  # Success/error messages
├── property_card.php           # Property listing card
├── search_form.php             # Property search form
├── contact_form.php            # Contact form
├── newsletter.php              # Newsletter signup
├── social_links.php            # Social media links
└── loading_spinner.php         # Loading indicators
```

### 7. Error Pages
```
app/views/errors/
├── 404.php                     # Page not found
├── 403.php                     # Access denied
├── 500.php                     # Server error
├── maintenance.php             # Site maintenance
└── error_layout.php            # Error page template
```

## 🎯 Page Naming Conventions

### Standard Naming Rules:
1. **Use lowercase** letters only
2. **Use underscores** for multi-word names (not hyphens)
3. **Be descriptive** but concise
4. **Follow consistency** across all pages

### Examples:
```php
✅ Good: property_detail.php, user_profile.php, admin_dashboard.php
❌ Bad: PropertyDetail.php, user-profile.php, admindashboard.php
```

## 🎯 Page Content Structure

### 1. Homepage Structure
```php
<?php
// app/views/pages/home.php

// Set page variables
$pageTitle = 'APS Dream Home - Find Your Dream Property';
$pageClass = 'home';
$metaDescription = 'Find your dream home with APS Dream Home. Premium real estate services.';

// Start output buffering
ob_start();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1>Find Your Dream Home</h1>
                <p>Discover premium properties with expert guidance</p>
                <div class="cta-buttons">
                    <a href="/properties" class="btn btn-primary">Browse Properties</a>
                    <a href="/contact" class="btn btn-outline-primary">Contact Us</a>
                </div>
            </div>
            <div class="col-lg-6">
                <?php include APP_PATH . 'views/components/search_form.php'; ?>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="featured-properties">
    <div class="container">
        <h2>Featured Properties</h2>
        <div class="row">
            <?php echo getFeaturedProperties(); ?>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="services-section">
    <div class="container">
        <h2>Our Services</h2>
        <div class="row">
            <?php echo getServicesList(); ?>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials-section">
    <div class="container">
        <h2>What Our Clients Say</h2>
        <?php include APP_PATH . 'views/components/testimonials.php'; ?>
    </div>
</section>

<?php
$content = ob_get_clean();

// Include base template
require APP_PATH . 'views/templates/base.php';
?>
```

### 2. Property Detail Page Structure
```php
<?php
// app/views/properties/detail.php

// Get property ID from URL
$propertyId = $_GET['id'] ?? 0;

// Fetch property data (this would come from database)
$property = getPropertyById($propertyId);

if (!$property) {
    header('Location: /404');
    exit;
}

// Set page variables
$pageTitle = $property['title'] . ' - APS Dream Home';
$pageClass = 'property-detail';
$metaDescription = $property['description'];
$ogImage = $property['main_image'];

ob_start();
?>

<!-- Property Header -->
<section class="property-header">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php include APP_PATH . 'views/components/breadcrumbs.php'; ?>
                <h1><?php echo htmlspecialchars($property['title']); ?></h1>
                <p class="location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo htmlspecialchars($property['location']); ?>
                </p>
                <div class="price">
                    <span class="amount">₹<?php echo number_format($property['price']); ?></span>
                    <span class="type"><?php echo htmlspecialchars($property['type']); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Property Gallery -->
<section class="property-gallery">
    <div class="container">
        <?php include APP_PATH . 'views/components/property_gallery.php'; ?>
    </div>
</section>

<!-- Property Details -->
<section class="property-details">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Property Features -->
                <div class="features-section">
                    <h3>Property Features</h3>
                    <?php include APP_PATH . 'views/components/property_features.php'; ?>
                </div>
                
                <!-- Description -->
                <div class="description-section">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($property['description'])); ?></p>
                </div>
                
                <!-- Floor Plan -->
                <?php if ($property['floor_plan']): ?>
                <div class="floor-plan-section">
                    <h3>Floor Plan</h3>
                    <img src="<?php echo $property['floor_plan']; ?>" alt="Floor Plan" class="img-fluid">
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <!-- Contact Form -->
                <div class="contact-widget">
                    <h3>Contact Agent</h3>
                    <?php include APP_PATH . 'views/components/contact_agent_form.php'; ?>
                </div>
                
                <!-- Schedule Visit -->
                <div class="schedule-widget mt-4">
                    <h3>Schedule Visit</h3>
                    <?php include APP_PATH . 'views/components/schedule_form.php'; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require APP_PATH . 'views/templates/base.php';
?>
```

### 3. Customer Dashboard Structure
```php
<?php
// app/views/customer/dashboard.php

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Get customer data
$customerId = $_SESSION['user_id'];
$customer = getCustomerById($customerId);
$stats = getCustomerStats($customerId);

// Set page variables
$pageTitle = 'My Dashboard - APS Dream Home';
$pageClass = 'customer-dashboard';

ob_start();
?>

<!-- Dashboard Header -->
<section class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>Welcome back, <?php echo htmlspecialchars($customer['name']); ?></h1>
                <p>Manage your properties and preferences</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="/profile" class="btn btn-outline-primary">Edit Profile</a>
                <a href="/properties" class="btn btn-primary">Browse Properties</a>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Stats -->
<section class="dashboard-stats">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['saved_properties']; ?></h3>
                        <p>Saved Properties</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['viewed_properties']; ?></h3>
                        <p>Viewed Properties</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['inquiries']; ?></h3>
                        <p>Inquiries</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats['appointments']; ?></h3>
                        <p>Appointments</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Activity -->
<section class="dashboard-activity">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="activity-card">
                    <h3>Recent Activity</h3>
                    <?php include APP_PATH . 'views/components/recent_activity.php'; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-list">
                        <a href="/saved-properties" class="action-item">
                            <i class="fas fa-heart"></i>
                            View Saved Properties
                        </a>
                        <a href="/inquiries" class="action-item">
                            <i class="fas fa-envelope"></i>
                            View Inquiries
                        </a>
                        <a href="/appointments" class="action-item">
                            <i class="fas fa-calendar"></i>
                            Manage Appointments
                        </a>
                        <a href="/messages" class="action-item">
                            <i class="fas fa-comments"></i>
                            View Messages
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$content = ob_get_clean();
require APP_PATH . 'views/templates/base.php';
?>
```

## 🎯 Quick Implementation Steps

### Step 1: Create Basic Structure
```bash
# Create main directories
mkdir -p app/views/{pages,properties,auth,customer,admin,components,errors,templates}

# Create template files
touch app/views/templates/{base.php,auth.php,admin.php}
touch app/views/components/{header.php,footer.php,navigation.php,alerts.php}
```

### Step 2: Move Existing Pages
```bash
# Find and move existing pages
find . -name "*.php" -path "*/views/*" | grep -E "(home|index|main)" | head -5
find . -name "*.php" -path "*/views/*" | grep -E "(property|listing)" | head -5
find . -name "*.php" -path "*/views/*" | grep -E "(login|register|auth)" | head -5
```

### Step 3: Create Sample Pages
```bash
# Create sample pages for testing
echo "<?php echo 'Homepage'; ?>" > app/views/pages/home.php
echo "<?php echo 'Property Listing'; ?>" > app/views/properties/index.php
echo "<?php echo 'Login Page'; ?>" > app/views/auth/login.php
```

### Step 4: Test Organization
```bash
# Test page access
http://localhost/apsdreamhomefinal/          # Should show homepage
http://localhost/apsdreamhomefinal/properties # Should show property listing
http://localhost/apsdreamhomefinal/login     # Should show login page
```

## 🎯 Benefits of This Organization

✅ **Logical Structure**: Easy to find pages
✅ **Scalable**: Easy to add new pages
✅ **Maintainable**: Consistent naming and structure
✅ **SEO-Friendly**: Clean URLs and proper organization
✅ **User-Friendly**: Intuitive navigation
✅ **Developer-Friendly**: Easy to understand and modify

This organization will make your project much more manageable and user-friendly!