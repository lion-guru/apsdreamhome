<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\BaseController;

/**
 * PageController Controller
 * Handles PageController related operations
 */
class PageController extends BaseController
{
    /**
     * Home method
     * @return void
     */
    public function home()
    {
        // TODO: Implement home functionality
        return $this->view('home');
    }
    
    /**
     * About method
     * @return void
     */
    public function about()
    {
        $this->data = [
            'title' => 'About Us - APS Dream Home',
            'description' => 'Learn about APS Dream Home - Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.',
            'company_info' => [
                'name' => 'APS Dream Homes Pvt Ltd',
                'established' => '2017',
                'experience' => '8+ Years',
                'projects' => '50+',
                'properties' => '500+',
                'happy_families' => '2000+',
                'registration_no' => 'U70109UP2022PTC163047'
            ],
            'mission' => 'To provide transparent and hassle-free real estate services with a focus on customer satisfaction and quality construction.',
            'vision' => 'To become the most trusted real estate developer in Uttar Pradesh by delivering excellence in every project.',
            'values' => [
                'Transparency',
                'Quality',
                'Customer Satisfaction',
                'Integrity',
                'Innovation'
            ],
            'team' => [
                (object)[
                    'name' => 'Amit Kumar Singh',
                    'position' => 'Managing Director',
                    'experience' => '15+ Years',
                    'description' => 'Leading the company with vision and expertise in real estate development.'
                ],
                (object)[
                    'name' => 'Priya Singh',
                    'position' => 'Operations Head',
                    'experience' => '10+ Years',
                    'description' => 'Managing day-to-day operations with focus on efficiency and quality.'
                ],
                (object)[
                    'name' => 'Rahul Verma',
                    'position' => 'Technical Director',
                    'experience' => '12+ Years',
                    'description' => 'Ensuring technical excellence and innovation in construction.'
                ]
            ]
        ];
        
        $this->render('about/index', $this->data, 'layouts/base');
    }

    /**
     * Contact method
     * @return void
     */
    public function contact()
    {
        // TODO: Implement contact functionality
        return $this->view('contact');
    }

    /**
     * Properties method
     * @return void
     */
    public function properties()
    {
        // TODO: Implement properties functionality
        return $this->view('properties');
    }

    /**
     * Services method
     * @return void
     */
    public function services()
    {
        // TODO: Implement services functionality
        return $this->view('services');
    }

    /**
     * Resell method
     * @return void
     */
    public function resell()
    {
        $data = [
            'page_title' => 'Resale Properties - APS Dream Home',
            'page_description' => 'Find the best resale properties in Gorakhpur, Lucknow and across Uttar Pradesh. Verified properties with clear documentation.',
            'resale_properties' => [
                [
                    'id' => 1,
                    'title' => '3BHK Apartment in Gomti Nagar',
                    'location' => 'Gomti Nagar, Lucknow',
                    'price' => 6500000,
                    'original_price' => 7500000,
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => 1450,
                    'age' => 5,
                    'type' => 'apartment',
                    'status' => 'available',
                    'description' => 'Well-maintained 3BHK apartment in prime location with modern amenities.',
                    'features' => ['Parking', 'Security', 'Power Backup', 'Lift'],
                    'images' => ['resale1.jpg', 'resale2.jpg'],
                    'listed_date' => '2026-02-15',
                    'views' => 245
                ],
                [
                    'id' => 2,
                    'title' => 'Independent House in Aliganj',
                    'location' => 'Aliganj, Lucknow',
                    'price' => 8500000,
                    'original_price' => 9500000,
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area' => 2200,
                    'age' => 8,
                    'type' => 'house',
                    'status' => 'available',
                    'description' => 'Spacious independent house with garden and parking space.',
                    'features' => ['Garden', 'Parking', 'Security', 'Water Supply'],
                    'images' => ['resale3.jpg', 'resale4.jpg'],
                    'listed_date' => '2026-02-20',
                    'views' => 189
                ],
                [
                    'id' => 3,
                    'title' => '2BHK Flat in Indira Nagar',
                    'location' => 'Indira Nagar, Lucknow',
                    'price' => 4200000,
                    'original_price' => 4800000,
                    'bedrooms' => 2,
                    'bathrooms' => 2,
                    'area' => 980,
                    'age' => 3,
                    'type' => 'apartment',
                    'status' => 'available',
                    'description' => 'Modern 2BHK flat with excellent connectivity and amenities.',
                    'features' => ['Parking', 'Security', 'Lift', 'Community Hall'],
                    'images' => ['resale5.jpg', 'resale6.jpg'],
                    'listed_date' => '2026-02-25',
                    'views' => 156
                ]
            ],
            'resale_stats' => [
                'total_properties' => 150,
                'avg_savings' => 12,
                'verified_properties' => 145,
                'happy_customers' => 2800
            ],
            'resale_benefits' => [
                [
                    'icon' => 'fa-check-circle',
                    'title' => 'Legal Verification',
                    'description' => 'All resale properties are legally verified and documentation is clear.'
                ],
                [
                    'icon' => 'fa-money-bill',
                    'title' => 'Best Prices',
                    'description' => 'Get the best deals on resale properties with transparent pricing.'
                ],
                [
                    'icon' => 'fa-home',
                    'title' => 'Immediate Possession',
                    'description' => 'Most resale properties are ready for immediate possession.'
                ],
                [
                    'icon' => 'fa-shield-alt',
                    'title' => 'Quality Assurance',
                    'description' => 'Thorough inspection ensures quality and condition of properties.'
                ]
            ],
            'process_steps' => [
                [
                    'step' => 1,
                    'title' => 'Property Selection',
                    'description' => 'Choose from our verified list of resale properties.'
                ],
                [
                    'step' => 2,
                    'title' => 'Site Visit',
                    'description' => 'Visit the property with our expert team.'
                ],
                [
                    'step' => 3,
                    'title' => 'Documentation',
                    'description' => 'Complete all legal documentation smoothly.'
                ],
                [
                    'step' => 4,
                    'title' => 'Registration',
                    'description' => 'Final registration and possession handover.'
                ]
            ]
        ];

        return $this->render('resell/index', $data, 'layouts/base');
    }

