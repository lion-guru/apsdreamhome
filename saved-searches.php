<?php
/**
 * Saved Searches Page
 * Displays all saved property searches for the logged-in user
 */

// Start session and include configuration
require_once 'includes/db_connection.php';
require_once 'includes/helpers/file_helpers.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit();
}

// Set page title and include header
$page_title = 'My Saved Searches | APS Dream Home';
$page_description = 'View and manage your saved property searches';
$page_keywords = 'saved searches, property search, real estate, APS Dream Home';

// Include header
include 'includes/header.php';
?>

<!-- Custom CSS for Saved Searches -->
<link rel="stylesheet" href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/css/saved-searches.css">

<!-- Page Header -->
<section class="page-header bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-2">My Saved Searches</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Saved Searches</li>
                    </ol>
                </nav>
            </div>
            <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
                <button class="btn btn-outline-primary" onclick="window.history.back()">
                    <i class="fas fa-arrow-left me-2"></i> Back to Previous Page
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-xl-3 mb-4">
                <!-- Sidebar with user menu -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="avatar avatar-lg me-3">
                                <div class="avatar-initial rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></h6>
                                <small class="text-muted">Member since <?= date('M Y', strtotime($_SESSION['created_at'] ?? 'now')) ?></small>
                            </div>
                        </div>
                        
                        <div class="list-group list-group-flush">
                            <a href="my-profile.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user me-2"></i> My Profile
                            </a>
                            <a href="my-properties.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-home me-2"></i> My Properties
                            </a>
                            <a href="saved-properties.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-heart me-2"></i> Saved Properties
                            </a>
                            <a href="saved-searches.php" class="list-group-item list-group-item-action active">
                                <i class="fas fa-search me-2"></i> Saved Searches
                            </a>
                            <a href="my-messages.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-envelope me-2"></i> Messages
                                <span class="badge bg-primary rounded-pill ms-auto">3</span>
                            </a>
                            <a href="my-settings.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                            <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Help Card -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h6 class="mb-3">Need Help?</h6>
                        <p class="small text-muted mb-3">If you need assistance with your saved searches, please contact our support team.</p>
                        <a href="contact.php" class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-headset me-1"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8 col-xl-9">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">My Saved Searches</h5>
                                <div>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="refreshSavedSearches" data-bs-toggle="tooltip" title="Refresh saved searches">
                                            <i class="fas fa-sync-alt me-1"></i> Refresh
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" id="selectAllSearches"><i class="far fa-check-square me-2"></i>Select All</a></li>
                                            <li><a class="dropdown-item" href="#" id="deselectAllSearches"><i class="far fa-square me-2"></i>Deselect All</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger bulk-action-btn" href="#" data-action="delete"><i class="far fa-trash-alt me-2"></i>Delete Selected</a></li>
                                            <li><a class="dropdown-item text-primary bulk-action-btn" href="#" data-action="export"><i class="far fa-file-export me-2"></i>Export Selected</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Search and Filter Bar -->
                            <div class="search-filter-bar">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                            <input type="text" class="form-control border-start-0" id="searchFilter" placeholder="Search saved searches...">
                                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" style="display: none;">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" id="filterByType">
                                            <option value="">All Property Types</option>
                                            <option value="house">Houses</option>
                                            <option value="apartment">Apartments</option>
                                            <option value="condo">Condos</option>
                                            <option value="townhouse">Townhouses</option>
                                            <option value="land">Land</option>
                                            <option value="commercial">Commercial</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fas fa-sort"></i></span>
                                            <select class="form-select" id="sortBy">
                                                <option value="newest">Newest First</option>
                                                <option value="oldest">Oldest First</option>
                                                <option value="name_asc">Name (A-Z)</option>
                                                <option value="name_desc">Name (Z-A)</option>
                                                <option value="price_asc">Price: Low to High</option>
                                                <option value="price_desc">Price: High to Low</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Advanced Filters Toggle -->
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="false" aria-controls="advancedFilters">
                                        <i class="fas fa-sliders-h me-1"></i> Advanced Filters
                                        <i class="fas fa-chevron-down ms-1" style="font-size: 0.7rem;"></i>
                                    </button>
                                </div>
                                
                                <!-- Advanced Filters Collapse -->
                                <div class="collapse mt-3" id="advancedFilters">
                                    <div class="card card-body bg-light">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Price Range</label>
                                                <div id="priceRange" class="mb-3"></div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted small">$0</span>
                                                    <span class="text-muted small">$5M+</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="bedrooms" class="form-label">Bedrooms</label>
                                                <select class="form-select" id="bedrooms">
                                                    <option value="">Any</option>
                                                    <option value="1">1+</option>
                                                    <option value="2">2+</option>
                                                    <option value="3">3+</option>
                                                    <option value="4">4+</option>
                                                    <option value="5">5+</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="bathrooms" class="form-label">Bathrooms</label>
                                                <select class="form-select" id="bathrooms">
                                                    <option value="">Any</option>
                                                    <option value="1">1+</option>
                                                    <option value="2">2+</option>
                                                    <option value="3">3+</option>
                                                    <option value="4">4+</option>
                                                    <option value="5">5+</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Amenities</label>
                                                <div class="row g-2">
                                                    <?php 
                                                    $amenities = [
                                                        'pool', 'garage', 'garden', 'fireplace', 'air_conditioning', 
                                                        'furnished', 'balcony', 'gym', 'elevator', 'parking'
                                                    ];
                                                    foreach ($amenities as $amenity): 
                                                        $label = ucwords(str_replace('_', ' ', $amenity));
                                                    ?>
                                                        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       id="amenity_<?php echo $amenity; ?>" 
                                                                       name="amenities[]" 
                                                                       value="<?php echo $amenity; ?>">
                                                                <label class="form-check-label small" for="amenity_<?php echo $amenity; ?>">
                                                                    <?php echo $label; ?>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Active Filters -->
                            <div class="active-filters d-flex flex-wrap gap-2 align-items-center p-3 mb-3 bg-light rounded" id="activeFilters" style="display: none !important;">
                                <small class="text-muted me-2">Active filters:</small>
                                <!-- Active filters will be dynamically inserted here -->
                            </div>
                        </div>
                        
                        <!-- Bulk Actions Bar (Hidden by default) -->
                        <div class="bulk-actions alert alert-light border d-none align-items-center py-2 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="form-check me-3">
                                    <input class="form-check-input select-all-searches" type="checkbox" id="selectAllSearchesCheckbox">
                                    <label class="form-check-label fw-bold" for="selectAllSearchesCheckbox">
                                        <span id="selectedCount">0</span> selected
                                    </label>
                                </div>
                                <div class="btn-group btn-group-sm me-2">
                                    <button type="button" class="btn btn-outline-danger bulk-action-btn" data-action="delete">
                                        <i class="far fa-trash-alt me-1"></i> Delete
                                    </button>
                                    <button type="button" class="btn btn-outline-primary bulk-action-btn" data-action="export">
                                        <i class="far fa-file-export me-1"></i> Export
                                    </button>
                                </div>
                                <button type="button" class="btn-close" aria-label="Clear selection"></button>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-4">Manage your saved property searches. Click on a search to load it or delete searches you no longer need.</p>
                        
                        <!-- Saved Searches List -->
                        <div id="savedSearchesList">
                            <!-- Content will be loaded via JavaScript -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 mb-0 text-muted">Loading your saved searches...</p>
                            </div>
                        </div>
                        
                        <!-- Empty State (initially hidden) -->
                        <div id="emptySavedSearches" class="text-center py-5 d-none">
                            <div class="mb-4">
                                <i class="far fa-bookmark fa-4x text-muted opacity-25"></i>
                            </div>
                            <h5>No Saved Searches</h5>
                            <p class="text-muted mb-4">You haven't saved any searches yet. Save your property searches to access them later.</p>
                            <a href="properties.php" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i> Find Properties
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Save Search Modal -->
<div class="modal fade" id="saveSearchModal" tabindex="-1" aria-labelledby="saveSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveSearchModalLabel">Save This Search</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="saveSearchForm">
                    <div class="mb-3">
                        <label for="searchName" class="form-label">Search Name</label>
                        <input type="text" class="form-control" id="searchName" required 
                               placeholder="E.g., Luxury homes in downtown" autofocus>
                        <div class="form-text">Give your search a descriptive name to easily find it later.</div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i> Your search criteria including filters will be saved.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmSaveSearch">
                    <i class="far fa-save me-1"></i> Save Search
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100"></div>

