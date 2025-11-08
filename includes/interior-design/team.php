<!-- Team Section -->
<section id="team" class="team-section bg-light">
    <div class="container">
        <div class="section-header text-center" data-aos="fade-up">
            <h2>Our Design Team</h2>
            <p>Meet our talented interior designers</p>
        </div>

        <div class="row">
            <?php foreach ($team_members as $member): ?>
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <div class="team-member">
                    <div class="member-img">
                        <img src="<?php echo htmlspecialchars($member['image']); ?>" 
                             alt="<?php echo htmlspecialchars($member['name']); ?>" 
                             class="img-fluid" 
                             loading="lazy">
                        <div class="social">
                            <?php if (!empty($member['linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" target="_blank">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($member['instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($member['instagram']); ?>" target="_blank">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($member['pinterest'])): ?>
                            <a href="<?php echo htmlspecialchars($member['pinterest']); ?>" target="_blank">
                                <i class="fab fa-pinterest"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="member-info">
                        <h4><?php echo htmlspecialchars($member['name']); ?></h4>
                        <span><?php echo htmlspecialchars($member['position']); ?></span>
                        <p><?php echo htmlspecialchars($member['bio']); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>