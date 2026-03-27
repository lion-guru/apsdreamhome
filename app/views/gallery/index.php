<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Gallery' ?> | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
        }
        body { font-family: 'Segoe UI', sans-serif; }
        .gallery-hero {
            background: linear-gradient(rgba(44,62,80,0.85), rgba(44,62,80,0.85)), 
                        url('https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1920');
            background-size: cover;
            padding: 120px 0;
            color: white;
            text-align: center;
        }
        .gallery-card {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            cursor: pointer;
        }
        .gallery-card img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.4s;
        }
        .gallery-card:hover img { transform: scale(1.1); }
        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.85));
            padding: 30px 20px 20px;
            color: white;
            transform: translateY(100%);
            transition: transform 0.3s;
        }
        .gallery-card:hover .gallery-overlay { transform: translateY(0); }
        .filter-btn {
            margin: 5px;
            border-radius: 25px;
            padding: 8px 20px;
        }
        .video-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.9);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e74c3c;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--primary);">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>">
                <i class="fas fa-home me-2"></i>APS Dream Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/properties">Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/about">About</a></li>
                    <li class="nav-item"><a class="nav-link active" href="<?= BASE_URL ?>/gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero -->
    <section class="gallery-hero">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Project Gallery</h1>
            <p class="lead">Explore our completed and ongoing real estate projects</p>
        </div>
    </section>

    <!-- Gallery -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <button class="btn btn-sm filter-btn <?= ($current_category ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>" 
                        onclick="location.href='<?= BASE_URL ?>/gallery'">All</button>
                <button class="btn btn-sm filter-btn <?= ($current_category ?? '') === 'completed' ? 'btn-primary' : 'btn-outline-primary' ?>" 
                        onclick="location.href='<?= BASE_URL ?>/gallery?category=completed'">Completed</button>
                <button class="btn btn-sm filter-btn <?= ($current_category ?? '') === 'ongoing' ? 'btn-primary' : 'btn-outline-primary' ?>" 
                        onclick="location.href='<?= BASE_URL ?>/gallery?category=ongoing'">Ongoing</button>
            </div>

            <div class="row">
                <?php if (empty($gallery_items)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-images fa-5x text-muted mb-4"></i>
                        <h4 class="text-muted">No gallery items found</h4>
                    </div>
                <?php else: ?>
                    <?php foreach ($gallery_items as $item): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="gallery-card">
                                <?php if (($item['type'] ?? 'image') === 'video'): ?>
                                    <img src="<?= $item['thumbnail'] ?? $item['url'] ?>" alt="<?= htmlspecialchars($item['title'] ?? '') ?>">
                                    <div class="video-icon"><i class="fas fa-play"></i></div>
                                <?php else: ?>
                                    <img src="<?= $item['thumbnail'] ?? $item['url'] ?>" alt="<?= htmlspecialchars($item['title'] ?? '') ?>">
                                <?php endif; ?>
                                <div class="gallery-overlay">
                                    <h5 class="mb-1"><?= htmlspecialchars($item['title'] ?? 'Gallery Item') ?></h5>
                                    <p class="mb-1 small"><?= htmlspecialchars($item['description'] ?? '') ?></p>
                                    <span class="badge bg-secondary"><?= ucfirst($item['category'] ?? 'general') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: var(--primary); color: white; padding: 40px 0;">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 APS Dream Home. All rights reserved.</p>
            <p class="small mt-2">
                <a href="<?= BASE_URL ?>/privacy" class="text-white-50">Privacy</a> | 
                <a href="<?= BASE_URL ?>/terms" class="text-white-50">Terms</a>
            </p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
