<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<style>
:root { --primary: #6a1b9a; --secondary: #ff6f00; --accent: #00c853; }
.interior-hero {
    background: linear-gradient(135deg, #4a148c 0%, #6a1b9a 50%, #8e24aa 100%);
    color: #fff; padding: 100px 0 80px; position: relative; overflow: hidden;
}
.interior-hero::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><circle fill="rgba(255,255,255,0.03)" cx="50" cy="50" r="40"/><circle fill="rgba(255,255,255,0.03)" cx="150" cy="150" r="60"/><circle fill="rgba(255,255,255,0.02)" cx="180" cy="30" r="30"/></svg>') repeat;
}
.service-card { border: none; border-radius: 16px; transition: all 0.3s ease; background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.08); height: 100%; padding: 2rem; }
.service-card:hover { transform: translateY(-5px); box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
.service-card .icon-wrap { width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 1rem; }
.portfolio-item { position: relative; border-radius: 12px; overflow: hidden; height: 250px; }
.portfolio-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
.portfolio-item:hover img { transform: scale(1.1); }
.portfolio-item .overlay { position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.8)); padding: 1.5rem; color: #fff; }
.team-card { text-align: center; padding: 2rem; border-radius: 16px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
.team-card img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 1rem; }
.testimonial-card { background: #fff; border-radius: 16px; padding: 2rem; box-shadow: 0 2px 15px rgba(0,0,0,0.06); height: 100%; }
.faq-accordion .accordion-button:not(.collapsed) { background: #f3e5f5; color: #4a148c; }
.lead-form-section { background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); }
.flash-message { position: fixed; top: 20px; right: 20px; z-index: 9999; animation: slideIn 0.3s ease; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.tool-card { text-align: center; padding: 1.5rem; border: 2px dashed #e0e0e0; border-radius: 12px; transition: all 0.3s; cursor: pointer; }
.tool-card:hover { border-color: #6a1b9a; background: #f3e5f5; }
</style>

<section class="interior-hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2"><i class="fas fa-palette me-1"></i> Premium Interior Design</span>
                <h1 class="display-4 fw-bold mb-4">Transform Your Space<br><span class="text-warning">Into a Masterpiece</span></h1>
                <p class="lead mb-4 fs-5 opacity-90">Professional interior design services in Gorakhpur, Lucknow, Varanasi & Kushinagar. We turn your dream home into reality with innovative designs and quality execution.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="#contact-form" class="btn btn-warning btn-lg px-4"><i class="fas fa-pen-fancy me-2"></i>Free Consultation</a>
                    <a href="#services" class="btn btn-outline-light btn-lg px-4"><i class="fas fa-list me-2"></i>Our Services</a>
                    <a href="#tools" class="btn btn-outline-light btn-lg px-4"><i class="fas fa-calculator me-2"></i>Free Tools</a>
                </div>
                <div class="row mt-5 g-3">
                    <div class="col-4"><h3 class="text-warning mb-0">200+</h3><small>Projects Done</small></div>
                    <div class="col-4"><h3 class="text-warning mb-0">10+</h3><small>Designers</small></div>
                    <div class="col-4"><h3 class="text-warning mb-0">98%</h3><small>Satisfaction</small></div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="position-relative">
                    <img loading="lazy" src="https://img.freepik.com/free-photo/living-room-interior-design_23-2148892625.jpg" alt="Interior Design" class="img-fluid rounded-4 shadow-lg">
                    <div class="position-absolute bottom-0 end-0 bg-white text-dark p-3 rounded-3 m-3 shadow">
                        <i class="fas fa-star text-warning me-1"></i> Award-Winning Designs
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<nav class="bg-white border-bottom shadow-sm" aria-label="breadcrumb">
    <div class="container py-2">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/services">Services</a></li>
            <li class="breadcrumb-item active">Interior Design</li>
        </ol>
    </div>
</nav>

<section id="services" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2">WHAT WE OFFER</span>
            <h2 class="display-5 fw-bold">Our Interior Design Services</h2>
            <p class="lead text-muted">Comprehensive interior design solutions for every space and budget</p>
        </div>
        <div class="row g-4">
            <?php if (!empty($services) && is_array($services)): ?>
                <?php foreach ($services as $svc): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-card">
                            <div class="icon-wrap bg-primary bg-opacity-10 text-primary">
                                <i class="<?= htmlspecialchars($svc['icon'] ?? 'fas fa-palette') ?>"></i>
                            </div>
                            <h4 class="fw-bold mb-3"><?= htmlspecialchars($svc['title'] ?? 'Design Service') ?></h4>
                            <p class="text-muted mb-4"><?= htmlspecialchars($svc['description'] ?? '') ?></p>
                            <?php if (!empty($svc['features'])): ?>
                                <?php $features = is_string($svc['features']) ? json_decode($svc['features'], true) : $svc['features']; ?>
                                <?php if (is_array($features)): ?>
                                    <ul class="list-unstyled">
                                        <?php foreach ($features as $f): ?>
                                            <li class="mb-1"><i class="fas fa-check-circle text-success me-2"></i><?= htmlspecialchars(is_string($f) ? $f : ($f['name'] ?? $f)) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php endif; ?>
                            <a href="#contact-form" class="btn btn-outline-primary mt-2">Enquire Now <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="fas fa-palette fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">Contact us for interior design services</h4>
                    <p class="text-muted mb-4">We offer residential, commercial, and modular interior design solutions</p>
                    <a href="#contact-form" class="btn btn-primary btn-lg">Get Started</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="tools" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-success mb-2 px-3 py-2">FREE TOOLS</span>
            <h2 class="display-5 fw-bold">Interior Design Tools</h2>
            <p class="lead text-muted">Use our free tools to plan and estimate your interior design project</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="tool-card p-4" onclick="$(this).find('.collapse').collapse('toggle')">
                    <i class="fas fa-calculator fa-3x text-primary mb-3"></i>
                    <h5>Cost Estimator</h5>
                    <p class="text-muted small mb-2">Estimate interior design costs per sq ft</p>
                    <div class="collapse">
                        <div class="card card-body border-0 bg-light text-start mt-2">
                            <div class="row g-2 mb-3">
                                <div class="col-6"><label class="small text-muted">Room Type</label><select class="form-select form-select-sm" id="costRoomType"><option>Bedroom</option><option>Living Room</option><option>Kitchen</option><option selected>Full Home</option><option>Bathroom</option></select></div>
                                <div class="col-6"><label class="small text-muted">Area (sq ft)</label><input type="number" class="form-control form-control-sm" id="costArea" value="1000" min="100"></div>
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-6"><label class="small text-muted">Quality</label><select class="form-select form-select-sm" id="costQuality"><option value="basic">Basic</option><option value="standard" selected>Standard</option><option value="premium">Premium</option><option value="luxury">Luxury</option></select></div>
                                <div class="col-6"><label class="small text-muted">Material Grade</label><select class="form-select form-select-sm" id="costMaterial"><option value="local">Local</option><option value="mid" selected>Mid-Range</option><option value="imported">Imported</option></select></div>
                            </div>
                            <button class="btn btn-sm btn-primary" onclick="estimateCost()"><i class="fas fa-calculator me-1"></i>Estimate Cost</button>
                            <div id="costResult" class="d-none mt-3 p-3 bg-white rounded-2 border">
                                <h6 class="mb-2">Cost Breakdown</h6>
                                <div id="costBreakdown"></div>
                                <hr class="my-2">
                                <div id="costTotal" class="fw-bold text-primary"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="tool-card p-4" onclick="$(this).find('.collapse').collapse('toggle')">
                    <i class="fas fa-ruler-combined fa-3x text-success mb-3"></i>
                    <h5>Room Size Planner</h5>
                    <p class="text-muted small mb-2">Convert and plan room dimensions</p>
                    <div class="collapse">
                        <div class="card card-body border-0 bg-light text-start mt-2">
                            <div class="row g-2 mb-3">
                                <div class="col-4"><label class="small text-muted">Width (ft)</label><input type="number" class="form-control form-control-sm" id="plannerW" value="12" min="4" max="50"></div>
                                <div class="col-4"><label class="small text-muted">Length (ft)</label><input type="number" class="form-control form-control-sm" id="plannerL" value="14" min="4" max="50"></div>
                                <div class="col-4"><label class="small text-muted">Convert to</label><select class="form-select form-select-sm" id="plannerUnit"><option value="sqft">Sq Ft</option><option value="sqm">Sq Meters</option><option value="sqyd">Sq Yards</option><option value="gaj">Gaj</option></select></div>
                            </div>
                            <button class="btn btn-sm btn-success" onclick="planRoom()"><i class="fas fa-arrows-alt me-1"></i>Calculate</button>
                            <div id="plannerResult" class="d-none mt-3 p-3 bg-white rounded-2 border">
                                <div id="plannerDetails"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="tool-card p-4" onclick="$(this).find('.collapse').collapse('toggle')">
                    <i class="fas fa-home fa-3x text-warning mb-3"></i>
                    <h5>Budget Planner</h5>
                    <p class="text-muted small mb-2">Plan your interior design budget</p>
                    <div class="collapse">
                        <div class="card card-body border-0 bg-light text-start mt-2">
                            <div class="row g-2 mb-3">
                                <div class="col-6"><label class="small text-muted">Total Budget (₹)</label><input type="number" class="form-control form-control-sm" id="budgetTotal" value="500000" min="5000" step="10000"></div>
                                <div class="col-6"><label class="small text-muted">Home Type</label><select class="form-select form-select-sm" id="budgetType"><option value="1bhk">1 BHK</option><option value="2bhk" selected>2 BHK</option><option value="3bhk">3 BHK</option><option value="villa">Villa</option></select></div>
                            </div>
                            <button class="btn btn-sm btn-warning" onclick="planBudget()"><i class="fas fa-pie-chart me-1"></i>Plan Budget</button>
                            <div id="budgetResult" class="d-none mt-3 p-3 bg-white rounded-2 border">
                                <h6 class="mb-2">Recommended Allocation</h6>
                                <div id="budgetBreakdown"></div>
                                <hr class="my-2">
                                <div id="budgetRemaining" class="small text-muted"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="tool-card border-2 p-4" onclick="$(this).find('.collapse').collapse('toggle')">
                    <i class="fas fa-palette fa-2x text-purple mb-2" style="color:#6a1b9a;"></i>
                    <h5>Room Color Palette Generator</h5>
                    <p class="text-muted small mb-2">Generate harmonious color schemes for any room</p>
                    <div class="collapse">
                        <div class="card card-body border-0 bg-light text-start mt-2">
                            <div class="row g-2 mb-3">
                                <div class="col-6"><label class="small text-muted">Room Type</label><select class="form-select form-select-sm" id="roomType"><option>Living Room</option><option>Bedroom</option><option>Kitchen</option><option>Bathroom</option><option>Home Office</option><option>Kids Room</option></select></div>
                                <div class="col-6"><label class="small text-muted">Style</label><select class="form-select form-select-sm" id="styleType"><option>Modern</option><option>Traditional</option><option>Minimalist</option><option>Bohemian</option><option>Industrial</option><option>Scandinavian</option></select></div>
                            </div>
                            <button class="btn btn-sm btn-primary mb-3" onclick="generatePalette()"><i class="fas fa-magic me-1"></i>Generate Palette</button>
                            <div id="paletteResult" class="d-none">
                                <label class="small text-muted">Suggested Color Palette:</label>
                                <div class="d-flex gap-2 mt-1" id="paletteColors"></div>
                                <p class="small text-muted mt-2 mb-0" id="paletteDesc"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="tool-card border-2 p-4" onclick="$(this).find('.collapse').collapse('toggle')">
                    <i class="fas fa-couch fa-2x text-success mb-2"></i>
                    <h5>Furniture Layout Planner</h5>
                    <p class="text-muted small mb-2">Plan furniture placement in any room size</p>
                    <div class="collapse">
                        <div class="card card-body border-0 bg-light text-start mt-2">
                            <div class="row g-2 mb-3">
                                <div class="col-4"><label class="small text-muted">Room Width (ft)</label><input type="number" class="form-control form-control-sm" id="roomW" value="12" min="6" max="30"></div>
                                <div class="col-4"><label class="small text-muted">Room Length (ft)</label><input type="number" class="form-control form-control-sm" id="roomL" value="14" min="6" max="40"></div>
                                <div class="col-4"><label class="small text-muted">Room Type</label><select class="form-select form-select-sm" id="furnitureType"><option>Living Room</option><option>Bedroom</option><option selected>Dining Room</option><option>Home Office</option></select></div>
                            </div>
                            <button class="btn btn-sm btn-success mb-3" onclick="planFurniture()"><i class="fas fa-arrows-alt me-1"></i>Plan Layout</button>
                            <div id="furnitureResult" class="d-none">
                                <div class="bg-white p-3 rounded-2 border" id="furnitureLayout" style="min-height:120px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function generatePalette() {
    const room = document.getElementById('roomType').value;
    const style = document.getElementById('styleType').value;
    const palettes = {
        'Living Room': {
            'Modern': { colors: ['#2C3E50','#E74C3C','#ECF0F1','#3498DB','#95A5A6'], desc: 'Bold contrast with deep navy, accent red, and clean whites for a contemporary living space.' },
            'Traditional': { colors: ['#8B4513','#D2691E','#F5DEB3','#2F4F4F','#DEB887'], desc: 'Warm earth tones with rich browns and cream for a timeless traditional feel.' },
            'Minimalist': { colors: ['#F5F5F5','#333333','#FFFFFF','#BDBDBD','#757575'], desc: 'Clean monochrome palette with subtle gray transitions for a clutter-free look.' },
            'Bohemian': { colors: ['#8E44AD','#E67E22','#1ABC9C','#F1C40F','#E74C3C'], desc: 'Vibrant, eclectic mix of purple, orange, teal, and gold for a free-spirited vibe.' },
            'Industrial': { colors: ['#4A4A4A','#D35400','#BDC3C7','#2C3E50','#E67E22'], desc: 'Raw urban feel with concrete grays, rust orange, and dark charcoal accents.' },
            'Scandinavian': { colors: ['#FFFFFF','#F0F0F0','#B8B8B8','#52958B','#D4A373'], desc: 'Light, airy whites and soft grays with sage green and warm beige touches.' }
        },
        'Bedroom': {
            'Modern': { colors: ['#4A235A','#AED6F1','#FDFEFE','#D4AC0D','#7FB3D8'], desc: 'Calming purple-blue scheme with gold accents for a luxurious bedroom retreat.' },
            'Minimalist': { colors: ['#F8F9FA','#DEE2E6','#495057','#CED4DA','#6C757D'], desc: 'Soft neutrals and gentle grays creating a serene, uncluttered sleeping space.' },
            'Scandinavian': { colors: ['#F7F7F7','#E8E8E4','#C1D5C1','#8BA78B','#D4C4A8'], desc: 'Peaceful greens and natural beige tones inspired by Nordic simplicity.' }
        },
        'Kitchen': {
            'Modern': { colors: ['#1A1A2E','#E94560','#FFFFFF','#16213E','#0F3460'], desc: 'Sleek dark navy with striking crimson accents for a gourmet kitchen.' },
            'Minimalist': { colors: ['#FFFFFF','#F0F0F0','#A0A0A0','#D0D0D0','#606060'], desc: 'Crisp white-on-white with subtle gray depth for an ultra-clean kitchen.' }
        }
    };
    const p = (palettes[room] && palettes[room][style]) || { colors: ['#6a1b9a','#ff6f00','#00c853','#2979ff','#ff1744'], desc: 'Default vibrant palette. Customize for your space.' };
    const cont = document.getElementById('paletteColors');
    cont.innerHTML = p.colors.map(c => `<div style="width:50px;height:50px;border-radius:8px;background:${c};border:2px solid #ddd;cursor:pointer" title="${c}" onclick="navigator.clipboard.writeText('${c}')"></div>`).join('');
    document.getElementById('paletteDesc').textContent = p.desc;
    document.getElementById('paletteResult').classList.remove('d-none');
}
function planFurniture() {
    const w = parseInt(document.getElementById('roomW').value) || 12;
    const l = parseInt(document.getElementById('roomL').value) || 14;
    const type = document.getElementById('furnitureType').value;
    const layouts = {
        'Living Room': [
            { name: '3-Seater Sofa', w: 3, l: 7 }, { name: '2-Seater Sofa', w: 3, l: 5 },
            { name: 'Coffee Table', w: 2.5, l: 4 }, { name: 'TV Unit', w: 1.5, l: 5 },
            { name: 'Side Table', w: 1.5, l: 1.5 }, { name: 'Floor Lamp', w: 1, l: 1 }
        ],
        'Bedroom': [
            { name: 'Queen Bed', w: 5, l: 6.5 }, { name: 'Wardrobe', w: 2, l: 5 },
            { name: 'Nightstand', w: 1.5, l: 1.5 }, { name: 'Dresser', w: 2, l: 4 }
        ],
        'Dining Room': [
            { name: '6-Seater Table', w: 3.5, l: 6 }, { name: 'Sideboard', w: 2, l: 4 },
            { name: 'Display Cabinet', w: 1.5, l: 3 }
        ],
        'Home Office': [
            { name: 'Desk', w: 2.5, l: 5 }, { name: 'Chair Area', w: 2, l: 2 },
            { name: 'Bookshelf', w: 1.5, l: 3 }, { name: 'File Cabinet', w: 1.5, l: 1.5 }
        ]
    };
    const items = layouts[type] || layouts['Living Room'];
    const scale = 12; const rw = w * scale; const rl = l * scale;
    let html = `<div style="position:relative;width:${rw}px;height:${rl}px;background:#f8f9fa;border:2px dashed #aaa;margin:0 auto;border-radius:4px;">`;
    let xp = 5, yp = 5;
    html += `<div style="position:absolute;top:2px;left:4px;font-size:10px;color:#999;">${w}ft x ${l}ft</div>`;
    items.forEach((item, i) => {
        const iw = item.w * scale; const il = item.l * scale;
        if (xp + iw + 5 > rw && yp > 5) { xp = 5; yp += Math.max(...items.map(it => it.l * scale)) + 5; }
        const colors = ['#6a1b9a','#3498db','#27ae60','#e74c3c','#f39c12','#8e44ad','#1abc9c','#e67e22'];
        html += `<div style="position:absolute;left:${xp}px;top:${yp}px;width:${iw}px;height:${il}px;background:${colors[i%colors.length]};border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:10px;color:#fff;font-weight:500;text-shadow:0 1px 2px rgba(0,0,0,0.3);">${item.name}</div>`;
        xp += iw + 5;
    });
    html += '</div>';
    html += `<p class="small text-muted mt-2 mb-0 text-center">Recommended layout for a ${w}ft x ${l}ft ${type}</p>`;
    document.getElementById('furnitureLayout').innerHTML = html;
    document.getElementById('furnitureResult').classList.remove('d-none');
}
function estimateCost() {
    const rates = {
        'Bedroom': { basic: 350, standard: 600, premium: 1000, luxury: 1600 },
        'Living Room': { basic: 400, standard: 700, premium: 1200, luxury: 1800 },
        'Kitchen': { basic: 500, standard: 900, premium: 1500, luxury: 2200 },
        'Full Home': { basic: 300, standard: 550, premium: 950, luxury: 1400 },
        'Bathroom': { basic: 450, standard: 800, premium: 1300, luxury: 2000 }
    };
    const materialMultiplier = { local: 0.85, mid: 1.0, imported: 1.4 };
    const room = document.getElementById('costRoomType').value;
    const area = parseFloat(document.getElementById('costArea').value) || 1000;
    const quality = document.getElementById('costQuality').value;
    const mat = document.getElementById('costMaterial').value;
    const rate = (rates[room] || rates['Full Home'])[quality] || 550;
    const baseCost = rate * area * materialMultiplier[mat];
    const categories = [
        { name: 'Flooring', pct: 20 }, { name: 'Paint & Walls', pct: 12 },
        { name: 'Furniture', pct: 30 }, { name: 'Lighting', pct: 8 },
        { name: 'Curtains & Blinds', pct: 5 }, { name: 'Modular Kitchen', pct: 10 },
        { name: 'Bathroom Fittings', pct: 5 }, { name: 'Labor & Installation', pct: 10 }
    ];
    let html = '';
    categories.forEach(c => {
        const amt = baseCost * c.pct / 100;
        html += `<div class="d-flex justify-content-between small"><span>${c.name}</span><span>₹${amt.toLocaleString('en-IN', {maximumFractionDigits:0})}</span></div>`;
    });
    document.getElementById('costBreakdown').innerHTML = html;
    document.getElementById('costTotal').textContent = 'Total Estimated Cost: ₹' + baseCost.toLocaleString('en-IN', {maximumFractionDigits:0});
    document.getElementById('costResult').classList.remove('d-none');
}
function planRoom() {
    const w = parseFloat(document.getElementById('plannerW').value) || 12;
    const l = parseFloat(document.getElementById('plannerL').value) || 14;
    const unit = document.getElementById('plannerUnit').value;
    const sqft = w * l;
    const conversions = {
        sqft: sqft,
        sqm: sqft / 10.764,
        sqyd: sqft / 9,
        gaj: sqft / 9
    };
    const labels = { sqft: 'Sq Ft', sqm: 'Sq Meters', sqyd: 'Sq Yards', gaj: 'Gaj' };
    let html = `<table class="table table-sm mb-0"><tr><td>Room Size</td><td><strong>${w} ft × ${l} ft</strong></td></tr>`;
    html += `<tr><td>Perimeter</td><td><strong>${2*(w+l)} ft</strong></td></tr>`;
    html += `<tr><td>Wall Area (10ft ceiling)</td><td><strong>${2*(w+l)*10} sq ft</strong></td></tr>`;
    Object.keys(conversions).forEach(k => {
        html += `<tr><td>Area in ${labels[k]}</td><td><strong>${conversions[k].toFixed(k==='sqm'?2:0)}</strong></td></tr>`;
    });
    if (unit !== 'sqft') {
        html += `<tr class="table-primary"><td>Your selected unit</td><td><strong>${conversions[unit].toFixed(unit==='sqm'?2:0)} ${labels[unit]}</strong></td></tr>`;
    }
    html += '</table>';
    document.getElementById('plannerDetails').innerHTML = html;
    document.getElementById('plannerResult').classList.remove('d-none');
}
function planBudget() {
    const total = parseFloat(document.getElementById('budgetTotal').value) || 500000;
    const type = document.getElementById('budgetType').value;
    const splits = {
        '1bhk': { 'Flooring': 15, 'Paint & Wallpaper': 12, 'Furniture': 25, 'Lighting & Fans': 10, 'Kitchen': 15, 'Bathroom': 8, 'Decor & Accessories': 8, 'Labor': 7 },
        '2bhk': { 'Flooring': 15, 'Paint & Wallpaper': 12, 'Furniture': 25, 'Lighting & Fans': 10, 'Kitchen': 15, 'Bathroom': 8, 'Decor & Accessories': 8, 'Labor': 7 },
        '3bhk': { 'Flooring': 15, 'Paint & Wallpaper': 12, 'Furniture': 25, 'Lighting & Fans': 10, 'Kitchen': 15, 'Bathroom': 8, 'Decor & Accessories': 8, 'Labor': 7 },
        'villa': { 'Flooring': 18, 'Paint & Wallpaper': 12, 'Furniture': 20, 'Lighting & Fans': 10, 'Kitchen': 12, 'Bathroom': 10, 'Outdoor': 8, 'Labor': 10 }
    };
    const allocation = splits[type] || splits['2bhk'];
    let html = '', used = 0;
    Object.keys(allocation).forEach(k => {
        const amt = total * allocation[k] / 100;
        used += amt;
        html += `<div class="d-flex justify-content-between small mb-1"><span>${k}</span><span class="fw-medium">₹${amt.toLocaleString('en-IN', {maximumFractionDigits:0})}</span></div>`;
        html += `<div class="progress mb-2" style="height:6px;"><div class="progress-bar" style="width:${allocation[k]}%"></div></div>`;
    });
    document.getElementById('budgetBreakdown').innerHTML = html;
    const rem = total - used;
    document.getElementById('budgetRemaining').textContent = 'Contingency Fund: ₹' + rem.toLocaleString('en-IN', {maximumFractionDigits:0}) + ' (' + (rem/total*100).toFixed(0) + '% of budget)';
    document.getElementById('budgetResult').classList.remove('d-none');
}
</script>

<?php if (!empty($portfolio)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2">OUR WORK</span>
            <h2 class="display-5 fw-bold">Portfolio</h2>
            <p class="lead text-muted">Browse our recent interior design projects</p>
        </div>
        <div class="row g-3">
            <?php foreach (array_slice($portfolio, 0, 6) as $item): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="portfolio-item">
                        <img src="<?= htmlspecialchars($item['image'] ?? '') ?>" alt="<?= htmlspecialchars($item['title'] ?? 'Portfolio') ?>" loading="lazy">
                        <div class="overlay">
                            <h5 class="mb-1"><?= htmlspecialchars($item['title'] ?? 'Project') ?></h5>
                            <small><?= htmlspecialchars($item['category'] ?? '') ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($testimonials)): ?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-primary mb-2 px-3 py-2">TESTIMONIALS</span>
            <h2 class="display-5 fw-bold">What Our Clients Say</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($testimonials as $t): ?>
                <div class="col-md-4">
                    <div class="testimonial-card">
                        <i class="fas fa-quote-left fa-2x text-primary opacity-25 mb-2"></i>
                        <p class="text-muted"><?= htmlspecialchars($t['content'] ?? $t['message'] ?? '') ?></p>
                        <div class="d-flex align-items-center mt-3">
                            <div><strong><?= htmlspecialchars($t['name'] ?? $t['client_name'] ?? 'Client') ?></strong><br><small class="text-muted"><?= htmlspecialchars($t['location'] ?? '') ?></small></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section id="contact-form" class="lead-form-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <span class="badge bg-primary mb-2 px-3 py-2">GET STARTED</span>
                    <h2 class="display-5 fw-bold">Book Your Free Consultation</h2>
                    <p class="lead text-muted">Get a free design consultation and estimate. No obligation, just expert advice.</p>
                </div>
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <form action="<?= BASE_URL ?>/service-interest" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="service_type" value="interior">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label fw-medium">Your Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control form-control-lg" required></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Phone Number <span class="text-danger">*</span></label><input type="tel" name="phone" class="form-control form-control-lg" required></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Email Address</label><input type="email" name="email" class="form-control form-control-lg"></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Property Type</label>
                                    <select name="property_type" class="form-select form-select-lg">
                                        <option value="">Select...</option>
                                        <option value="apartment">Apartment/Flat</option>
                                        <option value="house">Independent House</option>
                                        <option value="villa">Villa</option>
                                        <option value="office">Office/Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="form-label fw-medium">Approx. Area (sq ft)</label><input type="number" name="area" class="form-control form-control-lg" placeholder="e.g. 1200"></div>
                                <div class="col-md-6"><label class="form-label fw-medium">Budget Range (₹)</label>
                                    <select name="budget" class="form-select form-select-lg">
                                        <option value="">Select range</option>
                                        <option value="50000">Under ₹50,000</option>
                                        <option value="100000">₹50,000 - ₹1,00,000</option>
                                        <option value="200000">₹1,00,000 - ₹2,00,000</option>
                                        <option value="500000">₹2,00,000 - ₹5,00,000</option>
                                        <option value="1000000">₹5,00,000+</option>
                                    </select>
                                </div>
                                <div class="col-12"><label class="form-label fw-medium">Your Requirements</label><textarea name="message" rows="3" class="form-control" placeholder="Tell us about your interior design needs..."></textarea></div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-paper-plane me-2"></i>Get Free Consultation</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mt-4 g-3 text-center">
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-phone-alt text-primary fa-2x mb-2"></i><h6>Call Us</h6><p class="mb-0 text-muted"><?= $phoneDisplay ?></p></div></div>
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fab fa-whatsapp text-success fa-2x mb-2"></i><h6>WhatsApp</h6><p class="mb-0 text-muted"><?= $phoneDisplay ?></p></div></div>
                    <div class="col-md-4"><div class="p-3 bg-white rounded-3 shadow-sm"><i class="fas fa-map-marker-alt text-primary fa-2x mb-2"></i><h6>Visit Us</h6><p class="mb-0 text-muted">Gorakhpur, UP</p></div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($faqs)): ?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <span class="badge bg-primary mb-2 px-3 py-2">FAQ</span>
                    <h2 class="display-5 fw-bold">Frequently Asked Questions</h2>
                </div>
                <div class="accordion faq-accordion" id="interiorFaq">
                    <?php foreach ($faqs as $idx => $faq): ?>
                        <div class="accordion-item border-0 mb-2 shadow-sm rounded-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $idx > 0 ? 'collapsed' : '' ?> rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $idx ?>">
                                    <?= htmlspecialchars($faq['question'] ?? '') ?>
                                </button>
                            </h2>
                            <div id="faq<?= $idx ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" data-bs-parent="#interiorFaq">
                                <div class="accordion-body text-muted"><?= htmlspecialchars($faq['answer'] ?? '') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-4 bg-dark text-white text-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8 text-md-start">
                <h4 class="mb-1">Ready to Transform Your Space?</h4>
                <p class="mb-0 opacity-75">Get a free consultation and detailed estimate within 24 hours</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="tel:<?= $phoneRaw ?>" class="btn btn-warning btn-lg px-4"><i class="fas fa-phone me-2"></i>Call Now</a>
            </div>
        </div>
    </div>
</section>
