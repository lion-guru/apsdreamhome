<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); ?>
<style>
.team-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0d9488 100%);
    position: relative;
    overflow: hidden;
    min-height: 50vh;
    display: flex;
    align-items: center;
}
.team-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at 30% 50%, rgba(13,148,136,0.15) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(16,185,129,0.1) 0%, transparent 50%);
    animation: heroGlow 10s ease-in-out infinite alternate;
}
@keyframes heroGlow {
    0% { transform: translate(0,0) rotate(0deg); }
    100% { transform: translate(-5%,-5%) rotate(5deg); }
}
.team-hero-content { position: relative; z-index: 2; }
.team-hero h1 {
    font-size: clamp(2.5rem, 6vw, 4rem);
    font-weight: 800;
    background: linear-gradient(135deg, #fff 0%, #5eead4 50%, #10b981 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.team-hero .hero-subtitle {
    font-size: 1.15rem;
    color: rgba(255,255,255,0.7);
    max-width: 650px;
    line-height: 1.8;
}
.floating-shapes { position: absolute; inset: 0; z-index: 1; pointer-events: none; overflow: hidden; }
.floating-shapes span {
    position: absolute;
    display: block;
    width: 20px; height: 20px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
    animation: floatUp 12s infinite;
}
.floating-shapes span:nth-child(1) { left:10%; width:60px;height:60px; animation-delay:0s; animation-duration:14s; }
.floating-shapes span:nth-child(2) { left:25%; width:30px;height:30px; animation-delay:2s; animation-duration:10s; }
.floating-shapes span:nth-child(3) { left:50%; width:80px;height:80px; animation-delay:4s; animation-duration:16s; }
.floating-shapes span:nth-child(4) { left:70%; width:40px;height:40px; animation-delay:1s; animation-duration:12s; }
.floating-shapes span:nth-child(5) { left:85%; width:50px;height:50px; animation-delay:3s; animation-duration:15s; }
@keyframes floatUp {
    0% { transform: translateY(100px) scale(0); opacity:0; }
    10% { opacity:0.5; }
    90% { opacity:0.5; }
    100% { transform: translateY(-100px) scale(1); opacity:0; }
}

.team-stats {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    padding: 4rem 0;
}
.team-stats .stat-card { text-align: center; padding: 1.5rem; }
.team-stats .stat-number { font-size: 2.8rem; font-weight: 800; color: #fff; line-height: 1; }
.team-stats .stat-label { color: rgba(255,255,255,0.8); font-size: 0.95rem; margin-top: 0.5rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; }

.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
    margin-bottom: 2rem;
}
.filter-btn {
    padding: 0.5rem 1.2rem;
    border: 2px solid #e5e7eb;
    background: #fff;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.3s ease;
}
.filter-btn:hover, .filter-btn.active {
    background: linear-gradient(135deg, #0d9488, #0f766e);
    border-color: transparent;
    color: #fff;
    box-shadow: 0 4px 15px rgba(13,148,136,0.4);
    transform: translateY(-2px);
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
    gap: 1.5rem;
}
.team-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.06);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    cursor: pointer;
}
.team-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.12);
}
.team-card-photo {
    height: 200px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.team-card-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}
