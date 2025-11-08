<?php
/**
 * Sample Data Seeder - APS Dream Homes
 * Seeds the database with sample data for testing and demonstration
 */

require_once 'includes/db_connection.php';

try {
    $pdo = getDbConnection();
    echo "Starting sample data seeding...\n";

    // Seed Properties
    seedProperties($pdo);
    echo "✓ Properties seeded successfully\n";

    // Seed Team Members
    seedTeamMembers($pdo);
    echo "✓ Team members seeded successfully\n";

    // Seed Testimonials
    seedTestimonials($pdo);
    echo "✓ Testimonials seeded successfully\n";

    // Seed Jobs
    seedJobs($pdo);
    echo "✓ Job openings seeded successfully\n";

    // Seed FAQs
    seedFAQs($pdo);
    echo "✓ FAQs seeded successfully\n";

    // Seed Site Settings
    seedSiteSettings($pdo);
    echo "✓ Site settings seeded successfully\n";

    echo "\n🎉 Sample data seeding completed successfully!\n";
    echo "You now have sample content for testing your APS Dream Homes website.\n";

} catch (Exception $e) {
    echo "❌ Error seeding sample data: " . $e->getMessage() . "\n";
}

function seedProperties($pdo) {
    $properties = [
        [
            'title' => 'Luxury Villa in Gomti Nagar',
            'description' => 'Spacious 4BHK luxury villa with modern amenities, garden, and parking. Perfect for families looking for premium living in Lucknow\'s most prestigious neighborhood.',
            'price' => 8500000,
            'location' => 'Gomti Nagar, Lucknow',
            'bedrooms' => 4,
            'bathrooms' => 3,
            'area' => 2400,
            'property_type' => 'villa',
            'status' => 'active',
            'featured' => 1,
            'image_url' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop',
            'amenities' => 'Garden, Swimming Pool, Garage, Security, Power Backup'
        ],
        [
            'title' => 'Modern Apartment in Indira Nagar',
            'description' => 'Beautiful 3BHK apartment with city views, modern kitchen, and excellent connectivity. Ideal for professionals and small families.',
            'price' => 4500000,
            'location' => 'Indira Nagar, Lucknow',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'area' => 1800,
            'property_type' => 'apartment',
            'status' => 'active',
            'featured' => 1,
            'image_url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800&h=600&fit=crop',
            'amenities' => 'Lift, Parking, Security, Intercom, Garden'
        ],
        [
            'title' => 'Commercial Space in Hazratganj',
            'description' => 'Prime commercial property in the heart of Lucknow\'s business district. Perfect for offices, showrooms, or retail businesses.',
            'price' => 12000000,
            'location' => 'Hazratganj, Lucknow',
            'bedrooms' => 0,
            'bathrooms' => 2,
            'area' => 3000,
            'property_type' => 'commercial',
            'status' => 'active',
            'featured' => 0,
            'image_url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&h=600&fit=crop',
            'amenities' => 'Prime Location, Parking, Security, Power Backup'
        ],
        [
            'title' => 'Cozy 2BHK in Aliganj',
            'description' => 'Affordable 2BHK apartment perfect for first-time buyers or small families. Well-connected with schools, markets, and hospitals nearby.',
            'price' => 2800000,
            'location' => 'Aliganj, Lucknow',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'area' => 1200,
            'property_type' => 'apartment',
            'status' => 'active',
            'featured' => 0,
            'image_url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
            'amenities' => 'Parking, Security, Water Supply, Nearby Market'
        ],
        [
            'title' => 'Penthouse in Mahanagar',
            'description' => 'Exclusive penthouse with panoramic city views, private terrace, and luxury finishes. The ultimate luxury living experience.',
            'price' => 15000000,
            'location' => 'Mahanagar, Lucknow',
            'bedrooms' => 5,
            'bathrooms' => 4,
            'area' => 3500,
            'property_type' => 'penthouse',
            'status' => 'active',
            'featured' => 1,
            'image_url' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop',
            'amenities' => 'Private Terrace, City Views, Luxury Finishes, Concierge'
        ]
    ];

    foreach ($properties as $property) {
        $stmt = $pdo->prepare("
            INSERT INTO properties
            (title, description, price, location, bedrooms, bathrooms, area, property_type,
             status, featured, image_url, amenities, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $property['title'], $property['description'], $property['price'],
            $property['location'], $property['bedrooms'], $property['bathrooms'],
            $property['area'], $property['property_type'], $property['status'],
            $property['featured'], $property['image_url'], $property['amenities']
        ]);
    }
}

