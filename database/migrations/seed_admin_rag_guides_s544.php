<?php
/**
 * Migration: Seed admin/operator + customer/associate how-to RAG guides into ai_knowledge_base,
 * embed chunks for newly inserted docs only, and deactivate duplicate admin_menu_items.
 * Session: 544
 *
 * Why: those operator guides were added live to the DB (S542/S543) but lived nowhere in the repo —
 * a restore would have lost all of it and the sidebar duplicate menu rows would return.
 *
 * Idempotent: skip titles that already exist; only documents inserted in THIS run get chunks.
 * Safe re-run: menu dedupe matches 0 rows post-dedupe (all dupes already is_active=0).
 * Non-destructive: admin_menu_items rows are set is_active=0, never deleted (pre-dedupe backup at
 *                  backups/db/admin_menu_pre_dedupe_s541_2026-11-14.sql).
 *
 * @safe-change seed
 */
require_once 'config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "=== S544: seed admin/customer/associate RAG guides + menu dedupe ===\n";

$docs = [
    ['title' => 'How does the admin panel sidebar work?', 'tags' => ['admin','howto','navigation','sidebar'],
     'content' => "The admin sidebar (Admin ERP) groups every tool into 6 enterprise hubs (Inventory & Projects, Sales & CRM, MLM Network, Finance, System & HR, Operations & SaaS). Each hub contains sections, each section contains pages. If a page is missing, use the sidebar \"Quick find feature\" search box at the top. If a section looks collapsed, click the hub header or the section header; use the \"Reset View\" button to restore the full menu. Menu items are RBAC-filtered — your role (super_admin/admin/manager/...) controls which items are visible. If you have the role but not the item, ask a super_admin to grant it in Menu Permissions (/admin/menu-permissions)."],

    ['title' => 'How to add a new plot or bulk-import plots', 'tags' => ['admin','howto','plots','inventory'],
     'content' => "Admin → colonies → Plots Inventory (/admin/plots/inventory). Click \"Add Plot\" for one plot (colony, plot number, dims, rate/sqft, PLC flags). For many plots: \"Bulk Import\" tab on the same page — prepare a CSV with columns colony_id, plot_number, width_ft, length_ft, rate_per_sqft (and optional PLC flags), upload, preview, then import. After import, verify the numbers on /admin/plots/inventory. For pricing the plots use /admin/plots/batch-pricing (computed final prices from PLC rules). NB: booked/sold plots are never re-priced by the batch tool."],

    ['title' => 'How to create a new colony / township', 'tags' => ['admin','howto','colony','setup'],
     'content' => "Admin → colonies → Colony Management (/admin/colonies) → \"Add New Colony\". Fill: name, city, state, pincode, total area (acres), category & approvals (RERA), land-title pointer, anchor price/sqft, cover image, gallery later via \"Project Gallery\". Colonies appear instantly in the site search + public /projects and colony detail pages. To add a marketing page with a hero banner, see the /project-locations/* routes pattern (add a view + route + image like the Budha City page)."],

    ['title' => 'How to configure AWS S3 cloud backup', 'tags' => ['admin','howto','backup','s3','storage'],
     'content' => "1) AWS: create an IAM user with S3 access (s3:PutObject/GetObject on your backup bucket only); note the Access Key ID + Secret. 2) Admin → Service Configuration (/admin/service-configs) → \"AWS S3\" card → paste access_key, secret_key, region (e.g. ap-south-1), bucket name. 3) Test button. 4) Settings → Backup Manager (/admin/backups) → choose \"to-s3\" to push the DB dump + uploaded files. 5) Schedule it daily — see BACKUP_TASK or setting up Windows Task Scheduler (docs/WINDOWS_TASK_SCHEDULER.md). Secret keys are stored AES-256 encrypted (enc:v1:); never commit the raw key anywhere."],

    ['title' => 'How to set up SMTP email (Gmail / Office 365 / own server)', 'tags' => ['admin','howto','smtp','email'],
     'content' => "Admin → Service Configuration (/admin/service-configs) → \"SMTP\" card. Gmail: host smtp.gmail.com, port 587, encryption tls; password = APP PASSWORD (not your login) — create at myaccount.google.com/apppasswords (2-Step must be enabled). Office 365: smtp.office365.com, 587, tls, username = full mailbox. Own VPS: your mailserver host, 587/465. Set from_email/from_name to the address customers should see (must match your authenticated account). Test button sends a test mail; check Spam the first time, then add your domain SPF/DKIM at your DNS for deliverability."],

    ['title' => 'How to take or test an EMI auto-debit (NACH/eMandate) run', 'tags' => ['admin','howto','emi','nach','collections'],
     'content' => "Admin → EMI Auto-Pay (/admin/finance/emi-auto-pay). Customer authorizes a mandate (NACH debit) at booking or via the portal; the mandate appears under Active Mandates. Test run: use \"Run dry-run\" (or the CLI script run_nach_auto_debit.php with --dry-run) — it prints what WOULD be collected. Live run executes on the due date; failed debits show under Failed with reason. Never run live NACH without the bank/NPCI credentials in Service Configuration → Razorpay/NACH. After a successful run, each collection posts to the EMI installments ledger and the receipt PDF is generated automatically."],

    ['title' => 'How to record a customer payment / upload a receipt', 'tags' => ['admin','howto','payments','collections'],
     'content' => "Admin → Payments (/admin/payments). Use \"Add Payment\": pick booking, installment, amount, mode (cash/UPI/NEFT/RTGS/cheque), transaction ID/UTR, upload the receipt photo, save — it posts to the installment schedule, generates a receipt (PDF), updates the customer passbook, and the wallet/commission ledger. UPI mode should be reconciled the same day; cheque mode stays pending until cleared. For UPI intent deep-links, see Settings → UPI VPA."],

    ['title' => "How to create and activate an associate's salary grant", 'tags' => ['admin','howto','salary','mlm'],
     'content' => "Salary grants come from the Permanent Leadership Career Salary Plan (₹5k–₹20k/month for 6–12 months) tracked in mlm_salary_grants. Admin → MLM → \"Salary Plans\" activates for an associate who met the target (₹15L/60d → ₹30k over 6 mo … ₹1Cr/300d → ₹2.4L). Anti-free-rider rule: every month the grant is PAUSED unless that month had ≥2 verified site visits OR ≥₹50,000 team volume — 2 inactive months = paused_reason=inactive_maintenance; resumes when the associate is active again. Admin → \"Salary Grants\" shows active/paused/completed; never hand-edit status — let the monthly cron decide."],

    ['title' => 'How to produce a booking token refund or cancellation', 'tags' => ['admin','howto','cancellation','refund'],
     'content' => "Admin → Sales → Bookings (/admin/sales/bookings), open the booking → \"Cancel\". Refund policy: token amount NON-REFUNDABLE per terms, unless owner override (override_reason in remarks). For EMI-cancellation cases use BookingLifecycleService → canceled — it reverses the plot status to available within the same DB transaction. If money movement is required (refund), record it as a negative payment via /admin/payments with mode matching the original channel. Post to ledger and notify the customer by email/WhatsApp."],

    ['title' => 'How to add/remove admin users and grant menu items', 'tags' => ['admin','howto','users','rbac'],
     'content' => "Admin → Users Manager (/admin/users). \"Add User\" → name, email, phone, role (super_admin > admin > manager > finance/sales_* > employee). Role decides which sidebar items appear — for per-user extra access (e.g. one agent sees Feature Flags), go to Menu Permissions (/admin/menu-permissions) → pick the user → tick the items. Users Manager also shows last login + audit trail. To deactivate: edit → set status=inactive (never delete — audit history references the user id)."],

    ['title' => 'How to read the site-visit pipeline / approve cab pickups', 'tags' => ['admin','howto','site-visits','cab'],
     'content' => "Admin → CRM → Site Visits (/admin/site-visits) — funnel: scheduled → cab assigned → picked up → visited → interested/converted. Assign a cab by picking an Associate or a Driver + vehicle plate; the system sends the customer an SMS/WhatsApp with pickup time. When the visit completes, note the outcome — \"interested\" advances the lead stage; if the customer books, the associate gets auto-credit in the wallet ledger."],

    ['title' => 'How to issue allotment letters, agreements & registries', 'tags' => ['admin','howto','legal','registry','documents'],
     'content' => "Admin → Legal → Document Templates. Allotment letter: generated on token payment — customer downloads from portal. Registered deed / agreement to sell: use \"Registry Deed\" generation (needs colony Gata/Khasra + allotment copy). Full pipeline: generate → customer signs (e-sign/Leegality or DigiLocker) → upload to locker (Documents Vault) → mark registry done in Legal Pipeline."],

    ['title' => 'How to add or change PLC pricing rules', 'tags' => ['admin','howto','pricing','plc','csv'],
     'content' => "Admin → Plots → PLC Pricing Rules (/admin/plots/plc-rules). Add rules: e.g. \"corner\" +15%, \"main-road 60ft\" +10%, \"park-facing\" +7%. Rules compose by addition after the base rate. Set colony scope (Global or one colony), priority, active flag. Batch-apply with \"Batch Apply\" on /admin/plots/batch-pricing — recomputes available plots only and stamps new prices (booked/sold/reserved are skipped). Always check the impact preview before confirming."],

    ['title' => 'How to schedule automated reports by email', 'tags' => ['admin','howto','reports','cron'],
     'content' => "Admin → Scheduled Reports (/admin/scheduled-reports) → \"New Report\": pick report (Receivables Aging / Payables / Fund Flow / Sales Summary), frequency (daily/weekly/monthly + day), recipient (emails) — it stores next_run. The master cron (run_all_crons.php ➜ daily task) emails the XLSX via ExcelExportService. Monitor /admin/notifications for failures."],

    ['title' => 'How to manage feature flags (rollout, beta)', 'tags' => ['admin','howto','flags','rollout'],
     'content' => "Admin → Feature Flags (/admin/feature-flags). Each flag: key, description, %rollout, enabled, role-allowlist. \"enable_beta\"-style flags let you ship features dark — enable to 10% of users first, watch errors, then ramp to 100%. Off → every page reverts to prior behaviour immediately (no deploy). Keep flags surgical — one behaviour per flag, delete when finished."],

    ['title' => 'How the WhatsApp bot and site-visit drip work', 'tags' => ['admin','howto','whatsapp','drip','automation'],
     'content' => "WhatsApp inbound replies (share rates, \"hi\", \"book visit\") are handled by WhatsAppService::autoReply — keyword matching, 30-day-idempotent lead capture. Site-visit drip: after a visit ends, the drip engine (lead_nurture_actions) schedules +1/+3/+6-day follow-ups automatically; kill switch: /admin/marketing/campaigns → drip → pause. Templates live under /admin/marketing/templates; broadcast campaigns via /admin/campaigns."],

    ['title' => 'How to add a blog/FAQ/company page without code', 'tags' => ['admin','howto','cms','content'],
     'content' => "Admin → CMS (Content → Pages / Blogs). Create post: title, slug auto, body (what you type renders), feature image, optional SEO. Status = published. FAQ/blog pages update instantly — no code, no deploy. For a NEW top-level marketing page like /project-locations/<slug> you still need one file (app/views/locations/<slug>.php) + one PageController route + image in public/assets/images/…; ping the dev for new top-level pages."],

    ['title' => 'Why is a page 404-ing / white-screen / 500 — quick triage', 'tags' => ['admin','howto','troubleshooting'],
     'content' => "404: route doesn't exist — check routes/web.php for the exact path; most 404s are a stale link. 500: check logs/php_error.log tail (PHP fatal / SQLSTATE) — the top line names file:line. If you just deployed, clear the sidebar/session cache (Cache::delete), hard-refresh (Ctrl+Shift+R). White screen + 200: a view threw after output started — check php_error.log. Still stuck: screenshot + URL + timestamp; never paste raw SQL into chat, it will include secrets."],

    ['title' => 'How to promote a lead from website chat / inquiry to associate business', 'tags' => ['admin','howto','conversion','website'],
     'content' => "Every chatbot/website lead lands in Leads Manager (/admin/leads). Filter by \"unowned\" → assign to associate or sales team (assigned_to). The associate sees it in their portal + WhatsApp gets the handoff ping. If the customer books a site visit the funnel auto-advances; associate's booking creates the plot_bookings row + commission entry automatically. Follow up on \"cold\" leads >14 days weekly via bulk WhatsApp — /admin/crm/whatsapp bulk."],

    ['title' => 'How to run payroll / salary disbursement', 'tags' => ['admin','howto','payroll','hr'],
     'content' => "Admin → Payroll (/admin/payroll) → month → \"Generate\": it builds per-employee ledger lines (basic+hra+DA − PF/ESI/PT/TDS). Review exceptions, then \"Approve & Pay\" — it pays final salary to wallets/bank and posts to the ledger. Salary advance requests sit under /admin/salary-advances (₹ cap = 50% of net salary, EMI-deducted over 3–12 months). Form 16 auto-generates per employee after year-end."],

    ['title' => 'How to see my booking, schedule and upcoming EMIs', 'tags' => ['customer','howto','bookings','emi'],
     'content' => "Login → Dashboard (/user/dashboard) shows your bookings with total value, paid, outstanding. Click a booking → full schedule: installments due, paid, next date (/user/bookings). For the EMI view sorted by month: /user/emi-tracker. Overdue installments appear there with the ₹ due and due date; pay online from the same page via Razorpay or UPI QR, or set up NACH auto-debit (one-time mandate) so EMI auto-clears every month. Every payment auto-updates the passbook."],

    ['title' => 'How to activate my wallet and what can I use it for', 'tags' => ['customer','wallet','howto','packages'],
     'content' => "Login → /user/wallet. Choose a plan (Basic ₹499 / Pro ₹1,499 / Premium ₹2,999) — one-time activation. Points added after payment and expire after 1 year of inactivity. Use points for: partial EMI pre-payment, booking fee parts, or marketplace purchases. If referral/RWA credits arrive here they show as wallet points."],

    ['title' => 'How to download my receipts, allotment letter and agreement', 'tags' => ['customer','documents','howto','passbook'],
     'content' => "Login → /user/documents — that's your locker: allotment letter, agreement to sell, registry deed, payment receipts, NOC. Click any item → opens/downloads the PDF. For EMI statement (all installments) use /user/passbook → \"Download Statement (PDF)\". Every ₹ transaction is in the payments register with UTR/ref numbers."],

    ['title' => 'How to request an EMI overdraft, stop-payment or penalty dispute', 'tags' => ['customer','howto','disputes','emi'],
     'content' => "In the portal: /user/bookings → open booking → \"Other Actions\" → \"Call support\". For stop-payment / dispute on a deducted EMI: /customer/township-grievances category=finance with bank proof. For penalty waiver: same route, reason=penalty review, attach reason. The finance team answers within 72h."],

    ['title' => 'How to get an NOC / possession letter after final payment', 'tags' => ['customer','howto','noc','possession','registry'],
     'content' => "After your final payment: /user/bookings → booking → \"Registry\" → apply for Registry Appointment (/customer/registry-appointments). When the date confirms you get SMS/WhatsApp with the checklist (Aadhaar, PAN, original agreement, final payment receipt, 2 photos). You sign the sale deed at the appointment; the registered copy + possession letter come to your Documents locker within 15 days. NOC for mutation: branch office after registration (1–2 days turnaround from RERA desk)."],

    ['title' => 'How does the site-visit process work for a new plot?', 'tags' => ['customer','howto','site-visit','cab'],
     'content' => "Login → /user/site-visits → \"Book a site visit\". Pick colony, plot number, date/time — your cab pickup time + driver phone arrive by SMS/WhatsApp 2 hours before pickup. The associate walks you through layout, measurements, construction status. Site visits are free, zero commitment. Reschedule from the dashboard any time before the cab starts."],

    ['title' => 'How to refer a friend and get the referral bonus', 'tags' => ['customer','referral','howto'],
     'content' => "Login → Profile → \"Referral\" or /customer/referral. Your unique referral link is displayed. Share via WhatsApp/SMS/copy — when they book and make the token payment on your link, the reward is credited to your wallet points + associate receives the match. You get the highest bracket this quarter. Track referrals at /customer/referral."],

    ['title' => 'How an associate activates their account and starts selling', 'tags' => ['associate','howto','onboarding'],
     'content' => "Login with your associate OTP → /associate/dashboard. First-time: complete KYC (Aadhaar, PAN, bank, photo) — required for payout withdrawals. Commission ledger activates immediately; lead attribution starts from your referral code (/associate/referral): share, and when a customer books via it, the lead is yours. First steps: (1) profile KYC, (2) copy referral link, (3) 5 site visits with your first customer."],

    ['title' => 'How commissions are calculated and paid to associates', 'tags' => ['associate','howto','commission','payout'],
     'content' => "Associate portal → /associate/commissions → tabs: earned this month / lifetime / by type. Payout on 1st/7th monthly (wallet or bank for KYC-verified ≥ ₹500). Slab: 5% Associate → 20% Site Manager. Override: Senior(7%) vs Junior(5%) = 2% gap; direct sale + generation differential computed automatically on the commission ledger. Every ₹ shown is backed by a booking or verified visit — no manual entries."],

    ['title' => 'How to download TDS slips and monthly payout statements', 'tags' => ['associate','howto','tds','payout'],
     'content' => "Associate portal → /associate/tds-slips → year-wise TDS deducted from commission income (5% Sec 194H). Each entry links to the monthly statement PDF with gross, TDS, net paid + deposit acknowledgement. Form 16A arrives by 15 June. For custom dates use \"Request statement\" — accounts team emails within 2–3 days. PAN links everything — keep profile KYC PAN correct."],

    ['title' => 'How to withdraw wallet points to bank account', 'tags' => ['associate','howto','withdraw','wallet'],
     'content' => "Associate portal → /associate/wallet → \"Withdraw\". Enter amount, pick bank account (or KYC bank), submit — ops team approves within 24–72h. Minimum ₹100; above ₹20,000 needs KYC pre-verification (PAN + cancelled cheque). Pending KYC → on HOLD. Track in /associate/wallet → \"Withdrawal history\". TDS auto-deducted above ₹15,000 commission in a financial year."],

    ['title' => 'How to use the associate career ladder — from Associate to Site Manager', 'tags' => ['associate','howto','career','rewards'],
     'content' => "Portal → /associate/leaderboard (global ranking) → /associate/rewards (7-tier Career Rank Rewards). Qualify: Associate ₹0, Sr. Associate ₹10L+5 direct, BDM ₹35L+3 Sr. Assts, Sr. BDM ₹70L+3 BDMs, VP ₹1.5Cr+3 Sr. BDMs, President ₹3Cr+3 VPs, Site Manager ₹5Cr+3 Presidents. Physical rewards (Mobile→Tablet→Laptop→Tour→Pulsar→Bullet→Luxury Car) or wallet cash at company discretion. Rank shows on /associate/rank-eligibility."],

    ['title' => 'How to benefit from the Diwali/Permanent Leadership Salary Plan', 'tags' => ['associate','howto','salary','leadership'],
     'content' => "Hit ₹15L/60d → ₹5,000 x 6 (₹30,000). ₹30L/100d → ₹5,000 x 12 (₹60,000). ₹50L/150d → ₹8,000 x 12 (₹96,000). ₹75L/200d → ₹12,000 x 12 (₹1.44L). ₹1Cr/300d → ₹20,000 x 12 (₹2.4L). Monthly grant needs ≥2 site visits OR ≥₹50,000 team volume that month — miss it, grant pauses (withheld); 2 consecutive inactive months → paused_reason=inactive_maintenance until you resume. Now permanent — target, activate, maintain. See /associate/salary."],

    ['title' => 'How to request a salary advance / car-travel reimbursement', 'tags' => ['associate','howto','advance','expenses'],
     'content' => "Portal → /associate/advances → \"Apply for Salary Advance\". Max = 50% of 3-month avg monthly commission; repaid via equal EMIs auto-deducted. Festival pushes before Diwali/Holi (10% limit increase, 0% interest). Fuel/visit expense refund: /associate/allowances with slip + GPS → pending → approved → credited to wallet or next month deduction."],

    ['title' => 'How to share marketing posters & generate leads for your team', 'tags' => ['associate','howto','marketing','leads'],
     'content' => "Portal → /associate/posters → pick colony + language (English/Hindi/Hinglish). Download PDF/PNG with your referral code embedded; share. Every QR scan + code-booking is attributed to you. Track scans & conversions at /associate/leaderboard → \"Share Analytics\". Best: share on Sunday mornings + resize to Instagram story."],

    ['title' => 'How to see my downline network tree and L1/L2/L3', 'tags' => ['associate','howto','network','tree'],
     'content' => "Portal → /associate/network/tree — interactive D3 tree of your downline. Zoom, search by name/ID, expand/collapse, hover for rank/personal/team business/active (gray = inactive 30d). Find future L2/L3 leaders. Volume split (L1 vs L2 vs L3 points): /associate/team. If a downline member leaves, volume drops automatically."],

    ['title' => 'How to create a mini-site / share your personal property listing', 'tags' => ['associate','howto','minisite','landing'],
     'content' => "Portal → /associate/dashboard → \"Share My Site\" (or /associate/id-card for printed card w/ QR). Referral link: apsdreamhome.com/p/YOUR_CODE — visitors see YOUR branding, name, featured colony, WhatsApp button + booking form. Every inquiry auto-carries your associate ID. Put link as WhatsApp Business greeting, Instagram bio, Google Business Profile website."],
];

