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

    /**
     * Careers method
     * @return void
     */
    public function careers()
    {
        $data = [
            'page_title' => 'Careers - APS Dream Home',
            'page_description' => 'Join our growing team and build a rewarding career in real estate. Explore current job openings and internship opportunities.',
            'company_culture' => [
                'title' => 'Why Work With APS Dream Home?',
                'description' => 'At APS Dream Home, we believe in nurturing talent and providing growth opportunities. Join a team that values innovation, integrity, and excellence.',
                'values' => [
                    [
                        'icon' => 'fa-users',
                        'title' => 'Team Collaboration',
                        'description' => 'Work in a supportive environment where teamwork and collaboration drive success.'
                    ],
                    [
                        'icon' => 'fa-chart-line',
                        'title' => 'Growth Opportunities',
                        'description' => 'Continuous learning and career advancement with structured development programs.'
                    ],
                    [
                        'icon' => 'fa-trophy',
                        'title' => 'Recognition & Rewards',
                        'description' => 'Your hard work and dedication are recognized through competitive compensation and awards.'
                    ],
                    [
                        'icon' => 'fa-balance-scale',
                        'title' => 'Work-Life Balance',
                        'description' => 'We prioritize employee well-being with flexible work arrangements and comprehensive benefits.'
                    ]
                ]
            ],
            'current_openings' => [
                [
                    'id' => 1,
                    'title' => 'Senior Real Estate Executive',
                    'department' => 'Sales',
                    'location' => 'Gorakhpur',
                    'type' => 'Full-time',
                    'experience' => '3-5 years',
                    'salary' => '₹4,50,000 - ₹6,00,000 per annum',
                    'posted_date' => '2026-02-20',
                    'deadline' => '2026-03-15',
                    'description' => 'We are looking for an experienced Real Estate Executive to join our dynamic sales team.',
                    'responsibilities' => [
                        'Generate leads and convert them into sales',
                        'Provide property consultations to clients',
                        'Maintain relationships with existing clients',
                        'Achieve monthly sales targets',
                        'Coordinate with legal and finance teams'
                    ],
                    'requirements' => [
                        'Bachelor\'s degree in Business or related field',
                        '3+ years of experience in real estate sales',
                        'Excellent communication and negotiation skills',
                        'Knowledge of local property market',
                        'Valid driving license'
                    ],
                    'vacancies' => 2,
                    'urgent' => true
                ],
                [
                    'id' => 2,
                    'title' => 'Marketing Manager',
                    'department' => 'Marketing',
                    'location' => 'Lucknow',
                    'type' => 'Full-time',
                    'experience' => '4-7 years',
                    'salary' => '₹6,00,000 - ₹8,00,000 per annum',
                    'posted_date' => '2026-02-18',
                    'deadline' => '2026-03-10',
                    'description' => 'Lead our marketing initiatives and drive brand growth across multiple channels.',
                    'responsibilities' => [
                        'Develop and execute marketing strategies',
                        'Manage digital marketing campaigns',
                        'Coordinate with sales team for lead generation',
                        'Analyze market trends and competitor activities',
                        'Manage marketing budget and ROI'
                    ],
                    'requirements' => [
                        'MBA in Marketing or related field',
                        '4+ years of marketing experience',
                        'Strong analytical and strategic thinking skills',
                        'Experience with digital marketing tools',
                        'Creative mindset with leadership qualities'
                    ],
                    'vacancies' => 1,
                    'urgent' => false
                ],
                [
                    'id' => 3,
                    'title' => 'Site Engineer',
                    'department' => 'Construction',
                    'location' => 'Gorakhpur',
                    'type' => 'Full-time',
                    'experience' => '2-4 years',
                    'salary' => '₹3,50,000 - ₹4,50,000 per annum',
                    'posted_date' => '2026-02-15',
                    'deadline' => '2026-03-20',
                    'description' => 'Oversee construction projects and ensure quality standards are maintained.',
                    'responsibilities' => [
                        'Monitor construction progress and quality',
                        'Coordinate with contractors and vendors',
                        'Ensure compliance with safety regulations',
                        'Prepare daily progress reports',
                        'Resolve technical issues on site'
                    ],
                    'requirements' => [
                        'B.E. in Civil Engineering',
                        '2+ years of site supervision experience',
                        'Knowledge of construction techniques',
                        'Strong problem-solving skills',
                        'Willingness to travel to multiple sites'
                    ],
                    'vacancies' => 3,
                    'urgent' => false
                ],
                [
                    'id' => 4,
                    'title' => 'Customer Relationship Executive',
                    'department' => 'Customer Service',
                    'location' => 'Gorakhpur',
                    'type' => 'Full-time',
                    'experience' => '1-3 years',
                    'salary' => '₹2,40,000 - ₹3,00,000 per annum',
                    'posted_date' => '2026-02-12',
                    'deadline' => '2026-03-05',
                    'description' => 'Provide excellent customer service and maintain client relationships.',
                    'responsibilities' => [
                        'Handle customer inquiries and complaints',
                        'Provide after-sales support',
                        'Maintain customer database',
                        'Coordinate with sales and operations teams',
                        'Ensure customer satisfaction'
                    ],
                    'requirements' => [
                        'Graduate in any discipline',
                        '1+ year of customer service experience',
                        'Excellent communication skills',
                        'Proficiency in MS Office',
                        'Patient and empathetic attitude'
                    ],
                    'vacancies' => 2,
                    'urgent' => true
                ]
            ],
            'internship_programs' => [
                [
                    'title' => 'Sales Intern',
                    'duration' => '3 months',
                    'stipend' => '₹8,000 per month',
                    'description' => 'Learn real estate sales processes and gain hands-on experience.'
                ],
                [
                    'title' => 'Marketing Intern',
                    'duration' => '3 months',
                    'stipend' => '₹7,000 per month',
                    'description' => 'Assist in marketing campaigns and social media management.'
                ],
                [
                    'title' => 'HR Intern',
                    'duration' => '2 months',
                    'stipend' => '₹6,000 per month',
                    'description' => 'Support HR operations and recruitment processes.'
                ]
            ],
            'benefits' => [
                [
                    'icon' => 'fa-heartbeat',
                    'title' => 'Health Insurance',
                    'description' => 'Comprehensive medical coverage for you and your family'
                ],
                [
                    'icon' => 'fa-piggy-bank',
                    'title' => 'Provident Fund',
                    'description' => 'Secure your future with our PF and gratuity benefits'
                ],
                [
                    'icon' => 'fa-graduation-cap',
                    'title' => 'Training & Development',
                    'description' => 'Continuous learning opportunities and skill development programs'
                ],
                [
                    'icon' => 'fa-calendar-alt',
                    'title' => 'Paid Leave',
                    'description' => 'Generous annual leave and holiday policy'
                ],
                [
                    'icon' => 'fa-car',
                    'title' => 'Transport Allowance',
                    'description' => 'Monthly transport allowance for commuting convenience'
                ],
                [
                    'icon' => 'fa-mobile-alt',
                    'title' => 'Communication Allowance',
                    'description' => 'Monthly mobile and internet reimbursement'
                ]
            ],
            'recruitment_process' => [
                [
                    'step' => 1,
                    'title' => 'Application',
                    'description' => 'Submit your application through our career portal or email'
                ],
                [
                    'step' => 2,
                    'title' => 'Screening',
                    'description' => 'Our HR team reviews your application and shortlists candidates'
                ],
                [
                    'step' => 3,
                    'title' => 'Interview',
                    'description' => 'Multiple rounds of interviews with relevant stakeholders'
                ],
                [
                    'step' => 4,
                    'title' => 'Offer',
                    'description' => 'Successful candidates receive offer letters and join our team'
                ]
            ],
            'testimonials' => [
                [
                    'name' => 'Rahul Verma',
                    'position' => 'Senior Sales Executive',
                    'quote' => 'APS Dream Home has provided me with excellent growth opportunities and a supportive work environment.',
                    'duration' => '3 years'
                ],
                [
                    'name' => 'Priya Singh',
                    'position' => 'Marketing Manager',
                    'quote' => 'The company culture here encourages innovation and creativity. I love working with this team!',
                    'duration' => '2 years'
                ],
                [
                    'name' => 'Amit Kumar',
                    'position' => 'Site Engineer',
                    'quote' => 'Great learning opportunities and excellent management support. Proud to be part of APS Dream Home.',
                    'duration' => '1.5 years'
                ]
            ],
            'career_stats' => [
                'total_employees' => 150,
                'new_hires_this_year' => 45,
                'employee_satisfaction' => 92,
                'internal_promotions' => 28
            ]
        ];

        return $this->render('careers/index', $data, 'layouts/base');
    }

    /**
     * Team method
     * @return void
     */
    public function team()
    {
        $data = [
            'page_title' => 'Our Team - APS Dream Home',
            'page_description' => 'Meet the dedicated professionals behind APS Dream Home. Our experienced team is committed to delivering excellence in real estate services.',
            'leadership_team' => [
                [
                    'id' => 1,
                    'name' => 'Amit Kumar Singh',
                    'position' => 'Founder & Managing Director',
                    'bio' => 'With over 15 years of experience in real estate development, Amit founded APS Dream Home with a vision to transform the property landscape in Uttar Pradesh. His leadership and strategic thinking have been instrumental in the company\'s growth.',
                    'experience' => '15+ Years',
                    'education' => 'MBA - IIM Lucknow, B.Tech - IIT Kanpur',
                    'achievements' => [
                        'Developed 500+ properties across UP',
                        'Awarded "Best Real Estate Developer 2023"',
                        'Led 50+ successful project completions'
                    ],
                    'image' => 'team/amit-singh.jpg',
                    'email' => 'amit.singh@apsdreamhomes.com',
                    'phone' => '+91-9277121101',
                    'linkedin' => 'https://linkedin.com/in/amit-kumar-singh-aps'
                ],
                [
                    'id' => 2,
                    'name' => 'Priya Sharma',
                    'position' => 'Chief Operating Officer',
                    'bio' => 'Priya brings extensive operational expertise to APS Dream Home. Her focus on process optimization and customer satisfaction has helped establish the company as a trusted name in real estate.',
                    'experience' => '12+ Years',
                    'education' => 'MBA - XLRI Jamshedpur, B.Com - Delhi University',
                    'achievements' => [
                        'Streamlined operations reducing project timelines by 30%',
                        'Implemented customer-centric service models',
                        'Managed team of 100+ professionals'
                    ],
                    'image' => 'team/priya-sharma.jpg',
                    'email' => 'priya.sharma@apsdreamhomes.com',
                    'phone' => '+91-9277121102',
                    'linkedin' => 'https://linkedin.com/in/priya-sharma-aps'
                ],
                [
                    'id' => 3,
                    'name' => 'Rajesh Verma',
                    'position' => 'Chief Financial Officer',
                    'bio' => 'Rajesh oversees all financial aspects of APS Dream Home, ensuring fiscal discipline and sustainable growth. His expertise in financial planning has been crucial for the company\'s expansion.',
                    'experience' => '10+ Years',
                    'education' => 'CA - ICAI, B.Com - Lucknow University',
                    'achievements' => [
                        'Secured funding for projects worth ₹500+ crore',
                        'Implemented robust financial controls',
                        'Improved profitability by 25%'
                    ],
                    'image' => 'team/rajesh-verma.jpg',
                    'email' => 'rajesh.verma@apsdreamhomes.com',
                    'phone' => '+91-9277121103',
                    'linkedin' => 'https://linkedin.com/in/rajesh-verma-aps'
                ],
                [
                    'id' => 4,
                    'name' => 'Anjali Gupta',
                    'position' => 'Head of Marketing & Sales',
                    'bio' => 'Anjali leads the marketing and sales initiatives at APS Dream Home. Her innovative strategies and deep market understanding have significantly contributed to the company\'s market presence.',
                    'experience' => '8+ Years',
                    'education' => 'MBA - Marketing, NMIMS Mumbai',
                    'achievements' => [
                        'Increased sales by 40% year-on-year',
                        'Launched successful digital marketing campaigns',
                        'Expanded market presence to 5 new cities'
                    ],
                    'image' => 'team/anjali-gupta.jpg',
                    'email' => 'anjali.gupta@apsdreamhomes.com',
                    'phone' => '+91-9277121104',
                    'linkedin' => 'https://linkedin.com/in/anjali-gupta-aps'
                ]
            ],
            'department_heads' => [
                [
                    'id' => 5,
                    'name' => 'Vikram Singh',
                    'position' => 'Head - Construction',
                    'bio' => 'Vikram ensures quality construction and timely project delivery with his technical expertise and project management skills.',
                    'experience' => '12+ Years',
                    'education' => 'B.E. - Civil Engineering',
                    'image' => 'team/vikram-singh.jpg',
                    'email' => 'vikram.singh@apsdreamhomes.com',
                    'phone' => '+91-9277121105'
                ],
                [
                    'id' => 6,
                    'name' => 'Sneha Patel',
                    'position' => 'Head - Customer Relations',
                    'bio' => 'Sneha leads the customer service team, ensuring exceptional service and client satisfaction throughout the buying journey.',
                    'experience' => '7+ Years',
                    'education' => 'MBA - Customer Service',
                    'image' => 'team/sneha-patel.jpg',
                    'email' => 'sneha.patel@apsdreamhomes.com',
                    'phone' => '+91-9277121106'
                ],
                [
                    'id' => 7,
                    'name' => 'Rohit Kumar',
                    'position' => 'Head - Legal & Compliance',
                    'bio' => 'Rohit manages all legal aspects ensuring compliance and smooth property transactions for our clients.',
                    'experience' => '10+ Years',
                    'education' => 'LL.B. - Lucknow University',
                    'image' => 'team/rohit-kumar.jpg',
                    'email' => 'rohit.kumar@apsdreamhomes.com',
                    'phone' => '+91-9277121107'
                ],
                [
                    'id' => 8,
                    'name' => 'Kavita Singh',
                    'position' => 'Head - HR & Administration',
                    'bio' => 'Kavita oversees human resources and administrative functions, fostering a positive work environment and team growth.',
                    'experience' => '8+ Years',
                    'education' => 'MBA - HR',
                    'image' => 'team/kavita-singh.jpg',
                    'email' => 'kavita.singh@apsdreamhomes.com',
                    'phone' => '+91-9277121108'
                ]
            ],
            'team_stats' => [
                'total_members' => 150,
                'years_experience' => 100,
                'projects_completed' => 500,
                'happy_customers' => 2000
            ],
            'company_values' => [
                [
                    'icon' => 'fa-handshake',
                    'title' => 'Integrity',
                    'description' => 'We conduct business with honesty and transparency in all our dealings.'
                ],
                [
                    'icon' => 'fa-trophy',
                    'title' => 'Excellence',
                    'description' => 'We strive for the highest standards in quality and service delivery.'
                ],
                [
                    'icon' => 'fa-users',
                    'title' => 'Teamwork',
                    'description' => 'We believe in collaborative effort and mutual respect among team members.'
                ],
                [
                    'icon' => 'fa-lightbulb',
                    'title' => 'Innovation',
                    'description' => 'We embrace new ideas and innovative approaches to real estate development.'
                ]
            ],
            'join_team_info' => [
                'current_openings' => 8,
                'hiring_process' => '4-step selection process',
                'growth_opportunities' => 'Career advancement and skill development programs',
                'work_culture' => 'Supportive, collaborative, and growth-oriented environment'
            ]
        ];

        return $this->render('team/index', $data, 'layouts/base');
    }

    /**
     * Testimonials method
     * @return void
     */
    public function testimonials()
    {
        $data = [
            'page_title' => 'Testimonials - APS Dream Home',
            'page_description' => 'Read what our satisfied customers have to say about their experience with APS Dream Home. Real stories from real homeowners.',
            'customer_testimonials' => [
                [
                    'id' => 1,
                    'name' => 'Rahul Sharma',
                    'property' => '3BHK Apartment in Gomti Nagar',
                    'rating' => 5,
                    'review_date' => '2026-02-15',
                    'testimonial' => 'APS Dream Home made my dream of owning a home in Lucknow a reality. Their team was extremely professional and helped me find the perfect apartment. The entire process was smooth and transparent.',
                    'image' => 'testimonials/rahul-sharma.jpg',
                    'location' => 'Gomti Nagar, Lucknow',
                    'property_type' => 'Residential',
                    'experience_years' => '2 years'
                ],
                [
                    'id' => 2,
                    'name' => 'Priya Singh',
                    'property' => 'Commercial Space in Vibhuti Khand',
                    'rating' => 5,
                    'review_date' => '2026-01-20',
                    'testimonial' => 'I was looking for a commercial space for my business expansion, and APS Dream Home provided excellent options. Their market knowledge and negotiation skills helped me get the best deal.',
                    'image' => 'testimonials/priya-singh.jpg',
                    'location' => 'Vibhuti Khand, Gomti Nagar',
                    'property_type' => 'Commercial',
                    'experience_years' => '1 year'
                ],
                [
                    'id' => 3,
                    'name' => 'Amit Kumar',
                    'property' => '2BHK Apartment in Alambagh',
                    'rating' => 4,
                    'review_date' => '2025-12-10',
                    'testimonial' => 'Great experience with APS Dream Home! They understood my requirements perfectly and showed me properties that matched my budget. The after-sales service is also commendable.',
                    'image' => 'testimonials/amit-kumar.jpg',
                    'location' => 'Alambagh, Lucknow',
                    'property_type' => 'Residential',
                    'experience_years' => '3 years'
                ],
                [
                    'id' => 4,
                    'name' => 'Sneha Patel',
                    'property' => 'Villa in Hazratganj',
                    'rating' => 5,
                    'review_date' => '2025-11-25',
                    'testimonial' => 'Buying a villa through APS Dream Home was the best decision. Their team handled all the legal documentation and ensured a hassle-free transaction. Highly recommended!',
                    'image' => 'testimonials/sneha-patel.jpg',
                    'location' => 'Hazratganj, Lucknow',
                    'property_type' => 'Residential',
                    'experience_years' => '1.5 years'
                ],
                [
                    'id' => 5,
                    'name' => 'Vikram Verma',
                    'property' => 'Plot in Gorakhpur',
                    'rating' => 5,
                    'review_date' => '2025-10-15',
                    'testimonial' => 'I purchased a plot for building my dream house. APS Dream Home helped me with all the legal verification and ensured the property was free from any disputes. Excellent service!',
                    'image' => 'testimonials/vikram-verma.jpg',
                    'location' => 'Gorakhpur',
                    'property_type' => 'Land',
                    'experience_years' => '6 months'
                ],
                [
                    'id' => 6,
                    'name' => 'Anjali Gupta',
                    'property' => '3BHK in Gomti Nagar Extension',
                    'rating' => 4,
                    'review_date' => '2025-09-20',
                    'testimonial' => 'The team at APS Dream Home is very professional and knowledgeable. They guided me through the entire home buying process and helped me make an informed decision.',
                    'image' => 'testimonials/anjali-gupta.jpg',
                    'location' => 'Gomti Nagar Extension, Lucknow',
                    'property_type' => 'Residential',
                    'experience_years' => '8 months'
                ]
            ],
            'video_testimonials' => [
                [
                    'id' => 1,
                    'customer_name' => 'Rajesh Kumar',
                    'property' => 'Luxury Apartment in Gomti Nagar',
                    'video_url' => 'https://example.com/video1',
                    'thumbnail' => 'testimonials/video-thumb-1.jpg',
                    'duration' => '2:45',
                    'views' => 1250
                ],
                [
                    'id' => 2,
                    'customer_name' => 'Meera Singh',
                    'property' => 'Commercial Space in Lucknow',
                    'video_url' => 'https://example.com/video2',
                    'thumbnail' => 'testimonials/video-thumb-2.jpg',
                    'duration' => '3:12',
                    'views' => 890
                ],
                [
                    'id' => 3,
                    'customer_name' => 'Sanjay Verma',
                    'property' => 'Villa in Hazratganj',
                    'video_url' => 'https://example.com/video3',
                    'thumbnail' => 'testimonials/video-thumb-3.jpg',
                    'duration' => '2:30',
                    'views' => 756
                ]
            ],
            'testimonials_stats' => [
                'total_reviews' => 2500,
                'average_rating' => 4.8,
                'satisfied_customers' => 2000,
                'years_of_service' => 8
            ],
            'rating_distribution' => [
                '5_star' => 1800,
                '4_star' => 500,
                '3_star' => 150,
                '2_star' => 30,
                '1_star' => 20
            ],
            'featured_properties' => [
                [
                    'name' => 'Gomti Nagar Heights',
                    'total_reviews' => 156,
                    'average_rating' => 4.9,
                    'image' => 'properties/featured-1.jpg'
                ],
                [
                    'name' => 'Hazratganj Plaza',
                    'total_reviews' => 89,
                    'average_rating' => 4.7,
                    'image' => 'properties/featured-2.jpg'
                ],
                [
                    'name' => 'Vibhuti Khand Commercial',
                    'total_reviews' => 67,
                    'average_rating' => 4.8,
                    'image' => 'properties/featured-3.jpg'
                ]
            ]
        ];

        return $this->render('testimonials/index', $data, 'layouts/base');
    }

    /**
     * FAQ method
     * @return void
     */
    public function faq()
    {
        $data = [
            'page_title' => 'FAQ - APS Dream Home',
            'page_description' => 'Find answers to frequently asked questions about APS Dream Home, our services, properties, and the real estate buying process.',
            'faq_categories' => [
                [
                    'id' => 'general',
                    'name' => 'General Questions',
                    'icon' => 'fa-question-circle',
                    'faqs' => [
                        [
                            'question' => 'What is APS Dream Home?',
                            'answer' => 'APS Dream Home is a leading real estate developer and property consultancy in Uttar Pradesh, specializing in residential and commercial properties. With over 8 years of experience, we help clients find their dream homes and investment properties.'
                        ],
                        [
                            'question' => 'Where are your properties located?',
                            'answer' => 'We have properties across major locations in Uttar Pradesh including Lucknow, Gorakhpur, Gomti Nagar, Hazratganj, Vibhuti Khand, Alambagh, and other prime areas. We are continuously expanding to new locations.'
                        ],
                        [
                            'question' => 'How long have you been in business?',
                            'answer' => 'APS Dream Home has been serving clients for over 8 years, establishing ourselves as a trusted name in the real estate sector with more than 500 successful property transactions.'
                        ],
                        [
                            'question' => 'What makes APS Dream Home different from other real estate companies?',
                            'answer' => 'We differentiate ourselves through transparent pricing, legal verification of all properties, personalized service, expert guidance throughout the buying process, and strong after-sales support.'
                        ]
                    ]
                ],
                [
                    'id' => 'buying',
                    'name' => 'Buying Process',
                    'icon' => 'fa-home',
                    'faqs' => [
                        [
                            'question' => 'How do I start the property buying process?',
                            'answer' => 'Simply browse our properties online, contact our team, schedule property visits, select your preferred property, complete documentation, and finalize the purchase. Our team guides you through each step.'
                        ],
                        [
                            'question' => 'What documents are required to buy a property?',
                            'answer' => 'You typically need ID proof (Aadhaar/PAN), address proof, income proof, bank statements, and photographs. For specific properties, additional documents may be required based on the property type and financing method.'
                        ],
                        [
                            'question' => 'Can you help with property loans?',
                            'answer' => 'Yes, we have partnerships with leading banks and financial institutions. Our team assists you in getting pre-approved loans and helps with the complete loan application process.'
                        ],
                        [
                            'question' => 'How long does the buying process take?',
                            'answer' => 'The process typically takes 30-45 days for ready-to-move properties and longer for under-construction properties. This includes property selection, documentation, loan approval, and registration.'
                        ]
                    ]
                ],
                [
                    'id' => 'properties',
                    'name' => 'Properties',
                    'icon' => 'fa-building',
                    'faqs' => [
                        [
                            'question' => 'What types of properties do you offer?',
                            'answer' => 'We offer a wide range of properties including residential apartments, villas, independent houses, commercial spaces, office spaces, retail shops, and residential plots/land.'
                        ],
                        [
                            'question' => 'Are your properties legally verified?',
                            'answer' => 'Yes, all our properties undergo thorough legal verification. We ensure clear titles, proper approvals, and compliance with all local regulations before listing any property.'
                        ],
                        [
                            'question' => 'Do you have ready-to-move and under-construction properties?',
                            'answer' => 'Yes, we offer both ready-to-move properties for immediate possession and under-construction properties for those looking to invest in new developments with better pricing.'
                        ],
                        [
                            'question' => 'Can I visit properties before making a decision?',
                            'answer' => 'Absolutely! We encourage and facilitate property visits. Our team will schedule visits at your convenience and provide detailed information about each property.'
                        ]
                    ]
                ],
                [
                    'id' => 'payment',
                    'name' => 'Payment & Pricing',
                    'icon' => 'fa-rupee-sign',
                    'faqs' => [
                        [
                            'question' => 'How are property prices determined?',
                            'answer' => 'Property prices are based on location, size, amenities, construction quality, market demand, and legal compliance. We ensure competitive and transparent pricing.'
                        ],
                        [
                            'question' => 'What payment options are available?',
                            'answer' => 'We accept various payment methods including bank transfers, cheques, and demand drafts. For under-construction properties, we offer flexible payment plans linked to construction milestones.'
                        ],
                        [
                            'question' => 'Are there any hidden charges?',
                            'answer' => 'No, we believe in complete transparency. All charges including registration fees, taxes, and other costs are clearly communicated upfront with no hidden surprises.'
                        ],
                        [
                            'question' => 'Do you offer EMI options?',
                            'answer' => 'Yes, through our banking partners, we offer various EMI options with competitive interest rates. Our team helps you choose the best financing option based on your requirements.'
                        ]
                    ]
                ],
                [
                    'id' => 'legal',
                    'name' => 'Legal & Documentation',
                    'icon' => 'fa-file-contract',
                    'faqs' => [
                        [
                            'question' => 'Do you help with property registration?',
                            'answer' => 'Yes, we provide complete assistance with property registration, including documentation preparation, submission, and coordination with the relevant authorities.'
                        ],
                        [
                            'question' => 'What legal clearances do your properties have?',
                            'answer' => 'All our properties have necessary clearances including land use permissions, building approvals, environmental clearances, and compliance with local development regulations.'
                        ],
                        [
                            'question' => 'Can you help with property verification?',
                            'answer' => 'Yes, we conduct comprehensive property verification including title search, encumbrance check, and verification of all legal documents before any transaction.'
                        ],
                        [
                            'question' => 'What happens if there are legal issues after purchase?',
                            'answer' => 'We provide post-purchase legal support and have tie-ups with experienced legal professionals to address any issues that may arise after property purchase.'
                        ]
                    ]
                ],
                [
                    'id' => 'support',
                    'name' => 'Support & Services',
                    'icon' => 'fa-headset',
                    'faqs' => [
                        [
                            'question' => 'What after-sales services do you provide?',
                            'answer' => 'We offer comprehensive after-sales support including property handover assistance, utility connections, interior design consultation, and property management services.'
                        ],
                        [
                            'question' => 'How can I contact customer support?',
                            'answer' => 'You can reach our customer support team via phone at +91-9277121101, email at info@apsdreamhomes.com, or visit our office at 1st floor singhariya chauraha, Kunraghat, Gorakhpur.'
                        ],
                        [
                            'question' => 'Do you provide property management services?',
                            'answer' => 'Yes, we offer property management services for investors including tenant screening, rent collection, maintenance coordination, and regular property inspections.'
                        ],
                        [
                            'question' => 'Can you help with property resale?',
                            'answer' => 'Absolutely! We assist clients in reselling their properties through our extensive network, ensuring the best market value and smooth transaction process.'
                        ]
                    ]
                ]
            ],
            'contact_info' => [
                'phone' => '+91-9277121101',
                'email' => 'info@apsdreamhomes.com',
                'address' => '1st floor singhariya chauraha, Kunraghat, Gorakhpur, UP - 273008',
                'working_hours' => 'Mon-Sat: 9:30 AM - 7:00 PM, Sun: 10:00 AM - 5:00 PM'
            ],
            'quick_links' => [
                [
                    'title' => 'Browse Properties',
                    'url' => '/properties',
                    'description' => 'Explore our extensive property portfolio'
                ],
                [
                    'title' => 'Contact Us',
                    'url' => '/contact',
                    'description' => 'Get in touch with our expert team'
                ],
                [
                    'title' => 'Financing Options',
                    'url' => '/properties',
                    'description' => 'Learn about home loan options'
                ],
                [
                    'title' => 'Property Valuation',
                    'url' => '/contact',
                    'description' => 'Get your property valued by experts'
                ]
            ]
        ];

        return $this->render('faq/index', $data, 'layouts/base');
    }

}
