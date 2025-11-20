# APS Dream Home – Implementation Plan

## लक्ष्य
- सुरक्षा, कॉन्फ़िग, राउटिंग और व्यू सिस्टम को एकीकृत करना
- लॉगिंग और ऑब्ज़र्वेबिलिटी को स्टैण्डर्डाइज़ करना
- फ्रंटएंड बिल्ड/एसेट्स को क्लीन करना और परफॉर्मेंस बेहतर करना
- टेस्ट कवरेज और CI सेटअप के साथ स्थिर रिलीज़ प्रक्रिया बनाना

## वर्तमान स्थिति (डीप एनालिसिस सारांश)
- डुअल एंट्री-पॉइंट: `public/index.php:15–31` और `index.php:30–50, 54–81`
- डुअल राउटर: मॉडर्न `App` पाइपलाइन (`app/core/App.php:74–86, 153–155`) और कस्टम राउटर (`app/core/Router.php:11–26, 286–343`); Apache रीराइट `.htaccess:1–12`
- वेब/API रूट्स: `routes/web.php:216–381`, `routes/api.php:9–80`
- ऑथ/सेशन: `app/core/Auth.php:48–65, 80–86`, `app/core/SessionManager.php:18–31`, सिक्योरिटी हेडर्स `config/security.php:61–69`
- एरर/लॉगिंग: `index.php:54–60` बनाम `app/core/ErrorHandler.php:44–46` और `config/logging.php:20–27`
- DB कॉन्फ़िग: `config/database.php:9–22`, `.env:11–15` (की नेम मिसमैच `DB_PASS`/`DB_PASSWORD`)
- व्यूज़: Blade‑जैसा `resources/views/**/*` और लेगेसी `src/Views/**/*`
- फ्रंटएंड: Vite (`vite.config.js:5–13, 15–21`), एसेट्स `assets/**/*`

## तात्कालिक फिक्सेस (Week 0)
1. सीक्रेट्स `.env` में शिफ्ट करें: SMTP, AI/OpenRouter, WhatsApp (स्रोत: `includes/config.php:41–53, 90–101`)
2. CSP टाइटन करें: `'unsafe-inline'/'unsafe-eval'` हटाएँ (`config/security.php:63–69`)
3. लॉगिंग पाथ यूनिफ़ॉर्म करें: `logs/php_errors.log` को `storage/logs/` में मर्ज करें (`index.php:54–60`, `ErrorHandler.php:44–46`, `config/logging.php:20–27`)
4. DB की नेम संगत करें: `DB_PASS` और `DB_PASSWORD` कम्पैटिबिलिटी (`config/database.php:11`, `.env:15`)

### Applied Fixes
- `includes/db_connection.php:24–36` में PDO पासवर्ड कॉन्स्टैंट मिसमैच ठीक किया; अब `DB_PASSWORD` और `DB_PASS` दोनों सपोर्टेड
- `includes/db_connection.php:33–36` में mysqli हैंडल को `$con` तथा `$conn` दोनों पर मैप किया
- `admin/config.php:8–16` में `includes/db_connection.php` इंक्लूड किया ताकि ऐडमिन पेजेज़ में `$con` उपलब्ध रहे

## फेज 1: Security & Config Hardening
- `.env` वेरिएबल्स इंट्रोड्यूस करें: `MAIL_*`, `OPENROUTER_API_KEY`, `WHATSAPP_*`
- `includes/config.php` में हार्डकोडेड वैल्यू हटाएँ; `getenv()` से पढ़ें
- CSRF/रेट‑लिमिट/सेशन नीतियाँ `config/security.php` में रिव्यू करें; स्ट्रिक्ट हैडर पॉलिसीज़ लागू
- फ़ाइल अपलोड स्कैनिंग ऑन करें (`config/security.php:54–59`) और HTML प्यूरीफ़ायर सक्षम करें

## फेज 2: Routing Unification
- डिफ़ॉल्ट फ्रंट‑कंट्रोलर: `public/index.php` + `App\Core\App`
- लेगेसी `FrontRouter` को `App` के अंदर फॉलबैक के रूप में रैप करें, या धीरे‑धीरे डिप्रीकेट करें
- `routes/web.php` और `routes/api.php` को एक ही रजिस्ट्रेशन मेकैनिज़्म से चलाएँ; मिडलवेयर मैपिंग `app/core/Router.php:328–336`

## फेज 3: Views Consolidation
- टेम्पलेटिंग का एक सिस्टम चुनें (`resources/views`) और `src/Views` पेजों को माइग्रेट करें
- कॉमन लेआउट/पार्शियल्स बनाए रखें; SEO/एरर पेज `errors/*` को स्टैण्डर्ड करें