.team-card:hover .team-card-photo img { transform: scale(1.08); }
.team-card-photo .placeholder-icon { font-size: 4rem; color: rgba(255,255,255,0.8); }
.team-card-body {
    padding: 1.25rem;
    position: relative;
}
.team-card-body h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 0.2rem; color: #1f2937; }
.team-card-body .position {
    color: #0d9488;
    font-weight: 600;
    font-size: 0.8rem;
    margin-bottom: 0.5rem;
    display: inline-block;
    padding: 0.2rem 0.7rem;
    background: rgba(13,148,136,0.08);
    border-radius: 50px;
}
.team-card-body .bio {
    color: #6b7280;
    font-size: 0.82rem;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 0.5rem;
}
.team-card-body .expertise-tag {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 50px;
    font-size: 0.7rem;
    margin-right: 0.2rem;
    margin-bottom: 0.2rem;
}
.team-card-body .group-badge {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 600;
    margin-top: 0.3rem;
}
.team-card-footer {
    padding: 0.75rem 1.25rem;
    border-top: 1px solid #f3f4f6;
    display: flex;
    gap: 0.5rem;
}
.team-card-footer a {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #6b7280;
    background: #f3f4f6;
    transition: all 0.3s;
    text-decoration: none;
    font-size: 0.8rem;
}
.team-card-footer a:hover {
    background: linear-gradient(135deg, #0d9488, #0f766e);
    color: #fff;
    transform: translateY(-2px);
}

.section-title {
    font-size: clamp(1.8rem, 3vw, 2.4rem);
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.5rem;
}
.section-subtitle { color: #6b7280; max-width: 600px; margin: 0 auto 2rem; font-size: 1rem; }

.values-section {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f0ff 100%);
    padding: 5rem 0;
}
.value-card {
    text-align: center;
    padding: 2.5rem 1.5rem;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    transition: all 0.3s;
    height: 100%;
}
.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(13,148,136,0.15);
}
.value-card .icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    border-radius: 20px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    color: #fff;
}
.value-card h4 { font-weight: 700; font-size: 1.1rem; margin-bottom: 0.75rem; color: #1f2937; }
.value-card p { font-size: 0.9rem; color: #6b7280; line-height: 1.7; margin-bottom: 0; }

.cta-section {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0d9488 100%);
    padding: 5rem 0;
    text-align: center;
}
.cta-section h2 {
    font-size: clamp(2rem, 4vw, 2.8rem);
    font-weight: 800;
    color: #fff;
    margin-bottom: 1rem;
}
.cta-section p { color: rgba(255,255,255,0.7); max-width: 550px; margin: 0 auto 2rem; font-size: 1.1rem; }
.cta-section .btn-cta {
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    border: none;
    color: #fff;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}
.cta-section .btn-cta:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(13,148,136,0.4);
}

.cms-banner {
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 2rem;
    margin-top: 2rem;
    color: rgba(255,255,255,0.9);
    font-size: 1.05rem;
    line-height: 1.8;
    border: 1px solid rgba(255,255,255,0.08);
}

/* Group cards */
.group-card {
    border-radius: 20px;
    padding: 2rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    transition: all 0.3s;
    height: 100%;
}
.group-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
.group-card .group-icon { font-size: 2.5rem; margin-bottom: 1rem; }
.group-card h4 { font-weight: 700; font-size: 1.3rem; margin-bottom: 0.3rem; color:#fff; }
.group-card .slogan { font-style: italic; opacity: 0.9; font-size: 0.9rem; margin-bottom: 1rem; }
.group-card .score {
    font-size: 2rem;
    font-weight: 800;
    opacity: 0.9;
}
.group-card .score-label { font-size: 0.8rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.05em; }

/* Feature cards for special sections */
.feature-card {
    background: #fff;
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    transition: all 0.3s;
    height: 100%;
}
.feature-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(13,148,136,0.12); }
.feature-card .f-icon { font-size: 2.5rem; margin-bottom: 1rem; }
.feature-card h5 { font-weight: 700; font-size: 1.1rem; margin-bottom: 0.5rem; }
.feature-card p { font-size: 0.88rem; color: #6b7280; line-height: 1.6; margin-bottom: 0; }

.special-section {
    padding: 5rem 0;
    color: #4b5563 !important;
}
.special-section h1, .special-section h2, .special-section h3, .special-section h4, .special-section h5, .special-section strong {
    color: #1f2937 !important;
}
.special-section .text-muted {
    color: #6b7280 !important;
}
.special-section:nth-child(even) {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f0ff 100%);
}

/* Ensure bg-white cards have dark text regardless of global theme */
.bg-white { color: #4b5563 !important; }
.bg-white h5, .bg-white strong { color: #1f2937 !important; }
.bg-white .text-muted { color: #6b7280 !important; }

@media (max-width: 768px) {
    .team-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem; }
    .team-stats .stat-number { font-size: 2rem; }
    .filter-bar { gap: 0.3rem; }
    .filter-btn { padding: 0.3rem 0.8rem; font-size: 0.75rem; }
}
</style>

<!-- HERO -->
<section class="team-hero">
    <div class="floating-shapes">
        <span></span><span></span><span></span><span></span><span></span>
    </div>
    <div class="container team-hero-content py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1>Meet Our Expert Team</h1>
                <p class="hero-subtitle">Passionate professionals dedicated to making your real estate journey seamless, transparent, and successful. Backed by AI technology and a customer-first approach.</p>
                <?php if (!empty($pageContent)): ?>
                <div class="cms-banner"><?php echo e($pageContent); ?></div>
                <?php endif; ?>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-light btn-lg px-4 me-2 rounded-pill">
                    <i class="fas fa-handshake me-2"></i>Work With Us
                </a>
                <a href="<?php echo BASE_URL; ?>/careers" class="btn btn-outline-light btn-lg px-4 rounded-pill mt-2 mt-md-0">
                    <i class="fas fa-briefcase me-2"></i>Join Our Team
                </a>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<?php
$members_count = count($team_members ?? []);
$total_exp = 0;
foreach ($team_members ?? [] as $tm) {
    if (!empty($tm->experience)) {
        preg_match('/\d+/', $tm->experience, $em);
        if (!empty($em[0])) $total_exp += (int)$em[0];
    }
}
?>
<section class="team-stats">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $members_count > 0 ? $members_count . '+' : '50+' ?></div>
                    <div class="stat-label">Team Members</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?= $total_exp > 0 ? $total_exp . '+' : '75+' ?></div>
                    <div class="stat-label">Years Combined Exp</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number">2500+</div>
                    <div class="stat-label">Properties Sold</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Client Satisfaction</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MAIN TEAM SECTION -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2" class="style-47546">Our Leadership & Team</h2>
            <p class="text-muted" class="style-17535">Dedicated professionals working together to deliver exceptional real estate solutions with innovation and integrity.</p>
        </div>

        <?php if (!empty($team_members)): ?>
        <div class="filter-bar">
            <button class="filter-btn active" data-filter="all">All</button>
            <?php foreach (array_keys($category_groups) as $cat): ?>
            <button class="filter-btn" data-filter="<?php echo htmlspecialchars(strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $cat))); ?>"><?php echo htmlspecialchars(ucwords($cat)); ?></button>
            <?php endforeach; ?>
        </div>

        <div class="team-grid" id="teamGrid">
            <?php foreach ($team_members as $m): ?>
            <?php
                $cat = !empty($m->category) ? ucfirst(str_replace('_', ' ', $m->category)) : 'Team';
                $catSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', trim($cat)));
                // DB stores full relative path like "assets/images/team/name.jpg"
                $photoPath = ltrim($m->photo ?? '', '/');
                $photoUrl = !empty($photoPath) ? BASE_URL . '/' . htmlspecialchars($photoPath ?? '') : '';
                $hasPhoto = !empty($photoPath) && file_exists(__DIR__ . '/../../../' . $photoPath);
                $groupColors = ['APS Warriors' => '#dc2626', 'Dream Builders' => '#2563eb', 'Nari Shakti' => '#d946ef', 'Tech Pioneers' => '#059669'];
                $gColor = $groupColors[$m->group_name ?? ''] ?? '#0d9488';
            ?>
            <div class="team-card" data-category="<?php echo e($catSlug); ?>">
                <div class="team-card-photo">
                    <?php if ($hasPhoto): ?>
                    <img src="<?= $photoUrl ?>" alt="<?php echo htmlspecialchars($m->name); ?>" loading="lazy">
                    <?php else: ?>
                    <div class="placeholder-icon"><i class="fas fa-user-tie"></i></div>
                    <?php endif; ?>
                </div>
                <div class="team-card-body">
                    <h3><?php echo htmlspecialchars($m->name); ?></h3>
                    <span class="position"><?php echo htmlspecialchars($m->position); ?></span>
                    <?php if (!empty($m->bio)): ?>
                    <p class="bio"><?php echo htmlspecialchars($m->bio); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($m->expertise)): ?>
                    <div>
                        <?php foreach (explode(',', $m->expertise) as $tag): ?>
                        <span class="expertise-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($m->group_name)): ?>
                    <span class="group-badge" class="style-50153"><?= htmlspecialchars($m->group_name) ?></span>
                    <?php endif; ?>
                </div>
                <div class="team-card-footer">
                    <?php if (!empty($m->email)): ?>
                    <a href="mailto:<?php echo htmlspecialchars($m->email); ?>" title="Email"><i class="fas fa-envelope"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($m->phone)): ?>
                    <a href="tel:<?php echo htmlspecialchars($m->phone); ?>" title="Call"><i class="fas fa-phone"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($m->facebook_url)): ?>
                    <a href="<?php echo htmlspecialchars($m->facebook_url); ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($m->linkedin)): ?>
                    <a href="<?php echo htmlspecialchars($m->linkedin); ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($m->instagram_url)): ?>
                    <a href="<?php echo htmlspecialchars($m->instagram_url); ?>" target="_blank" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-users" class="style-27836"></i>
            <p class="text-muted">Team members are currently being updated. Please check back soon.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- WOMEN EMPOWERMENT / NARI SHAKTI -->
