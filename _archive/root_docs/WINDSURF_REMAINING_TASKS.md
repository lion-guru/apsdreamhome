# WIND SURF - REMAINING TASKS PROMPT
## APS Dream Home Project - April 4, 2026

---

## ALREADY DONE (Don't Repeat):
✅ Captcha fix - captcha_answer field
✅ lead_deals columns - property_id, assigned_to, stage
✅ lead_scoring table - created
✅ Service files - App::database() fixed

---

## NOW DO THESE TASKS:

### TASK 1: FOOTER & CONTACT INFO UPDATE

Update all footers with real company info:

```php
// Phone: +91 9277121112, +91 7007444842
// WhatsApp: +91 9277121112
// Email: info@apsdreamhome.com
// Address: 1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008
```

**Files to update:**
- `app/views/layouts/footer.php`
- `app/views/pages/contact.php`
- Any other footer files

### TASK 2: WHATSAPP BUTTON

Update WhatsApp floating button link:
```php
// OLD: https://wa.me/919876543210
// NEW: https://wa.me/919277121112
```

### TASK 3: GOOGLE MAPS EMBED

Add to Contact page:
```html
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.991144111075!2d83.30122467380973!3d26.840233976690463!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399149002e8a386b%3A0x907b565a09c02435!2sSuryoday%20Colony%20developed%20by%20APS%20Dream%20Homes!5e0!3m2!1sen!2sin!4v1775289074035!5m2!1sen!2sin" 
width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
```

### TASK 4: SOCIAL MEDIA LINKS

Update header/footer with real social links:
- Facebook: https://www.facebook.com/apsdreamhomes/
- Instagram: https://www.instagram.com/apsdreamhomes/
- Twitter/X: Create if not exists

### TASK 5: HEADER PHONE DISPLAY

Add phone number in header:
```html
<a href="tel:+919277121112"><i class="fas fa-phone"></i> +91 9277121112</a>
```

### TASK 6: PROPERTY IMAGES

Download images from:
- https://www.facebook.com/apsdreamhomes/ (Photos)
- https://www.instagram.com/apsdreamhomes/ (Posts)

Save to: `public/assets/images/properties/`

File names:
- suyoday-colony-1.jpg, suyoday-colony-2.jpg
- raghunath-nagri-1.jpg
- braj-radha-nagri-1.jpg
- commercial-property-1.jpg
- villa-1.jpg, villa-2.jpg

### TASK 7: DATABASE - Add to property_images

After downloading images:
```sql
INSERT INTO property_images (property_id, image_path, is_primary, sort_order) VALUES
(1, 'assets/images/properties/suyoday-colony-1.jpg', 1, 1),
(1, 'assets/images/properties/suyoday-colony-2.jpg', 0, 2),
(2, 'assets/images/properties/raghunath-nagri-1.jpg', 1, 1),
(3, 'assets/images/properties/braj-radha-nagri-1.jpg', 1, 1),
(4, 'assets/images/properties/budh-bihar-1.jpg', 1, 1),
(5, 'assets/images/properties/awadhpuri-1.jpg', 1, 1),
(6, 'assets/images/properties/commercial-1.jpg', 1, 1);
```

### TASK 8: DATABASE - Add Company Info

Create/Update `site_settings` table:
```sql
INSERT INTO site_settings (setting_key, setting_value) VALUES
('company_name', 'APS Dream Homes'),
('company_phone', '+91 9277121112'),
('company_whatsapp', '+91 9277121112'),
('company_email', 'info@apsdreamhome.com'),
('company_address', '1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008'),
('facebook_url', 'https://www.facebook.com/apsdreamhomes/'),
('instagram_url', 'https://www.instagram.com/apsdreamhomes/'),
('map_latitude', '26.840233976690463'),
('map_longitude', '83.30122467380973');
```

### TASK 9: ADMIN WORKFLOW TEST

After all above, test admin panel:
1. Login: http://localhost/apsdreamhome/admin/login
2. Credentials: admin@apsdreamhome.com / admin123
3. Go to Properties
4. Add new property with images
5. Verify shows on frontend

### TASK 10: VERIFY ALL PAGES

Test these pages after changes:
- Homepage - check footer, phone, WhatsApp
- Contact page - check map, phone, email
- Properties - check images loading
- Admin login - test captcha (7+9=16)

---

## TESTING CHECKLIST:

```
PUBLIC PAGES:
[ ] Homepage - Footer has correct phone/email
[ ] Homepage - WhatsApp button works
[ ] Contact page - Google Maps shows
[ ] Properties - Images load from database

ADMIN PAGES:
[ ] Admin login - Captcha works
[ ] Admin dashboard - Loads properly
[ ] Property add - Image upload works
[ ] Property edit - Save works

DATABASE:
[ ] property_images - Has records
[ ] site_settings - Has company info
[ ] Users - Admin exists
```

---

## CREDENTIALS:

**Admin Login:**
- URL: http://localhost/apsdreamhome/admin/login
- Email: admin@apsdreamhome.com
- Password: admin123
- Captcha: 7 + 9 = 16

---

## COMPLETE THEN REPORT:

After each task, test and report:
1. What you did
2. Files modified
3. Any errors
4. Screenshots if possible

---

## END OF PROMPT
