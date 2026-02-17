<?php

class CreateTestimonialsTable {
    public function up() {
        $sql = "
        CREATE TABLE IF NOT EXISTS `testimonials` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `rating` tinyint(1) NOT NULL DEFAULT 5,
            `testimonial` text NOT NULL,
            `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `status` (`status`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        
        -- Add some sample testimonials for demonstration
        INSERT INTO `testimonials` (`name`, `email`, `rating`, `testimonial`, `status`, `created_at`) VALUES
        ('Rahul Sharma', 'rahul@example.com', 5, 'Working with APS Dream Home was an amazing experience. They helped me find my dream home within my budget and made the entire process smooth and hassle-free.', 'approved', NOW() - INTERVAL 7 DAY),
        ('Priya Patel', 'priya@example.com', 4, 'The team at APS Dream Home provided exceptional service. They understood my requirements perfectly and showed me properties that matched exactly what I was looking for.', 'approved', NOW() - INTERVAL 5 DAY),
        ('Amit Singh', 'amit@example.com', 5, 'The rental management service from APS Dream Home has been exceptional. They handle everything professionally and I never have to worry about my property. Highly recommended!', 'approved', NOW() - INTERVAL 3 DAY);
        ";
        
        try {
            $conn = getMysqliConnection();
            $conn->multi_query($sql);
            
            // Clear any remaining results
            while ($conn->more_results() && $conn->next_result()) {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log('Error creating testimonials table: ' . $e->getMessage());
            return false;
        }
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS `testimonials`";
        
        try {
            $conn = getMysqliConnection();
            return $conn->query($sql);
        } catch (Exception $e) {
            error_log('Error dropping testimonials table: ' . $e->getMessage());
            return false;
        }
    }
}