function seedTeamMembers($pdo) {
    $team_members = [
        [
            'name' => 'Rajesh Kumar',
            'position' => 'Founder & CEO',
            'department' => 'management',
            'bio' => 'With over 15 years of experience in real estate, Rajesh founded APS Dream Homes with a vision to transform property buying and selling in India.',
            'experience' => 15,
            'specialization' => 'Strategic Planning, Business Development',
            'image_url' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop&crop=face',
            'status' => 'active',
            'display_order' => 1
        ],
        [
            'name' => 'Priya Sharma',
            'position' => 'Sales Director',
            'department' => 'sales',
            'bio' => 'Priya leads our sales team with expertise in customer relationship management and market analysis.',
            'experience' => 10,
            'specialization' => 'Customer Relations, Market Analysis',
            'image_url' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=300&h=300&fit=crop&crop=face',
            'status' => 'active',
            'display_order' => 2
        ],
        [
            'name' => 'Amit Patel',
            'position' => 'Legal Head',
            'department' => 'legal',
            'bio' => 'Amit ensures all our transactions are legally sound and provides expert guidance on property law.',
            'experience' => 12,
            'specialization' => 'Property Law, Documentation, Compliance',
            'image_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face',
            'status' => 'active',
            'display_order' => 3
        ],
        [
            'name' => 'Sneha Gupta',
            'position' => 'Financial Advisor',
            'department' => 'financial',
            'bio' => 'Sneha provides comprehensive financial planning and investment advice for property purchases.',
            'experience' => 8,
            'specialization' => 'Investment Planning, Loan Advisory',
            'image_url' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=300&h=300&fit=crop&crop=face',
            'status' => 'active',
            'display_order' => 4
        ],
        [
            'name' => 'Vikram Singh',
            'position' => 'Interior Designer',
            'department' => 'design',
            'bio' => 'Vikram creates beautiful, functional spaces that reflect our clients\' personalities and lifestyles.',
            'experience' => 9,
            'specialization' => 'Residential Design, Space Planning',
            'image_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=300&h=300&fit=crop&crop=face',
            'status' => 'active',
            'display_order' => 5
        ]
    ];

    foreach ($team_members as $member) {
        $stmt = $pdo->prepare("
            INSERT INTO team_members
            (name, position, department, bio, experience, specialization, image_url, status, display_order, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $member['name'], $member['position'], $member['department'],
            $member['bio'], $member['experience'], $member['specialization'],
            $member['image_url'], $member['status'], $member['display_order']
        ]);
    }
}

function seedTestimonials($pdo) {
    $testimonials = [
        [
            'name' => 'Anita and Rajesh Verma',
            'location' => 'Gomti Nagar, Lucknow',
            'rating' => 5,
            'testimonial' => 'APS Dream Homes made our dream of owning a home come true. Their team was professional, transparent, and guided us through every step. Highly recommended!',
            'property_type' => 'villa',
            'status' => 'active',
            'featured' => 1
        ],
        [
            'name' => 'Dr. Meera Singh',
            'location' => 'Indira Nagar, Lucknow',
            'rating' => 5,
            'testimonial' => 'Excellent service and genuine properties. The legal team ensured everything was perfect. Very satisfied with our apartment purchase.',
            'property_type' => 'apartment',
            'status' => 'active',
            'featured' => 1
        ],
        [
            'name' => 'Suresh Kumar',
            'location' => 'Hazratganj, Lucknow',
            'rating' => 4,
            'testimonial' => 'Great experience working with APS Dream Homes. They helped us find the perfect commercial space for our business. Professional service throughout.',
            'property_type' => 'commercial',
            'status' => 'active',
            'featured' => 0
        ],
        [
            'name' => 'Priya and Amit Sharma',
            'location' => 'Aliganj, Lucknow',
            'rating' => 5,
            'testimonial' => 'As first-time home buyers, we were nervous, but APS Dream Homes made the process smooth and stress-free. Thank you for our beautiful home!',
            'property_type' => 'apartment',
            'status' => 'active',
            'featured' => 1
        ]
    ];

    foreach ($testimonials as $testimonial) {
        $stmt = $pdo->prepare("
            INSERT INTO testimonials
            (name, location, rating, testimonial, property_type, status, featured, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $testimonial['name'], $testimonial['location'], $testimonial['rating'],
            $testimonial['testimonial'], $testimonial['property_type'],
            $testimonial['status'], $testimonial['featured']
        ]);
    }
}

