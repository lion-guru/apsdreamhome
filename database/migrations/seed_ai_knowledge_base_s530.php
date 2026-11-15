<?php
// Idempotent: seed a role-scoped AI knowledge base with rich articles.
// Each doc is scoped PUBLIC / STAFF / ADMIN — never leaked across the boundary.
// Re-run safely: existing titles are skipped.

$db = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
]);

$docs = [
    // ========================= ADMIN ONLY (internal company data)
    [
        'visibility' => 'admin', 'title' => 'APS Admin Panel — Master Navigation Guide',
        'source' => 'manual', 'tags' => ['admin','navigation','users','reports','gst'],
        'content' => "APS Dream Home admin panel quick reference.\n\n"
            . "ERP Dashboard: /admin/erp — Modules, KPIs, recent activity, cash-flow and lead-pipeline cards.\n"
            . "User management: /admin/users — list/add/edit users, assign roles (super_admin, admin, employee, associate, agent, customer), grant/revoke extra menus.\n"
            . "Leads: /admin/leads — view all CRM leads, assignment, lead scoring, trash at /admin/leads/trash.\n"
            . "Colonies: /admin/colonies — colony master data; /admin/colonies/{id} — Colony 360 detail; /admin/pilots/development — plot development tracker.\n"
            . "Properties & resale: /admin/properties — marketplace; /admin/properties/alerts — alerts inbox.\n"
            . "Payments: /admin/payments — collection & refund view; receipts printable.\n"
            . "GST: /admin/gst — GST compliance (returns vs invoices); /admin/gst-invoices — GST invoices list.\n"
            . "Receivables aging: /admin/reports/receivables-aging — debtor aging buckets (0-30/31-60/61-90/90+).\n"
            . "AI: /admin/ai/executive-assistant — board-level AI; /admin/ai/settings — provider keys; /admin/rag — RAG desk.\n"
            . "Marketing: /admin/marketing-campaigns — campaigns; /admin/promotional-offers — promo codes.\n"
            . "System: /admin/feature-flags — toggles; /admin/service-configs — API keys (AES-256-CBC); /admin/scheduled-reports — report scheduler."
    ],
    [
        'visibility' => 'admin', 'title' => 'How to Manage Users — RBAC Guide',
        'source' => 'manual', 'tags' => ['admin','rbac','users','roles'],
        'content' => "Managing users and roles in APS Dream Home.\n\n"
            . "1. Go to /admin/users and search for a user by name/phone/email.\n"
            . "2. Click Edit to change their role. Roles in APS: super_admin > admin > manager > employee > associate > agent > customer.\n"
            . "3. Role determines sidebar sections and menu grants. Extra menu grants (per-user) are managed at /admin/menu-permissions.\n"
            . "4. After saving, the user's next page load picks up new permissions automatically.\n"
            . "5. Deactivated users (/admin/users → Deactivate) keep all historical data but cannot login.\n"
            . "6. For a fresh install, /admin/create-super-admin seeds the first super admin account.\n"
            . "7. Audit every sensitive action at /admin/audit-log with date/user/role filters.\n\n"
            . "Principle of least privilege: give only the role and extras the user actually needs — never broad admin for a telecaller."
    ],
    [
        'visibility' => 'admin', 'title' => 'ERP Overview & Finance Quick Guide',
        'source' => 'manual', 'tags' => ['admin','erp','finance','cash','emi','gst'],
        'content' => "ERP overview for daily finance review.\n\n"
            . "ERP Dashboard at /admin/erp shows: active colonies count, total users, total leads, conversion rate, today's leads, available/sold plots, pending payments, associates count.\n\n"
            . "Payment collection live data at /admin/payments — transaction IDs, EMI vs advance, receipt downloads.\n\n"
            . "Cash drawer: /admin/finance/cash-drawer — daily opening/closing cash counts with ₹ note denomination.\n\n"
            . "GST compliance: /admin/gst — GSTR-1/GSTR-3B worksheets; /admin/gst-invoices — tax-invoice desk.\n\n"
            . "Receivables aging: /admin/reports/receivables-aging — bucket invoices by age; use Export to CSV for the CA.\n\n"
            . "Vendor payments: /admin/vendors — vendor master; /admin/vendors/pay — payment desk.\n\n"
            . "EMI Dunning: /admin/emi-dunning — overdue installments with payment links."
    ],
    // ========================= STAFF (associates, agents, employees)
    [
        'visibility' => 'staff', 'title' => 'Commission Internal Guide — Direct vs Override',
        'source' => 'manual', 'tags' => ['commission','override','direct-sale','associate'],
        'content' => "APS commission mechanics for sales team.\n\n"
            . "DIRECT-SALE COMMISSION: the closing associate earns their direct-sale commission percentage (rank-based slab) on the booking amount. Paid out through the associate wallet after the booking is confirmed and token amount received.\n\n"
            . "OVERRIDE COMMISSION: upline associates earn override commission on team members' sales. Levels and split percentages are centrally configured in /admin/commission-manage (super_admin only).\n\n"
            . "SLABS BY RANK: higher rank = higher direct-sale slab + more override depth. Rank promotion criteria: bookings volume + team sales volume — see /associate/ranks.\n\n"
            . "BONUSES: fast-start kickstart bonus, leadership career salary (see Diwali Dhamaka plan), peer-rank royalty (1% on junior's sales when junior matches senior's rank).\n"
            . "TDS: 5% deducted at source per Section 194H. Form-16A issued annually from /admin (HR → Finance).\n"
            . "PAYOUT: monthly on the 1st via WalletService::credit() → bank/wallet — auto-triggered by cron_salary_grants.php."
    ],
    [
        'visibility' => 'staff', 'title' => 'Site Visit Verification SOP (GPS-Verified)',
        'source' => 'manual', 'tags' => ['site-visit','gps','verification','sop'],
        'content' => "Site visit verification — verification step must happen before any commission is linked.\n\n"
            . "1. Customer books a site visit → assignable via /customer/site-visits, /associate/site-visits/schedule, or /admin/site-visits.\n"
            . "2. Customer receives SMS/WhatsApp with the engineer's name + Google Maps link.\n"
            . "3. On the visit day, the assigned associate/engineer opens the site_visits list — must be within 1 km of the plot's WGS84 pin to check-in.\n"
            . "4. On check-in, device GPS is compared to the plot's pin; distance > 1 km → check-in rejected, visit stays 'pending'.\n"
            . "5. After check-out, the customer fills a short feedback (satisfaction 1-5, plot interest).\n"
            . "6. If the customer likes the plot, the associate marks 'follow-up' and the booking desk picks it up.\n"
            . "7. Unknown/hiders: stale 'site_visits' > 7 days without check-in auto-flag on the admin dashboard for review."
    ],
    [
        'visibility' => 'staff', 'title' => 'EMI Defaulter Handling & Escalation SOP',
        'source' => 'manual', 'tags' => ['emi','defaulter','dunning','escalation'],
        'content' => "EMI defaulter handling standard operating procedure.\n\n"
            . "Days 1-7 overdue: polite reminder — auto SMS/WhatsApp with payment link (UPI intent), triggered by /admin/emi-dunning or the cron job.\n"
            . "Days 8-15 overdue: phone call by recovery desk. Record outcome (working/loan rephase/declined).\n"
            . "Days 16-30 overdue: penalty interest applies automatically as per the booking EMI plan; demand letter generated from /admin/demand-letters.\n"
            . "Days 31-60 overdue: legal notice stage — legal compliance team (see /admin/legal-colony-pipeline) prepares a show-cause notice.\n"
            . "Days 61+ overdue: termination process — the booking agreement cancellation clause applies (see /admin → Legal → Cancellation).\n\n"
            . "NOT customer issue? If the customer has a genuine financial crisis, log a 'forbearance' request at /admin/forbearance for review by the finance director.\n"
            . "Never use harsh language, never publicly embarrass. Escalation is process, not aggression."
    ],
    // ========================= PUBLIC-STYLE GUIDANCE (staff-safe external comms)
    [
        'visibility' => 'staff', 'title' => 'Customer Social Proof & Trust Scripts',
        'source' => 'manual', 'tags' => ['social-proof','trust','sales-script'],
        'content' => "Trust-building scripts for customer-facing conversations.\n\n"
            . "RERA question: 'Hum RERA-registered project hain. Aapko registration number dekh sakte hain — RERA UP/PRJ-… (see colony listing or ask for official copy).'\n"
            . "Price negotiation: 'Sir hamare base rate fixed hain company policy se. Lekin main try karonga wahi discount jisme aap kaam ho — main approval le aata hoon.'\n"
            . "Site visit: 'Ek baar plot dekh kar hi decide karo — free cab pickup drop included hai Sabah 10 baje.'\n"
            . "Loan support: 'Aapke documents le kar hum ICICI/HDFC/SBI se pre-approved loan arrange kar dete hain — 100% online process mein kuch din lagte hain.'\n"
            . "Payments: 'Token amount ₹5.5 lakh se book hota hai. Baki installments EMI se — visit karke EMI plan dekh saktay hain.'\n"
            . "Never promise: guaranteed price rise, fixed resale price, 100% occupancy, preferred plot number. All such commitments go through the sales director."
    ],
];

$created = 0; $skipped = 0;
foreach ($docs as $d) {
    $chk = $db->prepare("SELECT id FROM ai_knowledge_base WHERE title = ? LIMIT 1");
    $chk->execute([$d['title']]);
    if ($chk->fetchColumn()) {
        $skipped++;
        echo "SKIP [{$d['visibility']}] {$d['title']}\n";
        continue;
    }
    $stmt = $db->prepare("INSERT INTO ai_knowledge_base (tenant_id, title, content, source, tags, visibility, is_active, created_at, updated_at) VALUES (1, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
    $stmt->execute([
        $d['title'],
        $d['content'],
        $d['source'],
        json_encode($d['tags'], JSON_UNESCAPED_UNICODE),
        $d['visibility'],
    ]);
    $docsCreated = $db->lastInsertId();
    // Chunk & embed would be slow here — just log; the embed happens on next search if needed
    $created++;
    echo "CREATED [{$d['visibility']}] id=$docsCreated {$d['title']}\n";
}
echo "\nDone: created=$created skipped=$skipped\n";
