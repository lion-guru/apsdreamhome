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
                <h1 class="display-4 fw-bold mb-4"><?php echo ucfirst($category ?? 'Category'); ?> - Blog</h1>
                <p class="lead mb-0">Browse <?php echo $category ?? 'category'; ?> articles</p>
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
                    <h2>Latest Posts in <?php echo ucfirst($category ?? 'Category'); ?></h2>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Blog Post Title 1</h5>
                                    <p class="text-muted">Posted on: <?php echo date('F j, Y'); ?></p>
                                    <p>Excerpt of blog post...</p>
                                    <a href="#" class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Blog Post Title 2</h5>
                                    <p class="text-muted">Posted on: <?php echo date('F j, Y'); ?></p>
                                    <p>Excerpt of blog post...</p>
                                    <a href="#" class="btn btn-primary">Read More</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="sidebar">
                    <h4>Categories</h4>
                    <ul class="list-unstyled">
                        <li><a href="#">Real Estate</a></li>
                        <li><a href="#">Property Tips</a></li>
                        <li><a href="#">Market News</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
