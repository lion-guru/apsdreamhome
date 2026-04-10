<?php
// Agent Dashboard
$page_title = $page_title ?? 'Agent Dashboard - APS Dream Home';
$page_description = $page_description ?? 'Manage your real estate business';
$agent_stats = $agent_stats ?? [];
$recent_leads = $recent_leads ?? [];
$assigned_properties = $assigned_properties ?? [];
$commission_summary = $commission_summary ?? [];
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <p class="text-muted"><?php echo htmlspecialchars($page_description); ?></p>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Stats Cards -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Total Leads</h6>
                    <h3 class="text-primary"><?php echo htmlspecialchars($agent_stats['total_leads'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Converted Leads</h6>
                    <h3 class="text-success"><?php echo htmlspecialchars($agent_stats['converted_leads'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Total Properties</h6>
                    <h3 class="text-info"><?php echo htmlspecialchars($agent_stats['total_properties'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Total Commission</h6>
                    <h3 class="text-warning">₹<?php echo htmlspecialchars($agent_stats['total_commission'] ?? 0); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Recent Leads -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Leads</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_leads)): ?>
                        <p class="text-muted">No recent leads found.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($recent_leads as $lead): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($lead['name'] ?? 'Unknown'); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($lead['status'] ?? 'New'); ?> - <?php echo htmlspecialchars($lead['created_at'] ?? ''); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Assigned Properties -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Assigned Properties</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($assigned_properties)): ?>
                        <p class="text-muted">No properties assigned yet.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($assigned_properties as $property): ?>
                                <li class="list-group-item">
                                    <strong><?php echo htmlspecialchars($property['title'] ?? 'Unknown'); ?></strong>
                                    <br>
                                    <small class="text-muted">₹<?php echo htmlspecialchars($property['price'] ?? 0); ?> - <?php echo htmlspecialchars($property['status'] ?? 'Available'); ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Commission Summary -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">Commission Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Total Commission</h6>
                            <h4 class="text-success">₹<?php echo htmlspecialchars($commission_summary['total_commission'] ?? 0); ?></h4>
                        </div>
                        <div class="col-md-4">
                            <h6>Property Commission</h6>
                            <h4 class="text-info">₹<?php echo htmlspecialchars($commission_summary['property_commission'] ?? 0); ?></h4>
                        </div>
                        <div class="col-md-4">
                            <h6>Referral Commission</h6>
                            <h4 class="text-warning">₹<?php echo htmlspecialchars($commission_summary['referral_commission'] ?? 0); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