<section class="special-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 mb-3" class="style-51894"><i class="fas fa-fist-raised me-2"></i>Nari Shakti</span>
                <h2 class="section-title">Women Empowerment Initiative</h2>
                <p class="section-subtitle text-start mb-3">APS Dream Home is committed to empowering women in real estate. Our "Nari Shakti" initiative provides a platform for women to build careers, earn independently, and lead with confidence.</p>
                <div class="row g-3 mt-2">
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 bg-white rounded-3 shadow-sm">
                            <i class="fas fa-graduation-cap text-primary" class="style-4846"></i>
                            <div><strong>Free Training</strong><br><small class="text-muted">Learn real estate, negotiation, and customer handling</small></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 bg-white rounded-3 shadow-sm">
                            <i class="fas fa-hand-holding-usd text-success" class="style-4846"></i>
                            <div><strong>Flexible Earnings</strong><br><small class="text-muted">Work from home or on-site, earn on your terms</small></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 bg-white rounded-3 shadow-sm">
                            <i class="fas fa-users text-warning" class="style-4846"></i>
                            <div><strong>Community Support</strong><br><small class="text-muted">Join a network of empowered women across India</small></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-start gap-3 p-3 bg-white rounded-3 shadow-sm">
                            <i class="fas fa-chart-line text-info" class="style-4846"></i>
                            <div><strong>Leadership Track</strong><br><small class="text-muted">Clear growth path to team lead and manager roles</small></div>
                        </div>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-lg btn-outline-danger mt-4 rounded-pill"><i class="fas fa-fist-raised me-2"></i>Join Nari Shakti</a>
            </div>
            <div class="col-lg-6">
                <div class="row g-3">
                    <?php 
                    $women = array_filter($team_members ?? [], fn($m) => ($m->category ?? '') === 'women_wing');
                    foreach ($women as $w): 
                    ?>
                    <div class="col-md-6">
                        <div class="team-card" class="style-32337">
                            <div class="team-card-photo" class="style-26845">
                                <?php $wPhotoPath = ltrim($w->photo ?? '', '/'); $wPhoto = !empty($wPhotoPath) && file_exists(__DIR__ . '/../../../' . $wPhotoPath) ? BASE_URL . '/' . htmlspecialchars($wPhotoPath ?? '') : ''; ?>
                                <?php if ($wPhoto): ?>
                                <img src="<?= $wPhoto ?>" alt="">
                                <?php else: ?>
                                <div class="placeholder-icon"><i class="fas fa-user-tie"></i></div>
                                <?php endif; ?>
                            </div>
                            <div class="team-card-body p-3 text-center">
                                <h3 class="style-85997"><?= htmlspecialchars($w->name) ?></h3>
                                <span class="position"><?= htmlspecialchars($w->position) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="text-center p-4 w-100">
                            <i class="fas fa-plus-circle text-danger" class="style-56051"></i>
                            <p class="text-muted mt-2 mb-0"><strong>Be the next!</strong><br><small>Join Nari Shakti today</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TEAM GROUPS / COMPETITION -->
