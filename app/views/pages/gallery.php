<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $data['title']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .gallery-filters {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 40px;
        }
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .gallery-item:hover {
            transform: translateY(-5px);
        }
        .gallery-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .gallery-item:hover .gallery-image {
            transform: scale(1.1);
        }
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .gallery-item:hover .gallery-overlay {
            opacity: 1;
        }
        .filter-btn {
            border: none;
            background: transparent;
            padding: 8px 20px;
            margin: 5px;
            border-radius: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .filter-btn.active {
            background: #667eea;
            color: white;
        }
        .filter-btn:hover {
            background: #764ba2;
            color: white;
        }
        .stats-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 60px 0;
        }
    </style>
</head>
<body>
    <?php include '../app/views/includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">Project Gallery</h1>
                    <p class="lead mb-4">
                        Explore our completed projects and ongoing developments.
                        See the quality and craftsmanship that goes into every APS Dream Home.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Filters -->
    <section class="py-4">
        <div class="container">
            <div class="gallery-filters">
                <div class="text-center mb-4">
                    <h4>Filter by Category</h4>
                </div>
                <div class="text-center">
                    <button class="filter-btn active" data-filter="all">All Projects</button>
                    <button class="filter-btn" data-filter="residential">Residential</button>
                    <button class="filter-btn" data-filter="commercial">Commercial</button>
                    <button class="filter-btn" data-filter="completed">Completed</button>
                    <button class="filter-btn" data-filter="ongoing">Ongoing</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Grid -->
    <section class="py-4">
        <div class="container">
            <div class="row gallery-container">
                <!-- Residential Projects -->
                <div class="col-lg-4 col-md-6 gallery-item" data-category="residential completed">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="APS Green Valley" class="gallery-image">
                        <div class="gallery-overlay">
                            <h5>APS Green Valley</h5>
                            <p>Premium residential apartments in Gorakhpur</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Completed</span>
                                <a href="#" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal">
                                    <i class="fas fa-expand me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 gallery-item" data-category="residential completed">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="APS Royal Residency" class="gallery-image">
                        <div class="gallery-overlay">
                            <h5>APS Royal Residency</h5>
                            <p>Luxury villas with modern amenities</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Completed</span>
                                <a href="#" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal">
                                    <i class="fas fa-expand me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 gallery-item" data-category="residential ongoing">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="APS Lakeview" class="gallery-image">
                        <div class="gallery-overlay">
                            <h5>APS Lakeview</h5>
                            <p>Waterfront residential project</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">Ongoing</span>
                                <a href="#" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal">
                                    <i class="fas fa-expand me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Commercial Projects -->
                <div class="col-lg-4 col-md-6 gallery-item" data-category="commercial completed">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="APS Business Center" class="gallery-image">
                        <div class="gallery-overlay">
                            <h5>APS Business Center</h5>
                            <p>Modern commercial complex</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Completed</span>
                                <a href="#" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal">
                                    <i class="fas fa-expand me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 gallery-item" data-category="commercial ongoing">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1545558014-8692077e9b5c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="APS Tech Park" class="gallery-image">
                        <div class="gallery-overlay">
                            <h5>APS Tech Park</h5>
                            <p>IT park development project</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-warning">Ongoing</span>
                                <a href="#" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal">
                                    <i class="fas fa-expand me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 gallery-item" data-category="commercial completed">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                             alt="APS Shopping Mall" class="gallery-image">
                        <div class="gallery-overlay">
                            <h5>APS Shopping Mall</h5>
                            <p>Retail and entertainment complex</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-success">Completed</span>
                                <a href="#" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#imageModal">
                                    <i class="fas fa-expand me-1"></i>View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">500+</h2>
                    <h5>Properties Delivered</h5>
                    <p class="text-white-50">Homes built with quality and care</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">15+</h2>
                    <h5>Projects Completed</h5>
                    <p class="text-white-50">Successful developments across UP</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">8+</h2>
                    <h5>Years Experience</h5>
                    <p class="text-white-50">Building trust since 2016</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h2 class="display-4 fw-bold text-white mb-2">1000+</h2>
                    <h5>Happy Families</h5>
                    <p class="text-white-50">Dreams fulfilled</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Project Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="" class="img-fluid mb-3" style="max-height: 400px;">
                    <h5 id="modalTitle">Project Name</h5>
                    <p id="modalDescription" class="text-muted">Project description will appear here.</p>
                </div>
            </div>
        </div>
    </div>

    <?php include '../app/views/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gallery filtering
        document.addEventListener('DOMContentLoaded', function() {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const galleryItems = document.querySelectorAll('.gallery-item');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');

                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    // Filter items
                    galleryItems.forEach(item => {
                        const categories = item.getAttribute('data-category');
                        if (filter === 'all' || categories.includes(filter)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });
        });

        // Modal functionality
        const imageModal = document.getElementById('imageModal');
        imageModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const imgSrc = button.closest('.gallery-item').querySelector('.gallery-image').src;
            const title = button.closest('.gallery-item').querySelector('h5').textContent;
            const description = button.closest('.gallery-item').querySelector('p').textContent;

            document.getElementById('modalImage').src = imgSrc;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalDescription').textContent = description;
        });
    </script>
</body>
</html>
