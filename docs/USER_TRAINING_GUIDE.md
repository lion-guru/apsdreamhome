# 📖 APS Dream Home - Complete User Training Guide

## 🏠 Welcome to APS Dream Home Real Estate ERP/CRM System

This comprehensive guide will help you master the APS Dream Home platform - a complete real estate management system with MLM commission capabilities, customer management, booking system, and payment processing.

---

## 📋 Table of Contents

1. [Getting Started](#getting-started)
2. [Admin Dashboard Overview](#admin-dashboard-overview)
3. [User Management](#user-management)
4. [Property Management](#property-management)
5. [Customer Management](#customer-management)
6. [Associate Network & MLM](#associate-network--mlm)
7. [Booking System](#booking-system)
8. [Commission Management](#commission-management)
9. [Payment Processing](#payment-processing)
10. [EMI Management](#emi-management)
11. [Reports & Analytics](#reports--analytics)
12. [System Settings](#system-settings)
13. [Troubleshooting](#troubleshooting)
14. [Best Practices](#best-practices)

---

## 🚀 Getting Started

### System Requirements
- **Web Server:** Apache 2.4+ with PHP 8.2+
- **Database:** MySQL 8.0+ or MariaDB 10.4+
- **Browser:** Chrome, Firefox, Safari, Edge (latest versions)
- **Screen Resolution:** Minimum 1024x768 (recommended 1920x1080)

### Initial Setup
1. **Database Setup:** Ensure the database is properly configured with all 120+ tables
2. **Admin Account:** Default admin credentials are provided during installation
3. **First Login:** Access `http://yoursite.com/admin/` to begin

### Login Process
```
URL: http://yoursite.com/admin/
Username: admin@apsdreamhome.com
Password: [Your secure password]
```

---

## 🎛️ Admin Dashboard Overview

### Dashboard Components

#### 📊 **Statistics Cards**
- **Total Users:** Shows registered user count
- **Properties Listed:** Active property listings
- **Bookings This Month:** Current booking statistics
- **Revenue Generated:** Total business revenue

#### 📈 **Key Metrics**
- **Commission Paid:** Total commissions distributed
- **Active Associates:** MLM network size
- **Pending Payments:** Outstanding payments
- **Customer Satisfaction:** Feedback ratings

#### 🎯 **Quick Actions**
- Add New Property
- Register New Customer
- Process Payment
- Generate Report

### Navigation Menu
- **Dashboard:** Main overview screen
- **Properties:** Property management section
- **Customers:** Customer database
- **Associates:** MLM network management
- **Bookings:** Booking management
- **Payments:** Payment processing
- **Reports:** Analytics and reporting
- **Settings:** System configuration

---

## 👥 User Management

### Adding New Users

#### Step-by-Step Process:
1. **Navigate:** Go to `Users` → `Add New User`
2. **Fill Details:**
   ```
   Name: Full name of the user
   Email: Valid email address (used for login)
   Phone: Contact number with country code
   Type: Select user type (Customer/Associate/Employee)
   Password: Secure password (minimum 8 characters)
   Status: Active/Inactive
   ```
3. **Save:** Click "Create User" to complete

#### User Types:
- **Customer:** End users who buy/rent properties
- **Associate:** Sales partners earning commissions
- **Employee:** Internal staff members
- **Admin:** System administrators

### Managing Existing Users

#### Editing User Information:
1. **Search:** Use the search bar to find specific users
2. **Edit:** Click the edit icon next to user name
3. **Update:** Modify required fields
4. **Save:** Confirm changes

#### User Status Management:
- **Active:** User can login and use system
- **Inactive:** User account is suspended
- **Pending:** Awaiting verification

### User Permissions
Different user types have different access levels:
- **Admin:** Full system access
- **Manager:** Limited administrative functions
- **Associate:** Commission tracking, customer management
- **Customer:** Property browsing, booking management

---

## 🏢 Property Management

### Adding New Properties

#### Property Information:
```
Basic Details:
- Title: Property name/title
- Description: Detailed property description
- Property Type: Residential/Commercial/Plot
- Price: Market price in INR
- Area: Square feet measurement

Location Details:
- Address: Complete address
- City: City name
- State: State/Province
- Country: Country (default: India)
- Postal Code: ZIP/PIN code
- Coordinates: Latitude/Longitude (optional)

Features:
- Bedrooms: Number of bedrooms
- Bathrooms: Number of bathrooms
- Amenities: List of amenities
- Parking: Available parking spaces
```

#### Property Images:
1. **Upload:** Support for multiple images (JPG, PNG)
2. **Maximum Size:** 5MB per image
3. **Recommended Resolution:** 1920x1080 pixels
4. **Featured Image:** Mark one image as primary

### Property Categories
- **Residential:** Houses, apartments, villas
- **Commercial:** Offices, shops, warehouses
- **Plots:** Land parcels for development
- **Rental:** Properties for rent

### Property Status Management
- **Available:** Ready for booking
- **Sold:** Property has been sold
- **Reserved:** Temporarily held
- **Under Construction:** Development in progress

---

## 👨‍💼 Customer Management

### Customer Registration

#### Customer Types:
- **Individual:** Single person buyers
- **Corporate:** Company/business buyers
- **Investor:** Multiple property investors

#### Required Information:
```
Personal Details:
- Full Name
- Email Address
- Phone Number
- Date of Birth
- Gender

Address Information:
- Current Address
- City, State, PIN Code
- Permanent Address (if different)

Documentation:
- Aadhaar Number
- PAN Card Number
- Passport (if applicable)

Financial Information:
- Annual Income
- Occupation
- Bank Details (for payments)
```

### Customer Journey Tracking
1. **Lead Generation:** Initial inquiry
2. **Property Interest:** Shortlisted properties
3. **Site Visit:** Scheduled visits
4. **Negotiation:** Price discussions
5. **Booking:** Final booking confirmation
6. **Payment:** Payment processing
7. **Documentation:** Legal paperwork
8. **Handover:** Property delivery

### Customer Communication
- **Email Notifications:** Automated updates
- **SMS Alerts:** Important notifications
- **WhatsApp Integration:** Instant messaging
- **Call Logs:** Communication history

---

## 🤝 Associate Network & MLM

### Associate Registration

#### Associate Information:
```
Personal Details:
- Full Name
- Email Address
- Phone Number
- Address Details

Professional Details:
- Experience in Real Estate
- Area of Expertise
- Target Market
- License Information (if applicable)

MLM Structure:
- Sponsor/Parent Associate
- Commission Rate (%)
- Level in Hierarchy
- Team Size Target
```

### MLM Commission Structure

#### Commission Levels:
- **Level 1 (Direct):** 5-10% commission
- **Level 2 (Indirect):** 2-5% commission
- **Level 3 (Third Tier):** 1-3% commission
- **Level 4 (Fourth Tier):** 0.5-2% commission
- **Level 5 (Fifth Tier):** 0.25-1% commission

#### Commission Calculation:
```
Example Booking: ₹10,00,000 Property
- Level 1 Associate: ₹50,000 (5%)
- Level 2 Associate: ₹20,000 (2%)
- Level 3 Associate: ₹10,000 (1%)
- Total Commission: ₹80,000
```

### Associate Performance Tracking
- **Sales Volume:** Total sales generated
- **Team Performance:** Downline achievements
- **Commission Earned:** Total earnings
- **Rank/Level:** Current position in hierarchy

---

## 📋 Booking System

### Creating New Bookings

#### Booking Process:
1. **Select Property:** Choose from available properties
2. **Customer Selection:** Assign to existing or new customer
3. **Associate Assignment:** Select handling associate
4. **Booking Details:**
   ```
   Booking Amount: Property price
   Down Payment: Initial payment (typically 10-30%)
   Payment Schedule: EMI or lump sum
   Booking Date: Transaction date
   Expected Completion: Handover date
   ```

### Booking Status Management
- **Confirmed:** Booking is confirmed with payment
- **Pending:** Awaiting payment confirmation
- **Cancelled:** Booking has been cancelled
- **Completed:** Property has been handed over

### Payment Integration
- **Online Payments:** Razorpay, PayU, CCAvenue
- **Bank Transfer:** Direct bank transactions
- **Cash Payments:** Offline payment recording
- **EMI Options:** Installment plans

---

## 💰 Commission Management

### Commission Calculation

#### Automatic Calculation:
- System automatically calculates commissions based on booking amount
- MLM hierarchy determines distribution across levels
- Commission rates are configurable per associate

#### Manual Override:
- Admins can manually adjust commission amounts
- Special bonus/incentive additions
- Commission corrections for errors

### Commission Payment Process

#### Payment Workflow:
1. **Booking Confirmation:** Commission is calculated
2. **Approval Process:** Admin approval required
3. **Payment Processing:** Bank transfer/check generation
4. **Payment Confirmation:** Marked as paid in system
5. **Receipt Generation:** Payment receipt to associate

#### Payment Methods:
- **Bank Transfer:** Direct to associate account
- **Check Payment:** Physical check generation
- **Cash Payment:** Office cash disbursement
- **Digital Wallet:** PayTM, PhonePe, etc.

---

## 💳 Payment Processing

### Supported Payment Gateways

#### Online Gateways:
- **Razorpay:** Credit/Debit cards, UPI, Net Banking
- **PayU:** Multiple payment options
- **CCAvenue:** Comprehensive payment solutions
- **PayTM:** Digital wallet integration

#### Offline Methods:
- **Bank Transfer:** NEFT/RTGS/IMPS
- **Cash Payment:** Office collection
- **Check/DD:** Traditional payment methods

### Payment Tracking
- **Payment History:** Complete transaction log
- **Failed Payments:** Failed transaction management
- **Refund Processing:** Cancellation refunds
- **Payment Reports:** Financial reporting

---

## 📊 EMI Management

### EMI Plan Creation

#### EMI Configuration:
```
Principal Amount: Outstanding amount after down payment
Interest Rate: Annual interest rate (%)
Tenure: Number of months
EMI Amount: Monthly installment
Start Date: First EMI date
```

#### EMI Calculation Formula:
```
EMI = P × r × (1+r)^n / ((1+r)^n - 1)
Where:
P = Principal Amount
r = Monthly Interest Rate
n = Number of months
```

### EMI Tracking
- **Payment Schedule:** Complete EMI calendar
- **Payment Status:** Paid/Pending/Overdue
- **Auto-Debit Setup:** Bank mandate registration
- **Reminder System:** SMS/Email notifications

### Foreclosure Management
- **Early Closure:** Pre-payment facility
- **Partial Payments:** Additional payments toward principal
- **Foreclosure Charges:** Administrative fees
- **NOC Generation:** No Objection Certificate

---

## 📈 Reports & Analytics

### Available Reports

#### Sales Reports:
- **Daily Sales Summary**
- **Monthly Performance**
- **Property-wise Sales**
- **Associate Performance**
- **Commission Distribution**

#### Financial Reports:
- **Revenue Analysis**
- **Payment Collection**
- **Outstanding Amounts**
- **Commission Payouts**
- **EMI Collection Status**

#### Customer Reports:
- **Customer Acquisition**
- **Customer Satisfaction**
- **Repeat Customers**
- **Lead Conversion Rates**

### Report Generation
1. **Select Report Type:** Choose from available reports
2. **Set Date Range:** Specify reporting period
3. **Apply Filters:** Property, associate, customer filters
4. **Generate Report:** Create and download
5. **Export Options:** PDF, Excel, CSV formats

---

## ⚙️ System Settings

### General Settings
- **Company Information:** Name, address, contact details
- **System Preferences:** Date format, currency, language
- **Email Configuration:** SMTP settings for notifications
- **SMS Gateway:** SMS service provider settings

### Security Settings
- **Password Policy:** Minimum requirements
- **Session Timeout:** Auto-logout settings
- **Access Control:** Role-based permissions
- **Audit Logging:** System activity tracking

### Commission Settings
- **Default Rates:** Standard commission percentages  
- **MLM Levels:** Number of commission levels
- **Payment Schedule:** Commission payment frequency
- **Minimum Payout:** Minimum commission amount

---

## 🔧 Troubleshooting

### Common Issues

#### Login Problems:
**Issue:** Cannot login to admin panel
**Solution:**
1. Check username/password spelling
2. Verify account status (active/inactive)
3. Clear browser cache and cookies
4. Try different browser
5. Contact system administrator

#### Payment Issues:
**Issue:** Payment gateway not working
**Solution:**
1. Check internet connectivity
2. Verify gateway configuration
3. Test with different payment method
4. Check gateway service status
5. Contact payment provider support

#### Report Generation Errors:
**Issue:** Reports not generating properly
**Solution:**
1. Check date range validity
2. Verify filter selections
3. Ensure sufficient data exists
4. Try different report format
5. Contact technical support

### Performance Optimization
- **Browser Cache:** Clear regularly for optimal performance
- **Internet Speed:** Ensure stable broadband connection
- **System Resources:** Close unnecessary applications
- **Data Cleanup:** Regular database maintenance

---

## ✅ Best Practices

### Security Guidelines
1. **Strong Passwords:** Use complex passwords with regular changes
2. **Regular Backups:** Daily database backups
3. **Access Control:** Limit user permissions appropriately
4. **Software Updates:** Keep system updated
5. **Audit Trails:** Monitor system activities

### Data Management
1. **Regular Cleanup:** Archive old data periodically
2. **Data Validation:** Verify information accuracy
3. **Duplicate Prevention:** Check for duplicate entries
4. **Data Privacy:** Comply with privacy regulations
5. **Backup Strategy:** Multiple backup locations

### User Training
1. **Initial Training:** Comprehensive system training for new users
2. **Regular Updates:** Keep users informed of new features
3. **Documentation:** Maintain updated user manuals
4. **Support System:** Provide ongoing technical support
5. **Feedback Collection:** Regular user feedback sessions

### Performance Monitoring
1. **System Health:** Regular performance checks
2. **Database Optimization:** Query optimization
3. **Server Monitoring:** Resource usage tracking
4. **Error Logging:** Comprehensive error tracking
5. **User Activity:** Monitor user behavior patterns

---

## 📞 Support & Contact

### Technical Support
- **Email:** support@apsdreamhome.com
- **Phone:** +91-XXXX-XXXXXX
- **Help Desk:** Available 24/7
- **Remote Support:** TeamViewer/AnyDesk assistance

### Training & Documentation
- **User Manuals:** Comprehensive documentation
- **Video Tutorials:** Step-by-step guides
- **Webinars:** Regular training sessions
- **FAQ Section:** Common questions and answers

### System Updates
- **Release Notes:** New feature announcements
- **Update Schedule:** Regular system updates
- **Maintenance Windows:** Scheduled downtime notifications
- **Feature Requests:** User suggestion system

---

## 🎯 Success Tips

### For Administrators:
1. **Regular Monitoring:** Check system performance daily
2. **User Management:** Keep user accounts updated
3. **Data Backup:** Ensure regular backups
4. **Security Updates:** Apply security patches promptly
5. **User Training:** Provide adequate training to users

### For Associates:
1. **Customer Follow-up:** Regular customer communication
2. **Property Knowledge:** Stay updated on property details
3. **Commission Tracking:** Monitor earnings regularly
4. **Team Building:** Focus on building downline network
5. **Professional Development:** Enhance real estate knowledge

### For Customers:
1. **Document Preparation:** Keep all documents ready
2. **Payment Planning:** Plan payment schedules
3. **Communication:** Stay in touch with assigned associate
4. **Property Research:** Research property thoroughly
5. **Legal Verification:** Verify all legal documents

---

## 📋 Quick Reference Card

### Important URLs:
- **Admin Panel:** `http://yoursite.com/admin/`
- **Customer Portal:** `http://yoursite.com/customer/`
- **Associate Dashboard:** `http://yoursite.com/associate/`

### Emergency Contacts:
- **Technical Support:** +91-XXXX-XXXXXX
- **Admin Support:** admin@apsdreamhome.com
- **Sales Support:** sales@apsdreamhome.com

### Key Shortcuts:
- **Ctrl+N:** New Record
- **Ctrl+S:** Save Record
- **Ctrl+F:** Search Function
- **F5:** Refresh Page
- **Esc:** Cancel Operation

---

**© 2024 APS Dream Home - Real Estate ERP/CRM System**
*Version 1.0 - Last Updated: September 2024*

---

*This document is regularly updated. Please check for the latest version on the system help section.*