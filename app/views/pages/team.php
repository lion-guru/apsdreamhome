<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
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
    position: relative;
}
.team-stats .stat-card {
    text-align: center;
    padding: 1.5rem;
}
.team-stats .stat-number {
    font-size: 2.8rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}
.team-stats .stat-label {
    color: rgba(255,255,255,0.8);
    font-size: 0.95rem;
    margin-top: 0.5rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
    margin-bottom: 3rem;
}
.filter-btn {
    padding: 0.6rem 1.5rem;
    border: 2px solid #e5e7eb;
    background: #fff;
    border-radius: 50px;
    font-size: 0.9rem;
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
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
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
    height: 220px;
    background: linear-gradient(135deg, #0d9488, #0f766e);
    position: relative;
    overflow: hidden;
}
.team-card-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}
.team-card:hover .team-card-photo img {
    transform: scale(1.08);
}
.team-card-photo .placeholder-icon {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: rgba(255,255,255,0.8);
}
.team-card-photo .card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(0deg, rgba(0,0,0,0.5) 0%, transparent 60%);
    opacity: 0;
    transition: opacity 0.3s;
}
.team-card:hover .card-overlay { opacity: 1; }
.team-card-body {
    padding: 1.5rem;
    position: relative;
}
.team-card-body h3 {
    font-size: 1.2rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
    color: #1f2937;
}
.team-card-body .position {
    color: #0d9488;
    font-weight: 600;
    font-size: 0.85rem;
    margin-bottom: 0.75rem;
    display: inline-block;
    padding: 0.2rem 0.8rem;
    background: rgba(13,148,136,0.08);
    border-radius: 50px;
}
.team-card-body .bio {
    color: #6b7280;
    font-size: 0.88rem;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 1rem;
}
.team-card-body .expertise-tag {
    display: inline-block;
    padding: 0.15rem 0.6rem;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 50px;
    font-size: 0.75rem;
    margin-right: 0.3rem;
    margin-bottom: 0.3rem;
}
.team-card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f3f4f6;
    display: flex;
    gap: 0.75rem;
}
.team-card-footer a {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #6b7280;
    background: #f3f4f6;
    transition: all 0.3s;
    text-decoration: none;
    font-size: 0.9rem;
}
.team-card-footer a:hover {
    background: linear-gradient(135deg, #0d9488, #0f766e);
    color: #fff;
    transform: translateY(-2px);
}

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
@media (max-width: 768px) {
    .team-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; }
    .team-stats .stat-number { font-size: 2rem; }
    .filter-bar { gap: 0.4rem; }
    .filter-btn { padding: 0.4rem 1rem; font-size: 0.8rem; }
}
</style>

<section class="team-hero">
    <div class="floating-shapes">
        <span></span><span></span><span></span><span></span><span></span>
    </div>
    <div class="container team-hero-content py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1><?php echo __('team_hero_title', [], 'Meet Our Expert Team'); ?></h1>
                <p class="hero-subtitle"><?php echo __('team_hero_subtitle', [], 'Passionate professionals dedicated to making your real estate journey seamless, transparent, and successful.'); ?></p>
                <?php if (!empty($pageContent)): ?>
                <div class="cms-banner"><?php echo $pageContent; ?></div>
                <?php endif; ?>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="<?php echo BASE_URL; ?>/contact" class="btn btn-light btn-lg px-4 me-2 rounded-pill">
                    <i class="fas fa-handshake me-2"></i><?php echo __('team_work_with_us', [], 'Work With Us'); ?>
                </a>
                <a href="<?php echo BASE_URL; ?>/careers" class="btn btn-outline-light btn-lg px-4 rounded-pill mt-2 mt-md-0">
                    <i class="fas fa-briefcase me-2"></i><?php echo __('team_join_our_team', [], 'Join Our Team'); ?>
                </a>
            </div>
        </div>
    </div>
</section>

