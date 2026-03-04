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
        $data = [
            'page_title' => 'Welcome to APS Dream Home',
            'page_description' => 'Trusted Real Estate Partner in Gorakhpur, Lucknow & across Uttar Pradesh',
            'hero_stats' => [
                'properties' => '500+',
                'families' => '2k+',
                'agents' => '50+',
                'years' => '10+'
            ],
            'featured_properties' => [
                [
                    'id' => 1,
                    'title' => 'Luxury Apartment in Gomti Nagar',
                    'location' => 'Gomti Nagar, Lucknow',
                    'price' => '₹75,00,000',
                    'type' => 'apartment',
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => '1500 Sq.ft',
                    'image' => 'images/properties/luxury-apartment-1.jpg',
                    'featured' => true
                ],
                [
                    'id' => 2,
                    'title' => 'Modern Villa in Hazratganj',
                    'location' => 'Hazratganj, Lucknow',
                    'price' => '₹1.2 Crore',
                    'type' => 'villa',
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area' => '2000 Sq.ft',
                    'image' => 'images/properties/modern-villa-1.jpg',
                    'featured' => true
                ],
                [
                    'id' => 3,
                    'title' => 'Commercial Space in Vibhuti Khand',
                    'location' => 'Vibhuti Khand, Gomti Nagar',
                    'price' => '₹85,00,000',
                    'type' => 'commercial',
                    'bedrooms' => 0,
                    'bathrooms' => 2,
                    'area' => '1200 Sq.ft',
                    'image' => 'images/properties/commercial-space-1.jpg',
                    'featured' => false
                ]
            ],
            'property_types' => [
                ['name' => 'Apartments', 'icon' => 'fa-building', 'count' => '250+'],
                ['name' => 'Villas', 'icon' => 'fa-home', 'count' => '150+'],
                ['name' => 'Commercial', 'icon' => 'fa-store', 'count' => '75+'],
                ['name' => 'Plots/Land', 'icon' => 'fa-map', 'count' => '25+']
            ],
            'why_choose_us' => [
                [
                    'title' => 'Legal Verification',
                    'description' => 'All our properties are legally verified and free from disputes.',
                    'icon' => 'fa-shield-alt'
                ],
                [
                    'title' => 'Best Market Price',
                    'description' => 'We ensure you get the best deal with transparent pricing.',
                    'icon' => 'fa-tag'
                ],
                [
                    'title' => 'Expert Support',
                    'description' => 'Our team of experts guides you through every step of the process.',
                    'icon' => 'fa-headset'
                ]
            ],
            'testimonials' => [
                [
                    'name' => 'Rahul Kumar',
                    'property' => '3BHK Apartment in Gomti Nagar',
                    'content' => 'Excellent service from APS Dream Home. They helped me find my dream home within budget.',
                    'rating' => 5
                ],
                [
                    'name' => 'Priya Singh',
                    'property' => 'Villa in Hazratganj',
                    'content' => 'Professional team and transparent process. Highly recommend APS Dream Home.',
                    'rating' => 5
                ]
            ]
        ];

        return $this->render('home/index', $data, 'layouts/base');
    }
    
    /**
     * About method
     * @return void
     */
    public function about()
    {
        try {
            // Try to get data from database
            $pdo = new \PDO(
                "mysql:host=" . ($_ENV['DB_HOST'] ?? '127.0.0.1') . 
                ";port=" . ($_ENV['DB_PORT'] ?? '3306') . 
                ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'apsdreamhome') . 
                ";charset=utf8mb4",
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            $company_info_db = null;
            $team_members_db = [];
            $stats_db = null;

            // Get company info from database (check if table exists)
            $tables = $pdo->query("SHOW TABLES LIKE 'company_info'")->fetchAll();
            if (!empty($tables)) {
                $stmt = $pdo->query("SELECT * FROM company_info WHERE id = 1 LIMIT 1");
                $company_info_db = $stmt->fetch();
            }

            // Get team members from database (check if table exists)
            $tables = $pdo->query("SHOW TABLES LIKE 'team_members'")->fetchAll();
            if (!empty($tables)) {
                // Use a simple query that should work with most table structures
                $stmt = $pdo->query("SELECT * FROM team_members LIMIT 3");
                $team_members_db = $stmt->fetchAll();
            }

            // Get statistics from database (check if table exists)
            $tables = $pdo->query("SHOW TABLES LIKE 'company_stats'")->fetchAll();
            if (!empty($tables)) {
                $stmt = $pdo->query("SELECT * FROM company_stats WHERE id = 1 LIMIT 1");
                $stats_db = $stmt->fetch();
            }

            if ($company_info_db || $team_members_db || $stats_db) {
                // Use database data
                $data = [
                    'title' => $company_info_db['page_title'] ?? 'About Us - APS Dream Home',
                    'description' => $company_info_db['meta_description'] ?? 'Learn about APS Dream Home - Leading real estate developer in Gorakhpur with 8+ years of excellence in property development and customer satisfaction.',
                    'company_info' => [
                        'name' => $company_info_db['company_name'] ?? 'APS Dream Homes Pvt Ltd',
                        'established' => $company_info_db['established_year'] ?? '2017',
                        'experience' => $stats_db['experience_years'] ?? '8+ Years',
                        'projects' => $stats_db['completed_projects'] ?? '50+',
                        'properties' => $stats_db['properties_delivered'] ?? '500+',
                        'happy_families' => $stats_db['happy_customers'] ?? '2000+',
                        'registration_no' => $company_info_db['registration_no'] ?? 'U70109UP2022PTC163047'
                    ],
                    'mission' => $company_info_db['mission_statement'] ?? 'To provide transparent and hassle-free real estate services with a focus on customer satisfaction and quality construction.',
                    'vision' => $company_info_db['vision_statement'] ?? 'To become the most trusted real estate developer in Uttar Pradesh by delivering excellence in every project.',
                    'values' => $company_info_db['core_values'] ? explode(',', $company_info_db['core_values']) : [
                        'Transparency',
                        'Quality',
                        'Customer Satisfaction',
                        'Integrity',
                        'Innovation'
                    ],
                    'team' => !empty($team_members_db) ? array_map(function($member) {
                        return (object)[
                            'name' => $member['name'],
                            'position' => $member['position'],
                            'experience' => $member['experience'],
                            'description' => $member['description'],
                            'image' => $member['image'] ?? 'team/default.jpg'
                        ];
                    }, $team_members_db) : [
                        (object)[
                            'name' => 'Amit Kumar Singh',
                            'position' => 'Managing Director',
                            'experience' => '15+ Years',
                            'description' => 'Leading the company with vision and expertise in real estate development.',
                            'image' => 'team/amit.jpg'
                        ],
                        (object)[
                            'name' => 'Priya Singh',
                            'position' => 'Operations Head',
                            'experience' => '10+ Years',
                            'description' => 'Managing day-to-day operations with focus on efficiency and quality.',
                            'image' => 'team/priya.jpg'
                        ],
                        (object)[
                            'name' => 'Rahul Verma',
                            'position' => 'Technical Director',
                            'experience' => '12+ Years',
                            'description' => 'Ensuring technical excellence and innovation in construction.',
                            'image' => 'team/rahul.jpg'
                        ]
                    ]
                ];
            } else {
                throw new Exception("No data found in database");
            }
        } catch (Exception $e) {
            // Fallback to sample data if database fails
            $data = [
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
                        'description' => 'Leading the company with vision and expertise in real estate development.',
                        'image' => 'team/amit.jpg'
                    ],
                    (object)[
                        'name' => 'Priya Singh',
                        'position' => 'Operations Head',
                        'experience' => '10+ Years',
                        'description' => 'Managing day-to-day operations with focus on efficiency and quality.',
                        'image' => 'team/priya.jpg'
                    ],
                    (object)[
                        'name' => 'Rahul Verma',
                        'position' => 'Technical Director',
                        'experience' => '12+ Years',
                        'description' => 'Ensuring technical excellence and innovation in construction.',
                        'image' => 'team/rahul.jpg'
                    ]
                ]
            ];
        }
        
        return $this->render('about/index', $data, 'layouts/base');
    }

    /**
     * Contact method
     * @return void
     */
    public function contact()
    {
        $data = [
            'page_title' => 'Contact Us - APS Dream Home',
            'page_description' => 'Get in touch with APS Dream Home for all your real estate needs. Visit our office or call us to find your dream property.',
            'contact_info' => [
                'office_address' => '1st floor singhariya chauraha, Kunraghat, deoria Road, Gorakhpur, UP - 273008',
                'phone_numbers' => ['+91-9277121112', '+91-9277121112'],
                'email_addresses' => ['info@apsdreamhomes.com', 'sales@apsdreamhomes.com'],
                'working_hours' => [
                    'weekdays' => 'Mon-Sat: 9:30 AM - 7:00 PM',
                    'sunday' => 'Sun: 10:00 AM - 5:00 PM'
                ]
            ],
            'office_locations' => [
                [
                    'name' => 'Head Office - Gorakhpur',
                    'address' => '1st floor singhariya chauraha, Kunraghat, deoria Road',
                    'city' => 'Gorakhpur',
                    'state' => 'Uttar Pradesh',
                    'pincode' => '273008',
                    'phone' => '+91-9277121112',
                    'email' => 'info@apsdreamhomes.com',
                    'map_embed' => 'https://maps.google.com/maps?q=Gorakhpur,+Uttar+Pradesh&output=embed'
                ],
                [
                    'name' => 'Branch Office - Lucknow',
                    'address' => 'Gomti Nagar, Vibhuti Khand',
                    'city' => 'Lucknow', 
                    'state' => 'Uttar Pradesh',
                    'pincode' => '226010',
                    'phone' => '+91-9277121112',
                    'email' => 'lucknow@apsdreamhomes.com',
                    'map_embed' => 'https://maps.google.com/maps?q=Lucknow,+Uttar+Pradesh&output=embed'
                ]
            ],
            'contact_form' => [
                'fields' => [
                    'name' => ['type' => 'text', 'label' => 'Full Name', 'required' => true],
                    'email' => ['type' => 'email', 'label' => 'Email Address', 'required' => true],
                    'phone' => ['type' => 'tel', 'label' => 'Phone Number', 'required' => true],
                    'subject' => ['type' => 'select', 'label' => 'Subject', 'required' => true, 'options' => [
                        'Property Inquiry' => 'Property Inquiry',
                        'Schedule Visit' => 'Schedule Visit',
                        'General Query' => 'General Query',
                        'Complaint' => 'Complaint',
                        'Feedback' => 'Feedback'
                    ]],
                    'message' => ['type' => 'textarea', 'label' => 'Message', 'required' => true]
                ]
            ],
            'faq_items' => [
                [
                    'question' => 'What types of properties do you offer?',
                    'answer' => 'We offer residential apartments, villas, commercial spaces, and plots in Gorakhpur, Lucknow, and across Uttar Pradesh.'
                ],
                [
                    'question' => 'How can I schedule a property visit?',
                    'answer' => 'You can call us at +91-9277121112 or fill out the contact form. Our team will get back to you to arrange a convenient time.'
                ],
                [
                    'question' => 'Do you provide home loan assistance?',
                    'answer' => 'Yes, we have partnerships with leading banks and financial institutions to help you with home loan assistance and documentation.'
                ],
                [
                    'question' => 'Are your properties legally verified?',
                    'answer' => 'Absolutely! All our properties undergo thorough legal verification to ensure they are free from disputes and have clear titles.'
                ]
            ]
        ];

        return $this->render('contact/index', $data, 'layouts/base');
    }

    /**
     * Properties method
     * @return void
     */
    public function properties()
    {
        $data = [
            'page_title' => 'Properties - APS Dream Home',
            'page_description' => 'Browse our extensive collection of residential and commercial properties in Gorakhpur, Lucknow, and across Uttar Pradesh.',
            'property_stats' => [
                'total_properties' => '500+',
                'featured_properties' => '25+',
                'new_listings' => '10+',
                'avg_price_per_sqft' => '₹4500'
            ],
            'properties' => [
                [
                    'id' => 1,
                    'title' => 'Luxury Apartment in Gomti Nagar',
                    'location' => 'Gomti Nagar, Lucknow',
                    'price' => 7500000,
                    'type' => 'apartment',
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => 1500,
                    'featured' => true,
                    'image' => 'properties/luxury-apartment-1.jpg',
                    'description' => 'Spacious 3BHK luxury apartment with modern amenities and prime location in Gomti Nagar.',
                    'amenities' => ['Parking', 'Swimming Pool', 'Gym', 'Security', 'Power Backup'],
                    'status' => 'ready-to-move'
                ],
                [
                    'id' => 2,
                    'title' => 'Modern Villa in Hazratganj',
                    'location' => 'Hazratganj, Lucknow',
                    'price' => 12000000,
                    'type' => 'villa',
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area' => 2000,
                    'featured' => true,
                    'image' => 'properties/modern-villa-1.jpg',
                    'description' => 'Elegant 4BHK villa with private garden and premium finishing in the heart of Hazratganj.',
                    'amenities' => ['Private Garden', 'Swimming Pool', 'Gym', 'Security', 'Power Backup', 'Servant Room'],
                    'status' => 'ready-to-move'
                ],
                [
                    'id' => 3,
                    'title' => 'Commercial Space in Vibhuti Khand',
                    'location' => 'Vibhuti Khand, Gomti Nagar',
                    'price' => 8500000,
                    'type' => 'commercial',
                    'bedrooms' => 0,
                    'bathrooms' => 2,
                    'area' => 1200,
                    'featured' => false,
                    'image' => 'properties/commercial-space-1.jpg',
                    'description' => 'Premium commercial space ideal for offices and retail in prime business district.',
                    'amenities' => ['Parking', 'Power Backup', 'Security', 'Conference Room', 'Reception'],
                    'status' => 'ready-to-move'
                ],
                [
                    'id' => 4,
                    'title' => '2BHK Apartment in Alambagh',
                    'location' => 'Alambagh, Lucknow',
                    'price' => 4500000,
                    'type' => 'apartment',
                    'bedrooms' => 2,
                    'bathrooms' => 1,
                    'area' => 950,
                    'featured' => false,
                    'image' => 'properties/2bhk-apartment-1.jpg',
                    'description' => 'Affordable 2BHK apartment with good connectivity and essential amenities.',
                    'amenities' => ['Parking', 'Security', 'Power Backup'],
                    'status' => 'under-construction'
                ],
                [
                    'id' => 5,
                    'title' => '3BHK in Gomti Nagar Extension',
                    'location' => 'Gomti Nagar Extension, Lucknow',
                    'price' => 6500000,
                    'type' => 'apartment',
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => 1350,
                    'featured' => false,
                    'image' => 'properties/3bhk-extension-1.jpg',
                    'description' => 'Modern 3BHK apartment in developing area with great investment potential.',
                    'amenities' => ['Parking', 'Security', 'Power Backup', 'Children Play Area'],
                    'status' => 'under-construction'
                ]
            ],
            'property_types' => [
                'apartment' => 'Apartments',
                'villa' => 'Villas',
                'commercial' => 'Commercial Spaces',
                'plot' => 'Plots/Land'
            ],
            'featured_count' => 2,
            'total_properties' => 5
        ];

        return $this->render('properties/index', $data, 'layouts/base');
    }

    /**
     * Services method
     * @return void
     */
    public function services()
    {
        $data = [
            'page_title' => 'Our Services - APS Dream Home',
            'page_description' => 'Discover our comprehensive range of real estate services designed to help you find your perfect property or sell your current one with ease.',
            'services_stats' => [
                'properties_sold' => 500,
                'happy_clients' => 1000,
                'years_experience' => 15,
                'projects_completed' => 50
            ],
            'main_services' => [
                [
                    'id' => 1,
                    'title' => 'Property Buying',
                    'icon' => 'fas fa-home',
                    'description' => 'We help you find and purchase your dream property with expert guidance throughout the buying process.',
                    'features' => ['Property Search', 'Site Visits', 'Price Negotiation', 'Legal Documentation'],
                    'process_steps' => [
                        'Understand your requirements',
                        'Shortlist suitable properties',
                        'Arrange site visits',
                        'Negotiate best price',
                        'Complete legal formalities'
                    ]
                ],
                [
                    'id' => 2,
                    'title' => 'Property Selling',
                    'icon' => 'fas fa-dollar-sign',
                    'description' => 'Sell your property at the best market price with our extensive network and marketing expertise.',
                    'features' => ['Market Analysis', 'Property Valuation', 'Marketing', 'Buyer Verification'],
                    'process_steps' => [
                        'Free property valuation',
                        'Marketing strategy',
                        'Buyer screening',
                        'Negotiation support',
                        'Smooth documentation'
                    ]
                ],
                [
                    'id' => 3,
                    'title' => 'Legal Services',
                    'icon' => 'fas fa-balance-scale',
                    'description' => 'Complete legal assistance for property transactions including documentation and verification.',
                    'features' => ['Title Verification', 'Documentation', 'Registration Support', 'Legal Consultation'],
                    'process_steps' => [
                        'Document verification',
                        'Title search',
                        'Agreement drafting',
                        'Registration process',
                        'Post-sale support'
                    ]
                ],
                [
                    'id' => 4,
                    'title' => 'Financial Services',
                    'icon' => 'fas fa-chart-line',
                    'description' => 'Home loan assistance and financial planning to make your property purchase affordable.',
                    'features' => ['Loan Assistance', 'Bank Tie-ups', 'EMI Calculator', 'Financial Planning'],
                    'process_steps' => [
                        'Eligibility assessment',
                        'Bank application support',
                        'Loan negotiation',
                        'Documentation help',
                        'Disbursement coordination'
                    ]
                ]
            ],
            'specialized_services' => [
                [
                    'title' => 'NRI Services',
                    'icon' => 'fas fa-globe',
                    'description' => 'Dedicated services for NRI clients including remote property management and investment guidance.',
                    'features' => ['Remote Property Management', 'Investment Guidance', 'Documentation Support', 'Regular Updates']
                ],
                [
                    'title' => 'Investment Advisory',
                    'icon' => 'fas fa-chart-pie',
                    'description' => 'Expert advice on real estate investment opportunities with ROI analysis and market trends.',
                    'features' => ['Market Analysis', 'ROI Calculation', 'Risk Assessment', 'Portfolio Management']
                ],
                [
                    'title' => 'Property Management',
                    'icon' => 'fas fa-building',
                    'description' => 'Complete property management services for landlords including tenant management and maintenance.',
                    'features' => ['Tenant Management', 'Rent Collection', 'Maintenance Support', 'Legal Compliance']
                ],
                [
                    'title' => 'Vastu Consultation',
                    'icon' => 'fas fa-om',
                    'description' => 'Traditional Vastu consultation for harmonious living spaces and positive energy.',
                    'features' => ['Site Analysis', 'Layout Planning', 'Remedies', 'Consultation Reports']
                ]
            ],
            'why_choose_us' => [
                [
                    'title' => 'Expert Team',
                    'icon' => 'fas fa-users',
                    'description' => 'Highly experienced professionals with deep knowledge of real estate market.'
                ],
                [
                    'title' => 'Transparent Process',
                    'icon' => 'fas fa-eye',
                    'description' => 'Complete transparency in all dealings with no hidden charges.'
                ],
                [
                    'title' => 'Best Prices',
                    'icon' => 'fas fa-tag',
                    'description' => 'Competitive pricing and best value for your money.'
                ],
                [
                    'title' => 'Legal Safety',
                    'icon' => 'fas fa-shield-alt',
                    'description' => 'All properties legally verified with complete documentation.'
                ],
                [
                    'title' => 'Customer Support',
                    'icon' => 'fas fa-headset',
                    'description' => '24/7 customer support for all your queries and assistance.'
                ],
                [
                    'title' => 'Quick Processing',
                    'icon' => 'fas fa-clock',
                    'description' => 'Fast and efficient processing of all property transactions.'
                ]
            ],
            'testimonials' => [
                [
                    'name' => 'Rajesh Kumar',
                    'property' => '3BHK Apartment in Gomti Nagar',
                    'content' => 'Excellent service from start to finish. The team helped me find the perfect home within my budget.',
                    'rating' => 5
                ],
                [
                    'name' => 'Priya Sharma',
                    'property' => 'Commercial Space in Vibhuti Khand',
                    'content' => 'Professional approach and great market knowledge. Got the best price for my commercial property.',
                    'rating' => 5
                ],
                [
                    'name' => 'Amit Verma',
                    'property' => 'Villa in Hazratganj',
                    'content' => 'Smooth documentation process and transparent dealing. Highly recommend their services.',
                    'rating' => 5
                ]
            ]
        ];

        return $this->render('services/index', $data, 'layouts/base');
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
     * Legal Services method
     * @return void
     */
    public function legalServices()
    {
        $data = [
            'page_title' => 'Legal Services - APS Dream Home',
            'page_description' => 'Expert legal assistance for all your real estate needs. We provide comprehensive legal services including property verification, documentation, and registration support.',
            'legal_services' => [
                [
                    'id' => 1,
                    'title' => 'Property Title Verification',
                    'icon' => 'fa-search',
                    'description' => 'Thorough verification of property titles to ensure clear ownership and no legal disputes.',
                    'features' => ['Title Search', 'Ownership Verification', 'Encumbrance Check', 'Legal Heir Verification'],
                    'process_time' => '3-5 working days',
                    'price' => 'Starting from ₹5,000'
                ],
                [
                    'id' => 2,
                    'title' => 'Property Documentation',
                    'icon' => 'fa-file-contract',
                    'description' => 'Complete documentation support for property transactions with legal compliance.',
                    'features' => ['Agreement Drafting', 'Sale Deed Preparation', 'Power of Attorney', 'No Objection Certificates'],
                    'process_time' => '2-3 working days',
                    'price' => 'Starting from ₹3,000'
                ],
                [
                    'id' => 3,
                    'title' => 'Registration Support',
                    'icon' => 'fa-landmark',
                    'description' => 'End-to-end support for property registration with government authorities.',
                    'features' => ['Document Filing', 'Stamp Duty Calculation', 'Registration Process', 'Document Collection'],
                    'process_time' => '7-10 working days',
                    'price' => 'Starting from ₹8,000'
                ],
                [
                    'id' => 4,
                    'title' => 'Legal Consultation',
                    'icon' => 'fa-balance-scale',
                    'description' => 'Expert legal advice for complex property matters and dispute resolution.',
                    'features' => ['Legal Advisory', 'Dispute Resolution', 'Court Representation', 'Mediation Services'],
                    'process_time' => 'As per case complexity',
                    'price' => 'Starting from ₹10,000'
                ]
            ],
            'legal_stats' => [
                'cases_handled' => '500+',
                'successful_registrations' => '450+',
                'years_experience' => '15+',
                'client_satisfaction' => '98%'
            ],
            'process_steps' => [
                [
                    'step' => 1,
                    'title' => 'Initial Consultation',
                    'description' => 'Understand your requirements and assess the legal aspects of your property transaction.'
                ],
                [
                    'step' => 2,
                    'title' => 'Document Review',
                    'description' => 'Thorough review of all property documents and identification of legal requirements.'
                ],
                [
                    'step' => 3,
                    'title' => 'Legal Process',
                    'description' => 'Execute all legal procedures including verification, documentation, and registration.'
                ],
                [
                    'step' => 4,
                    'title' => 'Completion',
                    'description' => 'Final handover of documents and confirmation of successful legal completion.'
                ]
            ],
            'why_choose_us' => [
                [
                    'title' => 'Expert Legal Team',
                    'description' => 'Highly qualified legal professionals specializing in real estate law.',
                    'icon' => 'fa-users'
                ],
                [
                    'title' => 'Transparent Process',
                    'description' => 'Complete transparency in all legal procedures with no hidden charges.',
                    'icon' => 'fa-eye'
                ],
                [
                    'title' => 'Quick Turnaround',
                    'description' => 'Efficient legal processes with minimal turnaround time.',
                    'icon' => 'fa-clock'
                ],
                [
                    'title' => 'Affordable Services',
                    'description' => 'Competitive pricing for all legal services without compromising quality.',
                    'icon' => 'fa-tag'
                ]
            ],
            'testimonials' => [
                [
                    'name' => 'Rajesh Kumar',
                    'service' => 'Property Registration',
                    'content' => 'Excellent legal support for my property registration. The team handled everything professionally.',
                    'rating' => 5
                ],
                [
                    'name' => 'Anita Sharma',
                    'service' => 'Title Verification',
                    'content' => 'Thorough title verification helped me avoid a problematic property. Very grateful for their expertise.',
                    'rating' => 5
                ]
            ]
        ];

        return $this->render('legal-services/index', $data, 'layouts/base');
    }

    /**
     * Blog method
     * @return void
     */
    public function blog()
    {
        try {
            // Try to get data from database
            $pdo = new \PDO(
                "mysql:host=" . ($_ENV['DB_HOST'] ?? '127.0.0.1') . 
                ";port=" . ($_ENV['DB_PORT'] ?? '3306') . 
                ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'apsdreamhome') . 
                ";charset=utf8mb4",
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Get news articles from database
            $stmt = $pdo->query("SELECT * FROM news ORDER BY date DESC, created_at DESC");
            $news_articles = $stmt->fetchAll();

            if (!empty($news_articles)) {
                // Format database data
                $featured_posts = array_map(function($article) {
                    return [
                        'id' => $article['id'],
                        'title' => $article['title'],
                        'excerpt' => $article['summary'] ?? substr(strip_tags($article['content'] ?? ''), 0, 150) . '...',
                        'content' => $article['content'],
                        'featured_image' => $article['image'] ?? 'blog/default-featured.jpg',
                        'published_date' => $article['date'],
                        'category' => 'Real Estate News',
                        'reading_time' => ceil(str_word_count(strip_tags($article['content'] ?? '')) / 200) . ' min read',
                        'author' => [
                            'name' => 'APS Dream Home Team',
                            'role' => 'Real Estate Experts',
                            'avatar' => 'authors/aps-team.jpg'
                        ],
                        'featured' => $article['id'] <= 2, // First 2 articles as featured
                        'views' => rand(100, 5000),
                        'likes' => rand(10, 200),
                        'comments' => rand(0, 50)
                    ];
                }, array_slice($news_articles, 0, 3));

                $recent_posts = array_map(function($article) {
                    return [
                        'id' => $article['id'],
                        'title' => $article['title'],
                        'excerpt' => $article['summary'] ?? substr(strip_tags($article['content'] ?? ''), 0, 120) . '...',
                        'thumbnail' => $article['image'] ?? 'blog/thumbnails/default.jpg',
                        'published_date' => $article['date'],
                        'category' => 'Real Estate News',
                        'reading_time' => ceil(str_word_count(strip_tags($article['content'] ?? '')) / 200) . ' min read',
                        'author' => 'APS Dream Home Team',
                        'views' => rand(50, 2000)
                    ];
                }, $news_articles);

                $data = [
                    'page_title' => 'Blog - APS Dream Home',
                    'page_description' => 'Stay updated with the latest real estate trends, property tips, and market insights from our expert team.',
                    'blog_stats' => [
                        'total_posts' => count($news_articles),
                        'total_categories' => 5,
                        'total_authors' => 3,
                        'total_views' => array_sum(array_column($featured_posts, 'views')) + array_sum(array_column($recent_posts, 'views'))
                    ],
                    'blog_categories' => [
                        [
                            'name' => 'Property Buying',
                            'description' => 'Tips and guides for buying your dream property',
                            'post_count' => 15
                        ],
                        [
                            'name' => 'Investment',
                            'description' => 'Real estate investment strategies and opportunities',
                            'post_count' => 12
                        ],
                        [
                            'name' => 'Market Trends',
                            'description' => 'Latest market analysis and property trends',
                            'post_count' => 8
                        ],
                        [
                            'name' => 'Legal Tips',
                            'description' => 'Legal aspects and documentation guidance',
                            'post_count' => 6
                        ],
                        [
                            'name' => 'Home Loans',
                            'description' => 'Financing options and loan guidance',
                            'post_count' => 10
                        ],
                        [
                            'name' => 'Vastu Tips',
                            'description' => 'Vastu compliant property guidance',
                            'post_count' => 4
                        ]
                    ],
                    'featured_posts' => $featured_posts,
                    'recent_posts' => $recent_posts,
                    'popular_tags' => [
                        ['name' => 'property-buying', 'count' => 15],
                        ['name' => 'investment', 'count' => 12],
                        ['name' => 'home-loan', 'count' => 10],
                        ['name' => 'real-estate-tips', 'count' => 8],
                        ['name' => 'market-trends', 'count' => 6],
                        ['name' => 'vastu', 'count' => 4]
                    ]
                ];
            } else {
                throw new Exception("No news articles found");
            }
        } catch (Exception $e) {
            // Fallback to sample data if database fails
            $data = [
                'page_title' => 'Blog - APS Dream Home',
                'page_description' => 'Stay updated with the latest real estate trends, property tips, and market insights from our expert team.',
                'blog_stats' => [
                    'total_posts' => 91,
                    'total_categories' => 5,
                    'total_authors' => 8,
                    'total_views' => 45670,
                    'total_comments' => 234
                ],
                'blog_categories' => [
                    [
                        'name' => 'Property Buying',
                        'description' => 'Tips and guides for buying your dream property',
                        'post_count' => 15
                    ],
                    [
                        'name' => 'Investment',
                        'description' => 'Real estate investment strategies and opportunities',
                        'post_count' => 12
                    ],
                    [
                        'name' => 'Market Trends',
                        'description' => 'Latest market analysis and property trends',
                        'post_count' => 8
                    ],
                    [
                        'name' => 'Legal Tips',
                        'description' => 'Legal aspects and documentation guidance',
                        'post_count' => 6
                    ],
                    [
                        'name' => 'Home Loans',
                        'description' => 'Financing options and loan guidance',
                        'post_count' => 10
                    ],
                    [
                        'name' => 'Vastu Tips',
                        'description' => 'Vastu compliant property guidance',
                        'post_count' => 4
                    ]
                ],
                'featured_posts' => [
                    [
                        'id' => 1,
                        'title' => 'Top 10 Things to Check Before Buying a Property in 2024',
                        'excerpt' => 'Discover the essential checklist every homebuyer should follow before making their property purchase. From legal verification to property inspection, we cover everything you need to know.',
                        'featured_image' => 'blog/featured-1.jpg',
                        'published_date' => '2026-03-01',
                        'category' => 'Property Buying',
                        'reading_time' => '8 min read',
                        'author' => [
                            'name' => 'Amit Kumar Singh',
                            'role' => 'Real Estate Expert',
                            'avatar' => 'authors/amit.jpg'
                        ],
                        'featured' => true,
                        'views' => 5420,
                        'likes' => 145,
                        'comments' => 23
                    ],
                    [
                        'id' => 2,
                        'title' => 'How to Calculate ROI on Real Estate Investments',
                        'excerpt' => 'Learn the proven methods to calculate return on investment for your real estate properties. This comprehensive guide covers both residential and commercial property investments.',
                        'featured_image' => 'blog/featured-2.jpg',
                        'published_date' => '2026-02-28',
                        'category' => 'Investment',
                        'reading_time' => '6 min read',
                        'author' => [
                            'name' => 'Priya Sharma',
                            'role' => 'Investment Advisor',
                            'avatar' => 'authors/priya.jpg'
                        ],
                        'featured' => true,
                        'views' => 3890,
                        'likes' => 98,
                        'comments' => 15
                    ],
                    [
                        'id' => 3,
                        'title' => 'Vastu Tips for Your New Home: Complete Guide',
                        'excerpt' => 'Transform your living space with these essential Vastu principles. Our expert guide helps you create a harmonious and positive environment in your new home.',
                        'featured_image' => 'blog/featured-3.jpg',
                        'published_date' => '2026-02-25',
                        'category' => 'Vastu Tips',
                        'reading_time' => '5 min read',
                        'author' => [
                            'name' => 'Dr. Ramesh Kumar',
                            'role' => 'Vastu Consultant',
                            'avatar' => 'authors/ramesh.jpg'
                        ],
                        'featured' => false,
                        'views' => 2780,
                        'likes' => 76,
                        'comments' => 12
                    ]
                ],
                'recent_posts' => [
                    [
                        'id' => 4,
                        'title' => 'Home Loan Interest Rates: Current Market Analysis',
                        'excerpt' => 'Stay updated with the latest home loan interest rates and market trends. Our analysis helps you make informed decisions about your home financing.',
                        'thumbnail' => 'blog/recent-1.jpg',
                        'published_date' => '2026-02-20',
                        'category' => 'Home Loans',
                        'reading_time' => '4 min read',
                        'author' => 'Vikram Singh',
                        'views' => 1890
                    ],
                    [
                        'id' => 5,
                        'title' => 'Legal Documents Needed for Property Registration',
                        'excerpt' => 'Complete guide to all legal documents required for property registration in India. Make your property buying process smooth and hassle-free.',
                        'thumbnail' => 'blog/recent-2.jpg',
                        'published_date' => '2026-02-18',
                        'category' => 'Legal Tips',
                        'reading_time' => '7 min read',
                        'author' => 'Legal Team',
                        'views' => 2340
                    ],
                    [
                        'id' => 6,
                        'title' => 'Best Areas for Real Estate Investment in Lucknow',
                        'excerpt' => 'Discover the most promising locations for real estate investment in Lucknow. Our expert analysis covers growth potential and ROI expectations.',
                        'thumbnail' => 'blog/recent-3.jpg',
                        'published_date' => '2026-02-15',
                        'category' => 'Investment',
                        'reading_time' => '6 min read',
                        'author' => 'Property Experts',
                        'views' => 3120
                    ],
                    [
                        'id' => 7,
                        'title' => 'Property Tax Guide for Homeowners',
                        'excerpt' => 'Everything you need to know about property tax calculation, payment methods, and deadlines. Essential information for every homeowner.',
                        'thumbnail' => 'blog/recent-4.jpg',
                        'published_date' => '2026-02-12',
                        'category' => 'Legal Tips',
                        'reading_time' => '5 min read',
                        'author' => 'Tax Experts',
                        'views' => 1560
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
                ]
            ];
        }

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
        try {
            // Try to get data from database
            $pdo = new \PDO(
                "mysql:host=" . ($_ENV['DB_HOST'] ?? '127.0.0.1') . 
                ";port=" . ($_ENV['DB_PORT'] ?? '3306') . 
                ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'apsdreamhome') . 
                ";charset=utf8mb4",
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Get team members from database
            $stmt = $pdo->query("SELECT * FROM team_members WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
            $team_members = $stmt->fetchAll();

            // Get team stats
            $stats_stmt = $pdo->query("SELECT COUNT(*) as total_members, AVG(EXTRACT(YEAR FROM AGE(created_at))) as avg_experience FROM team_members WHERE status = 'active'");
            $team_stats = $stats_stmt->fetch();

            if (!empty($team_members)) {
                // Format database data
                $leadership_team = array_filter($team_members, function($member) {
                    return $member['category'] === 'leadership';
                });
                
                $department_heads = array_filter($team_members, function($member) {
                    return $member['category'] === 'department_head';
                });

                $data = [
                    'page_title' => 'Our Team - APS Dream Home',
                    'page_description' => 'Meet the dedicated professionals behind APS Dream Home. Our experienced team is committed to delivering excellence in real estate services.',
                    'leadership_team' => array_map(function($member) {
                        return [
                            'id' => $member['id'],
                            'name' => $member['name'],
                            'position' => $member['position'],
                            'bio' => $member['bio'],
                            'experience' => $member['experience'],
                            'education' => $member['education'],
                            'achievements' => json_decode($member['achievements'] ?? '[]', true),
                            'image' => $member['image'] ?? 'team/default-avatar.jpg',
                            'email' => $member['email'],
                            'phone' => $member['phone'],
                            'linkedin' => $member['linkedin'] ?? '#'
                        ];
                    }, $leadership_team),
                    'department_heads' => array_map(function($member) {
                        return [
                            'id' => $member['id'],
                            'name' => $member['name'],
                            'position' => $member['position'],
                            'bio' => $member['bio'],
                            'experience' => $member['experience'],
                            'education' => $member['education'],
                            'image' => $member['image'] ?? 'team/default-avatar.jpg',
                            'email' => $member['email'],
                            'phone' => $member['phone']
                        ];
                    }, $department_heads),
                    'team_stats' => [
                        'total_members' => $team_stats['total_members'] ?? 150,
                        'years_experience' => round($team_stats['avg_experience'] ?? 100),
                        'projects_completed' => 500,
                        'happy_customers' => 2000
                    ]
                ];
            } else {
                throw new Exception("No team members found");
            }
        } catch (Exception $e) {
            // Fallback to sample data if database fails
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
                        'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
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
                        'image' => 'https://images.unsplash.com/photo-1494790108755-2616b332c1ca?w=400',
                        'email' => 'priya.sharma@apsdreamhomes.com',
                        'phone' => '+91-9277121102',
                        'linkedin' => 'https://linkedin.com/in/priya-sharma-aps'
                    ]
                ],
                'department_heads' => [
                    [
                        'id' => 3,
                        'name' => 'Vikram Singh',
                        'position' => 'Head - Construction',
                        'bio' => 'Vikram ensures quality construction and timely project delivery with his technical expertise and project management skills.',
                        'experience' => '12+ Years',
                        'education' => 'B.E. - Civil Engineering',
                        'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400',
                        'email' => 'vikram.singh@apsdreamhomes.com',
                        'phone' => '+91-9277121105'
                    ],
                    [
                        'id' => 4,
                        'name' => 'Sneha Patel',
                        'position' => 'Head - Customer Relations',
                        'bio' => 'Sneha leads the customer service team, ensuring exceptional service and client satisfaction throughout the buying journey.',
                        'experience' => '7+ Years',
                        'education' => 'MBA - Customer Service',
                        'image' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400',
                        'email' => 'sneha.patel@apsdreamhomes.com',
                        'phone' => '+91-9277121106'
                    ]
                ],
                'team_stats' => [
                    'total_members' => 150,
                    'years_experience' => 100,
                    'projects_completed' => 500,
                    'happy_customers' => 2000
                ]
            ];

        return $this->render('team/index', $data, 'layouts/base');
        }
    }

    /**
     * Company Projects method
     * @return void
     */
    public function companyProjects()
    {
        $data = [
            'page_title' => 'Company Projects - APS Dream Home',
            'page_description' => 'Explore our completed and ongoing projects across Gorakhpur, Lucknow, and Uttar Pradesh.',
            'project_stats' => [
                'total' => '105+',
                'completed' => '45+',
                'ongoing' => '28+',
                'upcoming' => '32+'
            ],
            'company_projects' => [
                [
                    'id' => 1,
                    'title' => 'APS Heights',
                    'project_type' => 'residential',
                    'location' => 'Gorakhpur - Kunraghat',
                    'status' => 'completed',
                    'description' => 'Premium residential apartments with modern amenities',
                    'units' => '120 Units',
                    'price_range' => '₹45L - ₹85L',
                    'image_url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
                    'completion_date' => '2025-12-15',
                    'highlights' => ['24/7 Security', 'Power Backup', 'Children Play Area', 'Gym']
                ],
                [
                    'id' => 2,
                    'title' => 'Dream City Plaza',
                    'project_type' => 'commercial',
                    'location' => 'Gorakhpur - City Center',
                    'status' => 'ongoing',
                    'description' => 'Modern commercial complex with retail spaces and offices',
                    'units' => '85 Units',
                    'price_range' => '₹25L - ₹2Cr',
                    'image_url' => 'https://images.unsplash.com/photo-1497366214041-937c73f5ca5c?w=800',
                    'completion_date' => '2024-06-30',
                    'highlights' => ['Prime Location', 'Modern Design', 'Parking Available', '24/7 Security']
                ],
                [
                    'id' => 3,
                    'title' => 'Green Valley Villas',
                    'project_type' => 'residential',
                    'location' => 'Lucknow - Gomti Nagar',
                    'status' => 'ongoing',
                    'description' => 'Luxury villas with private gardens and premium amenities',
                    'units' => '35 Units',
                    'price_range' => '₹1.2Cr - ₹2.5Cr',
                    'image_url' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800',
                    'completion_date' => '2024-09-15',
                    'highlights' => ['Private Garden', 'Swimming Pool', 'Club House', 'Smart Home']
                ],
                [
                    'id' => 4,
                    'title' => 'Tech Park Phase 2',
                    'project_type' => 'commercial',
                    'location' => 'Gorakhpur - IT City',
                    'status' => 'upcoming',
                    'description' => 'State-of-the-art IT park with modern office spaces',
                    'units' => '200 Units',
                    'price_range' => '₹30L - ₹5Cr',
                    'image_url' => 'https://images.unsplash.com/photo-1467987509530-3c583bb6b684?w=800',
                    'completion_date' => '2025-03-30',
                    'highlights' => ['IT Infrastructure', 'Power Backup', 'Food Court', 'Gym']
                ]
            ],
            'company_info' => [
                'established' => '2022',
                'description' => 'APS Dream Homes Pvt Ltd is a registered real estate development company specializing in residential and commercial properties across Gorakhpur and surrounding regions. With a commitment to quality construction, innovative design, and customer satisfaction, we have established ourselves as a trusted name in the real estate industry.',
                'portfolio_description' => 'Our portfolio includes premium apartments, luxury villas, commercial spaces, and plotted developments, each designed to meet the evolving needs of modern homeowners and investors.',
                'locations' => 'Gorakhpur, Lucknow, Delhi NCR',
                'registration' => 'U70109UP2022PTC163047',
                'team_size' => '50+ Professionals'
            ],
            'our_values' => [
                [
                    'icon' => 'fa-home',
                    'title' => 'Quality Construction',
                    'description' => 'Premium materials and modern construction techniques'
                ],
                [
                    'icon' => 'fa-users',
                    'title' => 'Customer Satisfaction',
                    'description' => '1000+ happy families served across Uttar Pradesh'
                ],
                [
                    'icon' => 'fa-leaf',
                    'title' => 'Sustainable Development',
                    'description' => 'Eco-friendly construction practices and green buildings'
                ],
                [
                    'icon' => 'fa-shield-alt',
                    'title' => 'Legal Compliance',
                    'description' => '100% legal clearance and transparent documentation'
                ]
            ]
        ];

        // Debug: Check if data is set
        error_log('Company projects data: ' . print_r($data['company_projects'], true));

        return $this->render('pages/company_projects', $data, 'layouts/base');
    }

    /**
     * Testimonials method
     * @return void
     */
    public function testimonials()
    {
        try {
            // Try to get data from database
            $pdo = new \PDO(
                "mysql:host=" . ($_ENV['DB_HOST'] ?? '127.0.0.1') . 
                ";port=" . ($_ENV['DB_PORT'] ?? '3306') . 
                ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'apsdreamhome') . 
                ";charset=utf8mb4",
                $_ENV['DB_USERNAME'] ?? 'root',
                $_ENV['DB_PASSWORD'] ?? '',
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Get testimonials from database
            $stmt = $pdo->query("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY sort_order ASC, created_at DESC");
            $testimonials = $stmt->fetchAll();

            if (!empty($testimonials)) {
                // Format database data
                $customer_testimonials = array_map(function($testimonial) {
                    return [
                        'id' => $testimonial['id'],
                        'name' => $testimonial['name'],
                        'property' => $testimonial['property'],
                        'rating' => $testimonial['rating'],
                        'review_date' => $testimonial['review_date'],
                        'testimonial' => $testimonial['testimonial'],
                        'image' => $testimonial['image'] ?? 'testimonials/default-avatar.jpg',
                        'location' => $testimonial['location'],
                        'property_type' => $testimonial['property_type'],
                        'experience_years' => $testimonial['experience_years']
                    ];
                }, $testimonials);

                $data = [
                    'page_title' => 'Testimonials - APS Dream Home',
                    'page_description' => 'Read what our satisfied customers have to say about their experience with APS Dream Home. Real stories from real homeowners.',
                    'customer_testimonials' => $customer_testimonials,
                    'testimonial_stats' => [
                        'total_testimonials' => count($testimonials),
                        'average_rating' => 4.8,
                        'years_of_service' => 8,
                        'satisfaction_rate' => 98
                    ]
                ];
            } else {
                throw new Exception("No testimonials found");
            }
        } catch (Exception $e) {
            // Fallback to sample data if database fails
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
                        'image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
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
                        'image' => 'https://images.unsplash.com/photo-1494790108755-2616b332c1ca?w=400',
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
                        'image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400',
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
                        'image' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400',
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
                        'image' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400',
                        'location' => 'Gorakhpur',
                        'property_type' => 'Land',
                        'experience_years' => '6 months'
                    ]
                ],
                'testimonial_stats' => [
                    'total_testimonials' => 1000,
                    'average_rating' => 4.8,
                    'years_of_service' => 8,
                    'satisfaction_rate' => 98
                ]
            ];
        }

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

    public function privacy()
    {
        $data = [
            'page_title' => 'Privacy Policy - APS Dream Home',
            'page_description' => 'Our privacy policy explains how we collect, use, and protect your personal information when you use our real estate services.',
            'last_updated' => date('F d, Y', strtotime('-1 month')),
            'sections' => [
                [
                    'title' => 'Information We Collect',
                    'content' => 'We collect information you provide directly to us, such as when you create an account, use our services, or contact us for property inquiries.',
                    'items' => [
                        'Name, email address, phone number',
                        'Property preferences and requirements',
                        'Communication records',
                        'Website usage and browsing data'
                    ]
                ],
                [
                    'title' => 'How We Use Your Information',
                    'content' => 'We use the information we collect to provide, maintain, and improve our real estate services.',
                    'items' => [
                        'Process property inquiries and bookings',
                        'Send property recommendations and updates',
                        'Provide customer support',
                        'Improve our website and services'
                    ]
                ],
                [
                    'title' => 'Information Sharing',
                    'content' => 'We do not sell, trade, or otherwise transfer your personal information to third parties without your consent.',
                    'items' => [
                        'Property owners and developers (with consent)',
                        'Legal authorities (when required by law)',
                        'Service providers (for operational purposes)',
                        'Business partners (with consent)'
                    ]
                ],
                [
                    'title' => 'Data Security',
                    'content' => 'We implement appropriate security measures to protect your personal information against unauthorized access.',
                    'items' => [
                        'SSL encryption for data transmission',
                        'Secure data storage systems',
                        'Regular security audits',
                        'Employee training on data protection'
                    ]
                ],
                [
                    'title' => 'Your Rights',
                    'content' => 'You have the right to access, update, or delete your personal information.',
                    'items' => [
                        'Request a copy of your data',
                        'Update or correct your information',
                        'Delete your account and data',
                        'Opt-out of marketing communications'
                    ]
                ]
            ]
        ];

        return $this->render('privacy/index', $data, 'layouts/base');
    }

    public function terms()
    {
        $data = [
            'page_title' => 'Terms of Service - APS Dream Home',
            'page_description' => 'Our terms of service outline the rules and regulations for using our real estate platform and services.',
            'last_updated' => date('F d, Y', strtotime('-2 weeks')),
            'sections' => [
                [
                    'title' => 'Acceptance of Terms',
                    'content' => 'By accessing and using APS Dream Home services, you accept and agree to be bound by these terms and conditions.'
                ],
                [
                    'title' => 'Services Description',
                    'content' => 'We provide real estate consulting, property listings, and related services to help you find suitable properties.',
                    'items' => [
                        'Property listings and details',
                        'Site visit arrangements',
                        'Documentation assistance',
                        'Property consultation services'
                    ]
                ],
                [
                    'title' => 'User Responsibilities',
                    'content' => 'Users are responsible for maintaining the accuracy of their information and using our services ethically.',
                    'items' => [
                        'Provide accurate information',
                        'Respect property and privacy rights',
                        'Make timely payments for services',
                        'Follow property visit guidelines'
                    ]
                ],
                [
                    'title' => 'Property Information',
                    'content' => 'While we strive for accuracy, property information is subject to change without notice.',
                    'items' => [
                        'Prices and availability may change',
                        'Property features may vary',
                        'Legal verification recommended',
                        'Site visits encouraged before purchase'
                    ]
                ],
                [
                    'title' => 'Payment Terms',
                    'content' => 'Payment terms vary by service type and are clearly communicated before service commencement.',
                    'items' => [
                        'Consultation fees',
                        'Property booking amounts',
                        'Commission structures',
                        'Refund policies'
                    ]
                ],
                [
                    'title' => 'Limitation of Liability',
                    'content' => 'Our liability is limited to the extent permitted by law for any damages arising from our services.'
                ]
            ]
        ];

        return $this->render('terms/index', $data, 'layouts/base');
    }

    public function sitemap()
    {
        $data = [
            'page_title' => 'Sitemap - APS Dream Home',
            'page_description' => 'Complete sitemap of APS Dream Home website including all pages and sections.',
            'main_pages' => [
                [
                    'title' => 'Home',
                    'url' => '/',
                    'description' => 'Main landing page with property search and featured listings'
                ],
                [
                    'title' => 'Properties',
                    'url' => '/properties',
                    'description' => 'Browse all available properties with filters and search options'
                ],
                [
                    'title' => 'Projects',
                    'url' => '/projects',
                    'description' => 'View our ongoing and completed real estate projects'
                ],
                [
                    'title' => 'About Us',
                    'url' => '/about',
                    'description' => 'Learn about our company, mission, and team'
                ],
                [
                    'title' => 'Contact',
                    'url' => '/contact',
                    'description' => 'Get in touch with our team for inquiries and support'
                ]
            ],
            'property_pages' => [
                [
                    'title' => 'Resale Properties',
                    'url' => '/resell',
                    'description' => 'Browse verified resale properties with transparent pricing'
                ],
                [
                    'title' => 'Gallery',
                    'url' => '/gallery',
                    'description' => 'View images of our completed projects and properties'
                ],
                [
                    'title' => 'Blog',
                    'url' => '/blog',
                    'description' => 'Read real estate insights and market updates'
                ],
                [
                    'title' => 'Careers',
                    'url' => '/careers',
                    'description' => 'Explore job opportunities and join our team'
                ],
                [
                    'title' => 'Our Team',
                    'url' => '/team',
                    'description' => 'Meet our experienced real estate professionals'
                ],
                [
                    'title' => 'Testimonials',
                    'url' => '/testimonials',
                    'description' => 'Read reviews from our satisfied customers'
                ],
                [
                    'title' => 'FAQ',
                    'url' => '/faq',
                    'description' => 'Find answers to frequently asked questions'
                ]
            ],
            'legal_pages' => [
                [
                    'title' => 'Privacy Policy',
                    'url' => '/privacy',
                    'description' => 'How we collect and protect your personal information'
                ],
                [
                    'title' => 'Terms of Service',
                    'url' => '/terms',
                    'description' => 'Terms and conditions for using our services'
                ],
                [
                    'title' => 'Sitemap',
                    'url' => '/sitemap',
                    'description' => 'Complete overview of all website pages'
                ]
            ],
            'user_pages' => [
                [
                    'title' => 'Login',
                    'url' => '/login',
                    'description' => 'Sign in to your account'
                ],
                [
                    'title' => 'Register',
                    'url' => '/register',
                    'description' => 'Create a new account'
                ],
                [
                    'title' => 'User Dashboard',
                    'url' => '/dashboard',
                    'description' => 'Manage your account and saved properties'
                ]
            ],
            'admin_pages' => [
                [
                    'title' => 'Admin Panel',
                    'url' => '/admin',
                    'description' => 'Administrative dashboard for site management'
                ]
            ]
        ];

        return $this->render('sitemap/index', $data, 'layouts/base');
    }

}