<section class="special-section" class="style-90491">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 mb-3" class="style-51894"><i class="fas fa-trophy me-2"></i>Team Competition</span>
            <h2 class="section-title">Our Battle Groups</h2>
            <p class="section-subtitle">Friendly competition drives excellence. Our teams compete on sales, innovation, and customer satisfaction. Leaderboard updates every month!</p>
        </div>

        <?php if (!empty($team_groups)): ?>
        <div class="row g-4 mb-5">
            <?php 
            $rank = 1;
            $colors = ['#dc2626','#2563eb','#059669','#d946ef'];
            $icons = ['fas fa-trophy','fas fa-medal','fas fa-award','fas fa-star'];
            foreach ($team_groups as $i => $g): 
                $bgColor = $g['badget_color'] ?? $colors[$i % 4] ?? '#0d9488';
            ?>
            <div class="col-md-3">
                <div class="group-card" style="background-color: <?= $bgColor ?>;">
                    <?php if ($rank <= 3): ?>
                    <div class="style-273">
                        <i class="<?= $icons[$rank-1] ?>"></i>
                    </div>
                    <?php endif; ?>
                    <div class="group-icon"><i class="<?= htmlspecialchars($g['icon'] ?? 'fas fa-users') ?>"></i></div>
                    <h4><?= htmlspecialchars($g['name'] ?? '') ?></h4>
                    <div class="slogan">"<?= htmlspecialchars($g['slogan'] ?? '') ?>"</div>
                    <p class="style-3434"><?= htmlspecialchars($g['description'] ?? '') ?></p>
                    <div class="d-flex justify-content-between align-items-end mt-3">
                        <div><small class="style-80098">Leader: <?= htmlspecialchars($g['leader_name'] ?? 'TBD') ?></small></div>
                        <div class="text-end">
                            <div class="score"><?= number_format($g['score'] ?? 0) ?></div>
                            <div class="score-label">Points</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $rank++; endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="text-center">
            <p class="text-muted mb-3"><i class="fas fa-info-circle me-1"></i>Want to compete? Join a group and start earning points for your team!</p>
            <a href="<?= BASE_URL ?>/become-associate" class="btn btn-lg btn-warning rounded-pill"><i class="fas fa-fire me-2"></i>Join a Team</a>
        </div>
    </div>
