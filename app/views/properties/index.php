<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}
include __DIR__ . '/../layouts/header.php';

$locationValue = htmlspecialchars($_GET['location'] ?? '');
$typeValue = $_GET['type'] ?? '';
$minPrice = htmlspecialchars($_GET['min_price'] ?? '');
$maxPrice = htmlspecialchars($_GET['max_price'] ?? '');
?>

<div class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <span class="badge bg-primary-subtle text-primary-emphasis px-3 py-2 rounded-pill mb-2">Property marketplace</span>
            <h1 class="h3 fw-semibold mb-1">Explore Properties</h1>
            <p class="text-secondary mb-0">Browse verified listings across premium locations with transparent pricing.</p>
        </div>
        <div class="d-flex gap-2">
            <!-- View Toggle Buttons -->
            <div class="btn-group" role="group" aria-label="View toggle">
                <button type="button" class="btn btn-outline-secondary active" id="listViewBtn" data-view="list">
                    <i class="fas fa-list me-2"></i>List View
                </button>
                <button type="button" class="btn btn-outline-secondary" id="mapViewBtn" data-view="map">
                    <i class="fas fa-map-marker-alt me-2"></i>Map View
                </button>
            </div>
            <a href="/properties?sort=latest" class="btn btn-primary">
                <i class="fas fa-sort-amount-down me-2"></i>Sort by Latest
            </a>
        </div>
    </div>

    <div class="row">
        <aside class="col-lg-3 d-none d-lg-block">
            <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" style="top: 80px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="/properties" class="vstack gap-3">
                        <div>
                            <label for="location" class="form-label fw-semibold text-secondary">Location</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="City or area" value="<?php echo $locationValue; ?>">
                        </div>
                        <div>
                            <label for="type" class="form-label fw-semibold text-secondary">Property type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="" <?php echo $typeValue === '' ? 'selected' : ''; ?>>All types</option>
                                <option value="residential" <?php echo $typeValue === 'residential' ? 'selected' : ''; ?>>Residential</option>
                                <option value="commercial" <?php echo $typeValue === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                <option value="plot" <?php echo $typeValue === 'plot' ? 'selected' : ''; ?>>Plot / Land</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-secondary">Price range (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">Min</span>
                                <input type="number" class="form-control" name="min_price" placeholder="25,00,000" value="<?php echo $minPrice; ?>">
                            </div>
                            <div class="input-group mt-2">
                                <span class="input-group-text">Max</span>
                                <input type="number" class="form-control" name="max_price" placeholder="1,50,00,000" value="<?php echo $maxPrice; ?>">
                            </div>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-secondary">Amenities</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="amenity-parking" disabled>
                                <label class="form-check-label" for="amenity-parking">Reserved parking</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="amenity-clubhouse" disabled>
                                <label class="form-check-label" for="amenity-clubhouse">Clubhouse access</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="amenity-security" disabled>
                                <label class="form-check-label" for="amenity-security">24/7 security</label>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Apply filters</button>
                            <a href="/properties" class="btn btn-outline-secondary">Clear all</a>
                        </div>
                    </form>
                </div>
            </div>
        </aside>

        <main class="col-lg-9">
            <!-- Mobile filters -->
            <div class="offcanvas offcanvas-start" tabindex="-1" id="filtersOffcanvas" aria-labelledby="filtersOffcanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="filtersOffcanvasLabel">Filters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <form method="GET" action="/properties" class="vstack gap-3">
                        <div>
                            <label for="location-mobile" class="form-label fw-semibold text-secondary">Location</label>
                            <input type="text" class="form-control" id="location-mobile" name="location" value="<?php echo $locationValue; ?>">
                        </div>
                        <div>
                            <label for="type-mobile" class="form-label fw-semibold text-secondary">Property type</label>
                            <select class="form-select" id="type-mobile" name="type">
                                <option value="" <?php echo $typeValue === '' ? 'selected' : ''; ?>>All types</option>
                                <option value="residential" <?php echo $typeValue === 'residential' ? 'selected' : ''; ?>>Residential</option>
                                <option value="commercial" <?php echo $typeValue === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                <option value="plot" <?php echo $typeValue === 'plot' ? 'selected' : ''; ?>>Plot / Land</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-semibold text-secondary">Price range (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">Min</span>
                                <input type="number" class="form-control" name="min_price" value="<?php echo $minPrice; ?>">
                            </div>
                            <div class="input-group mt-2">
                                <span class="input-group-text">Max</span>
                                <input type="number" class="form-control" name="max_price" value="<?php echo $maxPrice; ?>">
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Apply filters</button>
                            <a href="/properties" class="btn btn-outline-secondary">Clear all</a>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (!empty($properties)): ?>
                <!-- List View -->
                <div id="listView" class="view-container">
                    <div class="row g-4">
                        <?php foreach ($properties as $property): ?>
                            <div class="col-12 col-md-6 col-xl-4">
                                <article class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                                    <div class="position-relative" style="height: 220px;">
                                        <?php if (!empty($property['image'])): ?>
                                            <img src="/uploads/properties/<?php echo htmlspecialchars($property['image']); ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                        <?php else: ?>
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light text-secondary">
                                                <i class="fas fa-home fa-2x"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span class="badge bg-primary-subtle text-primary-emphasis position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill">
                                            <?php echo ucfirst(htmlspecialchars($property['type'])); ?>
                                        </span>
                                    </div>
                                    <div class="card-body p-4">
                                        <h3 class="h5 fw-semibold mb-2"><?php echo htmlspecialchars($property['title']); ?></h3>
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-map-marker-alt text-primary me-2"></i><?php echo htmlspecialchars($property['location']); ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="fw-bold text-primary fs-5"><?php echo Helpers::formatCurrency($property['price']); ?></span>
                                            <?php if (!empty($property['status'])): ?>
                                                <span class="badge bg-success-subtle text-success-emphasis"><?php echo htmlspecialchars($property['status']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex gap-3 text-secondary small">
                                            <?php if (!empty($property['bedrooms'])): ?>
                                                <span><i class="fas fa-bed me-1"></i><?php echo htmlspecialchars($property['bedrooms']); ?> Beds</span>
                                            <?php endif; ?>
                                            <?php if (!empty($property['bathrooms'])): ?>
                                                <span><i class="fas fa-bath me-1"></i><?php echo htmlspecialchars($property['bathrooms']); ?> Baths</span>
                                            <?php endif; ?>
                                            <?php if (!empty($property['area'])): ?>
                                                <span><i class="fas fa-vector-square me-1"></i><?php echo htmlspecialchars($property['area']); ?> sq.ft</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 px-4 pb-4">
                                        <div class="d-grid gap-2">
                                            <a href="/properties/<?php echo $property['id']; ?>" class="btn btn-primary">View Details</a>
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                                <a href="/properties/<?php echo $property['id']; ?>/contact" class="btn btn-outline-primary">Contact Agent</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Map View -->
                <div id="mapView" class="view-container d-none">
                    <div id="propertyMap" style="height: 600px; width: 100%; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);"></div>
                </div>
            <?php else: ?>
                <div class="alert alert-info border-0 rounded-4" role="alert">
                    <i class="fas fa-info-circle me-2"></i>No properties found matching your criteria.
                    <a href="/properties" class="ms-2 text-decoration-none">Reset filters</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($properties) && isset($pagination)): ?>
                <nav class="mt-4" aria-label="Property pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $pagination['base_url'] . '&page=' . ($pagination['current_page'] - 1); ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                            <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $pagination['base_url'] . '&page=' . $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $pagination['base_url'] . '&page=' . ($pagination['current_page'] + 1); ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>

