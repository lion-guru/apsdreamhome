<?php
$property = $data['property'] ?? null;
$property_images = $data['property_images'] ?? [];
$related = $data['related_properties'] ?? [];
$reviews = $data['reviews'] ?? [];
$images = !empty($property_images) ? $property_images : [['image_path' => 'https://via.placeholder.com/800x400?text=No+Image']];

$amenities = [];
if (!empty($property['amenities'])) {
    $amenities = is_string($property['amenities']) ? (json_decode($property['amenities'], true) ?? [$property['amenities']]) : (array)$property['amenities'];
}

$jsonLd = null;
if ($property) {
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'RealEstateListing',
        'name' => $property['title'] ?? $property['name'] ?? '',
        'description' => $property['description'] ?? '',
        'url' => (defined('BASE_URL') ? BASE_URL : '') . '/property/' . ($property['id'] ?? ''),
        'image' => !empty($property['image']) ? ((defined('BASE_URL') ? BASE_URL : '') . '/assets/images/properties/' . $property['image']) : '',
        'offers' => [
            '@type' => 'Offer',
            'price' => (float)($property['price'] ?? 0),
            'priceCurrency' => 'INR'
        ]
    ];
}
if ($jsonLd) { $seo = is_array($seo ?? null) ? $seo : []; $seo['json_ld'] = $jsonLd; }
?>

