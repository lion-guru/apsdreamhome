<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Core\Database;
use Exception;

/**
 * PageController - Handles public-facing pages
 * 
 * This controller manages all public pages including homepage,
 * about, contact, and project pages.
 */
class PageController extends BaseController
{
    /**
     * Display the home page
     */
    public function home()
    {
        // Get hero statistics
        $hero_stats = [
            'years_experience' => 15,
            'projects_completed' => 50,
            'happy_customers' => 1000,
            'awards_won' => 25
        ];

        // Get featured properties
        $featured_properties = [
            [
                'id' => 1,
                'title' => 'Suyoday Colony',
                'location' => 'Gorakhpur',
                'price' => '₹7.5 Lakhs',
                'image' => 'suyoday.jpg',
                'type' => 'Residential',
                'status' => 'Available'
            ],
            [
                'id' => 2,
                'title' => 'Raghunat Nagri',
                'location' => 'Gorakhpur',
                'price' => '₹8.5 Lakhs',
                'image' => 'raghunat.jpg',
                'type' => 'Residential',
                'status' => 'Available'
            ],
            [
                'id' => 3,
                'title' => 'Braj Radha Nagri',
                'location' => 'Gorakhpur',
                'price' => '₹6.5 Lakhs',
                'image' => 'brajradha.jpg',
                'type' => 'Residential',
                'status' => 'Coming Soon'
            ]
        ];

        // Get property types
        $property_types = [
            'Residential' => 150,
            'Commercial' => 50,
            'Industrial' => 25,
            'Agricultural' => 100
        ];

        // Get "Why Choose Us" data
        $why_choose_us = [
            [
                'icon' => 'fa-home',
                'title' => 'Quality Construction',
                'description' => 'We use high-quality materials and modern construction techniques.'
            ],
            [
                'icon' => 'fa-shield-alt',
                'title' => 'Legal Compliance',
                'description' => 'All our projects are RERA registered and legally compliant.'
            ],
            [
                'icon' => 'fa-users',
                'title' => 'Customer Satisfaction',
                'description' => 'We prioritize customer satisfaction and provide excellent service.'
            ],
            [
                'icon' => 'fa-map-marked-alt',
                'title' => 'Prime Locations',
                'description' => 'Our projects are located in prime areas with excellent connectivity.'
            ]
        ];

        // Get testimonials
        $testimonials = [
            [
                'name' => 'Ramesh Kumar',
                'location' => 'Gorakhpur',
                'text' => 'I bought a plot in Suyoday Colony and I am very satisfied with the service and quality.',
                'rating' => 5
            ],
            [
                'name' => 'Sunita Devi',
                'location' => 'Lucknow',
                'text' => 'The team at APS Dream Home is very professional and helpful. Highly recommended!',
                'rating' => 5
            ],
            [
                'name' => 'Amit Singh',
                'location' => 'Kushinagar',
                'text' => 'Great experience with Budh Bihar Colony. The location and amenities are excellent.',
                'rating' => 4
            ]
        ];

        $data = [
            'hero_stats' => $hero_stats,
            'featured_properties' => $featured_properties,
            'property_types' => $property_types,
            'why_choose_us' => $why_choose_us,
            'testimonials' => $testimonials
        ];

        $this->render('pages/index', $data);
    }

    /**
     * Display the about page
     */
    public function about()
    {
        // Get company information
        $company_info = [
            'name' => 'APS Dream Home',
            'since' => 2008,
            'description' => 'APS Dream Home is a leading real estate development company specializing in residential and commercial properties. With over 15 years of experience, we have successfully delivered numerous projects across Uttar Pradesh.',
            'mission' => 'To provide quality housing solutions at affordable prices while maintaining the highest standards of construction and customer service.',
            'vision' => 'To become the most trusted real estate developer in Uttar Pradesh by delivering exceptional value to our customers.',
            'values' => [
                'Integrity',
                'Quality',
                'Customer Focus',
                'Innovation',
                'Transparency'
            ]
        ];

        // Get team members
        $team_members = [
            [
                'name' => 'Anurag Pratap Singh',
                'position' => 'Managing Director',
                'image' => 'director.jpg',
                'description' => 'With over 20 years of experience in real estate, Mr. Singh leads the company with vision and expertise.'
            ],
            [
                'name' => 'Priya Singh',
                'position' => 'Operations Head',
                'image' => 'operations.jpg',
                'description' => 'Ms. Singh ensures smooth operations and timely delivery of all projects.'
            ],
            [
                'name' => 'Rajesh Kumar',
                'position' => 'Sales Manager',
                'image' => 'sales.jpg',
                'description' => 'Mr. Kumar heads the sales team and ensures excellent customer service.'
            ]
        ];

        // Get company statistics
        $company_stats = [
            'years_in_business' => 15,
            'projects_delivered' => 50,
            'happy_customers' => 1000,
            'total_area' => '500+ Acres'
        ];

        $data = [
            'company_info' => $company_info,
            'team_members' => $team_members,
            'company_stats' => $company_stats
        ];

        $this->render('pages/about', $data);
    }