// ---------- part 1: seed docs (idempotent by title) ----------
$stmtCheck = $pdo->prepare("SELECT id FROM ai_knowledge_base WHERE title = ?");
$stmtIns = $pdo->prepare("INSERT INTO ai_knowledge_base (tenant_id, title, content, source, tags, metadata, is_active, created_at, updated_at) VALUES (1, ?, ?, 'manual', ?, ?, 1, NOW(), NOW())");
$newIds = [];
$ins = 0; $skip = 0;
$pdo->beginTransaction();
foreach ($docs as $d) {
    $stmtCheck->execute([$d['title']]);
    if ($stmtCheck->fetchColumn()) { $skip++; continue; }
    $tags = json_encode($d['tags'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $stmtIns->execute([$d['title'], $d['content'], $tags, '[]']);
    $newIds[] = (int)$pdo->lastInsertId();
    $ins++;
}
$pdo->commit();
echo "docs inserted={$ins} skipped={$skip}\n";

// ---------- part 2: embed ONLY the newly inserted docs ----------
if ($newIds) {
    $embedSvc = null;
    try {
        $embedSvc = \App\Services\AI\RAGService::getInstance();
    } catch (\Throwable $e) {
        echo "embed: RAGService not available (" . $e->getMessage() . ") — docs stored, run scripts/reembed_rag_chunks.php after setting up the AI key\n";
    }
    if ($embedSvc) {
        $m = new ReflectionMethod($embedSvc, 'chunkAndEmbedDocument');
        $m->setAccessible(true);
        $ok = 0; $fail = 0;
        foreach ($newIds as $id) {
            try {
                $pdo->prepare("DELETE FROM ai_knowledge_chunks WHERE knowledge_base_id = ?")->execute([$id]);
                $content = $pdo->query("SELECT content FROM ai_knowledge_base WHERE id = " . (int)$id)->fetchColumn();
                $m->invoke($embedSvc, $id, (string)$content);
                $ok++;
            } catch (\Throwable $e) {
                $fail++;
                echo "  embed failed doc#{$id}: " . $e->getMessage() . "\n";
            }
        }
        echo "chunks embedded={$ok} failed={$fail}\n";
    }
}

// ---------- part 3: deactivate duplicate menu items (idempotent) ----------
$toDeactivate = [];

$r = $pdo->query("SELECT section, url, MIN(id) keep_id FROM admin_menu_items WHERE is_active = 1 GROUP BY section, url HAVING COUNT(*) > 1");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $sec = $pdo->quote($row['section']);
    $url = $pdo->quote($row['url']);
    $keep = (int)$row['keep_id'];
    $m2 = $pdo->query("SELECT id FROM admin_menu_items WHERE section = $sec AND url = $url AND is_active = 1 AND id != $keep");
    while ($d = $m2->fetch(PDO::FETCH_ASSOC)) {
        $toDeactivate[(int)$d['id']] = 'same-section dupe keep=' . $keep;
    }
}

$r = $pdo->query("SELECT a.id, a.url
                  FROM admin_menu_items a
                  JOIN admin_menu_items b ON b.url = a.url AND b.section <> 'main' AND b.is_active = 1
                  WHERE a.is_active = 1 AND a.section = 'main' AND b.id <> a.id");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $toDeactivate[(int)$row['id']] = 'manifest main dupe';
}

$r = $pdo->query("SELECT url, MIN(id) keep_id FROM admin_menu_items WHERE is_active = 1 AND section <> 'main' GROUP BY url HAVING COUNT(*) > 1");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    $url = $pdo->quote($row['url']);
    $keep = (int)$row['keep_id'];
    $m2 = $pdo->query("SELECT id FROM admin_menu_items WHERE url = $url AND is_active = 1 AND id != $keep");
    while ($d = $m2->fetch(PDO::FETCH_ASSOC)) {
        if (!isset($toDeactivate[(int)$d['id']])) {
            $toDeactivate[(int)$d['id']] = 'cross-section dupe keep=' . $keep;
        }
    }
}

echo "menu items to deactivate: " . count($toDeactivate) . "\n";
if ($toDeactivate) {
    $idList = implode(',', array_map('intval', array_keys($toDeactivate)));
    $n = $pdo->exec("UPDATE admin_menu_items SET is_active = 0 WHERE id IN ($idList)");
    echo "menu rows deactivated: {$n}\n";
} else {
    echo "menu rows deactivated: 0 (already deduped)\n";
}

// ---------- part 4: cache bust (best effort) ----------
try {
    $svc = new \App\Services\AdminMenuService();
    if (method_exists($svc, 'clearMenuCache')) {
        $svc->clearMenuCache();
        echo "menu cache cleared\n";
    }
} catch (\Throwable $e) {
    echo "menu cache clear skipped: " . $e->getMessage() . "\n";
}

echo "=== S544 done ===\n";
