# WINDSURF PROMPTS - APS DREAM HOME

Use these prompts one by one. After each prompt is done, test in browser, then ask for next prompt.

---

## PROMPT 1: ngrok Setup Fix

```
Fix ngrok connection. The error is:
"ERR_NGROK_3200 - The endpoint is offline"

Run in terminal:
1. Stop any existing ngrok: `taskkill /F /IM ngrok.exe` (if running)
2. Start ngrok: `ngrok http 80`
3. Copy the new forwarding URL
4. Update any config files that have the old ngrok URL
```

---

## PROMPT 2: Remaining UI Fixes

```
Check these files for any remaining placeholder images or issues:

1. Check app/views/layouts/header.php - make sure only logo, no extra text
2. Check app/views/layouts/footer.php - no duplicate logo
3. Check if favicon is working (check public/index.php or .htaccess)

Fix any issues found.
```

---

## PROMPT 3: Login Test & Protected Pages

```
Test login flow:

1. Go to http://localhost/apsdreamhome/login
2. Login with: admin@apsdreamhome.com / admin123
3. After login, test these pages:
   - /mlm-dashboard
   - /ai-dashboard
   - /ai-assistant
   - /virtual-tour/1

Fix any errors you find.
```

---

## PROMPT 4: Admin Panel Test

```
Test admin panel:

1. Login as admin
2. Go to /admin/dashboard
3. Navigate to:
   - Properties
   - Leads
   - Bookings
   - Users

Check for:
- 404 errors
- 500 errors
- Missing pages
- Broken links

Fix any issues.
```

---

## PROMPT 5: Database Clean Up

```
Check database for:
1. Any orphaned records
2. Missing foreign keys
3. Unused tables

Create a report of:
- Tables with no data
- Tables that are duplicates
- Tables that can be archived

DO NOT delete anything without permission.
```

---

## PROMPT 6: Performance Check

```
Check for slow pages:

1. Enable query logging in database.php
2. Run these pages and note load time:
   - /
   - /properties
   - /about
3. Identify slow queries
4. Suggest optimizations (indexes, caching)

DO NOT make changes without permission.
```

---

## PROMPT 7: Security Check

```
Basic security audit:

1. Check for SQL injection vulnerabilities
2. Check for XSS vulnerabilities
3. Check for CSRF protection
4. Check password hashing
5. Check session security

Report findings. DO NOT make changes.
```

---

## PROMPT 8: Mobile Responsiveness

```
Test mobile view:

1. Resize browser to mobile (375px width)
2. Check these pages:
   - Homepage
   - Properties
   - Login/Register
3. Check:
   - Menu works on mobile
   - Images resize properly
   - Text is readable
   - Buttons are tap-friendly

Fix any CSS issues.
```

---

## PROMPT 9: Forms Testing

```
Test all forms:

1. Register page - fill and submit
2. Login page - test with wrong password
3. Contact page - submit form
4. Schedule meeting - fill form
5. Careers - submit application

Check:
- Validation works
- Error messages show
- Success messages show
- Data saves to database

Fix any broken forms.
```

---

## PROMPT 10: Final Full Test

```
Full regression test:

1. Test all public pages return 200
2. Test all forms submit successfully
3. Test all buttons click properly
4. Test all links work
5. Test on mobile view

Create a final status report.
```

---

## NOTES FOR USER

After each prompt:
1. Copy prompt to WindSurf
2. Let WindSurf do the work
3. Test the result in browser
4. Come back here with results
5. Ask for next prompt

This way we can systematically fix all issues!