    /**
     * Display the contact page
     */
    public function contact()
    {
        // Get contact information
        $contact_info = [
            'address' => 'APS Dream Home, Civil Lines, Gorakhpur - 273001, Uttar Pradesh',
            'phone' => '+91-9450-123-456',
            'email' => 'info@apsdreamhome.com',
            'website' => 'www.apsdreamhome.com'
        ];

        // Get office locations
        $office_locations = [
            [
                'city' => 'Gorakhpur',
                'address' => 'Civil Lines, Gorakhpur - 273001',
                'phone' => '+91-9450-123-456',
                'email' => 'gorakhpur@apsdreamhome.com',
                'timing' => '9:00 AM - 6:00 PM'
            ],
            [
                'city' => 'Lucknow',
                'address' => 'Gomti Nagar, Lucknow - 226010',
                'phone' => '+91-9450-789-012',
                'email' => 'lucknow@apsdreamhome.com',
                'timing' => '9:00 AM - 6:00 PM'
            ],
            [
                'city' => 'Kushinagar',
                'address' => 'Near Railway Station, Kushinagar - 274402',
                'phone' => '+91-9450-345-678',
                'email' => 'kushinagar@apsdreamhome.com',
                'timing' => '10:00 AM - 5:00 PM'
            ]
        ];

        $data = [
            'contact_info' => $contact_info,
            'office_locations' => $office_locations
        ];

        $this->render('pages/contact', $data);
    }

    /**
     * Display the career page
     */
    public function career()
    {
        // Get current openings
        $current_openings = [
            [
                'title' => 'Sales Executive',
                'location' => 'Gorakhpur',
                'experience' => '2-3 years',
                'description' => 'Looking for experienced sales executives to join our team.',
                'requirements' => [
                    'Graduate in any discipline',
                    'Good communication skills',
                    'Experience in real estate sales preferred',
                    'Self-motivated and target-oriented'
                ]
            ],
            [
                'title' => 'Site Engineer',
                'location' => 'Gorakhpur',
                'experience' => '3-5 years',
                'description' => 'We need experienced site engineers for our ongoing projects.',
                'requirements' => [
                    'B.Tech in Civil Engineering',
                    'Experience in construction site management',
                    'Knowledge of building materials and techniques',
                    'Strong analytical and problem-solving skills'
                ]
            ],
            [
                'title' => 'Marketing Manager',
                'location' => 'Lucknow',
                'experience' => '5-7 years',
                'description' => 'Seeking a marketing manager to lead our marketing initiatives.',
                'requirements' => [
                    'MBA in Marketing',
                    'Experience in real estate marketing',
                    'Strong leadership skills',
                    'Creative thinking and strategic planning'
                ]
            ]
        ];

        // Get company culture information
        $company_culture = [
            'work_environment' => 'Dynamic and collaborative work environment',
            'growth_opportunities' => 'Excellent career growth opportunities',
            'benefits' => [
                'Competitive salary package',
                'Performance bonuses',
                'Health insurance',
                'Provident fund',
                'Professional development programs'
            ],
            'values' => [
                'Teamwork',
                'Innovation',
                'Integrity',
                'Excellence',
                'Customer Focus'
            ]
        ];

        $data = [
            'current_openings' => $current_openings,
            'company_culture' => $company_culture
        ];

        $this->render('pages/careers', $data);
    }