## फेज 4: Logging & Observability
- फाइल लॉग्स: `storage/logs` में रोटेट/रिटेंशन (`config/logging.php:20–27, 135–141`)
- एरर JSON लॉग्स `application.log` को स्ट्रक्चर और अलर्टिंग से जोड़ें
- परफ़ॉर्मेंस/स्लो‑क्वेरी ट्रैकिंग (`config/security.php:106–113`)

## फेज 5: Data Layer Rationalization
- PDO को स्टैण्डर्ड करें; MySQLi लेगेसी यूसेज घटाएँ (`includes/config.php:161–176`)
- माइग्रेशन/सीड्स को व्यवस्थित करें; डुप्लीकेट `.sql` को समेकित करें

## फेज 6: Frontend Build Hygiene
- Vite इनपुट्स/आउटपुट्स की क्लीनअप (`vite.config.js:5–13`)
- `assets/**/*` में वेंडर/कस्टम को अलग करें; डुप्लीकेट्स हटाएँ
- PWA/परफ़ॉर्मेंस ट्यूनिंग, लाइटहाउस चेक्स
- लोकेल JS फाइलों की प्रूनिंग (`src/js/*` में 40+ लोकेल); Vite में केवल आवश्यक इम्पोर्ट रखें

## फेज 7: Tests & CI
- PHPUnit सूट स्टैण्डर्ड करें (`phpunit.xml` और `tests/**/*`)
- राउटिंग/ऑथ/DB/व्यूज़ के लिए क्रिटिकल टेस्ट जोड़ें
- CI स्क्रिप्ट: lint + static‑analysis + tests (`composer.json`:62–81, `package.json`:13–16)

## डॉक्यूमेंटेशन क्लीनअप
- सभी `*.md` फाइलों का ऑडिट करें, डुप्लीकेट/आउटडेटेड हटाएँ
- एक `docs/` संरचना: `architecture.md`, `security.md`, `routing.md`, `data-model.md`, `operations.md`
- रिलीज़ नोट्स और चेंजलॉग बनाए रखें

## वर्क मोड और गाइडलाइन्स
- हर बदलाव के साथ टेस्ट और वैलिडेशन अनिवार्य
- सीक्रेट्स कभी कमिट नहीं; `.env` से ही पढ़ें
- एक फ्रंट‑कंट्रोलर/राउटर, एक व्यू सिस्टम
- लॉगिंग पाथ/फ़ॉर्मेट स्टैण्डर्ड; अलर्टिंग कॉन्फ़िगर

## स्टेप‑बाय‑स्टेप टास्कलिस्ट
1. `.env` सीक्रेट्स माइग्रेशन और `includes/config.php` रेफ़ैक्टर
2. CSP सख्त करना और सिक्योरिटी हैडर्स अपडेट
3. एरर/लॉगिंग पाथ यूनिफ़ाई करना
4. DB env की कम्पैटिबिलिटी फ़िक्स
   - `includes/db_connection.php:24–36` अपडेट — पासवर्ड कॉन्स्टैंट हैंडलिंग
   - `admin/config.php:8–16` — यूनिफ़ॉर्म DB लोड
5. लेगेसी राउटिंग को `App` पाइपलाइन में समेकित करना
6. व्यूज़ माइग्रेशन प्लान और प्राथमिक पेज शिफ्ट
7. टेस्ट जोड़ना: राउटिंग/ऑथ/DB/एरर‑हैंडलिंग
8. फ्रंटएंड एसेट क्लीनअप और बिल्ड ट्यूनिंग
9. CI पाइपलाइन एक्टिवेट और रिलीज़ प्रोसेस बनाना
10. डॉक्यूमेंटेशन ऑडिट और रीऑर्ग

## जोखिम और शमन
- लेगेसी पथ ब्रेकिंग: ग्रैडुअल माइग्रेशन और फॉलबैक
- CSP सख्ती से JS टूट सकता है: नॉन्स/हैश‑आधारित स्ट्रैटेजी
- DB की मिसमैच से कनेक्शन फ़ेल: बैकवर्ड‑कम्पैटिबिलिटी कीज़ सपोर्ट

## सफलता मानक
- एकीकृत एंट्री/राउटर, एकीकृत लॉगिंग
- सभी सीक्रेट्स `.env`
- टेस्ट पास और CI ग्रीन
- डॉक्यूमेंटेशन अपडेटेड और डुप्लीकेट‑फ्री
## MCP Builder कम्पैटिबिलिटी
- एंट्री‑पॉइंट: `public/index.php` (Apache rewrite `.htaccess:10` सक्रिय)
- API कॉन्ट्रैक्ट: `api/v1/openapi.json` और `docs/api/README.md`
- Dev रन: `npm run dev` (Vite) + Apache/PHP सर्वर
- बिल्ड: `npm run build` → `/dist`; स्टैटिक एसेट्स `public/`
- कॉन्टेक्स्ट फाइल्स: `docs/` में आर्किटेक्चर/रूटिंग/डेटा मॉडेल

