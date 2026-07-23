# Communication Channels Setup Guide

## APS Dream Home - Complete Configuration Steps

---

## 📱 WhatsApp Business API Setup

### Prerequisites

- Meta Business Account (Facebook Business Manager)
- Verified business phone number
- Domain verified in Meta Business Manager

### Step 1: Create Meta App

1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Click **Create App** → **Business** → **Next**
3. Enter App Name: `APS Dream Home` → **Create App**
4. Add **WhatsApp** product to your app

### Step 2: Configure WhatsApp Business Account

1. In left sidebar: **WhatsApp** → **Getting Started**
2. You'll see:
   - **Phone Number ID** (e.g., `123456789012345`)
   - **WhatsApp Business Account ID** (e.g., `987654321098765`)
3. Click **Add Phone Number** → Verify your business phone via SMS/call
4. Complete Business Verification (required for production)

### Step 3: Get Access Token

1. **WhatsApp** → **API Setup** → **Generate Access Token**
2. Copy the **Permanent Access Token** (or use System User token for production)
3. Save this securely - it's your `whatsapp_api_token`

### Step 4: Configure Webhook

1. **WhatsApp** → **Configuration** → **Webhook**
2. Click **Edit** → Enter:
   - **Callback URL**: `https://yourdomain.com/apsdreamhome/api/communication/whatsapp-webhook`
   - **Verify Token**: Create a random string (e.g., `aps_whatsapp_webhook_2024`)
3. Click **Verify and Save**
4. Subscribe to events: ✅ `messages` ✅ `message_template_status_update`

### Step 5: Add to APS Dream Home Admin

1. Go to **Admin → Communication → WhatsApp Webhook**
2. Fill in:
   - **Enable WhatsApp Business API**: ✅ Yes
   - **WhatsApp Business Phone**: `+91XXXXXXXXXX` (your verified number)
   - **API Access Token**: Paste the token from Step 3
   - **Webhook Verified**: ✅ Yes (after Step 4)
3. Click **Save WhatsApp Settings**
4. Test with **Send Test WhatsApp** button

### Step 6: Create Message Templates (Required for Outbound)

1. **WhatsApp** → **Message Templates** → **Create Template**
2. Category: **Utility** or **Marketing**
3. Language: **English** (or Hindi)
4. Examples:
   - **Welcome Template**: `Hi {{1}}, welcome to APS Dream Home! Your dream property journey starts here.`
   - **Booking Confirmation**: `Hi {{1}}, your booking for {{2}} is confirmed. Booking ID: {{3}}`
   - **Payment Reminder**: `Hi {{1}}, your EMI of ₹{{2}} for {{3}} is due on {{4}}. Pay now: {{5}}`
5. Submit for approval (takes 1-24 hours)

---

## 🤖 Telegram Bot Setup

### Step 1: Create Bot with @BotFather

1. Open Telegram → Search `@BotFather`
2. Send `/newbot`
3. Enter bot name: `APS Dream Home`
4. Enter username: `apsdreamhome_bot` (must end with `_bot`)
5. Copy the **API Token** (format: `123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ`)

### Step 2: Set Bot Commands

1. Send `/setcommands` to @BotFather
2. Select your bot
3. Paste these commands:

```
start - Welcome message & main menu
help - Show help & support
properties - Browse available properties
contact - Contact sales team
offers - View current offers
book - Schedule site visit
```

### Step 3: Set Webhook

Run this URL in browser (replace YOUR_TOKEN):

```
https://api.telegram.org/botYOUR_TOKEN/setWebhook?url=https://yourdomain.com/apsdreamhome/api/communication/telegram-webhook
```

Or use curl:

```bash
curl -X POST "https://api.telegram.org/botYOUR_TOKEN/setWebhook" \
  -H "Content-Type: application/json" \
  -d '{"url": "https://yourdomain.com/apsdreamhome/api/communication/telegram-webhook"}'
```

### Step 4: Get Bot Info (Optional)

```
https://api.telegram.org/botYOUR_TOKEN/getMe
```

### Step 5: Add to APS Dream Home Admin

1. Go to **Admin → Communication → Telegram Bot**
2. Fill in:
   - **Bot Token**: Paste from Step 1
   - **Bot Username**: `apsdreamhome_bot` (without @)
   - **Webhook URL**: Auto-filled, copy to set in Step 3
   - **Webhook Verified**: ✅ Yes (after Step 3)
3. Click **Save Telegram Settings**
4. Test with **Send Test Telegram** button

### Step 6: Get Chat ID for Testing

1. Message your bot on Telegram
2. Visit: `https://api.telegram.org/botYOUR_TOKEN/getUpdates`
3. Find `"chat": {"id": 123456789, ...}`
4. Use this Chat ID in test form

---

## 📲 SMS Gateway Setup (MSG91)

### Step 1: Create MSG91 Account

