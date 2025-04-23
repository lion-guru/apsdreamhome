# APS Dream Homes Website

## प्रोजेक्ट संरचना (Project Structure)

इस प्रोजेक्ट को बेहतर तरीके से संगठित किया गया है ताकि कोड का प्रबंधन और रखरखाव आसान हो। निम्नलिखित फोल्डर संरचना का उपयोग किया गया है:

### मुख्य फोल्डर्स (Main Folders)

- **assets/** - सभी स्टैटिक एसेट्स के लिए
  - **css/** - मुख्य CSS फाइल्स
  - **js/** - JavaScript फाइल्स
  - **images/** - इमेज फाइल्स
  - **fonts/** - फॉन्ट फाइल्स

- **includes/** - कॉमन PHP कंपोनेंट्स
  - **templates/** - कॉमन टेम्पलेट्स जैसे हेडर और फुटर
  - **config/** - कॉन्फिगरेशन फाइल्स
  - **functions/** - हेल्पर फंक्शंस और यूटिलिटीज
  
- **database/** - डेटाबेस माइग्रेशन और सीड्स

### महत्वपूर्ण फाइल्स (Important Files)

- **config.php** - मुख्य कॉन्फिगरेशन फाइल
- **.env** - एनवायरनमेंट वेरिएबल्स (कमिट न करें)
- **.env.example** - एनवायरनमेंट वेरिएबल्स के लिए टेम्पलेट
- **.gitignore** - गिट इग्नोर रूल्स

## एनवायरनमेंट सेटअप (Environment Setup)

1. `.env.example` को `.env` में कॉपी करें:
   ```bash
   cp .env.example .env
   ```

2. `.env` फाइल को अपनी कॉन्फिगरेशन के साथ अपडेट करें:
   ```env
   # डेटाबेस कॉन्फिगरेशन
   DB_HOST=localhost
   DB_USER=your_username
   DB_PASS=your_password
   DB_NAME=realestatephp

   # गूगल ओथ कॉन्फिगरेशन
   GOOGLE_CLIENT_ID="your-google-client-id"
   GOOGLE_CLIENT_SECRET="your-google-client-secret"

   # जेमिनी एआई कॉन्फिगरेशन
   GEMINI_API_KEY="your-gemini-api-key"
   ```

3. डेटाबेस को इम्पोर्ट करें:
   ```bash
   mysql -u your_username -p realestatephp < database/realestatephp.sql
   ```

- Use phpMyAdmin as an alternative for import if you prefer a graphical interface.
- All demo logins will work immediately after import.

## Demo Data & Test Logins

The platform comes pre-seeded with demo data for all user types and employees for easy testing and stakeholder review.

**Demo Login Credentials:**

- Multiple user types: admin, associate, agent, builder, tenant, employee, superadmin, investor, customer, user
- Password for all demo employees: `Aps@128128`
- See `database/create_employees_table.sql` for demo employee details

## Dashboard Modernization (2025)

- **All dashboards** (admin, associate, agent, builder, tenant, employee, superadmin, investor, customer, user) have been modernized:
  - Bootstrap 5, FontAwesome, card-based UI
  - AI chatbot panel and AI-powered suggestions
  - Responsive, accessible layouts
  - Export/share features
  - Consistent backend logic and security
- **All duplicate/legacy dashboard files have been removed** for maintainability (April 2025).
- For new dashboard development, follow the patterns in any modernized dashboard file.

## नए पेज बनाना (Creating New Pages)

- Use Bootstrap 5 and FontAwesome for all new UI development.
- Follow card-based, responsive, and accessible design patterns.
- Use modular PHP and RBAC for all new features.
- Document new pages and modules in both this README and the DEVELOPER_HANDBOOK.md.

## Coding & Database Standards

- Use prepared statements for all DB queries
- Modularize new features (separate admin, API, customer, and DB logic)
- Use RBAC and audit logging for all sensitive actions
- Follow modern dashboard UI/UX standards for all user-facing modules

## Operations & Maintenance

- Backups: Use `scripts/backup.sh` and `scripts/restore.sh`
- CI/CD: `scripts/ci_cd.ps1` for linting, tests, and deployment
- Health: `admin/health_check.php` for self-healing and monitoring
- Duplicate Scan: Use `duplicate_files_report.csv` for codebase hygiene
- Ongoing removal of duplicate and legacy files is enforced (user-approved)

## Future Roadmap (2025+)

- Expand marketplace and AI/ML integrations
- Add more onboarding and automation flows
- Enhance analytics with real-time and predictive insights
- Invite partners to build on the developer portal and app store

## Unique Features

- Covers A-Z for real estate: sales, marketing, analytics, compliance, automation, and more
- Modular, future-proof, and highly extensible

---

For more details, see `DEVELOPER_HANDBOOK.md`.