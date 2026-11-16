<?php
$csrf_token = $_SESSION['csrf_token'] ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$ranks = [
    ['Associate', 1, 5.00, 2.00, 0.00, 0.00, 0.00, 0.00, 1000000],
    ['Sr. Associate', 2, 7.00, 3.00, 2.00, 5.00, 0.00, 0.00, 3500000],
    ['BDM', 3, 10.00, 4.00, 3.00, 8.00, 1.00, 0.00, 7000000],
    ['Sr. BDM', 4, 12.00, 5.00, 4.00, 10.00, 2.00, 1.00, 15000000],
    ['Vice President', 5, 15.00, 6.00, 5.00, 12.00, 3.00, 2.00, 30000000],
    ['President', 6, 18.00, 7.00, 6.00, 15.00, 4.00, 3.00, 50000000],
    ['Site Manager', 7, 20.00, 8.00, 7.00, 18.00, 5.00, 5.00, 999999999],
];
$activePlanName = $activePlanName ?? null;
?>
<style>
.cp-card{background:#1a1f36;border:1px solid #2a2f4a;border-radius:12px;color:#e0e0e0;margin-bottom:1.5rem}
.cp-card-header{background:linear-gradient(135deg,#141829,#1e2340);padding:1rem 1.5rem;border-bottom:1px solid #2a2f4a;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem}
.cp-card-body{padding:1.5rem}
.cp-input{background:#0f1225;border:1px solid #2a2f4a;border-radius:8px;color:#e0e0e0;padding:8px 12px;width:100%;font-size:.85rem}
.cp-input:focus{border-color:#4f8cff;outline:none;box-shadow:0 0 0 2px #4f8cff33}
.cp-label{color:#aeb8d0;font-size:.72rem;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;display:block;font-weight:600}
.cp-help{color:#7183a8;font-size:.7rem;margin-top:3px;line-height:1.35}
.cp-help b{color:#9fb4e8;font-weight:600}
.cp-step{display:flex;align-items:center;gap:.75rem;margin:1.5rem 0 .9rem}
.cp-step:first-of-type{margin-top:.5rem}
.cp-step .n{background:linear-gradient(135deg,#4f8cff,#6366f1);color:#fff;font-weight:700;font-size:.8rem;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cp-step h6{margin:0;font-size:.88rem;color:#dbe4ff;text-transform:none;letter-spacing:0}
.cp-step p{margin:2px 0 0;font-size:.72rem;color:#7183a8}
.cp-note{border-left:3px solid #4f8cff;background:#161c34;border-radius:8px;padding:.75rem 1rem;font-size:.78rem;color:#b9c6e8;margin:1rem 0;line-height:1.5}
.cp-note strong{color:#8fbaff}
.cp-note .ex{color:#ffd166;font-weight:600}
.cp-mini{background:#12172b;border:1px solid #2a2f4a;border-radius:10px;padding:.75rem .9rem;font-size:.72rem;color:#9aa9cf}
.cp-mini .t{color:#7f95d6;text-transform:uppercase;font-size:.68rem;letter-spacing:.5px;font-weight:700;margin-bottom:.4rem}
.cp-mini b{color:#ffd166}
.cp-btn{padding:9px 20px;border-radius:8px;font-size:.85rem;font-weight:500;border:none;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none}
.cp-btn-primary{background:linear-gradient(135deg,#4f8cff,#6366f1);color:#fff}
.cp-btn-primary:hover{transform:translateY(-1px);box-shadow:0 4px 15px #4f8cff44}
.cp-btn-outline{background:transparent;border:1px solid #4f8cff44;color:#4f8cff}
.rank-table th{background:#1e2340;color:#aeb8d0;font-size:.66rem;text-transform:uppercase;padding:8px 10px;border:none}
.rank-table th small{display:block;font-size:.62rem;color:#7183a8;text-transform:none;letter-spacing:0;font-weight:400;margin-top:2px}
.rank-table td{padding:8px 10px;border-top:1px solid #1e2340;vertical-align:middle}
.rank-table input{background:#0f1225;border:1px solid #2a2f4a;border-radius:6px;color:#e0e0e0;padding:6px 8px;width:100%;font-size:.82rem;text-align:right}
.rank-table input:focus{border-color:#4f8cff;outline:none}
@media (max-width: 1200px){ .cp-side{position:static !important;width:100% !important} }
</style>

<div class="row g-3">
<div class="col-xl-9">
<div class="cp-card">
    <div class="cp-card-header">
        <div>
            <h5 class="m-0"><i class="fas fa-layer-group me-2 text-primary"></i>Create Commission Plan</h5>
            <div class="small text-muted mt-1">Association salaries, bonuses aur GBV ka poora rules-book. Yahaan jo banao, wahi saare bookings pe panna (payout) ke liye engine use karega.</div>
        </div>
        <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline"><i class="fas fa-arrow-left"></i>Plans List</a>
    </div>
    <div class="cp-card-body">

        <!-- How it works strip -->
        <div class="cp-note">
            <strong>Yeh page kya karta hai?</strong> Yahan aap ek <strong>compensation plan</strong> define karte hain — yani "Company plot sale pe associate ko kitna commission, kis rank par kitna override, kab bonus dena hai" ka rule-sheet.
            <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:.74rem">
                <div><b>1.</b> Create — plan pehle <b>draft</b> banta hai, kuch payouts nahi padte</div>
                <div><b>2.</b> Review — niche ka table edit karke numbers set karo</div>
                <div><b>3.</b> Activate — Plans List page se <i class="fas fa-power-off"></i> dabao, tab se saare payouts isi plan se calculate honge</div>
            </div>
        </div>

        <form method="POST" action="<?= $base ?>/admin/commission-plans/store">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

            <!-- STEP 1: Identity -->
            <div class="cp-step"><div class="n">1</div><div><h6>Plan ki pehchan (identity)</h6><p>Plan ko naam do — code bhi do, kyunki engine code se payouts audit karega.</p></div></div>
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="cp-label" for="cp-name">Plan Name *</label>
                    <input id="cp-name" aria-label="Plan name" type="text" name="plan_name" class="cp-input" required placeholder="e.g. Standard Commission Plan v1">
                    <div class="cp-help">Display name. e.g. <b>“Standard Commission Plan v1”</b> — khud April 2026 update identify ho jayegi.</div>
                </div>
                <div class="col-md-2">
                    <label class="cp-label" for="cp-code">Plan Code *</label>
                    <input id="cp-code" aria-label="Plan code" type="text" name="plan_code" class="cp-input" required placeholder="e.g. STD" maxlength="20" style="text-transform:uppercase">
                    <div class="cp-help">Unique code (uppercase). e.g. <b>STD</b>, <b>2026-RE</b>. Engine logs isi se book karta hai.</div>
                </div>
                <div class="col-md-2">
                    <label class="cp-label" for="cp-type">Plan Type</label>
                    <select id="cp-type" aria-label="Plan type" name="plan_type" class="cp-input">
                        <option value="hybrid" selected>Hybrid (recommended)</option>
                        <option value="unilevel">Unilevel</option>
                        <option value="binary">Binary</option>
                        <option value="matrix">Matrix</option>
                    </select>
                    <div class="cp-help"><b>Hybrid</b> = direct sale + team override, dono pay. Neeche ka rank table ishi ke liye calibrate hai.</div>
                </div>
                <div class="col-md-2">
                    <label class="cp-label" for="cp-effective">Effective Date</label>
                    <input id="cp-effective" aria-label="Effective date" type="date" name="effective_date" class="cp-input" value="<?= date('Y-m-d') ?>">
                    <div class="cp-help">Plan kab se lagoo? Activate se pehle change ho sakta hai.</div>
                </div>
                <div class="col-md-3">
                    <label class="cp-label" for="cp-desc">Description (why this plan)</label>
                    <input id="cp-desc" aria-label="Description" type="text" name="description" class="cp-input" placeholder="e.g. April 2026 revision — slab rates +1%">
                    <div class="cp-help">Ek line — versions ke beech kya badla, futuro ke liye notes.</div>
                </div>
            </div>

            <!-- STEP 2: Payout controls -->
            <div class="cp-step"><div class="n">2</div><div><h6>Har ₹ sale ka budget kaise katega (Payout Controls)</h6><p>Ye 8 numbers decide karte hain ki company sale se kitna budget commission ke liye degi — associate-level math neeche Step 3 me hai.</p></div></div>

            <div class="cp-note" style="border-left-color:#ffd166">
                <strong>Socho kaise:</strong> Plot ₹15,00,000 me becha. Company <span class="ex">Global Cap 20%</span> se zyada commission nahi de sakti — yani ₹3,00,000 ke andar direct + team + bonuses poore fit hone chahiye. Baaki numbers usi ₹3,00,000 ke beech split ke rules hain.
            </div>

            <div class="row g-3 mb-2">
                <div class="col-md-2">
                    <label class="cp-label" for="cp-cap">Global Cap % — sabse bada lock</label>
                    <input id="cp-cap" aria-label="Global cap percent" type="number" name="global_cap_pct" class="cp-input" value="20" step="0.5" min="0" max="100">
                    <div class="cp-help"><b>Max</b> jo company ek sale se de sakti hai (direct + team + bonuses sab). Default 20% = ₹1,00,000/₹5,00,000.</div>
                </div>
                <div class="col-md-2">
                    <label class="cp-label" for="cp-ta">Track A % — slab difference</label>
                    <input id="cp-ta" aria-label="Track A percent" type="number" name="track_a_pct" class="cp-input" value="15" step="0.5" min="0" max="100">
                    <div class="cp-help">Seller ko direct commission usi rank slab se milta hai; uska <b>upar wala 20%</b> upper-level ke liye differential hai.</div>
                </div>
                <div class="col-md-2">
                    <label class="cp-label" for="cp-tb">Track B % — roll-up override</label>
                    <input id="cp-tb" aria-label="Track B percent" type="number" name="track_b_pct" class="cp-input" value="3" step="0.5" min="0" max="100">
                    <div class="cp-help"><b>Team ke downline</b> sale pe aapko milta hai override — slab diff ke baad ka slice.</div>
                </div>
                <div class="col-md-2">
                    <label class="cp-label" for="cp-tc">Track C % — milestone bonus</label>
                    <input id="cp-tc" aria-label="Track C percent" type="number" name="track_c_pct" class="cp-input" value="2" step="0.5" min="0" max="100">
                    <div class="cp-help">Rank targets (GBV) cross karne par milta hai onetime booster — 3.0 = ₹1,50,000/₹15,00,000 ke paanch.</div>
                </div>
                <div class="col-md-2">
                    <label class="cp-label" for="cp-rp">Royalty Pool % — company fund</label>
                    <input id="cp-rp" aria-label="Royalty pool percent" type="number" name="royalty_pool_pct" class="cp-input" value="2" step="0.5" min="0" max="100">
                    <div class="cp-help"><b>Company-funded</b> pool poore network me distribute hota hai har month — ₹2/₹100 revenue company ke royalty fund me.</div>                </div>
                <div class="col-md-1">
                    <label class="cp-label" for="cp-g1">Override G1 % — peer match</label>
                    <input id="cp-g1" aria-label="Override Gen1" type="number" name="same_level_override_gen1" class="cp-input" value="2" step="0.5" min="0">
                    <div class="cp-help"><b>Gen-1</b> (aapke direct recruit ne) usi rank par pahuncha to match override pay hoga.</div>
                </div>
                <div class="col-md-1">
                    <label class="cp-label" for="cp-g2">Override G2 %</label>
                    <input id="cp-g2" aria-label="Override Gen2" type="number" name="same_level_override_gen2" class="cp-input" value="1" step="0.5" min="0">
                    <div class="cp-help"><b>Gen-2</b> same-rank override — kam depth, isliye default half.</div>
                </div>
            </div>

            <!-- STEP 3: Rank levels -->
            <div class="cp-step"><div class="n">3</div><div><h6>Rank ke hisaab se commission slabs (auto-created defaults)</h6><p>Associate → Site Manager tak ki hierarchy. Inko edit karna matlab poori company ki payout seedha badlega — defaults safe hain, pichle plans ka experiment inhe override karne ke liye hi hain.</p></div></div>

            <div class="cp-note">
                <strong>GBV seedha Samjhein:</strong> monthly sales volume (Group BV). “1,00,00,000” matlab ₹1 crore. Rank wahi milta hai jab <b>GBV threshold</b> cross ho — payout slabs bhi waise hi level up hote hain.
                <div class="mt-1"><strong>Har column kya hai:</strong> Direct % = apni sale par • Team % = team ki sales par override (slab diff) • Level Bonus = naya rank pakadne par • Matching % = personal recruiter match • Leadership % = senior-slab ladder hota jaata hai • Performance % = monthly target hit par • GBV = rank hold karne ki minimum monthly volume</div>
            </div>

            <div style="overflow-x:auto;margin-bottom:1rem;border:1px solid #2a2f4a;border-radius:10px">
                <table class="table rank-table m-0">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Rank</th>
                            <th scope="col">Direct %<small>apni sale par</small></th>
                            <th scope="col">Team %<small>team sales differential</small></th>
                            <th scope="col">Level Bonus<small>rank promote bonus</small></th>
                            <th scope="col">Matching %<small>personal recruit match</small></th>
                            <th scope="col">Leadership %<small>senior slab ladder</small></th>
                            <th scope="col">Performance %<small>monthly target hit</small></th>
                            <th scope="col">GBV Threshold <small>monthly volume hold</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranks as $i => $r): ?>
                        <tr>
                            <td><?= $r[1] ?><input type="hidden" name="levels[<?= $i ?>][1]" value="<?= $r[1] ?>"></td>
                            <td><b><?= $r[0] ?></b><input type="hidden" name="levels[<?= $i ?>][0]" value="<?= htmlspecialchars($r[0]) ?>"></td>
                            <td><input type="number" aria-label="Direct commission % for <?= htmlspecialchars($r[0]) ?>" name="levels[<?= $i ?>][2]" value="<?= $r[2] ?>" step="0.5" min="0" max="100" title="Direct sale commission at this rank"></td>
                            <td><input type="number" aria-label="Team commission % for <?= htmlspecialchars($r[0]) ?>" name="levels[<?= $i ?>][3]" value="<?= $r[3] ?>" step="0.5" min="0" max="100" title="Override on team sales at this rank"></td>
                            <td><input type="number" aria-label="Level bonus % for <?= htmlspecialchars($r[0]) ?>" name="levels[<?= $i ?>][4]" value="<?= $r[4] ?>" step="0.5" min="0" max="100" title="Bonus when first achieving this rank"></td>
                            <td><input type="number" aria-label="Matching bonus % for <?= htmlspecialchars($r[0]) ?>" name="levels[<?= $i ?>][5]" value="<?= $r[5] ?>" step="0.5" min="0" max="100" title="Matching bonus on personally sponsored sales"></td>
                            <td><input type="number" aria-label="Leadership bonus % for <?= htmlspecialchars($r[0]) ?>" name="levels[<?= $i ?>][6]" value="<?= $r[6] ?>" step="0.5" min="0" max="100" title="Leadership differential at this rank"></td>
                            <td><input type="number" aria-label="Performance bonus % for <?= htmlspecialchars($r[0]) ?>" name="levels[<?= $i ?>][7]" value="<?= $r[7] ?>" step="0.5" min="0" max="100" title="Bonus on hitting monthly performance target"></td>
                            <td><input type="number" aria-label="Monthly GBV threshold for <?= htmlspecialchars($r[0]) ?>" name="levels[<?= $i ?>][8]" value="<?= $r[8] ?>" step="100000" min="0" title="Minimum monthly group sales volume to hold this rank"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 flex-wrap align-items-center">
                <button type="submit" class="cp-btn cp-btn-primary"><i class="fas fa-save"></i>Create Draft Plan (v1)</button>
                <a href="<?= $base ?>/admin/commission-plans" class="cp-btn cp-btn-outline"><i class="fas fa-times"></i>Cancel</a>
                <span class="small text-muted">Plan draft banta hai — confirm ke baad <b>Activate</b> karna plan list page se.</span>
            </div>
        </form>
    </div>
</div>
</div>

<!-- SIDE: worked example -->
<div class="col-xl-3">
    <div class="cp-mini cp-side mb-3" style="position:sticky;top:1rem">
        <div class="t"><i class="fas fa-calculator me-1"></i>Live example — kaise calculate hoga?</div>
        <div class="mb-2"><b>Sale ₹15,00,000</b> — seller <b>Sr. Associate</b> rank pe hai (7% direct):</div>
        <div style="padding-left:.5rem;border-left:2px solid #2a2f4a">
            Direct commission (7%) → <b>₹1,05,000</b><br>
            Team override 3% (Track B) → team ne kiya<br>
            Agar seller VP tha (15%) to ladder choti hai<br>
            Whole-budget cap 20% (₹3,00,000) kabhi nahi todhne ka rule embedded hai<br>
            Promotion se matching/performance bonus Step 3 ke slabs me se aata hai
        </div>
        <div class="mt-2"><b>GBV 1,00,00,000</b> = ₹1 crore/month — <b>Site Manager</b> rank yahi par hota hai — sab kuch isi table me dala ja sakta hai.</div>
    </div>

    <div class="cp-mini">
        <div class="t"><i class="fas fa-stream me-1"></i>Plan banne ke baad ka flow</div>
        <ol class="mb-0" style="padding-left:1.1rem;line-height:1.7">
            <li><b>Create</b> — plan <code>draft</code> v1 ke roop me save hota hai</li>
            <li><b>Review</b> — Plans List me deta dekho, edit karo jiya to</li>
            <li><b>Activate</b> — <i class="fas fa-power-off"></i> button, plan live ho jayega</li>
            <li><b>Effective</b> — engine snapshot leke isi plan se payouts calculate karta hai</li>
        </ol>
        <div class="mt-2" style="color:#7183a8">Active plan hamesha latest <code>version</code> ka hota hai. Sab purane draft version ki update ke liye Plans List → Edit.</div>
    </div>

    <div class="cp-mini mt-3">
        <div class="t"><i class="fas fa-triangle-exclamation me-1"></i>Yaad rakhein</div>
        <ul class="mb-0" style="padding-left:1.1rem;line-height:1.7">
            <li>Draft plan <b>koi commission nahi dega</b> — activate zaroori hai</li>
            <li>Activating kisi dusre plan ko deactivate karta hai</li>
            <li>Error hoga to plan banega nahi — <code>commission_plan_audit</code> me log ho jayega</li>
        </ul>
    </div>
</div>
</div>
