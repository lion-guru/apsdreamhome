# APS DREAM HOME - FULL DATA SETUP PROMPT

## COMPANY INFO (Add to Database)

### Contact Details:
- **Phone:** +91 9277121112, +91 7007444842
- **WhatsApp:** +91 9277121112
- **Email:** info@apsdreamhome.com, admin@apsdreamhome.com
- **Address:** 1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008

### Social Media:
- **Facebook Page:** https://www.facebook.com/apsdreamhomes/
- **Facebook Personal:** https://www.facebook.com/AbhaySinghSuryawansi/
- **Instagram:** https://www.instagram.com/apsdreamhomes/
- **JustDial:** https://www.justdial.com/Gorakhpur/Aps-Dream-Homes-Pvt-Ltd-Near-Ganpati-Lawn-Kunraghat/9999PX551-X551-220919133119-G7Q6_BZDET
- **Falcone Biz:** https://www.falconebiz.com/company/APS-DREAM-HOMES-PRIVATE-LIMITED-U70109UP2022PTC163047

### Google Maps:
- **Suryoday Colony (Main Office):**
  - Maps Link: https://maps.app.goo.gl/7zkfLc7f8kLv38vt9
  - Embed: `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3559.991144111075!2d83.30122467380973!3d26.840233976690463!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x399149002e8a386b%3A0x907b565a09c02435!2sSuryoday%20Colony%20developed%20by%20APS%20Dream%20Homes!5e0!3m2!1sen!2sin!4v1775289074035!5m2!1sen!2sin`

- **Raghunath Nagri Office:**
  - Maps Link: https://maps.app.goo.gl/1bZfa1jKh2WnmHW77
  - Embed: `https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3565.1728698977795!2d83.49284677380166!3d26.674953176789675!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39915d00034f316b%3A0x458f246c5816e59c!2sRaghunath%20Nagri%20Developed%20by%20APS%20Dream%20Homes!5e0!3m2!1sen!2sin!4v1775289130764!5m2!1sen!2sin`

---

## TASKS TO DO:

### 1. DATABASE - Add Company Info Table
Create/update `site_settings` or `company_info` table with:
```sql
-- Company Details
name: APS Dream Homes Pvt Ltd
phone: +91 9277121112, +91 7007444842
whatsapp: +91 9277121112
email: info@apsdreamhome.com
address: 1st floor, Singhariya Chauraha, Kunraghat, Gorakhpur, UP - 273008

-- Social Links
facebook: https://www.facebook.com/apsdreamhomes/
instagram: https://www.instagram.com/apsdreamhomes/
justdial: https://www.justdial.com/...
falconebiz: https://www.falconebiz.com/...

-- Maps
suryoday_lat: 26.840233976690463
suryoday_lng: 83.30122467380973
raghunath_lat: 26.674953176789675
raghunath_lng: 83.49284677380166
```

### 2. FOOTER - Update with Real Info
- Update phone: 9277121112
- Update address: Kunraghat, Gorakhpur
- Update email: info@apsdreamhome.com
- Add social media icons with real links

### 3. HEADER CONTACT BAR
Add phone number in header: +91 9277121112

### 4. GOOGLE MAPS
- Add map to Contact page
- Add map to Footer
- Use Suryoday Colony coordinates as main

### 5. WHATSAPP BUTTON
- Update WhatsApp link: `https://wa.me/919277121112`

### 6. IMAGES - Download from Facebook/Instagram
Go to these pages and download property photos:
- https://www.facebook.com/apsdreamhomes/ (Photos tab)
- https://www.instagram.com/apsdreamhomes/ (Posts)

Save to: `assets/images/properties/`

### 7. PROPERTY IMAGES - Add to Database
Download property images and add to `property_images` table:
```sql
INSERT INTO property_images (property_id, image_path, is_primary) VALUES
(1, 'assets/images/properties/suyoday-1.jpg', 1),
(1, 'assets/images/properties/suyoday-2.jpg', 0),
(2, 'assets/images/properties/raghunath-1.jpg', 1);
```

### 8. ADMIN WORKFLOW TEST
After adding data, test admin panel:
1. Login: admin@apsdreamhome.com / admin123
2. Go to Property Management
3. Add new property with images
4. Verify it shows on frontend

---

## TESTING CHECKLIST:
- [ ] Footer shows correct phone/email
- [ ] WhatsApp button works (links to 919277121112)
- [ ] Google Maps shows Suryoday Colony
- [ ] Property images load from database
- [ ] Admin can add/edit properties with images
- [ ] All 6+ properties have images

---

## LOGIN CREDENTIALS:
- **Admin:** admin@apsdreamhome.com / admin123
- **URL:** http://localhost/apsdreamhome/admin/login

---

## IMPORTANT:
1. First complete all database setup
2. Then test admin workflow
3. Verify images show on frontend
4. Report any errors