    /**
     * Display the company projects page
     */
    public function companyProjects()
    {
        // Get all projects
        $projects = [
            [
                'id' => 1,
                'name' => 'Suyoday Colony',
                'location' => 'Gorakhpur',
                'type' => 'Residential',
                'area' => '15 Acres',
                'total_plots' => '200+',
                'starting_price' => '₹7.5 Lakhs',
                'status' => 'Ongoing',
                'possession' => 'Dec 2025',
                'image' => 'suyoday.jpg',
                'description' => 'Premium residential plots with modern infrastructure and excellent connectivity.',
                'amenities' => [
                    'Wide roads with drainage',
                    '24x7 water supply',
                    'Underground electrical wiring',
                    'Green parks and tree plantation',
                    'Security with boundary walls',
                    'CCTV surveillance'
                ]
            ],
            [
                'id' => 2,
                'name' => 'Raghunat Nagri',
                'location' => 'Gorakhpur',
                'type' => 'Residential',
                'area' => '25 Acres',
                'total_plots' => '600+',
                'starting_price' => '₹8.5 Lakhs',
                'status' => 'Ongoing',
                'possession' => 'Dec 2026',
                'image' => 'raghunat.jpg',
                'description' => 'Premium residential plots with excellent connectivity and modern amenities.',
                'amenities' => [
                    '40 feet wide roads',
                    'Underground water and electricity',
                    'Community center',
                    'Children\'s play area',
                    'Jogging track',
                    '24x7 security'
                ]
            ],
            [
                'id' => 3,
                'name' => 'Braj Radha Nagri',
                'location' => 'Gorakhpur',
                'type' => 'Residential',
                'area' => '20 Acres',
                'total_plots' => '400+',
                'starting_price' => '₹6.5 Lakhs',
                'status' => 'Planned',
                'possession' => 'Jun 2026',
                'image' => 'brajradha.jpg',
                'description' => 'Affordable residential plots with modern amenities and strategic location.',
                'amenities' => [
                    '30 feet internal roads',
                    'Water and electricity supply',
                    'Landscaped gardens',
                    'Temple area',
                    'Street lighting',
                    'Security system'
                ]
            ],
            [
                'id' => 4,
                'name' => 'Budh Bihar Colony',
                'location' => 'Kushinagar',
                'type' => 'Integrated Township',
                'area' => '15+ Acres',
                'total_plots' => '300+',
                'starting_price' => '₹5.5 Lakhs',
                'status' => 'Ongoing',
                'possession' => 'Mar 2026',
                'image' => 'budhbihar.jpg',
                'description' => 'Integrated township at Premwaliya, Kushinagar Highway with all modern facilities.',
                'amenities' => [
                    'Commercial complex',
                    'School and hospital',
                    'Recreational facilities',
                    'Wide roads and drainage',
                    'Green spaces',
                    'Security and surveillance'
                ]
            ],
            [
                'id' => 5,
                'name' => 'Awadhpuri',
                'location' => 'Lucknow',
                'type' => 'Premium Residential',
                'area' => '20 Bigha',
                'total_plots' => '500+',
                'starting_price' => '₹12 Lakhs',
                'status' => 'Coming Soon',
                'possession' => 'Dec 2027',
                'image' => 'awadhpuri.jpg',
                'description' => '20 bigha premium project at Safadarganj, Lucknow with ultra-modern facilities.',
                'amenities' => [
                    'Ultra-modern infrastructure',
                    'Smart city features',
                    'Clubhouse and amenities',
                    'Underground utilities',
                    'Landscaped gardens',
                    'Advanced security'
                ]
            ]
        ];

        // Filter projects by status
        $ongoing_projects = array_filter($projects, function ($project) {
            return $project['status'] === 'Ongoing';
        });

        $planned_projects = array_filter($projects, function ($project) {
            return $project['status'] === 'Planned' || $project['status'] === 'Coming Soon';
        });

        $data = [
            'all_projects' => $projects,
            'ongoing_projects' => $ongoing_projects,
            'planned_projects' => $planned_projects
        ];

        $this->render('pages/company_projects', $data);
    }

    /**
     * Display the blog page
     */
    public function blog()
    {
        // Get blog posts
        $blog_posts = [
            [
                'id' => 1,
                'title' => '5 Tips for Buying Your First Home',
                'excerpt' => 'Buying your first home can be overwhelming. Here are 5 essential tips to help you make the right decision.',
                'content' => 'Buying your first home is a major milestone in life. It requires careful planning and consideration...',
                'author' => 'APS Dream Home Team',
                'date' => '2024-01-15',
                'image' => 'blog1.jpg',
                'category' => 'Buying Guide',
                'tags' => ['first home', 'buying tips', 'real estate']
            ],
            [
                'id' => 2,
                'title' => 'Why Gorakhpur is the Next Real Estate Hub',
                'excerpt' => 'Gorakhpur is emerging as a major real estate destination in Uttar Pradesh. Discover why...',
                'content' => 'Gorakhpur, the city of Lord Buddha, is rapidly transforming into a major real estate hub...',
                'author' => 'Market Analyst',
                'date' => '2024-01-10',
                'image' => 'blog2.jpg',
                'category' => 'Market Trends',
                'tags' => ['gorakhpur', 'real estate', 'investment']
            ],
            [
                'id' => 3,
                'title' => 'Understanding RERA: What Homebuyers Need to Know',
                'excerpt' => 'RERA has transformed the real estate sector. Here\'s what every homebuyer should know...',
                'content' => 'The Real Estate (Regulation and Development) Act, 2016 (RERA) has brought significant changes...',
                'author' => 'Legal Expert',
                'date' => '2024-01-05',
                'image' => 'blog3.jpg',
                'category' => 'Legal',
                'tags' => ['rera', 'homebuyers', 'legal']
            ]
        ];

        // Get blog categories
        $categories = [
            'Buying Guide' => 15,
            'Market Trends' => 12,
            'Legal' => 8,
            'Investment' => 10,
            'Home Improvement' => 6
        ];

        // Get popular tags
        $popular_tags = [
            'real estate' => 25,
            'home buying' => 20,
            'investment' => 18,
            'gorakhpur' => 15,
            'rera' => 12,
            'property' => 10
        ];

        $data = [
            'blog_posts' => $blog_posts,
            'categories' => $categories,
            'popular_tags' => $popular_tags
        ];

        $this->render('pages/blog', $data);
    }