1. Go to [MSG91](https://msg91.com/) → **Sign Up**
2. Verify email and mobile
3. Complete KYC (mandatory for India)

### Step 2: Get Auth Key

1. Login → **API** → **Auth Key**
2. Copy your **Auth Key** (32-char string)

### Step 3: Create Sender ID

1. **Sender ID** → **Create Sender ID**
2. Enter 6-character ID: `APSDRM` or `DREAMH`
3. Select **Transactional** route
4. Submit for DLT registration (takes 1-3 days)
5. Once approved, status shows **Active**

### Step 4: Create Templates (For Transactional)

1. **Templates** → **Create Template**
2. Type: **Transactional**
3. Examples:
   - **OTP Template**: `Your OTP is {{1}}. Valid for 5 mins. - APS Dream Home`
   - **Booking Confirmation**: `Booking confirmed for {{1}}. ID: {{2}}. - APS Dream Home`
   - **Payment Reminder**: `EMI of ₹{{1}} due on {{2}} for {{3}}. Pay: {{4}} - APS Dream Home`
4. Note down **Template IDs**

### Step 5: Add to APS Dream Home Admin

1. Go to **Admin → Communication → SMS Gateway**
2. Fill in:
   - **Enable SMS Gateway**: ✅ Yes
   - **MSG91 Auth Key**: Paste from Step 2
   - **Sender ID**: Your approved 6-char ID
   - **Default Route**: Transactional (recommended)
3. Click **Save SMS Settings**
4. Test with **Send Test SMS** button

### Free Tier Limits

- **300 SMS/month** free
- **100 SMS/day** free
- Perfect for testing and low-volume automation

---

## 📧 SMTP Email Setup

### Option A: Gmail (Free, 500/day limit)

1. Enable 2FA on Google Account
2. Go to [App Passwords](https://myaccount.google.com/apppasswords)
3. Create App Password: Select **Mail** → **Other** → Name: `APS Dream Home`
4. Copy 16-char password

**Settings:**

- **SMTP Host**: `smtp.gmail.com`
- **SMTP Port**: `587`
- **Encryption**: `TLS`
- **Username**: `your@gmail.com`
- **Password**: 16-char App Password
- **From Name**: `APS Dream Home`
- **From Email**: `your@gmail.com`
- **Reply-To**: `support@apsdreamhome.com`

### Option B: Outlook/Office 365 (Recommended for Business)

**Settings:**

- **SMTP Host**: `smtp.office365.com`
- **SMTP Port**: `587`
- **Encryption**: `TLS`
- **Username**: `your@domain.com`
- **Password**: Your Office 365 password
- **From Name**: `APS Dream Home`

### Option C: SendGrid / Mailgun / Amazon SES (Production)

For high-volume production use:

**SendGrid:**

- Host: `smtp.sendgrid.net`
- Port: `587`
- Username: `apikey`
- Password: Your SendGrid API Key

**Mailgun:**

- Host: `smtp.mailgun.org`
- Port: `587`
- Username: Your Mailgun SMTP username
- Password: Your Mailgun SMTP password

### Add to APS Dream Home Admin

1. Go to **Admin → Settings → Email SMTP Settings**
2. Fill all fields
3. Click **Save**
4. Test with **Send Test Email** button

---

## 🔗 Quick Access URLs

| Configuration      | Admin URL                              |
| ------------------ | -------------------------------------- |
| WhatsApp Setup     | `/admin/communication/whatsapp-setup`  |
| Telegram Setup     | `/admin/communication/telegram-setup`  |
| SMS Setup          | `/admin/communication/sms-setup`       |
| Email Templates    | `/admin/communication/email-templates` |
| Communication Logs | `/admin/communication/logs`            |
| SMTP Settings      | `/admin/settings/email`                |
| SMS Settings       | `/admin/settings/sms`                  |
| Payment Settings   | `/admin/settings/payment`              |

---

## ✅ Verification Checklist

After setup, verify each channel:

### WhatsApp

- [ ] Webhook verified (green check in Meta Console)
- [ ] Test message received on phone
- [ ] Template approved (status: Approved)
- [ ] Auto-reply works for "Hi"

### Telegram

- [ ] Bot responds to `/start`
- [ ] Webhook set (check with `/getWebhookInfo`)
- [ ] Test message received in chat
- [ ] Commands appear in menu

### SMS

- [ ] Sender ID approved (DLT)
- [ ] Test SMS received
- [ ] Balance shows in MSG91 dashboard

### Email

- [ ] Test email received in inbox (not spam)
- [ ] DKIM/SPF configured (for custom domains)
- [ ] Templates render correctly

---

## 🚨 Troubleshooting

### WhatsApp Not Sending

- Check webhook URL is HTTPS (required)
- Verify Access Token not expired
- Check template name matches exactly (case-sensitive)
- Ensure phone number has country code (+91)

### Telegram Bot Not Responding

- Verify webhook URL is accessible: `curl -X POST "https://api.telegram.org/botTOKEN/getWebhookInfo"`
- Check bot token is correct
- Ensure bot isn't blocked by user

### SMS Not Delivering

- Check Sender ID status (must be Active)
- Verify template ID matches exactly
- Check DLT registration for promotional route
- Check MSG91 balance

### Email Going to Spam

- Configure SPF: `v=spf1 include:_spf.google.com ~all` (Gmail)
- Configure DKIM in domain DNS
- Use verified domain, not @gmail.com for bulk
- Check blacklist status: mxtoolbox.com

---

## 📞 Support Contacts

| Service          | Support URL                        |
| ---------------- | ---------------------------------- |
| Meta Business    | https://business.facebook.com/help |
| Telegram Bot API | https://core.telegram.org/bots/api |
| MSG91            | https://msg91.com/support          |
| Gmail SMTP       | https://support.google.com/mail    |

---

_Last Updated: 2024 | APS Dream Home ERP_
