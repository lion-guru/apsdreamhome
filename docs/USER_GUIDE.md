# APS Dream Home – User Guide

Welcome to **APS Dream Home**, North India's trusted real-estate platform for buying, selling, renting, and investing in land, plots, flats, villas, and farmhouses. This guide walks you through every customer-facing feature: from creating your account to receiving saved-search email alerts and using the mobile app.

> **Need a quick answer?** Jump straight to the [FAQ](#frequently-asked-questions) or [Contact Support](#contact-support).

---

## Table of Contents

1. [Getting Started](#getting-started)
2. [Browsing Properties](#browsing-properties)
3. [Saving Properties (Favorites)](#saving-properties-favorites)
4. [Saved Searches & Email Alerts](#saved-searches--email-alerts)
5. [Posting Your Property](#posting-your-property)
6. [Booking Property Visits](#booking-property-visits)
7. [Your Customer Dashboard](#your-customer-dashboard)
8. [Account Settings & Profile](#account-settings--profile)
9. [Two-Factor Authentication (2FA)](#two-factor-authentication-2fa)
10. [Mobile App Usage](#mobile-app-usage)
11. [Property Comparison Tool](#property-comparison-tool)
12. [Property Alerts & Subscriptions](#property-alerts--subscriptions)
13. [Live Chat Support](#live-chat-support)
14. [Free Tools (EMI, Stamp Duty, Valuation)](#free-tools)
15. [Multi-Language Support](#multi-language-support)
16. [Privacy, Security & Your Rights](#privacy-security--your-rights)
17. [Frequently Asked Questions](#frequently-asked-questions)
18. [Contact Support](#contact-support)

---

## Getting Started

### 1. Visit the Homepage

Open your browser and go to **https://apsdreamhome.com** (or `http://localhost/apsdreamhome/` if running locally). The homepage shows featured projects, latest properties, hero search, and quick links to common services.

### 2. Create Your Account (Registration)

You can register as a **Customer**, **Associate** (MLM partner), **Agent**, or **Farmer** (land seller). Most users register as a customer.

**Steps to register:**

1. Click **"Register"** in the top-right corner of the header.
2. Choose **"Customer Registration"**.
3. Fill in the form:
   - **Full Name** – your legal name as per ID.
   - **Email** – a working email (we'll send a verification link).
   - **Phone** – 10-digit Indian mobile (we send OTP for verification).
   - **Password** – minimum 8 characters, mix of letters and numbers.
   - **Confirm Password** – re-enter to confirm.
4. (Optional) Enter a **Referral Code** if a friend or associate shared theirs – you both earn rewards.
5. Tick the **"I agree to the Terms & Conditions"** checkbox.
6. Click **"Create Account"**.
7. Check your email for the **verification link** and click it. Verified accounts unlock saved searches, alerts, and dashboard features.

> **Quick Tip:** You can also sign up using **Google** or **Facebook** in one click via the social login buttons.

### 3. Logging In

1. Click **"Login"** in the header.
2. Enter your **email or phone number** in the *Identity* field.
3. Enter your **password**.
4. Click **"Login"**.

If you've enabled 2FA, you'll be prompted for a 6-digit code from your authenticator app.

**Forgot your password?** Click **"Forgot Password?"** below the login form. Enter your email — we'll send a reset link valid for 30 minutes.

---

## Browsing Properties

The **Properties** page is the heart of APS Dream Home. Access it from the top navigation or visit `/properties` directly.

### Filters (Left Panel)

You can narrow results by:

- **Property Type** – Flat, Villa, Plot, Farmhouse, Shop, Bungalow.
- **Listing Type** – Buy, Rent.
- **Location** – City, district, or colony (with autocomplete typeahead).
- **Price Range** – Slider with min/max values in ₹ Lakh or Crore.
- **Bedrooms (BHK)** – 1, 2, 3, 4+.
- **Bathrooms** – 1, 2, 3+.
- **Furnishing** – Unfurnished, Semi-furnished, Fully-furnished.
- **Area (sq. ft.)** – Minimum and maximum.
- **Year Built** – Newly constructed, 5 years, 10+ years.

### Sorting

Sort results by:

- **Newest First** (default)
- **Price: Low to High**
- **Price: High to Low**
- **Most Viewed**
- **Recently Updated**

### Search Bar (Header)

The **typeahead search** in the header offers instant suggestions as you type. It searches across **property names**, **addresses**, and **locations**. Pick a suggestion to jump directly to a filtered results page.

### Map View

Toggle **Map View** to see properties pinned on an interactive map. Click any pin to see a popup card with price, photos, and a *"View Details"* button.

### Property Details Page

Click any property card to open its full detail page:

- High-resolution photo gallery (swipe on mobile).
- Full description, amenities, and key features.
- Floor plans (where available).
- Embedded location map.
- Seller / agent contact details.
- "Schedule a Visit" and "Inquire Now" buttons.
- Similar properties at the bottom.

---

## Saving Properties (Favorites)

Found a property you love but not ready to inquire? **Save it to favorites**.

1. On any property card or detail page, click the **♥ heart icon**.
2. The icon turns red and the property is added to your favorites list.
3. View all favorites from **Dashboard → Favorites** or visit `/user/favorites` directly.
4. Click the heart again to remove a property from favorites.

> Favorites are private to your account and persist forever (or until you remove them).

---

## Saved Searches & Email Alerts

Tired of repeating the same filters? **Save a search** and we'll email you whenever new matching properties are listed.

### Creating a Saved Search

1. Go to **Properties** and apply your filters (type, price, bedrooms, location, etc.).
2. Click the **"Save This Search"** button at the top of the results.
3. A modal pops up – we auto-suggest a name (e.g., *"3BHK in Gorakhpur under ₹50L"*).
4. Toggle **"Email me when new properties match"** ON.
5. Click **"Save"**.

### Managing Saved Searches

Visit **Dashboard → Saved Searches** (`/user/saved-searches`):

- See **all your saved searches** with the count of current matches.
- Click any search to **re-run it** with the latest properties.
- **Rename** or **delete** searches.
- **Toggle email alerts** ON/OFF for each search.
- View the **alert history** – when each alert was sent and which property triggered it.

### Email Alert Frequency

We process saved-search alerts **daily at 9:00 AM IST**. You'll receive a digest email listing only **new properties** that match (no duplicates). Each email includes an *"Unsubscribe"* link to instantly disable that specific alert.

---

## Posting Your Property

Selling or renting your plot, house, flat, shop, or farmhouse? List it for free on APS Dream Home.

### Steps to List a Property

1. From the header, click **"List Your Property"** (or visit `/list-property`).
2. Choose **Property Type**: Plot / House / Flat / Shop / Farmhouse / Bungalow.
3. Choose **Listing Type**: Sell or Rent.
4. Fill in the form:
   - **Your Name & Phone** (auto-filled if logged in).
   - **Property Title** (e.g., "3BHK in Suryoday Heights").
   - **Address** – street, locality, city, district, state, pincode (pincode auto-fills city/state).
   - **Area (sq. ft.)**, **Price**, **Price Type** (fixed / negotiable / on request).
   - **Description** – at least 100 characters describing the property.
   - **Bedrooms, Bathrooms, Furnishing, Year Built** (for buildings).
5. **Upload photos** – up to 10 images, JPG/PNG/WEBP, max 5MB each. Our system **auto-resizes** and **strips EXIF metadata** for your privacy.
6. Tick "I confirm this information is accurate".
7. Click **"Submit Property"**.

### What Happens Next?

- Your listing enters **Pending Review** status.
- Our team verifies the listing within **24–48 hours**.
- Once **approved**, your property goes **live** on the public Properties page.
- You receive an **email and SMS notification** when approved.
- Buyers can now contact you via the inquiry form on your listing.

> **Pro Tip:** Properties with **5+ photos**, **detailed description**, and a **realistic price** receive 3x more inquiries.

---

## Booking Property Visits

Want to see a plot or property in person before deciding? Use our **visit scheduling system**.

1. On any property detail page, click **"Schedule a Visit"**.
2. Pick a **date** from the calendar (only available dates are clickable).
3. Pick an **available time slot** (we use real-time locking to prevent double-booking).
4. Enter your **contact info** (auto-filled if logged in).
5. Add **special requests** (e.g., "Please prepare floor plans").
6. Click **"Confirm Visit"**.

You'll receive:
- A **confirmation email and SMS** with directions and the agent's name and phone.
- A **calendar invite (.ics file)** to add the visit to Google/Outlook/Apple Calendar.
- A **reminder SMS** 2 hours before the visit.

### Managing Your Visits

Go to **Dashboard → My Visits** (`/visits/my-visits`):

- See **upcoming**, **completed**, and **cancelled** visits.
- **Reschedule** or **cancel** (at least 4 hours in advance).
- **Submit feedback** after the visit (helps us improve).

---

## Your Customer Dashboard

Your **personal dashboard** is the command center for everything you do on APS Dream Home. Access it at `/user/dashboard` after logging in.

### Dashboard Widgets

- **Welcome Card** – greets you by name and shows your account stats.
- **My Properties** – count of properties you've listed (with status).
- **My Inquiries** – count of inquiries you've sent.
- **Property Views** – how many people viewed your listings.
- **Saved Searches** – count + quick link.
- **Favorites** – count + quick link.
- **Bookings** – plots/flats you've booked (with payment status).
- **Notifications** – unread alerts (clicking marks them read).
- **Refer & Earn** – your referral code, share buttons, earnings.

### Quick Actions

- **Post a Property**
- **Browse Properties**
- **Manage Saved Searches**
- **View Favorites**
- **Manage Email Alerts**
- **Edit Profile**

---

## Account Settings & Profile

### Editing Your Profile

Go to **Dashboard → Profile** (`/user/profile`):

- Update **name, email, phone**.
- Add **profile photo** (drag-and-drop or click to upload).
- Set **preferred language** (English / हिंदी).
- Change **password** (requires current password).
- Update **address** and **city** for personalized recommendations.

### Bank Details (For Sellers and Associates)

If you list properties or earn commissions, add bank details so we can pay you:

1. Go to **Dashboard → Bank Details** (`/user/bank-details`).
2. Enter **Account Holder Name**, **Account Number**, **IFSC Code**, **Bank Name**, **Branch**.
3. As you type IFSC, branch info auto-fills via our bank API.
4. Optionally add **UPI ID** for faster small payouts.
5. Click **"Save"** – we verify with a ₹1 penny-drop verification (refunded).

### Notification Preferences

Choose how we contact you:

- **Email** – property updates, alerts, system notices.
- **SMS** – critical alerts (visit reminders, payment confirmations).
- **WhatsApp** – marketing campaigns, promotions.
- **Push Notifications** – real-time browser/mobile alerts.

Toggle each independently from **Profile → Notification Preferences**.

---

## Two-Factor Authentication (2FA)

Add an extra layer of security with **time-based one-time passwords (TOTP)**.

### Setting Up 2FA

1. Go to **Dashboard → Profile → Security → Two-Factor Authentication**.
2. Click **"Enable 2FA"**.
3. Open an authenticator app on your phone:
   - **Google Authenticator** (free, Android/iOS)
   - **Microsoft Authenticator** (free)
   - **Authy** (free, supports backup)
4. Scan the **QR code** with your app.
5. Your app shows a 6-digit code that refreshes every 30 seconds.
6. Enter that code on our website and click **"Verify & Enable"**.
7. **Save your 8 backup codes** in a safe place (download as PDF). Use them if you lose your phone.

### Logging In with 2FA

After entering email + password, you'll see a 2FA prompt. Open your authenticator app, enter the current 6-digit code, and click **"Verify"**.

### Disabling 2FA

Go to **Profile → Security → Disable 2FA**. Enter your password to confirm.

> **Lost your phone and backup codes?** Email support@apsdreamhome.com with ID proof — we'll manually disable 2FA after identity verification.

---

## Mobile App Usage

APS Dream Home offers a **Progressive Web App (PWA)** that installs like a native mobile app — no app store needed.

### Installing the PWA

**On Android (Chrome / Edge):**

1. Visit the site in your browser.
2. Tap the **three-dot menu** → **"Add to Home Screen"**.
3. Confirm. The app icon appears on your home screen.

**On iPhone / iPad (Safari):**

1. Visit the site in Safari.
2. Tap the **Share button** (square with up-arrow).
3. Scroll and tap **"Add to Home Screen"**.
4. Tap **"Add"**.

**On Desktop (Chrome / Edge):**

1. Look for the **install icon** in the address bar (📥 or ⊕).
2. Click it, then click **"Install"**.

### Mobile App Features

- **Offline mode** – browse cached property listings even without internet.
- **Push notifications** – instant alerts for new matching properties.
- **Camera integration** – take photos directly when listing your property.
- **Biometric login** – fingerprint / Face ID (on supported devices).
- **Voice search** – say "3 BHK in Gorakhpur" to filter quickly.

---

## Property Comparison Tool

Compare up to **4 properties side-by-side** to make a confident decision.

1. On any property card, click **"+ Compare"**.
2. The comparison bar appears at the bottom showing selected properties.
3. Click **"Compare Now"** when you've added 2+ properties.
4. See a side-by-side table comparing price, area, BHK, amenities, location, year built.
5. The system **highlights the best value** in each row (cheapest price, largest area, etc.).
6. Click **"Share"** to send the comparison via a unique URL (no login required for viewers).

---

## Property Alerts & Subscriptions

Beyond saved searches, you can subscribe to **broad market alerts** for a city or property type.

1. Go to **Property Alerts** (`/property-alerts/subscribe`).
2. Choose **alert criteria**:
   - City / Location
   - Property type (Plot, Flat, etc.)
   - Price range
   - Notification channels (Email, SMS, WhatsApp, Push).
3. Click **"Subscribe"**.
4. Receive alerts daily, weekly, or instantly (your choice).

**Unsubscribe** any time via the link in every alert email or from **Dashboard → Manage Alerts**.

---

## Live Chat Support

Need help right now? Click the **chat bubble** (bottom-right of every page) to start a live conversation.

- A live agent is available **Mon–Sat, 9 AM – 7 PM IST**.
- Outside hours, our **AI chatbot Riya** answers common questions in **English and Hindi**.
- Conversations are saved – you can resume them later from any device.
- For complex queries, the chatbot transfers you to a human agent or **logs a ticket** that the support team responds to within 24 hours.

---

## Free Tools

We provide several free calculators and tools — no signup needed.

- **EMI Calculator** (`/tools/emi-calculator`) – calculate monthly home-loan payments based on principal, interest rate, and tenure.
- **Stamp Duty Calculator** (`/tools/stamp-duty`) – estimate stamp duty + registration fees for any Indian state.
- **Plot Area Converter** (`/tools/plot-converter`) – convert between sq ft, sq m, acres, bigha, gaj, katha, marla.
- **Property Valuation** (`/tools/valuation`) – get an instant AI-powered estimate of your property's market value based on location, size, and amenities.
- **Development Cost Calculator** (`/tools/development-cost`) – estimate construction cost per sq ft for villas and apartments.

---

## Multi-Language Support

The entire site is available in **English (default)** and **हिंदी (Hindi)**.

To change language:

1. Click the **language switcher** in the header (🇬🇧 EN / 🇮🇳 हिं).
2. The page reloads in your chosen language.
3. Your preference is saved for 30 days via cookie.

> All UI elements, emails, SMS, and notifications respect your language preference.

---

## Privacy, Security & Your Rights

We take your privacy seriously. Highlights from our [Privacy Policy](/privacy):

- **HTTPS-only** – all data is encrypted in transit (TLS 1.3).
- **bcrypt + Argon2id** password hashing – we never store plaintext passwords.
- **Auto-removal of EXIF metadata** from uploaded photos (no GPS leak).
- **GDPR-style data export** – request a download of all your data via Profile → Privacy.
- **Right to deletion** – delete your account permanently from Profile → Delete Account.
- **No spam** – we never sell your email or phone to third parties.
- **Cookie consent** – tracking cookies only run with your explicit opt-in.

---

## Frequently Asked Questions

### Q1. Is APS Dream Home free to use?

Yes. Browsing, searching, saving favorites, posting one property, and using all calculators are 100% free. We charge a small fee only for premium listings, featured placements, and successful sale brokerage (3%).

### Q2. How do I know a listing is genuine?

All properties undergo **24–48-hour admin verification** before going live. Verified listings show a **green ✓ "Verified" badge**. Always do an in-person visit before paying any amount.

### Q3. Will my contact details be public?

No. Buyers see your name + photo, but your phone and email are **masked** until you accept their inquiry. You retain full control over who contacts you.

### Q4. How are commissions calculated for associates?

Associates earn a **base commission** on every successful sale, plus **MLM rank bonuses**. Full breakdown is in the [Associate Manual](/docs/ASSOCIATE_MANUAL.md).

### Q5. Can I post the same property multiple times?

No. Duplicate listings are removed automatically. List each property once with full details.

### Q6. What payment methods do you accept for premium services?

Razorpay (UPI, cards, net-banking, wallets), Paytm, and direct bank transfer.

### Q7. How do I delete my account?

Go to **Profile → Privacy → Delete My Account**. Your data is removed within 30 days as per our data retention policy.

### Q8. The website isn't loading correctly. What should I do?

1. **Hard refresh**: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac).
2. **Clear browser cache** for the site.
3. Try a **different browser** (Chrome, Edge, Firefox, Safari).
4. **Disable ad-blockers** for our domain.
5. If still broken, [contact support](#contact-support).

### Q9. I forgot which email I registered with.

Email support@apsdreamhome.com with your full name and the phone number you registered. We'll look it up.

### Q10. Can I list properties outside India?

Currently we serve **Uttar Pradesh, Bihar, MP, Rajasthan, Maharashtra, Delhi, and 13 other Indian states**. International listings are coming in 2027.

### Q11. Need more help?

See our [User Guide](/docs/USER_GUIDE.md) or use the **Live Chat** widget in the bottom-right of every page.

---

## Contact Support

| Channel    | Details                                  | Hours                |
|------------|------------------------------------------|----------------------|
| Phone      | **+91 7007444842**                       | Mon–Sat 9 AM – 7 PM  |
| WhatsApp   | **+91 92771 21112**                      | Mon–Sat 9 AM – 7 PM  |
| Email      | **support@apsdreamhome.com**             | Replies within 24h   |
| Live Chat  | Bubble icon on every page                | 24/7 (AI), 9-7 Human |
| Office     | Gorakhpur, Uttar Pradesh, India          | Mon–Sat 10 AM – 6 PM |

For sales / partnership inquiries: **sales@apsdreamhome.com**
For grievances / data requests: **dpo@apsdreamhome.com**

---

**Last Updated:** June 5, 2026
**Document Version:** 1.0
**See also:** [Admin Manual](ADMIN_MANUAL.md) · [Developer Guide](DEVELOPER_GUIDE.md) · [API Reference](API.md)
