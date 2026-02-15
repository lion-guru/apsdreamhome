# डेटाबेस स्कीमा रिपोर्ट

## सारांश
यह रिपोर्ट `temp_test.sql` और PHP फ़ाइलों में एम्बेडेड डेटाबेस स्कीमाओं का सारांश है।

## टेबल परिभाषाएं

### 1. `temp_test.sql` से
- **test_table**
  - कॉलम: `id` (INT), `name` (VARCHAR(50))
  - स्रोत: `temp_test.sql`

### 2. `create_missing_legal_tables.php` से
- **legal_services**, **team_members**, **faqs**
  - कॉलम: `id`, `service_name`/`name`, `description`/`role`, `created_at`
  - स्रोत: `create_missing_legal_tables.php`

### 3. `import_database.php` से
- **about**, **admin**, **associates**, **bookings**, **career_applications**
  - कॉलम: `id`, `content`/`username`/`name`, `created_at`
  - स्रोत: `import_database.php`

### 4. `2025_09_27_000001_create_leads_tables.php` से
- **leads**, **lead_activities**, **lead_notes** (आदि)
  - कॉलम: `id`, `lead_id`, `activity_type`, `created_at`
  - स्रोत: माइग्रेशन फ़ाइल

### 5. `init_lead_management.php` से
- **contact_inquiries**, **clients**, **notifications**, **audit_log** (आदि)
  - कॉलम: `id`, `user_id`, `status`, `created_at`
  - स्रोत: `init_lead_management.php`

## महत्वपूर्ण निष्कर्ष
- `temp_schema.sql` में कोई टेबल परिभाषा नहीं मिली.
- अधिकांश स्कीमा PHP फ़ाइलों में एम्बेडेड हैं.
