<?php
/**
 * Customer Dashboard - APS Dream Home
 */

$layout = 'layouts/base';
$page_title = $page_title ?? 'Customer Dashboard - APS Dream Home';
$page_description = $page_description ?? 'Your personalized real estate dashboard';
?>

<section class="py-4">
    <div class="container">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="fw-bold mb-2">Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                                <p class="text-muted mb-0">Customer ID: <?php echo htmlspecialchars($user['customer_id']); ?> | Member since: <?php echo htmlspecialchars($user['join_date']); ?></p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="<?php echo BASE_URL; ?>/dashboard/profile" class="btn btn-outline-primary">
                                    <i class="fas fa-user-edit me-2"></i>Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-heart fa-2x mb-3"></i>
                        <h3 class="fw-bold"><?php echo $stats['favorites_count']; ?></h3>
                        <p class="mb-0">Favorites</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-envelope fa-2x mb-3"></i>
                        <h3 class="fw-bold"><?php echo $stats['inquiries_count']; ?></h3>
                        <p class="mb-0">Inquiries</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-eye fa-2x mb-3"></i>
                        <h3 class="fw-bold"><?php echo $stats['views_count']; ?></h3>
                        <p class="mb-0">Property Views</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-search fa-2x mb-3"></i>
                        <h3 class="fw-bold"><?php echo $stats['saved_searches_count']; ?></h3>
                        <p class="mb-0">Saved Searches</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities & Favorites -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <div class="fw-bold">
                                            <?php if ($activity['type'] === 'favorite'): ?>
                                                <i class="fas fa-heart text-primary me-2"></i>Favorited
                                            <?php elseif ($activity['type'] === 'inquiry'): ?>
                                                <i class="fas fa-envelope text-success me-2"></i>Inquired about
                                            <?php else: ?>
                                                <i class="fas fa-eye text-info me-2"></i>Viewed
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($activity['property']); ?>
                                        </div>
                                        <small class="text-muted"><?php echo htmlspecialchars($activity['date']); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">No recent activities</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Favorite Properties</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($favorite_properties)): ?>
                            <?php foreach ($favorite_properties as $property): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($property['title']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($property['location']); ?> • <?php echo htmlspecialchars($property['price']); ?></small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger remove-favorite" data-property-id="<?php echo $property['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">No favorite properties yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Inquiries -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Recent Inquiries</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_inquiries)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Property</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_inquiries as $inquiry): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($inquiry['property']); ?></div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $inquiry['status'] === 'Responded' ? 'success' : 'warning'; ?>">
                                                        <?php echo htmlspecialchars($inquiry['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($inquiry['date']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary">View Details</button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No inquiries yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommended Properties -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Recommended Properties</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recommended_properties)): ?>
                            <div class="row">
                                <?php foreach ($recommended_properties as $property): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card h-100">
                                            <img src="<?php echo BASE_URL; ?>/assets/images/placeholder-property.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h6>
                                                <p class="text-muted small mb-2"><?php echo htmlspecialchars($property['location']); ?></p>
                                                <p class="text-primary fw-bold mb-3"><?php echo htmlspecialchars($property['price']); ?></p>
                                                <div class="d-flex justify-content-between">
                                                    <button class="btn btn-sm btn-outline-primary add-favorite" data-property-id="<?php echo $property['id']; ?>">
                                                        <i class="fas fa-heart me-1"></i>Save
                                                    </button>
                                                    <button class="btn btn-sm btn-primary">View Details</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">No recommended properties available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove from favorites
    document.querySelectorAll('.remove-favorite').forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.dataset.propertyId;
            if (confirm('Remove this property from favorites?')) {
                // AJAX call to remove favorite
                fetch('<?php echo BASE_URL; ?>/dashboard/favorites/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        property_id: propertyId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        });
    });

    // Add to favorites
    document.querySelectorAll('.add-favorite').forEach(button => {
        button.addEventListener('click', function() {
            const propertyId = this.dataset.propertyId;
            
            // AJAX call to add favorite
            fetch('<?php echo BASE_URL; ?>/dashboard/favorites/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    property_id: propertyId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    });
});
</script>