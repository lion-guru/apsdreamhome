# Analyze screenshots with moondream via Ollama using PowerShell - Enhanced version

$ollamaUrl = "http://localhost:11434/api/generate"

$images = @(
    @{ 
        file = "homepage.png"; 
        prompt = "You are a senior UX/UI designer and real estate technology expert. Analyze this APS Dream Home homepage screenshot in DETAIL. Provide a comprehensive analysis covering:

1. VISUAL DESIGN & BRANDING:
   - Color scheme, typography, visual hierarchy
   - Brand consistency (logo, colors, messaging)
   - Professional appearance for real estate

2. HEADER & NAVIGATION:
   - Logo visibility and placement
   - Main navigation items visibility and clarity
   - Search bar visibility and functionality
   - Language selector, contact info visibility
   - Login/Register button prominence

3. HERO SECTION:
   - Headline clarity and impact
   - Search form design (Buy/Rent, Property Type, Location, Budget dropdowns)
   - CTA buttons visibility and placement
   - Visual appeal and conversion optimization

3. KEY SECTIONS:
   - EMI Calculator visibility and usability
   - Value proposition section (4 pillars)
   - Projects showcase (Suryoday, Braj Radha, etc.)
   - Trust metrics (2500+ buyers, etc.)
   - Services section (6 services)
   - Why Choose Us section
   - Investment comparison (Real Estate vs FD vs Gold)
   - Investment calculator
   - Free tools section (7 tools)
   - Testimonials
   - Newsletter signup
   - Footer links organization

4. MOBILE RESPONSIVENESS INDICATORS:
   - Hamburger menu visibility
   - Touch-friendly elements
   - Viewport considerations

5. ISSUES & IMPROVEMENTS:
   - Any visible UI bugs, alignment issues
   - Missing alt text indicators
   - Color contrast concerns
   - Missing elements
   - Conversion optimization opportunities

Provide specific, actionable feedback for each section."
    },
    @{ 
        file = "login_page.png"; 
        prompt = "Analyze this APS Dream Home login page screenshot in DETAIL. Provide comprehensive analysis:

1. VISUAL DESIGN & BRANDING:
   - Background design, color scheme
   - Logo visibility and branding consistency
   - Card/container design

2. FORM DESIGN & ACCESSIBILITY:
   - Email/phone field label clarity
   - Password field with show/hide toggle
   - Remember me checkbox
   - Forgot password link visibility
   - Sign In button prominence and styling
   - Form validation indicators

3. LOGIN OPTIONS:
   - Email/password primary form
   - Google OAuth button visibility
   - Phone login button visibility
   - Air Login (OTP without password) link visibility
   - Register link visibility

4. ROLE-BASED LOGIN OPTIONS:
   - Associate MLM & Commissions link
   - Agent Sales & Clients link
   - Admin Full Panel link
   - Visibility and clarity of role-based pathways

6. MOBILE RESPONSIVENESS:
   - Form field sizing for touch
   - Button sizing for touch targets
   - Viewport considerations

7. SECURITY & TRUST INDICATORS:
   - Secure badge/lock indicators
   - Privacy policy links
   - Terms of service links

8. ISSUES & IMPROVEMENTS:
   - Any UI bugs, alignment issues
   - Color contrast concerns
   - Missing elements
   - Conversion optimization opportunities"
    },
    @{ 
        file = "admin_erp_overview.png"; 
        prompt = "Analyze this APS Dream Home Admin ERP Overview dashboard screenshot in DETAIL. Provide comprehensive analysis:

1. LAYOUT & INFORMATION ARCHITECTURE:
   - Sidebar navigation organization (collapsible sections)
   - Header with breadcrumb, notifications, user menu
   - Main content area layout
   - Information hierarchy and visual grouping

2. SIDEBAR NAVIGATION:
   - Section organization (Dashboards, CRM, Properties, Sales, Finance, Commission, MLM, HR, Legal, Marketing, Content, Services, Reports, Operations, AI, Security, Communication, Users, SaaS, Settings, System Admin, Employee Portal)
   - Collapsible/expandable behavior indicators
   - Icon usage and clarity
   - Active state indication

3. MAIN CONTENT - ERP OVERVIEW:
   - Header with title and navigation links (Sales, Finance, MLM)
   - Quick Actions section with 6 action cards (Land Inventory, Sales, EMI Dunning, Finance Hub, MLM Network, Backoffice Ops)
   - Recent Activity feed
   - Cash Flow chart (7 days)
   - Lead Pipeline chart
   - Metric cards with values

3. DATA VISUALIZATION:
   - Chart types and readability
   - Data labels and legends
   - Color coding
   - Responsiveness

4. DATA QUALITY INDICATORS:
   - Real data vs placeholder data
   - Data freshness indicators
   - Numeric formatting (currency, numbers)

4. UI/UX QUALITY:
   - Visual consistency (colors, spacing, typography)
   - Card design and shadows
   - Button styling consistency
   - Link styling
   - Hover/focus states indicators

5. RESPONSIVE DESIGN:
   - Sidebar collapse behavior
   - Content reflow indicators
   - Table/chart responsiveness

6. PROFESSIONAL ADMIN APPEARANCE:
   - Enterprise-grade feel
   - Information density balance
   - Scannability

6. ISSUES & IMPROVEMENTS:
   - Any visible UI bugs
   - Color contrast concerns
   - Missing elements
   - Data density concerns
   - Navigation usability concerns"
    },
    @{ 
        file = "mlm_dashboard.png"; 
        prompt = "Analyze this MLM Commission Dashboard screenshot in DETAIL. Provide comprehensive analysis:

1. LAYOUT & STRUCTURE:
   - Page header with breadcrumb
   - Section organization
   - Information hierarchy

2. RANK DISTRIBUTION TABLE:
   - Column headers (RANK, ASSOCIATES, MIN LEGS, MIN VOLUME, RATE)
   - Row data for 7 ranks (Ass., Sr. Ass., BDM, Sr. BDM, V.P., President, Site Manager)
   - Data completeness (all cells populated)
   - Visual formatting (currency, percentages)

2. COMMISSION STREAMS TABLE:
   - Column headers (STREAM, COUNT, TOTAL ALL TIME, THIS MONTH, % OF TOTAL)
   - 13 commission streams (Direct Sale, Override, Matching Bonus, Royalty Pool, Level Bonus, Generation Bonus, Rank Advancement, MLM L1, MLM L2, Infinity Override, MLM L2, Team Bonus, Performance Bonus, MLM L3, Investment Sale)
   - Data completeness and formatting
   - Percentage calculations
   - TOTAL row accuracy

3. RECENT CRON RUNS:
   - Table with CRON, DATE, STATUS, ITEMS
   - Status indicators

3. COMMISSION MODEL SECTION:
   - Configuration visibility

4. QUICK ACTIONS:
   - View Commissions Ledger
   - Payout Batches
   - Clawback Log
   - Rank Benefits

4. DATA QUALITY:
   - Real data vs zeros
   - Currency formatting (Indian format with K)
   - Percentage formatting
   - Data freshness

5. VISUAL DESIGN:
   - Table styling (borders, padding, alignment)
   - Color coding for different streams
   - Card/section design
   - Typography hierarchy

6. MOBILE RESPONSIVENESS:
   - Table horizontal scroll indicators
   - Card stacking behavior

7. FINANCIAL DASHBOARD BEST PRACTICES:
   - Data density balance
   - Scannability
   - Key metric prominence
   - Drill-down indications

8. ISSUES & IMPROVEMENTS:
   - Any visible UI bugs
   - Zero-value rows concern
   - Data freshness concerns
   - Missing drill-down indicators
   - Export functionality visibility"
    }
)

function Analyze-Image($imagePath, $prompt) {
    $bytes = [System.IO.File]::ReadAllBytes($imagePath)
    $base64 = [Convert]::ToBase64String($bytes)
    
    $payload = @{
        model = "moondream"
        prompt = $prompt
        images = @($base64)
        stream = $false
        options = @{
            temperature = 0.1
            num_predict = 2000
        }
    }
    
    $json = $payload | ConvertTo-Json -Depth 10 -Compress
    
    try {
        $response = Invoke-RestMethod -Uri "http://localhost:11434/api/generate" -Method Post -Body $json -ContentType "application/json" -TimeoutSec 180
        return $response.response
    } catch {
        return "Error: $($_.Exception.Message)"
    }
}

foreach ($img in $images) {
    $path = "C:\xampp\htdocs\apsdreamhome\" + $img.file
    if (Test-Path $path) {
        Write-Host "`n============================================================"
        Write-Host "ANALYZING: $($img.file)"
        Write-Host "============================================================"
        $result = Analyze-Image -imagePath $path -prompt $img.prompt
        Write-Host $result
        Write-Host ""
    } else {
        Write-Host "File not found: $path"
    }
}