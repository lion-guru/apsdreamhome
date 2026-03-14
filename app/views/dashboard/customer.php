<?php
/**
 * Customer Dashboard - APS Dream Home
 */

$layout = 'layouts/base';
$page_title = $page_title ?? 'Customer Dashboard - APS Dream Home';
$page_description = $page_description ?? 'Your personalized real estate dashboard';
?>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.3);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            padding: 1.5rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.25);
        }

        .dashboard-header {
            background: var(--primary-gradient);
            border-radius: 24px;
            padding: 2.5rem;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 10px 20px rgba(0, 242, 254, 0.2);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            margin-bottom: 1rem;
        }

        .bg-gradient-blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .bg-gradient-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .bg-gradient-orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .bg-gradient-purple { background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%); }

        .property-thumb {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            object-fit: cover;
            margin-right: 1rem;
        }

        .badge-pill {
            border-radius: 50px;
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
    </style>

<section class="py-5 bg-light min-vh-100">
    <div class="container">
        <!-- Welcome Header -->
        <div class="dashboard-header animate-fade-in">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="fw-bold mb-2">Hello, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                    <p class="mb-0 opacity-75">
                        <i class="fas fa-id-badge me-2"></i><?php echo htmlspecialchars($user['customer_id']); ?> 
                        <span class="mx-2">|</span> 
                        <i class="fas fa-calendar-alt me-2"></i>Member since <?php echo date('M Y', strtotime($user['join_date'])); ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="/dashboard/profile" class="btn btn-white bg-white text-primary rounded-pill px-4 py-2 fw-medium shadow-sm transition-hover">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Grid -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="glass-card">
                    <div class="stat-icon bg-gradient-blue"><i class="fas fa-heart"></i></div>
                    <h3 class="fw-bold mb-1"><?php echo $stats['favorites_count']; ?></h3>
                    <p class="text-muted small mb-0">Saved Properties</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card">
                    <div class="stat-icon bg-gradient-green"><i class="fas fa-envelope"></i></div>
                    <h3 class="fw-bold mb-1"><?php echo $stats['inquiries_count']; ?></h3>
                    <p class="text-muted small mb-0">Active Inquiries</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card">
                    <div class="stat-icon bg-gradient-orange"><i class="fas fa-eye"></i></div>
                    <h3 class="fw-bold mb-1"><?php echo $stats['views_count']; ?></h3>
                    <p class="text-muted small mb-0">Recent Views</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="glass-card">
                    <div class="stat-icon bg-gradient-purple"><i class="fas fa-search"></i></div>
                    <h3 class="fw-bold mb-1"><?php echo $stats['saved_searches_count']; ?></h3>
                    <p class="text-muted small mb-0">Custom Alerts</p>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- Recent Activities -->
            <div class="col-md-7">
                <div class="glass-card">
                    <h5 class="fw-bold mb-4">Market Timeline</h5>
                    <div class="timeline-feed">
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="d-flex align-items-center p-3 mb-3 border-bottom border-opacity-10 last-child-no-border">
                                    <div class="activity-icon me-3">
                                        <?php if ($activity['type'] === 'favorite'): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-circle"><i class="fas fa-heart"></i></span>
                                        <?php elseif ($activity['type'] === 'inquiry'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle"><i class="fas fa-envelope"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-info bg-opacity-10 text-info p-2 rounded-circle"><i class="fas fa-eye"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-0 fw-medium">
                                            <?php echo htmlspecialchars($activity['property']); ?>
                                        </p>
                                        <small class="text-muted"><?php echo htmlspecialchars($activity['type'] === 'favorite' ? 'Added to favorites' : ($activity['type'] === 'inquiry' ? 'Sent an inquiry' : 'Viewed recently')); ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars($activity['date']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">No recent activity found.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Favorites Sidebar -->
            <div class="col-md-5">
                <div class="glass-card">
                    <h5 class="fw-bold mb-4">Quick Collection</h5>
                    <?php if (!empty($favorite_properties)): ?>
                        <?php foreach ($favorite_properties as $property): ?>
                            <div class="d-flex align-items-center p-3 mb-3 bg-white bg-opacity-50 rounded-4 border">
                                <img src="/assets/images/placeholder-property.jpg" class="property-thumb rounded-3">
                                <div class="flex-grow-1 overflow-hidden">
                                    <h6 class="mb-1 text-truncate fw-bold"><?php echo htmlspecialchars($property['title']); ?></h6>
                                    <p class="text-muted small mb-0 text-truncate"><?php echo htmlspecialchars($property['location']); ?></p>
                                    <p class="text-primary fw-bold small mb-0"><?php echo htmlspecialchars($property['price']); ?></p>
                                </div>
                                <a href="/property/<?php echo $property['id']; ?>" class="btn btn-sm btn-light rounded-circle shadow-sm ms-2"><i class="fas fa-arrow-right"></i></a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-heart text-muted mb-3 opacity-25 fa-2x"></i>
                            <p class="text-muted small">Your collection is empty.</p>
                        </div>
                    <?php endif; ?>
                    <button class="btn btn-outline-primary w-100 mt-3 rounded-pill">View All Favorites</button>
                </div>
            </div>
        </div>

        <!-- Curated Recommendations -->
        <h5 class="fw-bold mb-4">Inspired by your preferences</h5>
        <div class="row g-4">
            <?php if (!empty($recommended_properties)): ?>
                <?php foreach ($recommended_properties as $property): ?>
                    <div class="col-md-3">
                        <div class="glass-card p-0 overflow-hidden">
                            <div class="position-relative">
                                <img src="/assets/images/placeholder-property.jpg" class="w-100" style="height: 180px; object-fit: cover;">
                                <span class="badge bg-white text-dark position-absolute top-0 end-0 m-3 shadow-sm rounded-pill py-2 px-3 fw-bold"><?php echo htmlspecialchars($property['price']); ?></span>
                            </div>
                            <div class="p-4">
                                <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($property['title']); ?></h6>
                                <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($property['location']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <button class="btn btn-sm btn-outline-danger border-0 rounded-circle"><i class="far fa-heart"></i></button>
                                    <a href="/property/<?php echo $property['id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3 fw-medium">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="glass-card text-center py-5">
                        <p class="text-muted mb-0">Browse properties to get personalized recommendations.</p>
                    </div>
                </div>
            <?php endif; ?>
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