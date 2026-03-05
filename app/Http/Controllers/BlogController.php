<?php

// TODO: Add proper error handling with try-catch blocks

amespace App\Http\Controllers;

require_once __DIR__ . '/BaseController.php';

/**
 * Blog Controller
 * Manage blog posts and articles
 */
class BlogController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Blog index page
     */
    public function index()
    {
        // Sample blog data
        $blog_posts = [
            [
                'id' => 1,
                'title' => 'Top 10 Areas to Invest in Gorakhpur 2024',
                'excerpt' => 'Discover the most promising residential and commercial areas in Gorakhpur for real estate investment this year.',
                'content' => 'Full content would go here...',
                'category' => 'investment',
                'featured_image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
                'created_at' => '2024-01-15',
                'read_time' => 5,
                'author' => 'Property Expert',
                'tags' => ['investment', 'gorakhpur', 'real estate']
            ],
            [
                'id' => 2,
                'title' => 'Complete Guide to Home Loans in India',
                'excerpt' => 'Everything you need to know about getting a home loan, from eligibility to documentation.',
                'content' => 'Full content would go here...',
                'category' => 'finance',
                'featured_image' => 'https://images.unsplash.com/photo-1560518883-c0ac0b5edd73?w=800',
                'created_at' => '2024-01-10',
                'read_time' => 8,
                'author' => 'Finance Advisor',
                'tags' => ['home loan', 'finance', 'guide']
            ],
            [
                'id' => 3,
                'title' => 'Villas vs Apartments: Which is Better?',
                'excerpt' => 'A comprehensive comparison to help you decide between villas and apartments based on your lifestyle and budget.',
                'content' => 'Full content would go here...',
                'category' => 'buying-guide',
                'featured_image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800',
                'created_at' => '2024-01-05',
                'read_time' => 6,
                'author' => 'Real Estate Consultant',
                'tags' => ['villas', 'apartments', 'comparison']
            ],
            [
                'id' => 4,
                'title' => '5 Tips for First-Time Home Buyers',
                'excerpt' => 'Essential tips and tricks for first-time home buyers to make the right investment decisions.',
                'content' => 'Full content would go here...',
                'category' => 'buying-guide',
                'featured_image' => 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=800',
                'created_at' => '2024-01-01',
                'read_time' => 4,
                'author' => 'Property Advisor',
                'tags' => ['first-time buyer', 'tips', 'home buying']
            ],
            [
                'id' => 5,
                'title' => 'Real Estate Market Trends 2024',
                'excerpt' => 'Analysis of current market trends and predictions for the real estate sector in 2024.',
                'content' => 'Full content would go here...',
                'category' => 'market-trends',
                'featured_image' => 'https://images.unsplash.com/photo-1467987509530-3c583bb6b684?w=800',
                'created_at' => '2023-12-28',
                'read_time' => 7,
                'author' => 'Market Analyst',
                'tags' => ['market trends', '2024', 'predictions']
            ],
            [
                'id' => 6,
                'title' => 'How to Choose the Right Location for Your Dream Home',
                'excerpt' => 'Factors to consider when selecting the perfect location for your new home.',
                'content' => 'Full content would go here...',
                'category' => 'buying-guide',
                'featured_image' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=800',
                'created_at' => '2023-12-25',
                'read_time' => 5,
                'author' => 'Location Expert',
                'tags' => ['location', 'home buying', 'selection']
            ]
        ];

        $categories = [
            ['category' => 'investment', 'name' => 'Investment', 'count' => 15],
            ['category' => 'finance', 'name' => 'Finance', 'count' => 12],
            ['category' => 'buying-guide', 'name' => 'Buying Guide', 'count' => 18],
            ['category' => 'market-trends', 'name' => 'Market Trends', 'count' => 8],
            ['category' => 'legal', 'name' => 'Legal', 'count' => 6]
        ];

        $this->render('pages/blog', [
            'page_title' => 'Blog - APS Dream Home',
            'page_description' => 'Latest real estate news, tips, and insights from APS Dream Home',
            'blog_posts' => $blog_posts,
            'categories' => $categories
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