    /**
     * Display FAQ page
     */
    public function faq()
    {
        // Get FAQ categories and questions
        $faq_categories = [
            'General' => [
                [
                    'question' => 'What is APS Dream Home?',
                    'answer' => 'APS Dream Home is a leading real estate development company specializing in residential and commercial properties across Uttar Pradesh.'
                ],
                [
                    'question' => 'How long has APS Dream Home been in business?',
                    'answer' => 'We have been in the real estate business since 2008, with over 15 years of experience.'
                ],
                [
                    'question' => 'Where are your projects located?',
                    'answer' => 'Our projects are primarily located in Gorakhpur, Lucknow, and Kushinagar districts of Uttar Pradesh.'
                ]
            ],
            'Buying Process' => [
                [
                    'question' => 'How can I book a plot?',
                    'answer' => 'You can book a plot by visiting our office, calling our sales team, or filling out the inquiry form on our website.'
                ],
                [
                    'question' => 'What documents are required for booking?',
                    'answer' => 'You need to provide identity proof, address proof, PAN card, and passport-size photographs for booking.'
                ],
                [
                    'question' => 'What is the booking amount?',
                    'answer' => 'The booking amount is typically 10% of the total plot value, but it may vary depending on the project.'
                ]
            ],
            'Payment' => [
                [
                    'question' => 'What payment options are available?',
                    'answer' => 'We offer various payment options including lump sum payment, installment plans, and bank loan facilities.'
                ],
                [
                    'question' => 'Do you provide bank loan assistance?',
                    'answer' => 'Yes, we have tie-ups with major banks and can assist you with the loan application process.'
                ],
                [
                    'question' => 'Are there any hidden charges?',
                    'answer' => 'No, we believe in complete transparency. All charges are clearly mentioned in the agreement.'
                ]
            ],
            'Legal' => [
                [
                    'question' => 'Are your projects RERA registered?',
                    'answer' => 'Yes, all our ongoing projects are RERA registered and compliant with all regulations.'
                ],
                [
                    'question' => 'What legal documents will I receive?',
                    'answer' => 'You will receive the sale agreement, registry documents, and all other necessary legal papers.'
                ],
                [
                    'question' => 'Is the land free from legal disputes?',
                    'answer' => 'Yes, all our projects have clear titles and are free from any legal disputes.'
                ]
            ]
        ];

        $data = [
            'faq_categories' => $faq_categories
        ];

        $this->render('pages/faq', $data);
    }

    /**
     * Display team page
     */
    public function team()
    {
        // Get team members by department
        $team = [
            'management' => [
                [
                    'name' => 'Anurag Pratap Singh',
                    'position' => 'Managing Director',
                    'image' => 'director.jpg',
                    'description' => 'With over 20 years of experience in real estate, Mr. Singh leads the company with vision and expertise.',
                    'linkedin' => '#',
                    'email' => 'director@apsdreamhome.com'
                ],
                [
                    'name' => 'Priya Singh',
                    'position' => 'Operations Head',
                    'image' => 'operations.jpg',
                    'description' => 'Ms. Singh ensures smooth operations and timely delivery of all projects.',
                    'linkedin' => '#',
                    'email' => 'operations@apsdreamhome.com'
                ]
            ],
            'sales' => [
                [
                    'name' => 'Rajesh Kumar',
                    'position' => 'Sales Manager',
                    'image' => 'sales.jpg',
                    'description' => 'Mr. Kumar heads the sales team and ensures excellent customer service.',
                    'linkedin' => '#',
                    'email' => 'sales@apsdreamhome.com'
                ],
                [
                    'name' => 'Sunita Devi',
                    'position' => 'Sales Executive',
                    'image' => 'sales1.jpg',
                    'description' => 'Ms. Devi assists customers in finding their dream properties.',
                    'linkedin' => '#',
                    'email' => 'sunita@apsdreamhome.com'
                ]
            ],
            'technical' => [
                [
                    'name' => 'Amit Sharma',
                    'position' => 'Site Engineer',
                    'image' => 'engineer.jpg',
                    'description' => 'Mr. Sharma oversees construction quality and project execution.',
                    'linkedin' => '#',
                    'email' => 'engineer@apsdreamhome.com'
                ],
                [
                    'name' => 'Ravi Verma',
                    'position' => 'Architect',
                    'image' => 'architect.jpg',
                    'description' => 'Mr. Verma designs innovative and functional spaces for our projects.',
                    'linkedin' => '#',
                    'email' => 'architect@apsdreamhome.com'
                ]
            ],
            'support' => [
                [
                    'name' => 'Neha Gupta',
                    'position' => 'HR Manager',
                    'image' => 'hr.jpg',
                    'description' => 'Ms. Gupta manages human resources and organizational development.',
                    'linkedin' => '#',
                    'email' => 'hr@apsdreamhome.com'
                ],
                [
                    'name' => 'Vikas Singh',
                    'position' => 'Accountant',
                    'image' => 'accountant.jpg',
                    'description' => 'Mr. Singh handles all financial matters and accounting.',
                    'linkedin' => '#',
                    'email' => 'accounts@apsdreamhome.com'
                ]
            ]
        ];

        $data = [
            'team' => $team
        ];

        $this->render('pages/team', $data);
    }

