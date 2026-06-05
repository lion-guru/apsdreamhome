# APS Dream Home – Admin Manual

The **APS Dream Home Admin Panel** is the central control room for managing users, properties, leads, bookings, finance, HR, marketing, reports, and system settings. This manual covers every administrative function for managers, admins, and super-admins.

> **Audience:** Admins, super-admins, managers, employees with administrative roles.
> **Prerequisites:** A valid admin account and at least *manager* role permissions.

---

## Table of Contents

1. [Admin Login](#admin-login)
2. [Dashboard Overview](#dashboard-overview)
3. [User Management (CRUD)](#user-management)
4. [Property Approval Workflow](#property-approval-workflow)
5. [Lead Management (Kanban Board)](#lead-management-kanban-board)
6. [Booking Management](#booking-management)
7. [Colony / Project Management](#colony--project-management)
8. [Plot Inventory & Pricing](#plot-inventory--pricing)
9. [Finance Module](#finance-module)
10. [HRM Module](#hrm-module-human-resources)
11. [MLM & Commission Management](#mlm--commission-management)
12. [Marketing & Campaigns](#marketing--campaigns)
13. [Reports & Analytics](#reports--analytics)
14. [Live Chat Management](#live-chat-management)
15. [Voice AI Agents](#voice-ai-agents)
16. [Property Auctions](#property-auctions)
17. [Drip Campaigns](#drip-campaigns)
18. [Settings (Cache, Backups, Monitoring)](#settings)
19. [Email Templates](#email-templates)
20. [SMS / WhatsApp Templates](#sms--whatsapp-templates)
21. [Push Notifications](#push-notifications)
22. [Two-Factor Authentication (2FA Management)](#two-factor-authentication-management)
23. [Role-Based Access Control (RBAC)](#role-based-access-control-rbac)
24. [API Keys Management](#api-keys-management)
25. [Audit Log](#audit-log)
26. [Webhooks](#webhooks)
27. [Bulk Import / Export](#bulk-import--export)
28. [System Health Monitoring](#system-health-monitoring)
29. [Troubleshooting Common Issues](#troubleshooting-common-issues)

---

## Admin Login

1. Navigate to **`/admin/login`** (e.g., `http://localhost/apsdreamhome/admin/login`).
2. Enter your **admin email** and **password**.
3. (Optional) If 2FA is enabled, enter the 6-digit code from your authenticator app.
4. Solve the **CAPTCHA** (production only).
5. Click **"Login"**.

> **Test / Development:** Use `?test_login=1` query parameter to bypass CAPTCHA and password (e.g., `/admin/login?test_login=1`). This only works in `DEBUG_MODE=true`.

### Login Security

- **Failed logins** are throttled: 5 attempts in 15 minutes triggers a 30-minute lockout.
- All login attempts are logged to the **audit log** (`/admin/audit-log`).
- After 60 minutes of inactivity, the session expires automatically.

### First-Time Login

If you have just been added as an admin, you'll receive a one-time welcome email. Click the link to set your initial password. **Change your password immediately** and **enable 2FA** for production accounts.

---

## Dashboard Overview

The **Admin Dashboard** (`/admin/dashboard`) gives you a real-time snapshot of the business.

### KPI Cards (Top Row)

- **Total Users** – customer + associate + agent + employee counts.
- **Total Properties** – approved + pending + rejected.
- **Total Leads** – with conversion-rate %.
- **Total Bookings** – this month + lifetime revenue.

### Charts

- **Leads Over Time** – 30-day line chart by source.
- **Revenue Trend** – 6-month bar chart (Chart.js).
- **Pipeline Breakdown** – funnel by stage (new → contacted → qualified → won/lost).
- **Property Type Distribution** – pie chart (plots/flats/villas/etc.).

### Recent Activity Feed

Live stream of the most recent 20 events:
- New user registrations
- Property submissions
- Lead inquiries
- Visit bookings
- Payments received
- Admin logins

### Quick Actions

- Approve pending properties (with count badge)
- Review pending bookings
- Respond to support tickets
- Send broadcast notification

### Role-Specific Dashboards

Different admin roles see customized dashboards:

| Role | URL | Focus |
|------|-----|-------|
| Super Admin | `/admin/dashboard` | Everything |
| CEO | `/admin/ceo-dashboard` | High-level KPIs, P&L |
| CFO | `/admin/cfo-dashboard` | Finance, cash-flow, GST |
| Builder | `/admin/builder-dashboard` | Construction, materials |
| Sales Manager | `/admin/sales-dashboard` | Pipeline, agent performance |
| Chief Manager | `/admin/cm-dashboard` | Operations, team metrics |
| HR Manager | `/admin/hr-dashboard` | Attendance, payroll |

---

## User Management

Access at **`/admin/users`**.

### Listing Users

- Filter by **role** (admin, agent, associate, customer, employee, farmer).
- Filter by **status** (active, suspended, pending).
- Search by name, email, or phone.
- Sort by registration date, last login, or activity score.
- Bulk select to **suspend**, **activate**, or **delete** multiple users.

### Creating a User

1. Click **"+ Add User"**.
2. Fill **name, email, phone, role, password** (or generate random).
3. Assign **department / branch** (for employees).
4. Tick **"Send welcome email"** to auto-email login credentials.
5. Click **"Create"**.

### Editing a User

Click any user row to open the **detail page**:

- **Profile tab** – basic info, profile photo, address.
- **Activity tab** – login history, IP addresses.
- **Properties tab** – properties they've listed.
- **Bookings tab** – their bookings.
- **Commissions tab** – earnings (for associates).
- **Permissions tab** – custom role overrides.

### Suspending / Deleting Users

- **Suspend** – user can't log in but data is preserved.
- **Delete** – soft-delete with 30-day recovery window. After 30 days, all personal data is anonymized to comply with GDPR-style requests.

### Impersonation (Super-Admin Only)

Super-admins can **"Login as User"** to debug user-reported issues. All impersonation actions are logged.

---

## Property Approval Workflow

Access at **`/admin/user-properties`** or **`/admin/properties`**.

### Reviewing a New Listing

1. Open the **Pending Properties** tab (badge shows count).
2. Click a listing to see full details + uploaded photos.
3. Verify:
   - **Title and description** are accurate and non-spam.
   - **Price** is realistic (compare to similar listings).
   - **Photos** are not stock images or watermarked.
   - **Location** is plottable on the map.
   - **Contact info** matches the user's account.
4. Click **"Approve"** to make it live, or **"Reject"** with a reason.
5. Rejected sellers get an email with the rejection reason and can resubmit.

### Bulk Actions

Select multiple properties → choose **Approve**, **Reject**, or **Mark as Sold** from the bulk dropdown.

### Featured Properties

Toggle the **"⭐ Featured"** flag on a property to highlight it on the homepage and search results (premium listings).

### Property Performance

Click any approved property to see:
- **Views** (daily / total)
- **Inquiries received**
- **Visits scheduled**
- **Comparison count** (how many users compared it)
- **Average time on page**

---

## Lead Management (Kanban Board)

Visit **`/admin/lead-kanban`** for the drag-and-drop pipeline view.

### Kanban Stages

1. **New** – just-submitted leads.
2. **Contacted** – first call/email made.
3. **Qualified** – budget and intent confirmed.
4. **Site Visit Scheduled** – property visit booked.
5. **Negotiation** – discussing price.
6. **Won** – sale closed.
7. **Lost** – deal didn't close (add reason).
8. **Follow-Up** – nurture for later.

### Working a Lead

1. Click a card to expand details.
2. See **contact info, source, score (0–100), assigned agent, history**.
3. Add **notes**, **next-action date**, **call recording**.
4. **Drag the card** to a new stage to update status.
5. **Color-coded scores**: green (80+), yellow (50–79), red (<50).
6. **Auto-assign**: leads can be auto-routed to agents based on territory, language, or availability.

### Saved Filters

Save common filters like *"Hot Leads in Gorakhpur"* or *"Cold leads > 30 days"* for quick reuse.

### Lead Sources

Track where leads come from: Website, Phone, WhatsApp, Walk-in, Referral, Social Media, AI Chatbot, Cold Calling.

---

## Booking Management

Access at **`/admin/bookings`**.

### Booking Lifecycle

1. **Pending** – customer submitted booking, waiting for admin approval.
2. **Confirmed** – admin approved, awaiting payment.
3. **Partially Paid** – initial deposit received.
4. **Fully Paid** – all installments complete.
5. **Cancelled** – customer or admin cancelled.

### Approving a Booking

1. Click a pending booking.
2. Review **plot details, customer info, agreed price, payment plan**.
3. Click **"Approve"** – customer gets confirmation email + payment link.
4. Or **"Reject"** with reason (refunds any deposit).

### Recording Payments

1. Open the booking → **Payments** tab.
2. Click **"Record Payment"**.
3. Enter **amount, date, method (UPI/cheque/bank-transfer), reference number**.
4. Upload **receipt scan** (optional).
5. The booking status auto-updates and customer gets a receipt PDF via email.

### Cancellation & Refunds

1. Open booking → click **"Cancel Booking"**.
2. Enter **reason** and **refund amount** (per cancellation policy).
3. Choose **refund mode** (UPI, NEFT, cheque).
4. Status updates and a refund-confirmation email is sent.

---

## Colony / Project Management

Access at **`/admin/colonies`** (or `/admin/projects`).

### Creating a Colony

1. Click **"+ Add Colony"**.
2. Fill:
   - **Name, location, district, state, pincode**.
   - **Total area** (acres / sq ft).
   - **Description** (rich-text editor with images).
   - **Amenities** (multi-select: park, school, hospital, club, gym, security, etc.).
   - **Hero photo + gallery** (up to 30 images).
   - **Master plan PDF** (for download).
   - **Launch date, possession date, RERA number** (mandatory for new launches).
3. Click **"Save"**.

### Adding Plots to a Colony

After creating the colony, go to the **Plots** tab:

1. Click **"+ Add Plot"** (or **"Bulk Import"**).
2. For each plot: number, dimensions (W×L), area (sq ft), facing, price per sq ft, status (available/booked/sold).
3. Or upload a **CSV** with all plot details.

### Pricing Strategy

Use the **Plot Cost Calculator** (`/admin/plot-costs/colony/{id}`):

- Track **acquisition cost, development cost, marketing cost** per plot.
- See **profit margin** in real time.
- Adjust selling price with one click; **price history** is automatically logged.

---

## Plot Inventory & Pricing

Access at **`/admin/inventory`** for the inventory grid.

### Inventory Grid View

Color-coded by status:
- 🟢 **Available**
- 🟡 **Reserved**
- 🔴 **Sold**
- ⚫ **Blocked** (legal/internal)

Click any plot for instant edit (price, status, customer link).

### Bulk Price Update

1. Filter plots by colony or status.
2. Select all.
3. Click **"Update Price"**.
4. Apply a **percentage increase** or a **fixed amount**.
5. Preview changes before confirming.
6. Old prices are stored in **`price_history`** table for auditability.

---

## Finance Module

Comprehensive finance suite under **`/admin/finance`**.

### Sub-modules

- **Invoices** (`/admin/invoices`) – create, send, track property-sale invoices.
- **Expenses** (`/admin/expenses`) – office, marketing, salaries, utilities.
- **Income** (`/admin/accounting/income`) – sales revenue tracking.
- **Bank Accounts** – multiple accounts with reconciliation.
- **GST Returns** – auto-calculate GSTR-1 / GSTR-3B for filing.
- **Cash-Flow Projections** – 6-month forward forecast.
- **Budget vs Actual** – departmental budget tracking.
- **Tax Slabs** – India-specific income tax brackets and TDS rates.

### Creating an Invoice

1. **Invoices → + New**.
2. Choose customer (search-as-you-type from users table).
3. Choose property/plot.
4. Enter **line items** (description, quantity, unit price, tax).
5. System auto-calculates **subtotal, GST, total**.
6. Click **"Generate PDF"** then **"Send via Email"**.
7. Invoice status: **Draft → Sent → Partially Paid → Paid → Overdue**.

### Reconciliation

1. Upload **bank statement CSV** (HDFC/SBI/ICICI/etc.).
2. System matches transactions to invoices / expenses automatically.
3. Manually link unmatched ones.
4. Generates a **bank reconciliation report** monthly.

---

## HRM Module (Human Resources)

Access at **`/admin/hr/users`** for employee list.

### Employee Management

- Add employee → assign department, designation, salary.
- Manage **attendance** with daily check-in/out.
- Track **leaves** (casual, sick, paid).
- **Payroll** generation (monthly, with deductions/PF/PT/TDS).
- **Performance reviews** (quarterly).
- **Document storage** (offer letter, ID proof, contract).
- **KPIs and goals** tracking.

### Attendance

1. **`/admin/hrm/attendance`** shows the live attendance board.
2. Mark **present, absent, half-day, on-leave** in bulk.
3. Calculate working hours from biometric integration (optional).
4. **Late mark** auto-triggered if check-in is past 9:30 AM.

### Payroll

1. **`/admin/hrm/payroll`** → click **"Generate"** for current month.
2. System pulls **attendance + advances + bonuses + deductions**.
3. Review the draft, adjust if needed.
4. Click **"Approve & Disburse"** to send salary slips via email.
5. Salary disbursement file (NEFT batch) downloadable as **CSV**.

### Recruitment

- Post **jobs** at `/careers` (publicly visible).
- Receive applications → review CVs → schedule interviews.
- Track applicant pipeline: **applied → shortlisted → interviewed → offered → hired/rejected**.

---

## MLM & Commission Management

Access at **`/admin/mlm`** and `/admin/commissions`.

### Associate Network

- View **network tree** at `/admin/mlm/tree` (visual hierarchy with downline counts).
- See **rank distribution** at `/admin/mlm/ranks` (bronze, silver, gold, platinum, diamond, royal).
- **Manually adjust** rank for any associate.
- View **rank-up requirements** per tier.

### Commission Rates

`/admin/mlm/commission-rates`:

- Set **direct sale commission** (default 3%).
- Set **MLM bonus** per level (Level 1: 1%, Level 2: 0.5%, etc.).
- Configure **rank bonuses** (bronze: 0.5%, gold: 1.5%, etc.).
- Schedule **promotions** (e.g., "+1% all October").

### Payout Workflow

1. **`/admin/payouts`** lists all pending payouts.
2. Verify amount, recipient, bank details.
3. Click **"Approve"** → generates a NEFT batch CSV.
4. Upload payment proof.
5. System marks payout as **"Paid"** and emails the associate.

---

## Marketing & Campaigns

Access at **`/admin/marketing-campaigns`**.

### Campaign Types

- **Email** (HTML templates with personalization).
- **SMS** (160 chars or 1600 with concatenation).
- **WhatsApp** (templates approved by Meta).
- **Push notifications** (web + mobile).
- **In-app** (banner inside the customer dashboard).

### Creating a Campaign

1. Click **"+ New Campaign"**.
2. Choose **channel**.
3. Pick a **template** or write from scratch using `{{first_name}}`, `{{property_link}}`, etc., as merge tags.
4. **Target audience**: filter by role, city, last-active date, saved-search filters, etc. Audience size displayed in real time.
5. **Schedule**: send now or pick a future date/time.
6. Preview the message on **mobile / desktop**.
7. Click **"Launch"**.

### Tracking

For every campaign, see:
- **Sent count**
- **Delivered**
- **Opened (with open-rate %)**
- **Clicked (with CTR %)**
- **Unsubscribed**
- **Conversions** (leads / bookings attributed)

---

## Reports & Analytics

`/admin/reports` is the report hub.

### Pre-Built Reports

| Report | URL | Purpose |
|--------|-----|---------|
| Sales Funnel | `/admin/reports/funnel` | 4-stage conversion |
| Agent Performance | `/admin/reports/agent-performance` | Leaderboard, ratings |
| Monthly Conversion | `/admin/reports/conversion` | 12-month trend |
| MLM Growth | `/admin/reports/mlm-growth` | Associate count, revenue |
| ROI Calculator | `/admin/reports/roi` | Project-level ROI |
| Custom Report | `/admin/reports-engine` | Build your own |

### Custom Report Builder

1. **`/admin/reports-engine`** → click **"+ New Report"**.
2. Choose a **base entity** (Leads, Properties, Bookings, Users, etc.).
3. Drag **columns** to add.
4. Apply **filters**.
5. Choose **grouping** and **sort**.
6. Choose **chart type** (table, bar, line, pie).
7. **Save** and schedule **daily/weekly email delivery** as PDF or CSV.

### Real-Time Analytics

`/admin/features/realtime-analytics` shows a live dashboard refreshing every 60 seconds:
- Active users right now
- Leads coming in (last hour)
- Bookings (today)
- Revenue (today)

---

## Live Chat Management

`/admin/live-chat` is the agent console.

### Agent Workflow

- See all **open chats** with visitor info (page, referrer, country).
- Pick up an unassigned chat by clicking **"Take Chat"**.
- Use **quick-reply templates** for common questions.
- Add **internal notes** (only visible to other agents).
- **Transfer** to another agent if needed.
- **End chat** with summary; system auto-sends a transcript via email.

### Settings

`/admin/live-chat/settings`:

- Toggle **AI chatbot** fallback when no agent online.
- Set **business hours**.
- Configure **welcome message** and **away message**.
- Manage **quick-reply templates**.

---

## Voice AI Agents

`/admin/voice-agents` powers automated phone outreach for leads.

### Built-In Agents

- **Site Visit Booker** – schedules visits.
- **Property Consultant** – answers questions about a specific property.
- **Lead Nurturer** – follows up cold leads.

### Scheduling Calls

1. **`/admin/voice-agents/schedule`** → click **"+ Bulk Schedule"**.
2. Filter leads → click **"Auto-Assign Agent"**.
3. Pick **time window** (e.g., 10 AM – 12 PM tomorrow).
4. System spaces calls 5 min apart to avoid overload.
5. Click **"Schedule"**.

### Reviewing Results

`/admin/voice-agents/extracted-leads`:

- Each call's **transcript and sentiment**.
- Extracted info (budget, timeline, preferred location).
- Status: **Verified / Pending / Failed**.
- Convert qualified leads to real leads with one click.

---

## Property Auctions

`/admin/auctions` for online auctions (English / Sealed / Dutch / Reserve).

### Creating an Auction

1. Pick a **property** from inventory.
2. Choose **type** (English = open bids, Sealed = blind bids, Dutch = price drops over time, Reserve = min price).
3. Set **starting price, reserve price, bid increment, deposit amount**.
4. Set **start + end timestamps**.
5. Toggle **auto-extend** (if bid placed in last 5 min, extend by 10 min).
6. Click **"Schedule Auction"**.

### Live Auction

- Auction status: **Pending → Live → Ending Soon → Ended**.
- Real-time bid log.
- **Verify deposits** before allowing bids.
- After auction ends, winner notified; pending bookings created automatically.

---

## Drip Campaigns

`/admin/drip-campaigns` automate multi-step email sequences for lead nurturing.

### Pre-Built Campaigns

- **New-Lead Welcome** (5 emails over 7 days)
- **Hot-Lead Conversion** (3 emails over 3 days)
- **Cold-Lead Re-engagement** (4 emails over 14 days)
- **Post-Visit Follow-Up** (2 emails after a visit)

### Customizing

1. Open campaign → **Sequence** tab.
2. Each email has: **delay (X days)**, **subject**, **HTML body** with `{{merge_tags}}`.
3. Drag-drop to reorder.
4. Save.

### Auto-Enrollment

Configure triggers:
- New lead created → enroll in *New-Lead Welcome*.
- Lead idle 14 days → enroll in *Cold-Lead Re-engagement*.
- Site visit completed → enroll in *Post-Visit Follow-Up*.

---

## Settings

`/admin/settings` covers everything system-wide.

### General Settings

- Company name, logo, tagline.
- Contact info, address.
- Working hours.
- Default language, currency, timezone.
- Maintenance-mode toggle (shows a friendly banner to visitors).

### Cache Management

`/admin/cache`:

- See current **cache driver** (Redis / File fallback).
- View **hit rate, total keys, memory used**.
- **Flush All** – clears both Redis + file caches.
- **Flush Redis Only** – useful for debugging.
- **Test Connection** – verifies Redis is reachable.

### Backup Management

`/admin/backups`:

- View list of automatic backups (daily database + uploads).
- **Download** any backup (.sql.gz + assets.tar.gz).
- **Trigger manual backup** on demand.
- **Restore** by uploading a backup file (super-admin only).

### Monitoring

`/admin/system-health`:

- **PHP version + extensions**.
- **Database health** (size, query throughput).
- **Disk usage** (warning >70%, danger >90%).
- **Memory usage**.
- **Cache health**.
- **All services load** check.

---

## Email Templates

`/admin/email-templates` to manage HTML emails.

### Pre-Built Templates

- Welcome email
- Property approved
- Property rejected
- Booking confirmation
- Visit reminder
- Saved-search digest
- Password reset
- 2FA setup
- Payment receipt
- Commission earned

### Editing

1. Click a template.
2. Live preview on right side.
3. Edit subject, HTML body, plain-text fallback.
4. Insert merge tags: `{{first_name}}`, `{{property_title}}`, `{{amount}}`, etc.
5. Click **"Send Test"** to email yourself a preview.
6. Save.

---

## SMS / WhatsApp Templates

`/admin/sms-templates` and `/admin/whatsapp-integration`.

- Pre-built templates for OTP, visit reminders, payment confirmations.
- **WhatsApp Business** templates must be Meta-approved (status visible).
- Manage **opt-out keywords** (STOP, REMOVE, etc.) per channel.

---

## Push Notifications

`/admin/notifications/push` to send web + mobile push.

1. Choose **target audience**.
2. Compose **title (max 50 chars), body (max 150), icon, deep-link URL**.
3. Preview on Android / iOS / desktop.
4. **Send Now** or schedule.
5. Track **delivered / clicked** in real time.

---

## Two-Factor Authentication Management

`/admin/users/{id}/2fa`:

- See if a user has 2FA enabled.
- **Reset 2FA** for a user (e.g., they lost their phone) – they'll be prompted to set it up again on next login.
- **Enforce 2FA** for all admins via global setting.

---

## Role-Based Access Control (RBAC)

`/admin/roles` to manage roles and permissions.

### Default Roles

| Role | Description |
|------|-------------|
| super_admin | Everything (cannot be deleted) |
| admin | All admin features except super-admin settings |
| manager | Manage users / properties / leads in their branch |
| employee | Limited daily-ops access |
| agent | Personal pipeline + customer chat |
| associate | MLM dashboard + own commissions |
| customer | End-user portal |
| farmer | Land seller portal |

### Creating Custom Role

1. **`/admin/roles/create`**.
2. Name (e.g., *"Branch Manager – Gorakhpur"*).
3. Toggle **permissions** in tree view (e.g., `properties.approve`, `bookings.cancel`).
4. Save.
5. Assign to users via **`/admin/users/{id}/edit → Role`**.

---

## API Keys Management

`/admin/api-keys` for third-party integrations.

1. Click **"+ Create Key"**.
2. Name (e.g., *"Mobile App Production"*).
3. Pick **scopes** (read:leads, write:properties, etc.).
4. Set **rate limit** (default 60 req/min).
5. Set **expiry date** (optional).
6. Click **"Create"** → the **plaintext secret is shown ONCE**. Copy it immediately.
7. Use as `Authorization: Bearer <api_key>:<api_secret>` in API calls.

To revoke, click **"Revoke"** – the key stops working immediately.

---

## Audit Log

`/admin/audit-log` keeps a tamper-proof record of every admin action.

### What's Logged

- All **logins / logouts** (success + failed).
- **Property approvals / rejections**.
- **User suspensions / deletions**.
- **Payments recorded / refunds issued**.
- **Settings changes**.
- **Role / permission changes**.
- **Database access via god-mode** (super-admin only).

### Filters

- By user.
- By action type.
- By date range.
- By IP address.
- By entity (e.g., all events on property #123).

Export logs as **CSV** for external compliance review.

---

## Webhooks

`/admin/webhooks` for outbound real-time integrations.

### Use Cases

- Push new leads to your CRM.
- Notify Slack on every booking.
- Send payment events to accounting software.
- Trigger Zapier workflows.

### Creating a Webhook

1. **"+ Add Endpoint"**.
2. Enter your **URL** (e.g., `https://hooks.zapier.com/...`).
3. Subscribe to **events** (e.g., `lead.created`, `booking.paid`, `property.approved`, or `*` for all).
4. System generates a **secret** for HMAC-SHA256 signing.
5. Test the endpoint – we send a test payload.
6. Save.

Every delivery is logged with **status (success / retrying / failed)** and **response body**. Failures auto-retry 3× with exponential backoff.

---

## Bulk Import / Export

`/admin/bulk-operations` for CSV operations.

### Import

Supported tables: **leads, user_properties, plots, customers, newsletter_subscribers**.

1. Download the **CSV template** for the table.
2. Fill data (header row matches column names).
3. Upload.
4. System validates each row → reports the first 10 errors.
5. Click **"Import Valid Rows"**.

### Export

Supported tables: **bookings, commissions, users**, plus all the importable ones.

1. Pick the table.
2. Apply optional **filters**.
3. Click **"Export"** – downloads **UTF-8 CSV with BOM** (Excel-compatible).

---

## System Health Monitoring

`/admin/system-health`:

7 live checks:

1. **PHP** – version, extensions (PDO, mbstring, GD, etc.).
2. **Database** – size, query throughput.
3. **Disk** – usage % with progress bar.
4. **Memory** – peak usage.
5. **Cache** – Redis + file health.
6. **Tables** – sanity check on core tables.
7. **Services** – 8 core services load test.

Each check returns **OK / Warning / Error** with a color-coded badge. Hover for full details.

---

## Troubleshooting Common Issues

### "500 Internal Server Error"

1. Check `logs/php_error.log` for the actual error.
2. Common causes: missing column, wrong file permissions, expired session.
3. Visit `/admin/system-health` for a health check.

### Slow Admin Pages

1. **Flush the cache** at `/admin/cache`.
2. Check **Redis status** – if offline, pages fall back to slower file cache.
3. Check **disk usage** – low disk slows everything.
4. Check **DB slow query log** at `/admin/system-health → Database`.

### User Can't Log In

1. **`/admin/users`** → find user.
2. Check **status** is "active".
3. Check **failed login attempts** – may be locked out (`/admin/security → Blocked IPs`).
4. **Reset password** if needed.
5. Check **2FA** – disable if user lost their device.

### Property Not Showing Publicly

1. Verify **status = approved**.
2. Verify **price > 0** and **photos uploaded**.
3. Clear cache: `/admin/cache → Flush Redis`.
4. Check the listing's location is mapped (cities table linkage).

### Emails Not Sending

1. `/admin/settings/email` – verify SMTP credentials.
2. Send a **test email** from settings page.
3. Check `email_queue` table – stuck emails will say "failed".
4. Re-queue failed emails: `/admin/notifications → Process Queue`.

### Payment Not Reflecting

1. **Reconcile bank statement** via Finance → Reconciliation.
2. Manually record the payment if bank webhook didn't fire.
3. Check **transaction log** for the actual gateway response.

### Backup Restore Fails

1. Check disk space (need 2× backup size free).
2. Verify the backup file is not corrupted (`gzip -t backup.sql.gz`).
3. Ensure MySQL has enough `max_allowed_packet` (set to ≥ 64MB).
4. Contact support if issues persist.

---

**Last Updated:** June 5, 2026
**Document Version:** 1.0
**See also:** [User Guide](USER_GUIDE.md) · [Developer Guide](DEVELOPER_GUIDE.md) · [API Reference](API.md)