<style>
.pd-hero{position:relative;background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 50%,#0d9488 100%);padding:50px 0 40px;overflow:hidden;margin-bottom:-30px}
.pd-hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")}
.pd-hero .hero-content{position:relative;z-index:2}
.pd-hero h1{font-size:2rem;font-weight:800;color:#fff;margin-bottom:8px;letter-spacing:-0.5px}
.pd-hero .pd-breadcrumb{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.pd-hero .pd-breadcrumb a{color:rgba(255,255,255,0.7);text-decoration:none;font-size:0.85rem;transition:color 0.2s}
.pd-hero .pd-breadcrumb a:hover{color:#fff}
.pd-hero .pd-breadcrumb .sep{color:rgba(255,255,255,0.3);font-size:0.7rem}
.pd-hero .pd-breadcrumb .current{color:rgba(255,255,255,0.95);font-size:0.85rem;font-weight:600}

.pd-gallery{position:relative;border-radius:20px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.1)}
.pd-gallery .carousel-item img{height:420px;object-fit:cover}
.pd-gallery .carousel-indicators{bottom:12px}
.pd-gallery .carousel-indicators button{width:10px;height:10px;border-radius:50%;margin:0 4px}
.pd-gallery .gallery-counter{position:absolute;top:16px;right:16px;background:rgba(0,0,0,0.6);backdrop-filter:blur(8px);color:#fff;padding:6px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;z-index:5}
.pd-gallery .gallery-fullscreen{position:absolute;top:16px;left:16px;background:rgba(0,0,0,0.6);backdrop-filter:blur(8px);color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;z-index:5;border:none;transition:background 0.2s}
.pd-gallery .gallery-fullscreen:hover{background:rgba(0,0,0,0.8)}
.pd-gallery .thumb-strip{display:flex;gap:6px;padding:8px;background:#f8fafc;overflow-x:auto}
.pd-gallery .thumb-strip img{height:56px;width:76px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid transparent;transition:all 0.2s;flex-shrink:0}
.pd-gallery .thumb-strip img.active{border-color:#0d9488}
.pd-gallery .thumb-strip img:hover{opacity:0.85}

.pd-info-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden}
.pd-info-card .pd-title-section{padding:24px 28px;border-bottom:1px solid #f1f5f9}
.pd-info-card .pd-title-section h2{font-size:1.5rem;font-weight:800;color:#1e293b;margin-bottom:4px;line-height:1.3}
.pd-info-card .pd-title-section .pd-location{color:#64748b;font-size:0.9rem;display:flex;align-items:center;gap:6px}
.pd-info-card .pd-title-section .pd-location i{color:#0d9488}

.pd-price-bar{display:flex;align-items:center;justify-content:space-between;padding:20px 28px;background:linear-gradient(135deg,#f0fdfa,#ecfdf5);border-bottom:1px solid #f1f5f9}
.pd-price-bar .pd-price{font-size:1.8rem;font-weight:800;color:#0d9488;line-height:1}
.pd-price-bar .pd-price small{font-size:0.75rem;font-weight:500;color:#64748b}
.pd-price-bar .pd-actions{display:flex;gap:8px}

.pd-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:0;padding:0;border-bottom:1px solid #f1f5f9}
.pd-stats .stat{text-align:center;padding:16px 12px;border-right:1px solid #f1f5f9}
.pd-stats .stat:last-child{border-right:none}
.pd-stats .stat i{font-size:1.1rem;color:#0d9488;margin-bottom:4px;display:block}
.pd-stats .stat .val{font-size:1rem;font-weight:700;color:#1e293b;display:block}
.pd-stats .stat .lbl{font-size:0.7rem;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px}

.pd-section{padding:24px 28px}
.pd-section h5{font-size:1rem;font-weight:700;color:#1e293b;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.pd-section h5 i{color:#0d9488;font-size:0.9rem}
.pd-section .pd-desc{color:#475569;font-size:0.9rem;line-height:1.7}

.pd-detail-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.pd-detail-item{display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f8fafc;border-radius:12px;transition:background 0.2s}
.pd-detail-item:hover{background:#f1f5f9}
.pd-detail-item i{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f0fdfa,#ccfbf1);display:flex;align-items:center;justify-content:center;color:#0d9488;font-size:0.85rem;flex-shrink:0}
.pd-detail-item .di-text{font-size:0.82rem;color:#475569;line-height:1.3}
.pd-detail-item .di-text strong{display:block;color:#1e293b;font-size:0.88rem}

.pd-amenities{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.pd-amenity{display:flex;align-items:center;gap:8px;padding:10px 14px;background:#f8fafc;border-radius:10px;font-size:0.82rem;color:#475569;transition:all 0.2s}
.pd-amenity:hover{background:#f0fdfa;color:#0d9488}
.pd-amenity i{color:#10b981;font-size:0.8rem}

.pd-rera{display:flex;align-items:center;gap:12px;padding:16px 20px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:14px;margin:0 28px 24px;border:1px solid #bfdbfe}
.pd-rera i{font-size:1.5rem;color:#3b82f6}
.pd-rera .rera-text{font-size:0.85rem;color:#1e40af}
.pd-rera .rera-text strong{display:block;font-size:0.95rem}

.pd-sidebar-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden}
.pd-sidebar-card .card-head{background:linear-gradient(135deg,#0d9488,#0f766e);padding:18px 22px;color:#fff}
.pd-sidebar-card .card-head h5{margin:0;font-size:1rem;font-weight:700}
.pd-sidebar-card .card-body-padded{padding:20px 22px}

.pd-enquiry-form .form-control{border-radius:10px;border:1.5px solid #e2e8f0;font-size:0.88rem;padding:10px 14px;transition:all 0.2s}
.pd-enquiry-form .form-control:focus{border-color:#0d9488;box-shadow:0 0 0 3px rgba(13,148,136,0.1)}
.pd-enquiry-form .form-label{font-size:0.8rem;font-weight:600;color:#475569}
.pd-enquiry-form .btn-submit{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border:none;border-radius:12px;padding:12px;font-weight:600;font-size:0.9rem;transition:all 0.2s}
.pd-enquiry-form .btn-submit:hover{background:linear-gradient(135deg,#0f766e,#115e59);transform:translateY(-1px)}
.pd-enquiry-form .btn-call{background:linear-gradient(135deg,#10b981,#059669);color:#fff;border:none;border-radius:12px;padding:12px;font-weight:600;font-size:0.9rem;width:100%;transition:all 0.2s}
.pd-enquiry-form .btn-call:hover{background:linear-gradient(135deg,#059669,#047857);transform:translateY(-1px)}
.pd-enquiry-form .btn-buy{background:linear-gradient(135deg,#6366f1,#0d9488);color:#fff;border:none;border-radius:12px;padding:14px;font-weight:600;font-size:0.95rem;width:100%;transition:all 0.2s}
.pd-enquiry-form .btn-buy:hover{background:linear-gradient(135deg,#0d9488,#4338ca);transform:translateY(-1px)}

.pd-related-item{display:flex;gap:12px;padding:14px 0;border-bottom:1px solid #f1f5f9;text-decoration:none;color:inherit;transition:background 0.2s}
.pd-related-item:last-child{border-bottom:none}
.pd-related-item:hover{background:#f8fafc;border-radius:12px;margin:0 -10px;padding:14px 10px}
.pd-related-item img{width:72px;height:56px;object-fit:cover;border-radius:10px;flex-shrink:0}
.pd-related-item .rel-name{font-size:0.85rem;font-weight:600;color:#1e293b;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden}
.pd-related-item .rel-price{font-size:0.88rem;font-weight:700;color:#0d9488}

.pd-reviews-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden}
.pd-review-item{padding:20px 0;border-bottom:1px solid #f1f5f9}
.pd-review-item:last-child{border-bottom:none}
.pd-review-item .review-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.pd-review-item .reviewer{font-weight:600;color:#1e293b}
.pd-review-item .review-date{font-size:0.78rem;color:#94a3b8}
.pd-review-item .review-stars i{color:#f59e0b;font-size:0.85rem}
.pd-review-item .review-text{color:#475569;font-size:0.88rem;line-height:1.6;margin:0}

.pd-lightbox{position:fixed;inset:0;background:rgba(0,0,0,0.95);z-index:99999;display:none;align-items:center;justify-content:center}
.pd-lightbox.open{display:flex}
.pd-lightbox img{max-width:90vw;max-height:85vh;object-fit:contain;border-radius:8px}
.pd-lightbox .lb-close{position:absolute;top:20px;right:30px;color:#fff;font-size:36px;cursor:pointer;z-index:10;background:none;border:none}
.pd-lightbox .lb-nav{position:absolute;top:50%;transform:translateY(-50%);color:#fff;font-size:40px;cursor:pointer;padding:20px;background:none;border:none;z-index:10}
.pd-lightbox .lb-prev{left:20px}
.pd-lightbox .lb-next{right:20px}
.pd-lightbox .lb-counter{position:absolute;bottom:20px;left:50%;transform:translateX(-50%);color:rgba(255,255,255,0.7);font-size:0.85rem}

@media(max-width:768px){
.pd-hero{padding:30px 0 25px}
.pd-hero h1{font-size:1.4rem}
.pd-gallery .carousel-item img{height:250px}
.pd-price-bar{flex-direction:column;gap:12px;align-items:flex-start}
.pd-stats{grid-template-columns:repeat(2,1fr)}
.pd-stats .stat:nth-child(2){border-right:none}
.pd-detail-grid{grid-template-columns:1fr}
.pd-amenities{grid-template-columns:repeat(2,1fr)}
.pd-info-card .pd-title-section,.pd-section{padding:20px}
.pd-rera{margin:0 20px 20px}
}
</style>

<div class="pd-hero">
    <div class="container hero-content">
        <div class="pd-breadcrumb mb-2">
            <a href="/"><i class="fas fa-home"></i> Home</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <a href="/properties">Properties</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current"><?= htmlspecialchars($property['title'] ?? $property['name'] ?? 'Property') ?></span>
        </div>
        <?php if ($property): ?>
            <h1><?= htmlspecialchars($property['title'] ?? $property['name'] ?? 'Property') ?></h1>
            <div class="pd-location" style="color:rgba(255,255,255,0.8);font-size:0.9rem">
                <i class="fas fa-map-marker-alt"></i>
                <?= htmlspecialchars($property['location'] ?? $property['address'] ?? '') ?>
                <?php if (!empty($property['status'])): ?>
                    <span class="badge" style="background:rgba(16,185,129,0.2);color:#10b981;border:1px solid rgba(16,185,129,0.3);margin-left:12px;padding:4px 12px;border-radius:8px;font-size:0.75rem">
                        <?= ucfirst($property['status']) ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <h1>Property Not Found</h1>
        <?php endif; ?>
    </div>
</div>

<div class="container" style="position:relative;z-index:5">
<?php if ($property): ?>
<div class="row g-4">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Gallery -->
        <div class="pd-gallery mb-4 scroll-reveal">
            <div id="propertyCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($images as $i => $img): ?>
                        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                            <?php $src = !empty($img['image_path']) ? $img['image_path'] : BASE_URL . '/assets/images/placeholder/property.svg'; ?>
                            <img src="<?= htmlspecialchars($src) ?>" class="d-block w-100 gallery-trigger" style="height:420px;object-fit:cover;cursor:pointer" alt="Property image <?= $i+1 ?>" onclick="openLightbox(<?= $i ?>)">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#propertyCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" style="background:rgba(0,0,0,0.5);border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#propertyCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" style="background:rgba(0,0,0,0.5);border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center"></span>
                </button>
                <div class="gallery-counter"><?= count($images) ?> photos</div>
                <button class="gallery-fullscreen" onclick="openLightbox(0)" title="View fullscreen"><i class="fas fa-expand"></i></button>
            </div>
            <?php if (count($images) > 1): ?>
            <div class="thumb-strip">
                <?php foreach ($images as $i => $img): ?>
                    <?php $src = !empty($img['image_path']) ? $img['image_path'] : BASE_URL . '/assets/images/placeholder/property.svg'; ?>
                    <img src="<?= htmlspecialchars($src) ?>" class="<?= $i === 0 ? 'active' : '' ?>" onclick="goToSlide(<?= $i ?>)" alt="Thumb <?= $i+1 ?>">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Property Info Card -->
        <div class="pd-info-card mb-4 scroll-reveal">
            <div class="pd-title-section">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h2><?= htmlspecialchars($property['title'] ?? $property['name'] ?? '') ?></h2>
                        <div class="pd-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($property['location'] ?? $property['address'] ?? '') ?>
                        </div>
                    </div>
                    <button class="btn btn-sm" style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px" onclick="toggleWishlist(this)" data-id="<?= $property['id'] ?? 0 ?>">
                        <i class="far fa-heart" style="color:#ef4444"></i>
                    </button>
                </div>
            </div>

            <div class="pd-price-bar">
                <div class="pd-price">
                    ₹<?= number_format((float)($property['price'] ?? 0)) ?>
                    <?php if (!empty($property['price_type'])): ?>
                        <small><?= $property['price_type'] ?></small>
                    <?php endif; ?>
                </div>
                <div class="pd-actions">
                    <button class="btn" style="background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border:none;border-radius:10px;padding:8px 20px;font-weight:600" onclick="document.getElementById('enquiryForm').scrollIntoView({behavior:'smooth'})">
                        <i class="fas fa-envelope me-1"></i> Enquire
                    </button>
                    <a href="tel:+919277121112" class="btn" style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;border-radius:10px;padding:8px 20px;font-weight:600;text-decoration:none">
                        <i class="fas fa-phone me-1"></i> Call
                    </a>
                </div>
            </div>

            <div class="pd-stats">
                <?php if (!empty($property['area_sqft']) || !empty($property['area']) || !empty($property['size'])): ?>
                <div class="stat">
                    <i class="fas fa-vector-square"></i>
                    <span class="val"><?= number_format((float)($property['area_sqft'] ?? str_replace([',',' sqft','sq.ft'], '', $property['area'] ?? $property['size'] ?? 0))) ?></span>
                    <span class="lbl">Sq. Ft.</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($property['bedrooms'])): ?>
                <div class="stat">
                    <i class="fas fa-bed"></i>
                    <span class="val"><?= (int)$property['bedrooms'] ?></span>
                    <span class="lbl">Bedrooms</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($property['bathrooms'])): ?>
                <div class="stat">
                    <i class="fas fa-bath"></i>
                    <span class="val"><?= (int)$property['bathrooms'] ?></span>
                    <span class="lbl">Bathrooms</span>
                </div>
                <?php endif; ?>
                <?php if (!empty($property['property_type'])): ?>
                <div class="stat">
                    <i class="fas fa-home"></i>
                    <span class="val"><?= ucfirst($property['property_type']) ?></span>
                    <span class="lbl">Type</span>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($property['description'])): ?>
            <div class="pd-section">
                <h5><i class="fas fa-align-left"></i> Description</h5>
                <div class="pd-desc"><?= nl2br(htmlspecialchars($property['description'])) ?></div>
            </div>
            <?php endif; ?>

            <div class="pd-section" style="border-top:1px solid #f1f5f9">
                <h5><i class="fas fa-info-circle"></i> Property Details</h5>
                <div class="pd-detail-grid">
                    <?php if (!empty($property['bedrooms'])): ?>
                    <div class="pd-detail-item">
                        <i class="fas fa-bed"></i>
                        <div class="di-text"><strong><?= $property['bedrooms'] ?></strong>Bedrooms</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($property['bathrooms'])): ?>
                    <div class="pd-detail-item">
                        <i class="fas fa-bath"></i>
                        <div class="di-text"><strong><?= $property['bathrooms'] ?></strong>Bathrooms</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($property['parking'])): ?>
                    <div class="pd-detail-item">
                        <i class="fas fa-car"></i>
                        <div class="di-text"><strong><?= $property['parking'] ?></strong>Parking</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($property['furnished'])): ?>
                    <div class="pd-detail-item">
                        <i class="fas fa-couch"></i>
                        <div class="di-text"><strong><?= ucfirst($property['furnished']) ?></strong>Furnished</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($property['facing'])): ?>
                    <div class="pd-detail-item">
                        <i class="fas fa-compass"></i>
                        <div class="di-text"><strong><?= ucfirst($property['facing']) ?></strong>Facing</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($property['floor'])): ?>
                    <div class="pd-detail-item">
                        <i class="fas fa-building"></i>
                        <div class="di-text"><strong>Floor <?= $property['floor'] ?></strong><?= $property['total_floors'] ? 'of '.$property['total_floors'] : '' ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($property['year_built'])): ?>
                    <div class="pd-detail-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div class="di-text"><strong><?= $property['year_built'] ?></strong>Year Built</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($property['property_type'])): ?>
                    <div class="pd-detail-item">
                        <i class="fas fa-home"></i>
                        <div class="di-text"><strong><?= ucfirst($property['property_type']) ?></strong>Property Type</div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($property['listing_type'])): ?>
                    <div class="pd-detail-item">
                        <i class="fas fa-tag"></i>
                        <div class="di-text"><strong><?= ucfirst($property['listing_type']) ?></strong>Listing Type</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($amenities)): ?>
            <div class="pd-section" style="border-top:1px solid #f1f5f9">
                <h5><i class="fas fa-star"></i> Amenities</h5>
                <div class="pd-amenities">
                    <?php foreach ($amenities as $amenity): ?>
                        <div class="pd-amenity"><i class="fas fa-check"></i> <?= htmlspecialchars($amenity) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($property['rera_number'])): ?>
            <div class="pd-rera">
                <i class="fas fa-shield-alt"></i>
                <div class="rera-text">
                    <strong>RERA Registered</strong>
                    <?= htmlspecialchars($property['rera_number']) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Enquiry Card -->
        <div class="pd-sidebar-card mb-4 scroll-reveal" id="enquiryForm">
            <div class="card-head">
                <h5><i class="fas fa-paper-plane me-2"></i> Interested in this property?</h5>
            </div>
            <div class="card-body-padded pd-enquiry-form">
                <form action="<?= BASE_URL ?>/contact" method="POST" id="enquiryFormElement">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="property_id" value="<?= $property['id'] ?? 0 ?>">
                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Full name" required
                               value="<?= htmlspecialchars($_SESSION['user_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="mobile" class="form-control" pattern="[0-9]{10}" placeholder="10-digit number" required
                               value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="your@email.com" required
                               value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="3" placeholder="I'm interested in this property...">I'm interested in this property. Please contact me.</textarea>
                    </div>
                    <button type="submit" class="btn btn-submit w-100 mb-2">
                        <i class="fas fa-paper-plane me-1"></i> Send Enquiry
                    </button>
                </form>
                <div class="d-flex gap-2 mt-2">
                    <a href="tel:+919277121112" class="btn btn-call flex-fill">
                        <i class="fas fa-phone me-1"></i> Call Now
                    </a>
                </div>
                <?php if (!empty($property['price'])): ?>
                <a href="<?= BASE_URL ?>/payment/initiate?property_id=<?= $property['id'] ?? 0 ?>&amount=<?= $property['price'] ?>" class="btn btn-buy w-100 mt-2">
                    <i class="fas fa-credit-card me-1"></i> Buy Now — ₹<?= number_format($property['price']) ?>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Properties -->
        <?php if (!empty($related)): ?>
        <div class="pd-sidebar-card scroll-reveal">
            <div class="card-head" style="background:linear-gradient(135deg,#6366f1,#0d9488)">
                <h5><i class="fas fa-th-large me-2"></i> Similar Properties</h5>
            </div>
            <div class="card-body-padded" style="padding:8px 22px">
                <?php foreach (array_slice($related, 0, 4) as $rel): ?>
                    <a href="/property/<?= $rel['id'] ?>" class="pd-related-item">
                        <?php $relImg = !empty($rel['image']) ? BASE_URL.'/assets/images/properties/'.$rel['image'] : BASE_URL.'/assets/images/placeholder/property.svg'; ?>
                        <img src="<?= $relImg ?>" alt="<?= htmlspecialchars($rel['title'] ?? $rel['name'] ?? '') ?>">
                        <div>
                            <div class="rel-name"><?= htmlspecialchars($rel['title'] ?? $rel['name'] ?? '') ?></div>
                            <div class="rel-price">₹<?= number_format($rel['price'] ?? 0) ?></div>
                            <small style="color:#94a3b8;font-size:0.75rem"><?= htmlspecialchars($rel['location'] ?? $rel['address'] ?? '') ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reviews Section -->
<div class="pd-reviews-card mt-4 mb-5 scroll-reveal">
    <div style="padding:20px 28px;border-bottom:1px solid #f1f5f9;background:linear-gradient(135deg,#fefce8,#fef9c3)">
        <h5 style="margin:0;font-weight:700;color:#1e293b"><i class="fas fa-star me-2" style="color:#f59e0b"></i> Customer Reviews</h5>
    </div>
    <div style="padding:20px 28px">
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="pd-review-item">
                    <div class="review-header">
                        <span class="reviewer"><?= htmlspecialchars($review['user_name'] ?? $review['name'] ?? 'Anonymous') ?></span>
                        <span class="review-date"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                    </div>
                    <div class="review-stars mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa<?= $i <= $review['rating'] ? 's' : 'r' ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="review-text"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="fas fa-comment-dots fa-2x mb-2" style="color:#e2e8f0"></i>
                <p class="text-muted mb-0">No reviews yet. Be the first to review!</p>
            </div>
        <?php endif; ?>

        <hr style="margin:24px 0;border-color:#f1f5f9">
        <h6 class="fw-bold mb-3" style="color:#1e293b">Write a Review</h6>
        <form action="<?= BASE_URL ?>/property/review" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="property_id" value="<?= $property['id'] ?? 0 ?>">
            <div class="row mb-3">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="form-label" style="font-size:0.8rem;font-weight:600;color:#475569">Name *</label>
                    <input type="text" name="name" class="form-control" style="border-radius:10px;border:1.5px solid #e2e8f0" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="font-size:0.8rem;font-weight:600;color:#475569">Email *</label>
                    <input type="email" name="email" class="form-control" style="border-radius:10px;border:1.5px solid #e2e8f0" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;color:#475569">Rating *</label>
                <select name="rating" class="form-select" style="border-radius:10px;border:1.5px solid #e2e8f0" required>
                    <option value="">Select Rating</option>
                    <option value="5">5 — Excellent</option>
                    <option value="4">4 — Good</option>
                    <option value="3">3 — Average</option>
                    <option value="2">2 — Poor</option>
                    <option value="1">1 — Terrible</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:0.8rem;font-weight:600;color:#475569">Your Review *</label>
                <textarea name="review_text" class="form-control" rows="4" style="border-radius:10px;border:1.5px solid #e2e8f0" required placeholder="Share your experience..."></textarea>
            </div>
            <button type="submit" class="btn" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:#fff;border:none;border-radius:10px;padding:10px 24px;font-weight:600">
                <i class="fas fa-paper-plane me-1"></i> Submit Review
            </button>
        </form>
    </div>
</div>

<?php else: ?>
<!-- Property Not Found -->
<div class="text-center py-5 scroll-reveal">
    <div style="width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,#fef2f2,#fecaca);display:flex;align-items:center;justify-content:center;margin:0 auto 24px">
        <i class="fas fa-home fa-3x" style="color:#ef4444"></i>
    </div>
    <h3 style="color:#1e293b;font-weight:700">Property Not Found</h3>
    <p style="color:#64748b;max-width:400px;margin:12px auto 24px">The property you're looking for doesn't exist or has been removed.</p>
    <a href="/properties" class="btn px-4" style="background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;border-radius:12px;font-weight:600">
        <i class="fas fa-search me-1"></i> Browse Properties
    </a>
</div>
<?php endif; ?>
</div>

<!-- Lightbox -->
<div class="pd-lightbox" id="propertyLightbox" onclick="closeLightbox(event)">
    <button class="lb-close" onclick="closeLightbox()">&times;</button>
    <button class="lb-nav lb-prev" onclick="changeLightbox(-1)">&#10094;</button>
    <button class="lb-nav lb-next" onclick="changeLightbox(1)">&#10095;</button>
    <img id="lightboxImage" src="" alt="Full size">
    <div class="lb-counter" id="lightboxCounter"></div>
</div>

<script>
<?php if (!empty($property_images)): ?>
var lightboxImages = <?= json_encode(array_map(function($img) { return $img['image_path']; }, $images)) ?>;
var currentLightbox = 0;
function openLightbox(index) {
    currentLightbox = index;
    document.getElementById('lightboxImage').src = lightboxImages[index];
    document.getElementById('propertyLightbox').classList.add('open');
    document.getElementById('lightboxCounter').textContent = (index+1)+' / '+lightboxImages.length;
    document.body.style.overflow = 'hidden';
}
function closeLightbox(e) { if (e && e.target !== e.currentTarget) return; document.getElementById('propertyLightbox').classList.remove('open'); document.body.style.overflow = ''; }
function changeLightbox(dir) { currentLightbox = (currentLightbox + dir + lightboxImages.length) % lightboxImages.length; openLightbox(currentLightbox); }
function goToSlide(index) {
    var carousel = bootstrap.Carousel.getInstance(document.getElementById('propertyCarousel'));
    if (carousel) carousel.to(index);
    document.querySelectorAll('.thumb-strip img').forEach((t,i) => t.classList.toggle('active', i === index));
}
document.addEventListener('keydown', function(e) {
    if (!document.getElementById('propertyLightbox').classList.contains('open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') changeLightbox(-1);
    if (e.key === 'ArrowRight') changeLightbox(1);
});
document.getElementById('propertyCarousel')?.addEventListener('slid.bs.carousel', function(e) {
    document.querySelectorAll('.thumb-strip img').forEach((t,i) => t.classList.toggle('active', i === e.to));
});
<?php endif; ?>

function toggleWishlist(btn) {
    var icon = btn.querySelector('i');
    var isFilled = icon.classList.contains('fas');
    icon.className = isFilled ? 'far fa-heart' : 'fas fa-heart';
}
</script>
