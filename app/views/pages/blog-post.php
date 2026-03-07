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
                <h1 class="display-4 fw-bold mb-4">Blog Post</h1>
                <p class="lead mb-0">Read our latest blog post</p>
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
                    <h2>Blog Post Title</h2>
                    <p class="text-muted">Posted on: <?php echo date('F j, Y'); ?></p>
                    <div class="blog-content">
                        <p>Blog post content will be displayed here...</p>
                    </div>
                </article>
            </div>
            <div class="col-lg-4">
                <div class="sidebar">
                    <h4>Recent Posts</h4>
                    <ul class="list-unstyled">
                        <li><a href="#">Recent Post 1</a></li>
                        <li><a href="#">Recent Post 2</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>