    /**
     * Display testimonials page
     */
    public function testimonials()
    {
        // Get testimonials
        $testimonials = [
            [
                'name' => 'Ramesh Kumar',
                'location' => 'Gorakhpur',
                'project' => 'Suyoday Colony',
                'text' => 'I bought a plot in Suyoday Colony and I am very satisfied with the service and quality. The team was very helpful throughout the process.',
                'rating' => 5,
                'date' => '2024-01-10',
                'image' => 'customer1.jpg'
            ],
            [
                'name' => 'Sunita Devi',
                'location' => 'Lucknow',
                'project' => 'Awadhpuri',
                'text' => 'The team at APS Dream Home is very professional and helpful. They guided me through the entire buying process and made it very smooth.',
                'rating' => 5,
                'date' => '2024-01-05',
                'image' => 'customer2.jpg'
            ],
            [
                'name' => 'Amit Singh',
                'location' => 'Kushinagar',
                'project' => 'Budh Bihar Colony',
                'text' => 'Great experience with Budh Bihar Colony. The location and amenities are excellent. The project is well-planned and executed.',
                'rating' => 4,
                'date' => '2023-12-20',
                'image' => 'customer3.jpg'
            ],
            [
                'name' => 'Pooja Sharma',
                'location' => 'Gorakhpur',
                'project' => 'Raghunat Nagri',
                'text' => 'The infrastructure and amenities in Raghunat Nagri are top-class. The team ensured timely possession and all promises were delivered.',
                'rating' => 5,
                'date' => '2023-12-15',
                'image' => 'customer4.jpg'
            ],
            [
                'name' => 'Rahul Verma',
                'location' => 'Gorakhpur',
                'project' => 'Braj Radha Nagri',
                'text' => 'Affordable pricing with quality construction. The team was very cooperative and transparent throughout the process.',
                'rating' => 4,
                'date' => '2023-12-10',
                'image' => 'customer5.jpg'
            ],
            [
                'name' => 'Anita Gupta',
                'location' => 'Lucknow',
                'project' => 'Awadhpuri',
                'text' => 'Premium project with excellent features. The location is strategic and the future prospects are very good.',
                'rating' => 5,
                'date' => '2023-12-05',
                'image' => 'customer6.jpg'
            ]
        ];

        // Get testimonial statistics
        $stats = [
            'total_testimonials' => 1000,
            'average_rating' => 4.8,
            'satisfied_customers' => 950,
            'projects_reviewed' => 50
        ];

        $data = [
            'testimonials' => $testimonials,
            'stats' => $stats
        ];

        $this->render('pages/testimonials', $data);
    }

    /**
     * Handle contact form submission
     */
    public function submitContact()
    {
        // Validate form data
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';

        // Basic validation
        if (empty($name) || empty($email) || empty($phone) || empty($message)) {
            $_SESSION['error'] = 'All fields are required';
            header('Location: ' . BASE_URL . '/contact');
            exit;
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email address';
            header('Location: ' . BASE_URL . '/contact');
            exit;
        }

        // Validate phone
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            $_SESSION['error'] = 'Invalid phone number';
            header('Location: ' . BASE_URL . '/contact');
            exit;
        }

