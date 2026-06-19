<?php
/**
 * Blog Category Page
 * Blog posts by category
 */
?>

<!-- Blog Category Hero -->
<section class="hero-section bg-gradient-info text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-4"><?php echo ucfirst($category ?? __('blog_category', [], 'Category')); ?> - <?php echo __('blog', [], 'Blog'); ?></h1>
                <p class="lead mb-0"><?php echo __('blog_browse_category', [], 'Browse'); ?> <?php echo $category ?? __('blog_category_lowercase', [], 'category'); ?> <?php echo __('blog_articles', [], 'articles'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Blog Category Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="blog-posts">
                    <h2><?php echo __('blog_latest_posts_in', [], 'Latest Posts in'); ?> <?php echo ucfirst($category ?? __('blog_category', [], 'Category')); ?></h2>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card aps-cp-card">
                                <div class="card-body aps-cp-card-body">
                                    <h5><?php echo __('blog_post_title_1', [], 'Blog Post Title 1'); ?></h5>
                                    <p class="text-muted"><?php echo __('blog_posted_on', [], 'Posted on:'); ?> <?php echo date('F j, Y'); ?></p>
                                    <p><?php echo __('blog_excerpt_placeholder', [], 'Excerpt of blog post...'); ?></p>
                                    <a href="#" class="btn btn-primary"><?php echo __('blog_read_more', [], 'Read More'); ?></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card aps-cp-card">
                                <div class="card-body aps-cp-card-body">
                                    <h5><?php echo __('blog_post_title_2', [], 'Blog Post Title 2'); ?></h5>
                                    <p class="text-muted"><?php echo __('blog_posted_on', [], 'Posted on:'); ?> <?php echo date('F j, Y'); ?></p>
                                    <p><?php echo __('blog_excerpt_placeholder', [], 'Excerpt of blog post...'); ?></p>
                                    <a href="#" class="btn btn-primary"><?php echo __('blog_read_more', [], 'Read More'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sidebar">
                    <h4><?php echo __('blog_categories', [], 'Categories'); ?></h4>
                    <ul class="list-unstyled">
                        <li><a href="#"><?php echo __('blog_cat_real_estate', [], 'Real Estate'); ?></a></li>
                        <li><a href="#"><?php echo __('blog_cat_property_tips', [], 'Property Tips'); ?></a></li>
                        <li><a href="#"><?php echo __('blog_cat_market_news', [], 'Market News'); ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