<section class="team-stats">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number">50+</div>
                    <div class="stat-label"><?php echo __('team_stat_members', [], 'Team Members'); ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number">75+</div>
                    <div class="stat-label"><?php echo __('team_stat_experience', [], 'Years Combined Exp'); ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number">2500+</div>
                    <div class="stat-label"><?php echo __('team_stat_properties_sold', [], 'Properties Sold'); ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-number">98%</div>
                    <div class="stat-label"><?php echo __('team_stat_satisfaction', [], 'Client Satisfaction'); ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2" style="color:#1f2937;"><?php echo __('team_leadership_title', [], 'Our Leadership & Team'); ?></h2>
            <p class="text-muted" style="max-width:600px;margin:0 auto;"><?php echo __('team_leadership_subtitle', [], 'Dedicated professionals working together to deliver exceptional real estate solutions.'); ?></p>
        </div>

        <?php if (!empty($team_members)): ?>
        <div class="filter-bar">
            <button class="filter-btn active" data-filter="all"><?php echo __('filter_all', [], 'All'); ?></button>
            <?php foreach (array_keys($expertise_groups) as $cat): ?>
            <button class="filter-btn" data-filter="<?php echo htmlspecialchars(strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $cat))); ?>"><?php echo htmlspecialchars($cat); ?></button>
            <?php endforeach; ?>
        </div>

        <div class="team-grid" id="teamGrid">
            <?php foreach ($team_members as $m): ?>
            <?php
                $cat = $m->expertise ? explode(',', $m->expertise)[0] : 'Other';
                $catSlug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', trim($cat)));
                $photoUrl = !empty($m->photo) ? BASE_URL . '/assets/images/' . htmlspecialchars($m->photo) : '';
                $hasPhoto = !empty($m->photo) && file_exists(__DIR__ . '/../../assets/images/' . $m->photo);
            ?>
            <div class="team-card" data-category="<?php echo $catSlug; ?>">
                <div class="team-card-photo">
                    <?php if ($hasPhoto): ?>
                    <img src="<?= $photoUrl ?>" alt="<?php echo htmlspecialchars($m->name); ?>" loading="lazy">
                    <?php else: ?>
                    <div class="placeholder-icon"><i class="fas fa-user-tie"></i></div>
                    <?php endif; ?>
                    <div class="card-overlay"></div>
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
                </div>
                <div class="team-card-footer">
                    <?php if (!empty($m->email)): ?>
                    <a href="mailto:<?php echo htmlspecialchars($m->email); ?>" title="Email"><i class="fas fa-envelope"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($m->phone)): ?>
                    <a href="tel:<?php echo htmlspecialchars($m->phone); ?>" title="Call"><i class="fas fa-phone"></i></a>
                    <?php endif; ?>
                    <?php if (!empty($m->linkedin)): ?>
                    <a href="<?php echo htmlspecialchars($m->linkedin); ?>" target="_blank" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-users" style="font-size:3rem;color:#d1d5db;margin-bottom:1rem;"></i>
            <p class="text-muted"><?php echo __('team_members_updating', [], 'Team members are currently being updated. Please check back soon.'); ?></p>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="values-section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold" style="color:#1f2937;"><?php echo __('team_values_title', [], 'Our Core Values'); ?></h2>
            <p class="text-muted" style="max-width:550px;margin:0 auto;"><?php echo __('team_values_subtitle', [], 'The principles that guide every decision we make and every relationship we build.'); ?></p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="icon"><i class="fas fa-shield-alt"></i></div>
                    <h4><?php echo __('team_value_integrity', [], 'Integrity'); ?></h4>
                    <p><?php echo __('team_value_integrity_desc', [], 'We uphold the highest standards of honesty and transparency in every transaction.'); ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="icon"><i class="fas fa-hand-holding-heart"></i></div>
                    <h4><?php echo __('team_value_client_focus', [], 'Client Focus'); ?></h4>
                    <p><?php echo __('team_value_client_focus_desc', [], 'Your dreams and needs come first. We listen, understand, and deliver solutions that matter.'); ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="icon"><i class="fas fa-trophy"></i></div>
                    <h4><?php echo __('team_value_excellence', [], 'Excellence'); ?></h4>
                    <p><?php echo __('team_value_excellence_desc', [], 'We strive for excellence in everything we do, setting benchmarks in the real estate industry.'); ?></p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="icon"><i class="fas fa-lightbulb"></i></div>
                    <h4><?php echo __('team_value_innovation', [], 'Innovation'); ?></h4>
                    <p><?php echo __('team_value_innovation_desc', [], 'Embracing technology and innovative approaches to provide modern real estate solutions.'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <h2><?php echo __('team_cta_title', [], 'Ready to Work with Our Expert Team?'); ?></h2>
        <p><?php echo __('team_cta_subtitle', [], 'Let us help you find the perfect property. Schedule a consultation with our experts today.'); ?></p>
        <div>
            <a href="<?php echo BASE_URL; ?>/contact" class="btn-cta me-2 mb-2">
                <i class="fas fa-calendar-check me-2"></i><?php echo __('team_get_in_touch', [], 'Get in Touch'); ?>
            </a>
            <a href="tel:<?= $phoneRaw ?>" class="btn-cta mb-2" style="background:linear-gradient(135deg,#0d9488,#0f766e);">
                <i class="fas fa-phone-alt me-2"></i><?php echo __('team_call_now', [], 'Call Now'); ?>
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
            if (filter === 'all' || card.dataset.category === filter) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
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