function seedJobs($pdo) {
    $jobs = [
        [
            'title' => 'Senior Real Estate Consultant',
            'description' => 'We are looking for experienced real estate consultants to join our growing team. You will be responsible for helping clients find their dream properties and guiding them through the buying process.',
            'location' => 'Lucknow, Uttar Pradesh',
            'job_type' => 'full_time',
            'salary_min' => 400000,
            'salary_max' => 800000,
            'requirements' => 'Minimum 3 years experience in real estate sales
Excellent communication and negotiation skills
Knowledge of local property market
Valid driving license
Graduate degree preferred',
            'application_deadline' => date('Y-m-d', strtotime('+30 days')),
            'status' => 'active'
        ],
        [
            'title' => 'Property Manager',
            'description' => 'Manage rental properties, handle tenant relations, and ensure properties are well-maintained. Coordinate with maintenance teams and handle property inspections.',
            'location' => 'Lucknow, Uttar Pradesh',
            'job_type' => 'full_time',
            'salary_min' => 300000,
            'salary_max' => 500000,
            'requirements' => '2+ years experience in property management
Strong organizational skills
Knowledge of property laws and regulations
Customer service oriented
Basic computer skills required',
            'application_deadline' => date('Y-m-d', strtotime('+25 days')),
            'status' => 'active'
        ],
        [
            'title' => 'Marketing Executive',
            'description' => 'Develop and execute marketing campaigns for properties, manage social media presence, and create content to attract potential buyers and sellers.',
            'location' => 'Lucknow, Uttar Pradesh',
            'job_type' => 'full_time',
            'salary_min' => 250000,
            'salary_max' => 450000,
            'requirements' => '1-2 years experience in digital marketing
Proficiency in social media platforms
Content creation skills
Basic graphic design knowledge
Creative thinking and analytical skills',
            'application_deadline' => date('Y-m-d', strtotime('+20 days')),
            'status' => 'active'
        ]
    ];

    foreach ($jobs as $job) {
        $stmt = $pdo->prepare("
            INSERT INTO jobs
            (title, description, location, job_type, salary_min, salary_max, requirements,
             application_deadline, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $job['title'], $job['description'], $job['location'], $job['job_type'],
            $job['salary_min'], $job['salary_max'], $job['requirements'],
            $job['application_deadline'], $job['status']
        ]);
    }
}

function seedFAQs($pdo) {
    $faqs = [
        [
            'question' => 'How do I list my property for sale with APS Dream Homes?',
            'answer' => 'Simply contact us through our website or visit our office. Our team will visit your property, assess its value, and create a comprehensive listing with professional photos and marketing materials.',
            'category' => 'selling',
            'status' => 'active',
            'display_order' => 1
        ],
        [
            'question' => 'What documents do I need to buy a property?',
            'answer' => 'Essential documents include: PAN card, Aadhaar card, passport size photographs, address proof, income proof (salary slips/IT returns), bank statements, and property documents if applicable.',
            'category' => 'buying',
            'status' => 'active',
            'display_order' => 2
        ],
        [
            'question' => 'Do you provide home loans?',
            'answer' => 'We work with leading banks and financial institutions to help you get the best home loan rates. Our financial advisors will guide you through the entire loan process.',
            'category' => 'financing',
            'status' => 'active',
            'display_order' => 3
        ],
        [
            'question' => 'How long does the property buying process take?',
            'answer' => 'The typical property buying process takes 30-45 days from property selection to registration. This includes legal verification, loan processing, and final registration.',
            'category' => 'buying',
            'status' => 'active',
            'display_order' => 4
        ],
        [
            'question' => 'What are your service charges?',
            'answer' => 'Our consultation is free. We charge a reasonable brokerage fee only after successful transaction completion. The fee structure varies based on property value and is transparently communicated upfront.',
            'category' => 'services',
            'status' => 'active',
            'display_order' => 5
        ]
    ];

    foreach ($faqs as $faq) {
        $stmt = $pdo->prepare("
            INSERT INTO faqs
            (question, answer, category, status, display_order, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $faq['question'], $faq['answer'], $faq['category'],
            $faq['status'], $faq['display_order']
        ]);
    }
}

function seedSiteSettings($pdo) {
    $settings = [
        ['site_title', 'APS Dream Homes - Your Trusted Real Estate Partner'],
        ['site_description', 'Find your dream home with APS Dream Homes. We offer premium properties, expert consultation, and complete real estate solutions in Lucknow and beyond.'],
        ['contact_email', 'info@apsdreamhomes.com'],
        ['contact_phone', '+91-522-400-1234'],
        ['contact_address', '123, Gomti Nagar, Lucknow, Uttar Pradesh - 226010'],
        ['facebook_url', 'https://facebook.com/apsdreamhomes'],
        ['twitter_url', 'https://twitter.com/apsdreamhomes'],
        ['instagram_url', 'https://instagram.com/apsdreamhomes'],
        ['linkedin_url', 'https://linkedin.com/company/apsdreamhomes'],
        ['working_hours', 'Monday - Saturday: 9:00 AM - 7:00 PM, Sunday: 10:00 AM - 4:00 PM'],
        ['about_company', 'APS Dream Homes is Lucknow\'s premier real estate company, dedicated to helping families find their perfect homes and investors discover profitable opportunities.']
    ];

    foreach ($settings as $setting) {
        $stmt = $pdo->prepare("
            INSERT INTO site_settings (setting_key, setting_value, created_at, updated_at)
            VALUES (?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
        ");
        $stmt->execute([$setting[0], $setting[1]]);
    }
}
?>
