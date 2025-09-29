# 🛠️ APS Dream Home - Customization Guide

## 🎯 **How to Customize Your Website**

This guide shows you how to personalize your APS Dream Home website with your own:
- Company information
- Contact details
- Services
- Property listings
- Design preferences

---

## 📝 **1. Update Company Information**

### **Edit: `index.php` (Homepage)**
```php
// Line 1: Change website title
<title>APS Dream Home - Your Real Estate Partner</title>

// Line 50-60: Update main heading
<h1 class="display-4 fw-bold text-primary">APS Dream Home</h1>
<p class="lead">Your trusted partner in real estate solutions...</p>

// Line 100-120: Update statistics
<div class="col-md-3 text-center">
    <h2 class="counter" data-count="150">150</h2>
    <p>Properties Sold</p>
</div>
```

### **Edit: `about.php` (About Us Page)**
```php
// Line 20-30: Update company story
<h2>About APS Dream Home</h2>
<p>Founded in [Year], APS Dream Home has been serving...</p>

// Line 50-70: Update mission/vision
<h3>Our Mission</h3>
<p>To provide exceptional real estate services...</p>
```

---

## 📞 **2. Update Contact Information**

### **Edit: `index.php` (Footer)**
```php
// Line 400-420: Update contact details
<li><i class="fas fa-map-marker-alt me-2"></i> Your City, State, India</li>
<li><i class="fas fa-phone-alt me-2"></i> +91-YourPhoneNumber</li>
<li><i class="fas fa-envelope me-2"></i> info@yourcompany.com</li>
<li><i class="fas fa-clock me-2"></i> Mon-Sat: 9:00 AM - 8:00 PM</li>
```

### **Edit: `contact.php`**
```php
// Line 30-50: Update contact form details
<h5>Contact Information</h5>
<p><strong>Address:</strong> Your complete address here</p>
<p><strong>Phone:</strong> +91-YourPhoneNumber</p>
<p><strong>Email:</strong> info@yourcompany.com</p>
```

---

## 🏠 **3. Add Your Own Properties**

### **Method 1: Use Admin Panel**
1. Go to: `http://localhost/apsdreamhomefinal/admin_panel.php`
2. Click "Add New Property"
3. Fill in your property details
4. Upload property images
5. Save the property

### **Method 2: Edit `add_demo_properties.php`**
```php
// Add your own property
$properties[] = [
    'title' => 'Your Property Title',
    'description' => 'Your property description...',
    'price' => 2500000, // Price in rupees
    'type' => 'apartment', // apartment, villa, flat, commercial
    'bedrooms' => 2,
    'bathrooms' => 2,
    'area_sqft' => 1200,
    'location' => 'Your City, Your State',
    'address' => 'Complete address here',
    'features' => 'Parking, Security, Garden',
    'image_url' => 'https://your-image-url.com/image.jpg'
];
```

---

## 🎨 **4. Customize Design & Colors**

### **Edit: `index.php`**
```php
// Line 150-170: Change main colors
.btn-primary { background-color: #your-color; }
.text-primary { color: #your-color; }
.navbar-brand { color: #your-color; }

// Line 200-220: Update hero section background
.hero-section {
    background: linear-gradient(your-colors);
}
```

### **Add Custom CSS**
```php
// Add to <style> section in index.php
.your-custom-class {
    color: #your-brand-color;
    font-family: 'Your Font', sans-serif;
}
```

---

## 🖼️ **5. Add Your Logo & Images**

### **Step 1: Upload Images**
1. Create folder: `images/`
2. Upload your logo: `images/logo.png`
3. Upload property images: `images/properties/`

### **Step 2: Update Logo in Navigation**
```php
// Edit: index.php (Line 250-260)
<a class="navbar-brand" href="/">
    <img src="images/logo.png" alt="Your Company" height="40">
    Your Company Name
</a>
```

---

## 📊 **6. Update Services Section**

### **Edit: `index.php`**
```php
// Line 300-350: Customize services
<div class="col-lg-4 col-md-6">
    <div class="card service-card h-100">
        <div class="card-body text-center p-4">
            <i class="fas fa-home feature-icon"></i>
            <h5>Property Sales</h5>
            <p>Your service description here...</p>
        </div>
    </div>
</div>
```

---

## 🔧 **7. Database Customization**

### **Update Company Settings**
```sql
-- Run this in your database
UPDATE site_settings SET setting_value = 'Your Company Name' WHERE setting_name = 'company_name';
UPDATE site_settings SET setting_value = 'Your company description' WHERE setting_name = 'company_description';
```

### **Add Your Social Media Links**
```sql
INSERT INTO site_settings (setting_name, setting_value) VALUES
('facebook_url', 'https://facebook.com/yourpage'),
('twitter_url', 'https://twitter.com/yourhandle'),
('instagram_url', 'https://instagram.com/youraccount');
```

---

## 📱 **8. Mobile Responsiveness**

### **Test Your Website**
1. Open in mobile browser
2. Check all pages load properly
3. Test navigation menu
4. Verify contact forms work

### **Mobile-Specific Edits**
```php
// Add to CSS for better mobile experience
@media (max-width: 768px) {
    .hero-section h1 {
        font-size: 2rem;
    }
    .navbar-brand {
        font-size: 1.2rem;
    }
}
```

---

## 🎯 **Quick Customization Checklist**

- [ ] Update company name and description
- [ ] Add your contact information
- [ ] Upload your logo
- [ ] Add your property listings
- [ ] Customize colors to match your brand
- [ ] Update social media links
- [ ] Test all pages on mobile
- [ ] Add your business hours
- [ ] Update service descriptions

---

## 🚀 **Need Help?**

If you need help customizing any section:

1. **Tell me what you want to change**
   - "Change the company name to [Your Name]"
   - "Update contact number to [Your Number]"
   - "Add my logo to the website"

2. **Share your content**
   - Your company information
   - Property details
   - Contact information
   - Brand colors

3. **I'll make the changes for you!**

**Your APS Dream Home is ready for your personal touch!** 🎉