<!-- Include Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Include JavaScript -->
<script src="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/js/saved-searches-filters.js"></script>
<script src="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/assets/js/saved-searches.js"></script>

<!-- Initialize price range slider -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.5.0/nouislider.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.5.0/nouislider.min.css">
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize price range slider
    const priceRange = document.getElementById('priceRange');
    if (priceRange) {
        noUiSlider.create(priceRange, {
            start: [0, 5000000],
            connect: true,
            range: {
                'min': 0,
                'max': 5000000
            },
            step: 10000,
            format: {
                to: function (value) {
                    return parseInt(value);
                },
                from: function (value) {
                    return parseInt(value);
                }
            }
        });

        // Update price range filter when slider changes
        priceRange.noUiSlider.on('update', function(values, handle) {
            const minPrice = parseInt(values[0]);
            const maxPrice = parseInt(values[1]);
            
            activeFilters.priceRange = {
                min: minPrice > 0 ? minPrice : null,
                max: maxPrice < 5000000 ? maxPrice : null
            };
            
            applyFilters();
            updateActiveFiltersUI();
        });
    }
    
    // Handle bedrooms filter
    const bedroomsSelect = document.getElementById('bedrooms');
    if (bedroomsSelect) {
        bedroomsSelect.addEventListener('change', function() {
            activeFilters.bedrooms = this.value || null;
            applyFilters();
            updateActiveFiltersUI();
        });
    }
    
    // Handle bathrooms filter
    const bathroomsSelect = document.getElementById('bathrooms');
    if (bathroomsSelect) {
        bathroomsSelect.addEventListener('change', function() {
            activeFilters.bathrooms = this.value || null;
            applyFilters();
            updateActiveFiltersUI();
        });
    }
    
    // Handle amenities filter
    const amenityCheckboxes = document.querySelectorAll('input[name="amenities[]"]');
    amenityCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedAmenities = Array.from(document.querySelectorAll('input[name="amenities[]"]:checked'))
                .map(checkbox => checkbox.value);
                
            activeFilters.amenities = checkedAmenities;
            applyFilters();
            updateActiveFiltersUI();
        });
    });
});
</script>
<script>
// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Load saved searches when page loads
    if (typeof loadSavedSearches === 'function') {
        loadSavedSearches();
    }
    
    // Add event listener for refresh button
    const refreshBtn = document.getElementById('refreshSavedSearches');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            if (typeof loadSavedSearches === 'function') {
                loadSavedSearches();
            }
        });
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