    /**
     * Gallery method
     * @return void
     */
    public function gallery()
    {
        $data = [
            'page_title' => 'Gallery - APS Dream Home',
            'page_description' => 'Explore our completed projects, property showcases, and construction quality through our extensive gallery.',
            'gallery_categories' => [
                [
                    'id' => 1,
                    'name' => 'Completed Projects',
                    'description' => 'Showcase of our successfully completed residential and commercial projects',
                    'image_count' => 45,
                    'cover_image' => 'gallery/completed-projects-cover.jpg'
                ],
                [
                    'id' => 2,
                    'name' => 'Ongoing Projects',
                    'description' => 'Current projects under construction with progress updates',
                    'image_count' => 28,
                    'cover_image' => 'gallery/ongoing-projects-cover.jpg'
                ],
                [
                    'id' => 3,
                    'name' => 'Property Interiors',
                    'description' => 'Interior designs and finishes of our premium properties',
                    'image_count' => 67,
                    'cover_image' => 'gallery/interiors-cover.jpg'
                ],
                [
                    'id' => 4,
                    'name' => 'Amenities & Facilities',
                    'description' => 'Modern amenities and recreational facilities in our projects',
                    'image_count' => 34,
                    'cover_image' => 'gallery/amenities-cover.jpg'
                ],
                [
                    'id' => 5,
                    'name' => 'Happy Families',
                    'description' => 'Testimonials and moments with our satisfied customers',
                    'image_count' => 52,
                    'cover_image' => 'gallery/happy-families-cover.jpg'
                ],
                [
                    'id' => 6,
                    'name' => 'Construction Quality',
                    'description' => 'Behind the scenes of our quality construction process',
                    'image_count' => 23,
                    'cover_image' => 'gallery/construction-cover.jpg'
                ]
            ],
            'featured_images' => [
                [
                    'id' => 1,
                    'title' => 'APS Gardenia - Premium Apartments',
                    'category' => 'Completed Projects',
                    'image' => 'gallery/aps-gardenia-exterior.jpg',
                    'thumbnail' => 'gallery/thumbnails/aps-gardenia-exterior-thumb.jpg',
                    'description' => 'Beautiful exterior of our flagship residential project',
                    'date' => '2026-02-15',
                    'views' => 1250
                ],
                [
                    'id' => 2,
                    'title' => 'Modern Living Room Design',
                    'category' => 'Property Interiors',
                    'image' => 'gallery/modern-living-room.jpg',
                    'thumbnail' => 'gallery/thumbnails/modern-living-room-thumb.jpg',
                    'description' => 'Spacious and elegantly designed living area',
                    'date' => '2026-02-10',
                    'views' => 980
                ],
                [
                    'id' => 3,
                    'title' => 'Swimming Pool & Recreation Area',
                    'category' => 'Amenities & Facilities',
                    'image' => 'gallery/swimming-pool.jpg',
                    'thumbnail' => 'gallery/thumbnails/swimming-pool-thumb.jpg',
                    'description' => 'Premium recreational facilities for residents',
                    'date' => '2026-02-08',
                    'views' => 1450
                ],
                [
                    'id' => 4,
                    'title' => 'Happy Family - New Homeowners',
                    'category' => 'Happy Families',
                    'image' => 'gallery/happy-family-1.jpg',
                    'thumbnail' => 'gallery/thumbnails/happy-family-1-thumb.jpg',
                    'description' => 'Another happy family with their dream home',
                    'date' => '2026-02-05',
                    'views' => 890
                ],
                [
                    'id' => 5,
                    'title' => 'APS Plaza - Commercial Complex',
                    'category' => 'Completed Projects',
                    'image' => 'gallery/aps-plaza-commercial.jpg',
                    'thumbnail' => 'gallery/thumbnails/aps-plaza-commercial-thumb.jpg',
                    'description' => 'Modern commercial spaces in prime location',
                    'date' => '2026-02-01',
                    'views' => 1100
                ],
                [
                    'id' => 6,
                    'title' => 'Construction in Progress',
                    'category' => 'Construction Quality',
                    'image' => 'gallery/construction-progress.jpg',
                    'thumbnail' => 'gallery/thumbnails/construction-progress-thumb.jpg',
                    'description' => 'Quality construction process with modern techniques',
                    'date' => '2026-01-28',
                    'views' => 650
                ]
            ],
            'gallery_stats' => [
                'total_images' => 249,
                'total_categories' => 6,
                'completed_projects' => 45,
                'happy_customers' => 2800
            ],
            'recent_updates' => [
                [
                    'title' => 'APS Gardenia Phase 2 Completion',
                    'date' => '2026-02-20',
                    'images_added' => 15,
                    'category' => 'Completed Projects'
                ],
                [
                    'title' => 'New Interior Design Gallery',
                    'date' => '2026-02-18',
                    'images_added' => 22,
                    'category' => 'Property Interiors'
                ],
                [
                    'title' => 'Customer Testimonials Updated',
                    'date' => '2026-02-15',
                    'images_added' => 8,
                    'category' => 'Happy Families'
                ]
            ]
        ];

        return $this->render('gallery/index', $data, 'layouts/base');
    }

