<?php
namespace App\Http\Controllers;

/**
 * Blog Controller
 * Manage blog posts and articles
 */
class BlogController extends BaseController
{
    /**
     * Blog index page
     */
    public function index()
    {
        $this->render('pages/blog', [
            'page_title' => 'Blog - APS Dream Home',
            'page_description' => 'Latest real estate news, tips, and insights from APS Dream Home'
        ]);
    }
    
    /**
     * Single blog post
     */
    public function show($slug)
    {
        $this->render('pages/blog-post', [
            'page_title' => 'Blog Post - APS Dream Home',
            'page_description' => 'Read our latest blog post',
            'slug' => $slug
        ]);
    }
    
    /**
     * Blog category
     */
    public function category($category)
    {
        $this->render('pages/blog-category', [
            'page_title' => ucfirst($category) . ' - Blog - APS Dream Home',
            'page_description' => 'Browse ' . $category . ' articles',
            'category' => $category
        ]);
    }
}