</section>

<!-- AI & TECHNOLOGY -->
<section class="special-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 mb-3" class="style-51894"><i class="fas fa-microchip me-2"></i>Tech Powered</span>
                <h2 class="section-title">AI & Software Innovation</h2>
                <p class="section-subtitle text-start mb-3">APS Dream Home is not just a real estate company — we're a tech company that does real estate. Our in-house team builds AI tools, automation, and software that gives us a competitive edge.</p>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="feature-card text-start p-3">
                            <div class="f-icon text-success" class="style-43152"><i class="fas fa-robot"></i></div>
                            <h5 class="style-85997">AI Lead Scoring</h5>
                            <p class="style-64777">Smart algorithms score and prioritize leads for maximum conversion.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card text-start p-3">
                            <div class="f-icon text-primary" class="style-43152"><i class="fas fa-phone-volume"></i></div>
                            <h5 class="style-85997">AI Voice Agents</h5>
                            <p class="style-64777">Automated calling, follow-ups, and lead nurturing 24/7.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card text-start p-3">
                            <div class="f-icon text-warning" class="style-43152"><i class="fas fa-chart-bar"></i></div>
                            <h5 class="style-85997">Price Prediction</h5>
                            <p class="style-64777">ML models predict optimal pricing for properties and plots.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card text-start p-3">
                            <div class="f-icon text-info" class="style-43152"><i class="fas fa-calculator"></i></div>
                            <h5 class="style-85997">Smart Calculators</h5>
                            <p class="style-64777">EMI, stamp duty, loan eligibility — all AI-powered.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="bg-white rounded-4 shadow-sm p-4">
                    <h5 class="fw-bold mb-3"><i class="fas fa-code me-2 text-success"></i>Our Tech Stack</h5>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge bg-dark">PHP 8.2</span>
                        <span class="badge bg-primary">MySQL</span>
                        <span class="badge bg-info">Redis</span>
                        <span class="badge bg-warning text-dark">JavaScript</span>
                        <span class="badge bg-danger">AI/ML</span>
                        <span class="badge bg-success">WebSocket</span>
                        <span class="badge bg-secondary">Docker</span>
                        <span class="badge bg-purple" class="style-90453">Flutter</span>
                    </div>
                    <p class="text-muted small">Built in-house by our Tech Pioneers team led by Vijay Verma (CTO). From AI chatbots to interactive property maps — everything is custom-built for the Indian real estate market.</p>
                    <a href="<?= BASE_URL ?>/tools-hub" class="btn btn-outline-success rounded-pill"><i class="fas fa-flask me-2"></i>Explore Our Tools</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- COLLEGE STUDENTS / RENTAL EARNING -->