    /**
     * Blog method
     * @return void
     */
    public function blog()
    {
        $data = [
            'page_title' => 'Blog - APS Dream Home',
            'page_description' => 'Stay updated with the latest real estate trends, property tips, and market insights from our expert team.',
            'blog_categories' => [
                [
                    'id' => 1,
                    'name' => 'Property Tips',
                    'slug' => 'property-tips',
                    'post_count' => 24,
                    'description' => 'Expert advice on buying, selling, and maintaining properties'
                ],
                [
                    'id' => 2,
                    'name' => 'Market Trends',
                    'slug' => 'market-trends',
                    'post_count' => 18,
                    'description' => 'Latest real estate market analysis and predictions'
                ],
                [
                    'id' => 3,
                    'name' => 'Investment Guide',
                    'slug' => 'investment-guide',
                    'post_count' => 15,
                    'description' => 'Smart investment strategies for real estate'
                ],
                [
                    'id' => 4,
                    'name' => 'Home Improvement',
                    'slug' => 'home-improvement',
                    'post_count' => 22,
                    'description' => 'Tips and ideas for home renovation and improvement'
                ],
                [
                    'id' => 5,
                    'name' => 'Legal & Documentation',
                    'slug' => 'legal-documentation',
                    'post_count' => 12,
                    'description' => 'Understanding legal aspects of real estate transactions'
                ]
            ],
            'featured_posts' => [
                [
                    'id' => 1,
                    'title' => 'Top 10 Things to Check Before Buying a Property in 2026',
                    'slug' => 'top-10-things-check-buying-property-2026',
                    'excerpt' => 'A comprehensive guide to help you make informed decisions when purchasing your dream home. From legal verification to structural inspection, we cover everything you need to know.',
                    'content' => 'Buying a property is one of the biggest financial decisions you\'ll ever make. In this comprehensive guide, we walk you through the essential checks and considerations before making your purchase...',
                    'author' => [
                        'name' => 'Rajesh Kumar',
                        'avatar' => 'authors/rajesh-kumar.jpg',
                        'role' => 'Senior Property Advisor'
                    ],
                    'category' => 'Property Tips',
                    'featured_image' => 'blog/featured-1.jpg',
                    'published_date' => '2026-02-20',
                    'updated_date' => '2026-02-22',
                    'reading_time' => '8 min read',
                    'views' => 2450,
                    'likes' => 89,
                    'comments' => 23,
                    'tags' => ['property-buying', 'real-estate-tips', 'home-purchase', 'investment'],
                    'featured' => true
                ],
                [
                    'id' => 2,
                    'title' => 'Lucknow Real Estate Market: Trends & Predictions for 2026',
                    'slug' => 'lucknow-real-estate-market-trends-predictions-2026',
                    'excerpt' => 'An in-depth analysis of Lucknow\'s property market, including emerging hotspots, price trends, and investment opportunities for homebuyers and investors.',
                    'content' => 'The Lucknow real estate market has shown remarkable resilience and growth over the past few years. Our comprehensive analysis reveals key trends that are shaping the future of property investments in the city...',
                    'author' => [
                        'name' => 'Priya Sharma',
                        'avatar' => 'authors/priya-sharma.jpg',
                        'role' => 'Market Analyst'
                    ],
                    'category' => 'Market Trends',
                    'featured_image' => 'blog/featured-2.jpg',
                    'published_date' => '2026-02-18',
                    'updated_date' => '2026-02-19',
                    'reading_time' => '6 min read',
                    'views' => 1890,
                    'likes' => 67,
                    'comments' => 15,
                    'tags' => ['lucknow', 'market-trends', 'investment', 'property-prices'],
                    'featured' => true
                ],
                [
                    'id' => 3,
                    'title' => 'Home Renovation Ideas That Increase Property Value',
                    'slug' => 'home-renovation-ideas-increase-property-value',
                    'excerpt' => 'Discover the most impactful home improvement projects that offer the best return on investment and significantly boost your property\'s market value.',
                    'content' => 'Strategic home renovations can dramatically increase your property\'s value while improving your living experience. We\'ve compiled the most effective renovation ideas that offer excellent ROI...',
                    'author' => [
                        'name' => 'Amit Verma',
                        'avatar' => 'authors/amit-verma.jpg',
                        'role' => 'Interior Designer'
                    ],
                    'category' => 'Home Improvement',
                    'featured_image' => 'blog/featured-3.jpg',
                    'published_date' => '2026-02-15',
                    'updated_date' => '2026-02-16',
                    'reading_time' => '7 min read',
                    'views' => 1675,
                    'likes' => 92,
                    'comments' => 31,
                    'tags' => ['renovation', 'home-improvement', 'property-value', 'roi'],
                    'featured' => true
                ]
            ],
            'recent_posts' => [
                [
                    'id' => 4,
                    'title' => 'Understanding RERA: A Homebuyer\'s Complete Guide',
                    'slug' => 'understanding-rera-homebuyers-complete-guide',
                    'excerpt' => 'Everything you need to know about RERA and how it protects homebuyers\' interests in real estate transactions.',
                    'author' => 'Rajesh Kumar',
                    'category' => 'Legal & Documentation',
                    'published_date' => '2026-02-14',
                    'reading_time' => '5 min read',
                    'thumbnail' => 'blog/recent-1.jpg',
                    'views' => 890
                ],
                [
                    'id' => 5,
                    'title' => 'Vastu Tips for New Home Construction',
                    'slug' => 'vastu-tips-new-home-construction',
                    'excerpt' => 'Traditional Vastu principles to consider when building your dream home for positive energy and prosperity.',
                    'author' => 'Priya Sharma',
                    'category' => 'Property Tips',
                    'published_date' => '2026-02-12',
                    'reading_time' => '4 min read',
                    'thumbnail' => 'blog/recent-2.jpg',
                    'views' => 756
                ],
                [
                    'id' => 6,
                    'title' => 'Smart Home Features That Add Value to Your Property',
                    'slug' => 'smart-home-features-add-value-property',
                    'excerpt' => 'Modern smart home technologies that not only enhance convenience but also increase your property\'s market value.',
                    'author' => 'Amit Verma',
                    'category' => 'Home Improvement',
                    'published_date' => '2026-02-10',
                    'reading_time' => '6 min read',
                    'thumbnail' => 'blog/recent-3.jpg',
                    'views' => 623
                ],
                [
                    'id' => 7,
                    'title' => 'Tax Benefits of Home Loans in India 2026',
                    'slug' => 'tax-benefits-home-loans-india-2026',
                    'excerpt' => 'Complete guide to tax deductions and benefits available on home loans under various sections of the Income Tax Act.',
                    'author' => 'Rajesh Kumar',
                    'category' => 'Investment Guide',
                    'published_date' => '2026-02-08',
                    'reading_time' => '7 min read',
                    'thumbnail' => 'blog/recent-4.jpg',
                    'views' => 1120
                ]
            ],
            'popular_tags' => [
                ['name' => 'property-buying', 'count' => 45],
                ['name' => 'investment', 'count' => 38],
                ['name' => 'home-loan', 'count' => 32],
                ['name' => 'real-estate-tips', 'count' => 28],
                ['name' => 'market-trends', 'count' => 25],
                ['name' => 'renovation', 'count' => 22],
                ['name' => 'vastu', 'count' => 18],
                ['name' => 'tax-benefits', 'count' => 15]
            ],
            'blog_stats' => [
                'total_posts' => 91,
                'total_categories' => 5,
                'total_authors' => 8,
                'total_views' => 45670,
                'total_comments' => 234
            ]
        ];

        return $this->render('blog/index', $data, 'layouts/base');
    }

}