        // Save contact inquiry to database
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO contact_inquiries (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $phone, $subject, $message]);

            // Send email notification
            $to = 'info@apsdreamhome.com';
            $subject_email = 'New Contact Inquiry: ' . $subject;
            $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
            $headers = "From: $email\r\n";

            mail($to, $subject_email, $body, $headers);

            $_SESSION['success'] = 'Your inquiry has been submitted successfully. We will contact you soon.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to submit inquiry. Please try again.';
        }

        header('Location: ' . BASE_URL . '/contact');
        exit;
    }

    /**
     * Handle career application submission
     */
    public function submitCareerApplication()
    {
        // Validate form data
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $position = $_POST['position'] ?? '';
        $experience = $_POST['experience'] ?? '';
        $message = $_POST['message'] ?? '';

        // Basic validation
        if (empty($name) || empty($email) || empty($phone) || empty($position)) {
            $_SESSION['error'] = 'All fields are required';
            header('Location: ' . BASE_URL . '/career');
            exit;
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email address';
            header('Location: ' . BASE_URL . '/career');
            exit;
        }

        // Handle file upload
        $resume_path = '';
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/resumes/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = time() . '_' . basename($_FILES['resume']['name']);
            $resume_path = $upload_dir . $file_name;

            if (!move_uploaded_file($_FILES['resume']['tmp_name'], $resume_path)) {
                $_SESSION['error'] = 'Failed to upload resume';
                header('Location: ' . BASE_URL . '/career');
                exit;
            }
        }

        // Save application to database
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO career_applications (name, email, phone, position, experience, message, resume_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $phone, $position, $experience, $message, $resume_path]);

            // Send email notification
            $to = 'hr@apsdreamhome.com';
            $subject_email = 'New Career Application: ' . $position;
            $body = "Name: $name\nEmail: $email\nPhone: $phone\nPosition: $position\nExperience: $experience\n\nMessage:\n$message";
            $headers = "From: $email\r\n";

            mail($to, $subject_email, $body, $headers);

            $_SESSION['success'] = 'Your application has been submitted successfully. We will review it and contact you soon.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to submit application. Please try again.';
        }

        header('Location: ' . BASE_URL . '/career');
        exit;
    }

    /**
     * Suyoday Colony Project Page
     */
    public function suyodayColony()
    {
        $data = [
            'page_title' => 'Suyoday Colony - APS Dream Home',
            'page_description' => 'Premium residential plots in Gorakhpur with modern infrastructure',
            'project' => [
                'name' => 'Suyoday Colony',
                'location' => 'Gorakhpur',
                'type' => 'Residential Plots',
                'area' => '15 Acres',
                'total_plots' => '200+',
                'starting_price' => '₹7.5 Lakhs',
                'status' => 'Ongoing',
                'possession' => 'Dec 2025',
                'rera_approved' => 'Yes'
            ]
        ];

        $this->render('pages/suyoday_colony', $data);
    }

    /**
     * Raghunat Nagri Project Page
     */
    public function raghunatNagri()
    {
        $data = [
            'page_title' => 'Raghunat Nagri - APS Dream Home',
            'page_description' => 'Premium residential plots in Gorakhpur with excellent connectivity',
            'project' => [
                'name' => 'Raghunat Nagri',
                'location' => 'Gorakhpur',
                'type' => 'Residential Plots',
                'area' => '25 Acres',
                'total_plots' => '600+',
                'starting_price' => '₹8.5 Lakhs',
                'status' => 'Ongoing',
                'possession' => 'Dec 2026',
                'rera_approved' => 'Yes'
            ]
        ];

        $this->render('pages/raghunat_nagri', $data);
    }

    /**
     * Braj Radha Nagri Project Page
     */
    public function brajRadhaNagri()
    {
        $data = [
            'page_title' => 'Braj Radha Nagri - APS Dream Home',
            'page_description' => 'Affordable residential plots with modern amenities in Gorakhpur',
            'project' => [
                'name' => 'Braj Radha Nagri',
                'location' => 'Gorakhpur',
                'type' => 'Residential Plots',
                'area' => '20 Acres',
                'total_plots' => '400+',
                'starting_price' => '₹6.5 Lakhs',
                'status' => 'Planned',
                'possession' => 'Jun 2026',
                'rera_approved' => 'Applied'
            ]
        ];

        $this->render('pages/braj_radha_nagri', $data);
    }

    /**
     * Budh Bihar Colony Project Page
     */
    public function budhBiharColony()
    {
        $data = [
            'page_title' => 'Budh Bihar Colony - APS Dream Home',
            'page_description' => 'Integrated township at Premwaliya, Kushinagar Highway',
            'project' => [
                'name' => 'Budh Bihar Colony',
                'location' => 'Kushinagar',
                'type' => 'Integrated Township',
                'area' => '15+ Acres',
                'total_plots' => '300+',
                'starting_price' => '₹5.5 Lakhs',
                'status' => 'Ongoing',
                'possession' => 'Mar 2026',
                'rera_approved' => 'Yes'
            ]
        ];

        $this->render('pages/budh_bihar_colony', $data);
    }

    /**
     * Awadhpuri Project Page
     */
    public function awadhpuri()
    {
        $data = [
            'page_title' => 'Awadhpuri - APS Dream Home',
            'page_description' => '20 bigha premium project at Safadarganj, Lucknow',
            'project' => [
                'name' => 'Awadhpuri',
                'location' => 'Lucknow',
                'type' => 'Premium Residential',
                'area' => '20 Bigha',
                'total_plots' => '500+',
                'starting_price' => '₹12 Lakhs',
                'status' => 'Coming Soon',
                'possession' => 'Dec 2027',
                'rera_approved' => 'Applied'
            ]
        ];

        $this->render('pages/awadhpuri', $data);
    }

    /**
     * Display Terms and Conditions page
     */
    public function terms()
    {
        error_log("PAGECONTROLLER: terms() method called");

        $data = [
            'page_title' => 'Terms and Conditions - APS Dream Home',
            'page_description' => 'Terms and conditions for using APS Dream Home real estate services'
        ];

        error_log("PAGECONTROLLER: About to render terms_conditions view");
        $this->render('pages/legal/terms_conditions', $data);
        error_log("PAGECONTROLLER: Finished rendering terms_conditions view");
    }

    /**
     * Display Privacy Policy page
     */
    public function privacy()
    {
        $data = [
            'page_title' => 'Privacy Policy - APS Dream Home',
            'page_description' => 'Privacy policy for APS Dream Home real estate services and data protection'
        ];

        $this->render('pages/legal/privacy_policy', $data);
    }

    /**
     * Get Featured Properties API
     * Returns JSON response of featured properties for AJAX requests
     */
    public function getFeaturedProperties()
    {
        // Set response header
        header('Content-Type: application/json');

        // Featured properties data
        $featured_properties = [
            [
                'id' => 1,
                'title' => 'Suyoday Colony',
                'location' => 'Gorakhpur',
                'price' => '₹7.5 Lakhs',
                'image' => BASE_URL . '/assets/images/projects/gorakhpur/suryoday.jpg',
                'type' => 'Residential',
                'status' => 'Available',
                'description' => 'Premium residential plots with modern infrastructure'
            ],
            [
                'id' => 2,
                'title' => 'Raghunat Nagri',
                'location' => 'Gorakhpur',
                'price' => '₹8.5 Lakhs',
                'image' => BASE_URL . '/assets/images/projects/gorakhpur/raghunath nagri motiram.JPG',
                'type' => 'Residential',
                'status' => 'Available',
                'description' => 'Premium residential plots in developing area'
            ],
            [
                'id' => 3,
                'title' => 'Braj Radha Nagri',
                'location' => 'Gorakhpur',
                'price' => '₹6.5 Lakhs',
                'image' => BASE_URL . '/assets/images/projects/gorakhpur/suryoday1.jpeg',
                'type' => 'Residential',
                'status' => 'Available',
                'description' => 'Affordable residential plots with amenities'
            ]
        ];

        // Return JSON response
        echo json_encode([
            'success' => true,
            'data' => $featured_properties,
            'count' => count($featured_properties)
        ]);
        exit;
    }

    // WhatsApp Chat
    public function whatsappChat()
    {
        $data = [
            'page_title' => 'WhatsApp Chat - APS Dream Home',
            'page_description' => 'Connect with us via WhatsApp for instant support'
        ];
        $this->render('pages/whatsapp_chat', $data);
    }

    // Virtual Tour
    public function virtualTour()
    {
        $data = [
            'page_title' => 'Virtual Tour - APS Dream Home',
            'page_description' => 'Take a virtual tour of our premium properties'
        ];
        $this->render('pages/virtual_tour', $data);
    }

    // User AI Suggestions
    public function userAiSuggestions()
    {
        $data = [
            'page_title' => 'AI Suggestions - APS Dream Home',
            'page_description' => 'Get AI-powered property suggestions'
        ];
        $this->render('pages/user_ai_suggestions', $data);
    }

    // User Saved Searches
    public function userSavedSearches()
    {
        $data = [
            'page_title' => 'Saved Searches - APS Dream Home',
            'page_description' => 'View your saved property searches'
        ];
        $this->render('pages/user/saved_searches', $data);
    }

    // User Notifications
    public function userNotifications()
    {
        $data = [
            'page_title' => 'Notifications - APS Dream Home',
            'page_description' => 'Manage your notifications and alerts'
        ];
        $this->render('pages/user/notifications', $data);
    }

    // User Investments
    public function userInvestments()
    {
        $data = [
            'page_title' => 'Investments - APS Dream Home',
            'page_description' => 'Track your property investments'
        ];
        $this->render('pages/user/investments', $data);
    }

    // User Edit Profile
    public function userEditProfile()
    {
        $data = [
            'page_title' => 'Edit Profile - APS Dream Home',
            'page_description' => 'Update your profile information'
        ];
        $this->render('pages/user/edit_profile', $data);
    }

    // Under Construction
    public function underConstruction()
    {
        $data = [
            'page_title' => 'Under Construction - APS Dream Home',
            'page_description' => 'This page is under construction'
        ];
        $this->render('pages/under_construction', $data);
    }

    // Thank You
    public function thankYou()
    {
        $data = [
            'page_title' => 'Thank You - APS Dream Home',
            'page_description' => 'Thank you for contacting APS Dream Home'
        ];
        $this->render('pages/thank_you', $data);
    }

    // Support
    public function support()
    {
        $data = [
            'page_title' => 'Support - APS Dream Home',
            'page_description' => 'Get help and support from APS Dream Home'
        ];
        $this->render('pages/support', $data);
    }

    // Sitemap
    public function sitemap()
    {
        $data = [
            'page_title' => 'Sitemap - APS Dream Home',
            'page_description' => 'Complete sitemap of APS Dream Home website'
        ];
        $this->render('pages/sitemap', $data);
    }

    // Services
    public function services()
    {
        $data = [
            'page_title' => 'Services - APS Dream Home',
            'page_description' => 'Comprehensive real estate services by APS Dream Home'
        ];
        $this->render('pages/services', $data);
    }

    // Resell
    public function resell()
    {
        $data = [
            'page_title' => 'Resell Property - APS Dream Home',
            'page_description' => 'Sell your property through APS Dream Home'
        ];
        $this->render('pages/resell', $data);
    }

    // Plots Availability
    public function plotsAvailability()
    {
        $data = [
            'page_title' => 'Plots Availability - APS Dream Home',
            'page_description' => 'Check available plots across our projects'
        ];
        $this->render('pages/plots-availability', $data);
    }

    // Plot
    public function plot()
    {
        $data = [
            'page_title' => 'Plot Details - APS Dream Home',
            'page_description' => 'View detailed plot information'
        ];
        $this->render('pages/plot', $data);
    }

    // News
    public function news()
    {
        $data = [
            'page_title' => 'News - APS Dream Home',
            'page_description' => 'Latest news and updates from APS Dream Home'
        ];
        $this->render('pages/news', $data);
    }

    // Navigation
    public function navigation()
    {
        $data = [
            'page_title' => 'Navigation - APS Dream Home',
            'page_description' => 'Navigate APS Dream Home website'
        ];
        $this->render('pages/navigation', $data);
    }

    // MLM Dashboard
    public function mlmDashboard()
    {
        $data = [
            'page_title' => 'MLM Dashboard - APS Dream Home',
            'page_description' => 'Manage your MLM network and earnings'
        ];
        $this->render('pages/mlm-dashboard', $data);
    }

    // Financial Services
    public function financialServices()
    {
        $data = [
            'page_title' => 'Financial Services - APS Dream Home',
            'page_description' => 'Banking and financial services for property buyers'
        ];
        $this->render('pages/financial_services', $data);
    }

    // Featured Properties
    public function featuredProperties()
    {
        $data = [
            'page_title' => 'Featured Properties - APS Dream Home',
            'page_description' => 'Handpicked premium properties by APS Dream Home'
        ];
        $this->render('pages/featured_properties', $data);
    }

    // FAQs
    public function faqs()
    {
        $data = [
            'page_title' => 'FAQs - APS Dream Home',
            'page_description' => 'Frequently asked questions about APS Dream Home'
        ];
        $this->render('pages/faqs', $data);
    }

    // Downloads
    public function downloads()
    {
        $data = [
            'page_title' => 'Downloads - APS Dream Home',
            'page_description' => 'Download brochures and documents from APS Dream Home'
        ];
        $this->render('pages/downloads', $data);
    }

    // Customer Reviews
    public function customerReviews()
    {
        $data = [
            'page_title' => 'Customer Reviews - APS Dream Home',
            'page_description' => 'Read reviews from our satisfied customers'
        ];
        $this->render('pages/customer_reviews', $data);
    }

    // Create Mobile App
    public function createMobileApp()
    {
        $data = [
            'page_title' => 'Mobile App - APS Dream Home',
            'page_description' => 'Download APS Dream Home mobile application'
        ];
        $this->render('pages/create_mobile_app', $data);
    }

    // Interior Design
    public function interiorDesign()
    {
        $data = [
            'page_title' => 'Interior Design - APS Dream Home',
            'page_description' => 'Professional interior design services'
        ];
        $this->render('pages/interior_design', $data);
    }

    // Email System
    public function emailSystem()
    {
        $data = [
            'page_title' => 'Email System - APS Dream Home',
            'page_description' => 'Send emails to APS Dream Home team'
        ];
        $this->render('pages/email_system', $data);
    }

    // Legal Terms Conditions
    public function legalTermsConditions()
    {
        $data = [
            'page_title' => 'Terms & Conditions - APS Dream Home',
            'page_description' => 'Detailed terms and conditions of APS Dream Home'
        ];
        $this->render('pages/legal/terms_conditions', $data);
    }

    // Legal Services
    public function legalServices()
    {
        $data = [
            'page_title' => 'Legal Services - APS Dream Home',
            'page_description' => 'Legal services provided by APS Dream Home'
        ];
        $this->render('pages/legal/services', $data);
    }

    // Legal Documents
    public function legalDocuments()
    {
        $data = [
            'page_title' => 'Legal Documents - APS Dream Home',
            'page_description' => 'Access legal documents and agreements'
        ];
        $this->render('pages/legal/documents', $data);
    }

    // System Log Security Event
    public function systemLogSecurityEvent()
    {
        $data = [
            'page_title' => 'Security Log - APS Dream Home',
            'page_description' => 'System security event logging'
        ];
        $this->render('pages/system/log_security_event', $data);
    }

    // System Launch System
    public function systemLaunchSystem()
    {
        $data = [
            'page_title' => 'Launch System - APS Dream Home',
            'page_description' => 'System launch and deployment interface'
        ];
        $this->render('pages/system/launch_system', $data);
    }

    // System KYC Upload
    public function systemKycUpload()
    {
        $data = [
            'page_title' => 'KYC Upload - APS Dream Home',
            'page_description' => 'Know Your Customer verification system'
        ];
        $this->render('pages/system/kyc-upload', $data);
    }

    // WhatsApp Templates
    public function whatsappTemplates()
    {
        $data = [
            'page_title' => 'WhatsApp Templates - APS Dream Home',
            'page_description' => 'WhatsApp message templates for marketing'
        ];
        $this->render('pages/whatsapp-templates', $data);
    }
}