<section class="special-section" class="style-90491">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 mb-3" class="style-51894"><i class="fas fa-graduation-cap me-2"></i>Student Program</span>
                <h2 class="section-title">College Students — Earn While You Learn</h2>
                <p class="section-subtitle text-start mb-3">Perfect for students who want financial independence. Focus on rental properties — the easiest entry point in real estate.</p>
                <ul class="list-unstyled">
                    <li class="mb-3 d-flex align-items-start gap-3">
                        <i class="fas fa-home text-primary mt-1" class="style-30322"></i>
                        <div><strong>Rental Property Focus</strong><br><small class="text-muted">Help tenants find rental homes, earn referral fees. No investment needed.</small></div>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-3">
                        <i class="fas fa-clock text-success mt-1" class="style-30322"></i>
                        <div><strong>Flexible Hours</strong><br><small class="text-muted">Work around your class schedule. Part-time, from your phone.</small></div>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-3">
                        <i class="fas fa-laptop-code text-info mt-1" class="style-30322"></i>
                        <div><strong>Tech Tools Access</strong><br><small class="text-muted">Use our AI tools for lead finding, property matching, and client management.</small></div>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-3">
                        <i class="fas fa-certificate text-warning mt-1" class="style-30322"></i>
                        <div><strong>Internship Certificate</strong><br><small class="text-muted">Earn a recognized internship certificate after 3 months.</small></div>
                    </li>
                </ul>
                <a href="<?= BASE_URL ?>/contact" class="btn btn-lg btn-primary rounded-pill"><i class="fas fa-graduation-cap me-2"></i>Register as Student Partner</a>
            </div>
            <div class="col-lg-7">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="feature-card p-3 text-start">
                            <div class="f-icon text-primary" class="style-4846"><i class="fas fa-building"></i></div>
                            <h5 class="style-77830">Rental Listings</h5>
                            <p class="style-64777">Help property owners list rentals. Earn per listing.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-card p-3 text-start">
                            <div class="f-icon text-success" class="style-4846"><i class="fas fa-handshake"></i></div>
                            <h5 class="style-77830">Tenant Matching</h5>
                            <p class="style-64777">Connect tenants to properties. Earn referral bonus.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-card p-3 text-start">
                            <div class="f-icon text-warning" class="style-4846"><i class="fas fa-bullhorn"></i></div>
                            <h5 class="style-77830">Campus Ambassador</h5>
                            <p class="style-64777">Represent APS on your campus. Earn stipend + incentives.</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="feature-card p-3 text-start">
                            <div class="f-icon text-info" class="style-4846"><i class="fas fa-robot"></i></div>
                            <h5 class="style-77830">AI Tools Access</h5>
                            <p class="style-64777">Use our AI for lead gen, property matching, more.</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="bg-light rounded-3 p-3 text-center">
                            <p class="mb-1 fw-bold">What students say:</p>
                            <p class="text-muted small mb-0"><i class="fas fa-quote-left me-1 text-primary"></i>Earned my first rental commission in week one. The AI tools make it super easy.<i class="fas fa-quote-right ms-1 text-primary"></i></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CORE VALUES -->
<section class="values-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold" class="style-47546">Our Core Values</h2>
            <p class="text-muted" class="style-37392">The principles that guide every decision we make and every relationship we build.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="icon"><i class="fas fa-shield-alt"></i></div>
                    <h4>Integrity</h4>
                    <p>We uphold the highest standards of honesty and transparency in every transaction.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="icon"><i class="fas fa-hand-holding-heart"></i></div>
                    <h4>Client Focus</h4>
                    <p>Your dreams and needs come first. We listen, understand, and deliver solutions that matter.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="icon"><i class="fas fa-trophy"></i></div>
                    <h4>Excellence</h4>
                    <p>We strive for excellence in everything we do, setting benchmarks in real estate.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="icon"><i class="fas fa-lightbulb"></i></div>
                    <h4>Innovation</h4>
                    <p>Embracing technology and AI to provide modern real estate solutions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Work with Our Expert Team?</h2>
        <p>Let us help you find the perfect property. Schedule a consultation with our experts today.</p>
        <div>
            <a href="<?php echo BASE_URL; ?>/contact" class="btn-cta me-2 mb-2">
                <i class="fas fa-calendar-check me-2"></i>Get in Touch
            </a>
            <a href="tel:<?= $phoneRaw ?>" class="btn-cta mb-2" class="style-11030">
                <i class="fas fa-phone-alt me-2"></i>Call Now
            </a>
        </div>
    </div>
</section>

<script>
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.team-card').forEach(card => {
            card.style.display = (filter === 'all' || card.dataset.category === filter) ? '' : 'none';
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.team-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => { entry.target.style.opacity = '1'; entry.target.style.transform = 'translateY(0)'; }, i * 100);
            }
        });
    }, { threshold: 0.1 });
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.5s ease';
        observer.observe(card);
    });
});
</script>
