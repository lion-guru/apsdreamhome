# APS Dream Home - Project Notes
Last Updated: April 4, 2026

## RECENT COMPLETED WORK

### Bug Fixes
- AI Property Valuation page (area_sqft column + property_type → type)
- Database migration: area_sqft column added to properties table
- Compare page rera_status issue fixed
- VirtualTourController parameter fix
- AIHealthMonitor App::database() fix
- AIManager App::database() fix

### Features Implemented
- Virtual Tour feature (360° viewer)
- Meeting Scheduler
- Bank/Payment Integration (Razorpay, Stripe)
- AI Dashboard & Assistant routes
- MLM Dashboard connection
- Schedule Meeting functionality

### UI Fixes
- Logo added: `assets/images/logo/apslogonew.jpg`
- Header updated with real logo
- Footer updated with real logo
- Removed placeholder text from header

## PROJECT STATUS: 95% COMPLETE

### ✅ WORKING PUBLIC PAGES
| Page | Status |
|------|--------|
| / (Home) | ✅ Working |
| /about | ✅ Working |
| /properties | ✅ Working |
| /contact | ✅ Working |
| /login | ✅ Working |
| /register | ✅ Working |
| /compare | ✅ Working |
| /schedule-meeting | ✅ Working |
| /careers | ✅ Working |
| /ai-valuation | ✅ Working |

### 🔐 LOGIN REQUIRED PAGES
| Page | Status |
|------|--------|
| /virtual-tour | ✅ Working (login required) |
| /mlm-dashboard | ✅ Working (login required) |
| /ai-dashboard | ✅ Working (login required) |
| /ai-assistant | ✅ Working (login required) |

### 📁 Key File Locations
- Logo: `public/assets/images/logo/apslogonew.jpg`
- Header: `app/views/layouts/header.php`
- Footer: `app/views/layouts/footer.php`
- Routes: `routes/web.php`
- Controllers: `app/Http/Controllers/`

## CREDENTIALS
- Admin: admin@apsdreamhome.com / admin123
- User: user@apsdreamhome.com / user123

## HOSTING INFO
- Local: http://localhost/apsdreamhome/
- Ngrok: bit.ly/apsdreamhomes (start ngrok: `ngrok http 80`)
- XAMPP configured for direct hosting

## MCP Servers (PC Level)
- filesystem, memory, sequential-thinking, playwright

## PC Tools Installed
- ripgrep, bat, fd, jq, httpie, shellcheck
- prettier, eslint, phpstan, php-cs-fixer
- tldr, mycli, composer, git, gh

## TOKEN BACHANAO RULES
- Use local tools (grep, read, bash) before AI
- Use PHPStan for syntax checking
- Use ESLint for JS linting
- Use tldr for quick help
- Read files with read tool

## NOTES
- All changes committed to Git
- Path structure maintained for XAMPP hosting
- No path changes needed for deployment
