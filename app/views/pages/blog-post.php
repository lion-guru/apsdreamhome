<?php
/**
 * Blog Post Page
 * Individual blog post display
 */
?>

<!-- Blog Post Hero -->
<section class="hero-section bg-gradient-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4"><?php echo __('blog_post_page_title', [], 'Blog Post'); ?></h1>
                <p class="lead mb-0"><?php echo __('blog_read_latest', [], 'Read our latest blog post'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Blog Post Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <article class="blog-post">
                    <h2><?php echo __('blog_post_title_placeholder', [], 'Blog Post Title'); ?></h2>
                    <p class="text-muted"><?php echo __('blog_posted_on', [], 'Posted on:'); ?> <?php echo date('F j, Y'); ?></p>
                    <div class="blog-content">
                        <p><?php echo __('blog_content_placeholder', [], 'Blog post content will be displayed here...'); ?></p>
                    </div>
                </article>
            </div>
            <div class="col-lg-4">
                <div class="sidebar">
                    <h4><?php echo __('blog_recent_posts', [], 'Recent Posts'); ?></h4>
                    <ul class="list-unstyled">
                        <li><a href="#"><?php echo __('blog_recent_post_1', [], 'Recent Post 1'); ?></a></li>
                        <li><a href="#"><?php echo __('blog_recent_post_2', [], 'Recent Post 2'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