<script>
// Property data for map markers
const properties = <?php echo json_encode($properties ?? []); ?>;

// View toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const listViewBtn = document.getElementById('listViewBtn');
    const mapViewBtn = document.getElementById('mapViewBtn');
    const listView = document.getElementById('listView');
    const mapView = document.getElementById('mapView');

    function toggleView(view) {
        if (view === 'list') {
            listView.classList.remove('d-none');
            mapView.classList.add('d-none');
            listViewBtn.classList.add('active');
            mapViewBtn.classList.remove('active');
        } else {
            listView.classList.add('d-none');
            mapView.classList.remove('d-none');
            listViewBtn.classList.remove('active');
            mapViewBtn.classList.add('active');
            // Initialize map when switching to map view
            if (typeof google !== 'undefined') {
                initMap();
            }
        }
    }

    listViewBtn.addEventListener('click', () => toggleView('list'));
    mapViewBtn.addEventListener('click', () => toggleView('map'));

    // Initialize map if Google Maps is loaded
    if (typeof google !== 'undefined') {
        initMap();
    }
});

// Initialize Google Maps
function initMap() {
    // Default center (can be based on user's location or first property)
    let centerLat = 28.6139; // Delhi coordinates as default
    let centerLng = 77.2090;

    // If we have properties with coordinates, center on the first one
    if (properties.length > 0 && properties[0].latitude && properties[0].longitude) {
        centerLat = parseFloat(properties[0].latitude);
        centerLng = parseFloat(properties[0].longitude);
    }

    const mapOptions = {
        center: { lat: centerLat, lng: centerLng },
        zoom: 12,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true,
        zoomControl: true,
        styles: [
            {
                featureType: 'poi',
                stylers: [{ visibility: 'off' }]
            }
        ]
    };

    const map = new google.maps.Map(document.getElementById('propertyMap'), mapOptions);

    // Add property markers
    properties.forEach(property => {
        if (property.latitude && property.longitude) {
            const marker = new google.maps.Marker({
                position: {
                    lat: parseFloat(property.latitude),
                    lng: parseFloat(property.longitude)
                },
                map: map,
                title: property.title,
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="20" cy="20" r="18" fill="#007bff" stroke="white" stroke-width="2"/>
                            <path d="M20 10 L26 20 L20 16 L14 20 Z" fill="white"/>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(40, 40)
                }
            });

            // Info window content
            const infoWindowContent = `
                <div style="max-width: 250px;">
                    <h6 style="margin: 0 0 8px 0; color: #333;">${property.title}</h6>
                    <p style="margin: 0 0 8px 0; color: #666; font-size: 14px;">
                        <i class="fas fa-map-marker-alt" style="color: #dc3545;"></i> ${property.location}
                    </p>
                    <p style="margin: 0 0 8px 0; font-weight: bold; color: #007bff; font-size: 16px;">
                        ₹${new Intl.NumberFormat('en-IN').format(property.price)}
                    </p>
                    <div style="display: flex; gap: 12px; font-size: 12px; color: #666;">
                        ${property.bedrooms ? `<span><i class="fas fa-bed"></i> ${property.bedrooms} Beds</span>` : ''}
                        ${property.bathrooms ? `<span><i class="fas fa-bath"></i> ${property.bathrooms} Baths</span>` : ''}
                        ${property.area ? `<span><i class="fas fa-vector-square"></i> ${property.area} sq.ft</span>` : ''}
                    </div>
                    <a href="/properties/${property.id}" style="display: inline-block; margin-top: 8px; padding: 6px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 14px;">View Details</a>
                </div>
            `;

            const infoWindow = new google.maps.InfoWindow({
                content: infoWindowContent
            });

            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });
        }
    });

    // Add geolocation button
    const locationButton = document.createElement('button');
    locationButton.textContent = '📍 Find My Location';
    locationButton.classList.add('custom-map-control-button');
    locationButton.style.cssText = `
        background-color: #fff;
        border: 2px solid #fff;
        border-radius: 3px;
        box-shadow: 0 2px 6px rgba(0,0,0,.3);
        cursor: pointer;
        font-family: Roboto,Arial,sans-serif;
        font-size: 16px;
        line-height: 38px;
        margin: 4px 10px;
        padding: 0 5px;
        text-align: center;
    `;

    map.controls[google.maps.ControlPosition.TOP_CENTER].push(locationButton);

    locationButton.addEventListener('click', () => {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const pos = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                    };

                    map.setCenter(pos);
                    map.setZoom(15);

                    // Add user location marker
                    new google.maps.Marker({
                        position: pos,
                        map: map,
                        title: 'Your Location',
                        icon: {
                            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                                <svg width="30" height="30" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="15" cy="15" r="12" fill="#28a745" stroke="white" stroke-width="2"/>
                                    <circle cx="15" cy="15" r="6" fill="white"/>
                                </svg>
                            `),
                            scaledSize: new google.maps.Size(30, 30)
                        }
                    });
                },
                () => {
                    alert('Error: The Geolocation service failed.');
                }
            );
        } else {
            alert('Error: Your browser doesn\'t support geolocation.');
        }
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>