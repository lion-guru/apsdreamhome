<?php
/**
 * Team Performance Widget
 */
$members = $widgetData['members'] ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<div class="table-responsive">
    <table class="table table-sm table-hover mb-0">
        <thead>
            <tr>
                <th>Member</th>
                <th class="text-center">Bookings</th>
                <th class="text-center">Revenue</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($members)): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">No team data</td>
                </tr>
            <?php else: ?>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-2">
                                    <div class="avatar-title bg-primary text-white rounded-circle">
                                        <?php echo strtoupper(substr($member['name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-medium"><?php echo htmlspecialchars($member['name'] ?? 'Member'); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($member['role'] ?? ''); ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center"><?php echo $member['bookings'] ?? 0; ?></td>
                        <td class="text-center">₹<?php echo number_format($member['revenue'] ?? 0, 0); ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?php echo $member['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($member['status'] ?? 'active'); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>