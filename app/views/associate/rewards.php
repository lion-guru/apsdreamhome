<?php
/**
 * Associate Career Rank Rewards Portal
 * Grounded in APS Dream Home Official Rank Rewards Matrix:
 * Associate -> Mobile, Sr. Ass. -> Tablet, BDM -> Laptop, Sr. BDM -> Tour,
 * V.P. -> Pulsar Bike, President -> Bullet Bike, Site Manager -> Luxury Car.
 */
$user = $user ?? [];
$rewards = $rewards ?? [];
$stats = $stats ?? [];
$currentRankName = $current_rank_name ?? 'Ass.';
$effectiveVolume = (float)($effective_volume ?? 0.0);
$csrfToken = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid px-4 py-3">

    <!-- PAGE HEADER -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1 text-dark fw-bold">
                <i class="fas fa-trophy text-warning me-2"></i> करियर रैंक रिवॉर्ड्स प्रोग्राम (Career Rank Rewards)
            </h2>
            <p class="text-muted small mb-0">
                Official APS Dream Home Rank Rewards & Physical Handover Portal — From Smart 5G Mobile to Premium Luxury Car.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?php echo BASE_URL; ?>/associate/salary" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-hand-holding-usd me-1"></i> Diwali Salary Plan
            </a>
            <a href="<?php echo BASE_URL; ?>/associate/allowances" class="btn btn-outline-success btn-sm">
                <i class="fas fa-gas-pump me-1"></i> Site Visit & Office Allowances
            </a>
            <a href="<?php echo BASE_URL; ?>/associate/mlm-plan" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-sitemap me-1"></i> Full Compensation Plan
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- HERO METRICS -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Current Rank</div>
                    <div class="h4 mb-0 fw-bold text-primary mt-1">
                        <i class="fas fa-medal text-warning me-1"></i> <?php echo htmlspecialchars($currentRankName); ?>
                    </div>
                    <div class="small text-muted mt-1">Associate Level</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Team Business Volume</div>
                    <div class="h4 mb-0 fw-bold text-dark mt-1">
                        ₹<?php echo number_format($effectiveVolume); ?>
                    </div>
                    <div class="small text-muted mt-1">Cumulative Group Volume</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Rewards Unlocked</div>
                    <div class="h4 mb-0 fw-bold text-success mt-1">
                        <?php echo (int)($stats['total_achieved'] ?? 0); ?> / 7 Tiers
                    </div>
                    <div class="small text-muted mt-1"><?php echo (int)($stats['total_delivered'] ?? 0); ?> Delivered / Fulfilled</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Next Reward In Sight</div>
                    <div class="h5 mb-0 fw-bold text-warning text-truncate mt-1" title="<?php echo htmlspecialchars($stats['next_reward'] ?? 'All Tiers Achieved'); ?>">
                        <i class="fas fa-gift me-1"></i> <?php echo htmlspecialchars($stats['next_reward'] ?? 'All Achieved! 🏆'); ?>
                    </div>
                    <div class="small text-muted mt-1">Keep closing sales!</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 7 CAREER RANK REWARDS PROGRESSION CARDS -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-award text-primary me-2"></i> 7-Tier Career Rank Rewards Matrix
            </h5>
            <span class="badge bg-light text-dark border">
                Official Company Promotional Flyer Grounded
            </span>
        </div>

        <div class="row g-3">
            <?php foreach ($rewards as $idx => $r): ?>
                <?php
                $status = $r['status'] ?? 'locked';
                $isEligible = ($status === 'eligible');
                $isClaimed = in_array($status, ['claimed', 'approved'], true);
                $isDispatched = ($status === 'dispatched');
                $isDelivered = in_array($status, ['delivered', 'converted_to_wallet'], true);
                $dbRec = $r['db_record'] ?? [];
                $rewardId = $dbRec['id'] ?? 0;
                ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden <?php echo $isDelivered ? 'border-top border-4 border-success' : ($isEligible ? 'border-top border-4 border-primary' : ''); ?>">
                        
                        <!-- HEADER PILL -->
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white"
                                             style="width: 48px; height: 48px; background-color: <?php echo htmlspecialchars($r['color']); ?>; font-size: 20px;">
                                            <i class="<?php echo htmlspecialchars($r['icon']); ?>"></i>
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark border small fw-normal">Tier <?php echo $idx + 1; ?>: <?php echo htmlspecialchars($r['rank_title']); ?></span>
                                            <h5 class="fw-bold mb-0 text-dark mt-1"><?php echo htmlspecialchars($r['reward_name']); ?></h5>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-warning text-dark fw-bold px-2 py-1">
                                            ₹<?php echo number_format($r['reward_value']); ?>
                                        </span>
                                    </div>
                                </div>

                                <p class="text-muted small mb-3">
                                    <?php echo htmlspecialchars($r['description']); ?>
                                </p>

                                <!-- QUALIFICATION CRITERIA -->
                                <div class="bg-light rounded p-2 mb-3 small">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Target Volume:</span>
                                        <span class="fw-bold text-dark">
                                            <?php echo $r['volume_target'] > 0 ? '₹' . number_format($r['volume_target']) : 'Starting Rank (₹0)'; ?>
                                        </span>
                                    </div>
                                    <?php if ($r['legs_required'] > 0): ?>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">Team Structure:</span>
                                            <span class="fw-bold text-dark">
                                                <?php echo (int)$r['legs_required']; ?> Direct <?php echo htmlspecialchars($r['required_rank'] ?? 'Associates'); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- PROGRESS BARS (IF LOCKED OR IN PROGRESS) -->
                                <?php if ($status === 'locked'): ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                            <span>Volume Progress</span>
                                            <span class="fw-bold"><?php echo $r['vol_progress']; ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $r['vol_progress']; ?>%"></div>
                                        </div>
                                        <?php if ($r['legs_required'] > 0): ?>
                                            <div class="d-flex justify-content-between small text-muted mt-2 mb-1">
                                                <span>Team Legs Progress (<?php echo (int)$r['leg_count']; ?>/<?php echo (int)$r['legs_required']; ?>)</span>
                                                <span class="fw-bold"><?php echo $r['leg_progress']; ?>%</span>
                                            </div>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo $r['leg_progress']; ?>%"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- ACTION / STATUS AREA -->
                            <div class="pt-2 border-top">
                                <?php if ($isEligible && $rewardId > 0): ?>
                                    <button type="button" class="btn btn-success w-100 fw-bold shadow-sm"
                                            data-bs-toggle="modal" data-bs-target="#claimModal<?php echo $rewardId; ?>">
                                        <i class="fas fa-gift me-1"></i> Claim Reward Now
                                    </button>
                                <?php elseif ($status === 'claimed'): ?>
                                    <div class="text-center py-1">
                                        <span class="badge bg-warning text-dark py-2 px-3 w-100">
                                            <i class="fas fa-clock me-1"></i> Claim Submitted — Under Review
                                        </span>
                                    </div>
                                <?php elseif ($status === 'approved'): ?>
                                    <div class="text-center py-1">
                                        <span class="badge bg-primary py-2 px-3 w-100">
                                            <i class="fas fa-check-double me-1"></i> Approved — Preparing Dispatch / Handover
                                        </span>
                                    </div>
                                <?php elseif ($status === 'dispatched'): ?>
                                    <div class="text-center py-1">
                                        <span class="badge bg-info text-dark py-2 px-3 w-100">
                                            <i class="fas fa-shipping-fast me-1"></i> Dispatched: <?php echo htmlspecialchars($dbRec['courier_partner'] ?? 'Courier'); ?>
                                            (<?php echo htmlspecialchars($dbRec['tracking_number'] ?? 'In Transit'); ?>)
                                        </span>
                                    </div>
                                <?php elseif ($status === 'delivered'): ?>
                                    <div class="text-center py-1">
                                        <span class="badge bg-success py-2 px-3 w-100">
                                            <i class="fas fa-hand-holding-heart me-1"></i> Officially Handed Over & Delivered
                                        </span>
                                    </div>
                                <?php elseif ($status === 'converted_to_wallet'): ?>
                                    <div class="text-center py-1">
                                        <span class="badge bg-purple text-white py-2 px-3 w-100" style="background-color: #7c3aed;">
                                            <i class="fas fa-wallet me-1"></i> ₹<?php echo number_format((float)($dbRec['cash_converted_amount'] ?? $r['reward_value'])); ?> Credited to Wallet
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-1">
                                        <span class="badge bg-secondary py-2 px-3 w-100 fw-normal">
                                            <i class="fas fa-lock me-1"></i> Locked — Target in Progress
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- CLAIM MODAL (IF REWARD IS ELIGIBLE) -->
                <?php if ($rewardId > 0 && ($isEligible || $status === 'rejected')): ?>
                    <div class="modal fade" id="claimModal<?php echo $rewardId; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <form method="POST" action="<?php echo BASE_URL; ?>/associate/rewards/<?php echo $rewardId; ?>/claim">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title fw-bold">
                                            <i class="fas fa-gift me-2"></i> Claim <?php echo htmlspecialchars($r['reward_name']); ?>
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="text-center mb-3">
                                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-2"
                                                 style="width: 60px; height: 60px; background-color: <?php echo htmlspecialchars($r['color']); ?>; font-size: 26px;">
                                                <i class="<?php echo htmlspecialchars($r['icon']); ?>"></i>
                                            </div>
                                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($r['reward_name']); ?></h5>
                                            <span class="badge bg-warning text-dark">Worth ₹<?php echo number_format($r['reward_value']); ?></span>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Preferred Delivery Method</label>
                                            <select aria-label="Delivery type" name="delivery_type" class="form-select" id="deliveryTypeSelect<?php echo $rewardId; ?>" onchange="toggleDeliveryFields(<?php echo $rewardId; ?>)">
                                                <option value="physical_handover">🏢 Office Ceremony Handover (Gorakhpur Head Office / Gala Event)</option>
                                                <option value="courier_dispatch">📦 Doorstep Courier Dispatch (To My Address)</option>
                                                <option value="wallet_cash_equivalent">💳 Convert to Direct Cash in My Wallet (₹<?php echo number_format($r['reward_value']); ?>)</option>
                                            </select>
                                            <div class="form-text small">You may choose to receive the physical item or convert its cash equivalent to your wallet balance.</div>
                                        </div>

                                        <div id="addressSection<?php echo $rewardId; ?>">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Contact Phone Number</label>
                                                <input type="text" name="contact_phone" class="form-control"
                                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="10-digit mobile number">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Delivery / Shipping Address</label>
                                                <textarea aria-label="Shipping address" name="shipping_address" class="form-control" rows="2"
                                                          placeholder="Full street address with landmark and PIN code"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Additional Notes (Optional)</label>
                                            <textarea aria-label="Notes" name="notes" class="form-control" rows="2" placeholder="Any preferences, device color, or handover remarks..."></textarea>
                                        </div>

                                        <div class="alert alert-info py-2 small mb-0">
                                            <i class="fas fa-info-circle me-1"></i> Admin verification desk will review your volume qualification and contact you regarding dispatch / ceremony handover.
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success btn-sm fw-bold">
                                            <i class="fas fa-check-circle me-1"></i> Confirm & Submit Claim
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- CLAIMS & HANDOVER TRACKING LEDGER -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-clipboard-check text-success me-2"></i> Claims & Fulfillment Tracking Ledger
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th scope="col" class="ps-3">Reward Item</th>
                            <th scope="col">Rank Slug</th>
                            <th scope="col">Value</th>
                            <th scope="col">Delivery Mode</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tracking / Serial / Event</th>
                            <th scope="col">Timeline</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php
                        $claimedList = array_filter($rewards, fn($r) => !empty($r['db_record']));
                        ?>
                        <?php if (empty($claimedList)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-gift fa-2x mb-2 text-muted opacity-50 d-block"></i>
                                    No reward claims submitted yet. Build team sales to unlock authentic rank rewards!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($claimedList as $r): ?>
                                <?php
                                $d = $r['db_record'];
                                $cStatus = $d['claim_status'] ?? 'eligible';
                                ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">
                                        <i class="<?php echo htmlspecialchars($r['icon']); ?> me-2 text-primary"></i>
                                        <?php echo htmlspecialchars($r['reward_name']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($r['rank_name']); ?></span>
                                    </td>
                                    <td class="fw-bold">
                                        ₹<?php echo number_format($r['reward_value']); ?>
                                    </td>
                                    <td>
                                        <?php if ($d['delivery_type'] === 'wallet_cash_equivalent'): ?>
                                            <span class="badge bg-purple text-white" style="background-color: #7c3aed;">💳 Wallet Cash</span>
                                        <?php elseif ($d['delivery_type'] === 'courier_dispatch'): ?>
                                            <span class="badge bg-info text-dark">📦 Courier</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">🏢 Office Handover</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($cStatus === 'eligible'): ?>
                                            <span class="badge bg-success">Eligible to Claim</span>
                                        <?php elseif ($cStatus === 'claimed'): ?>
                                            <span class="badge bg-warning text-dark">Claim Submitted</span>
                                        <?php elseif ($cStatus === 'approved'): ?>
                                            <span class="badge bg-primary">Approved</span>
                                        <?php elseif ($cStatus === 'dispatched'): ?>
                                            <span class="badge bg-info text-dark">Dispatched</span>
                                        <?php elseif ($cStatus === 'delivered'): ?>
                                            <span class="badge bg-success">Delivered / Handed Over</span>
                                        <?php elseif ($cStatus === 'converted_to_wallet'): ?>
                                            <span class="badge bg-dark">Converted to Wallet</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($cStatus); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($d['tracking_number'])): ?>
                                            <div><strong>Tracking:</strong> <?php echo htmlspecialchars($d['tracking_number']); ?> (<?php echo htmlspecialchars($d['courier_partner'] ?? ''); ?>)</div>
                                        <?php endif; ?>
                                        <?php if (!empty($d['serial_number'])): ?>
                                            <div><strong>Serial / IMEI:</strong> <?php echo htmlspecialchars($d['serial_number']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($d['handover_event'])): ?>
                                            <div><strong>Event:</strong> <?php echo htmlspecialchars($d['handover_event']); ?></div>
                                        <?php endif; ?>
                                        <?php if (empty($d['tracking_number']) && empty($d['serial_number']) && empty($d['handover_event'])): ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted">
                                        <?php if (!empty($d['claimed_at'])): ?>
                                            <div>Claimed: <?php echo date('d M Y', strtotime($d['claimed_at'])); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($d['delivered_at'])): ?>
                                            <div class="text-success">Delivered: <?php echo date('d M Y', strtotime($d['delivered_at'])); ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- OFFICIAL POLICY ACCORDION -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fas fa-file-contract text-primary me-2"></i> Official Reward Guidelines & Handover Policy
            </h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-3 small text-muted">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded h-100">
                        <strong class="text-dark d-block mb-1"><i class="fas fa-certificate text-warning me-1"></i> 100% Genuine Physical Handover</strong>
                        All vehicles (Pulsar, Bullet, Luxury Car) and electronics (Mobile, Tablet, Laptop) are brand new, purchased through authorized company dealership partners with warranty and RTO paperwork.
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded h-100">
                        <strong class="text-dark d-block mb-1"><i class="fas fa-exchange-alt text-success me-1"></i> Optional Wallet Cash Conversion</strong>
                        Associates who prefer monetary compensation may choose to convert their eligible rank reward into equivalent cash credited directly to their wallet for withdrawal or EMI adjustment.
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded h-100">
                        <strong class="text-dark d-block mb-1"><i class="fas fa-shield-alt text-primary me-1"></i> Group Volume & Structure Integrity</strong>
                        Rewards are evaluated against genuine registered plot bookings with verified Bayana/Registry receipts. Cancelled or refunded transactions do not count towards rank reward milestones.
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function toggleDeliveryFields(rewardId) {
    var sel = document.getElementById('deliveryTypeSelect' + rewardId);
    var addrSec = document.getElementById('addressSection' + rewardId);
    if (!sel || !addrSec) return;
    if (sel.value === 'wallet_cash_equivalent') {
        addrSec.style.display = 'none';
    } else {
        addrSec.style.display = 'block';
    }
}
</script>

