-- SQL to add sample featured properties to APS Dream Home database
-- This includes complete property details and amenities

-- First, let's ensure we have the property types
INSERT IGNORE INTO `property_type` (`type_name`, `description`, `status`) VALUES 
('Apartment', 'Modern apartments with premium amenities', 'active'),
('Villa', 'Luxury villas with private spaces', 'active'),
('Plot', 'Residential and commercial plots', 'active'),
('Farm House', 'Spacious farm houses with greenery', 'active'),
('Commercial', 'Commercial spaces and offices', 'active');

-- Add sample featured properties
INSERT INTO `property` (
    `title`, `pcontent`, `type`, `bhk`, `stype`, `bedroom`, `bathroom`, 
    `balcony`, `kitchen`, `hall`, `floor`, `size`, `price`, `location`, 
    `city`, `state`, `feature`, `pimage`, `pimage1`, `pimage2`, `pimage3`, 
    `pimage4`, `uid`, `status`, `mapimage`, `is_featured`, `featured_until`,
    `created_at`, `updated_at`
) VALUES 
-- Luxury Apartment in Mumbai
(
    'Luxury 3BHK Apartment in Bandra West', 
    'Experience luxury living in the heart of Bandra with this stunning 3BHK apartment. Features premium finishes, modern amenities, and breathtaking views of the Arabian Sea. Perfect for those who appreciate fine living.',
    'Apartment', 
    '3 BHK', 
    'Luxury', 
    3, 
    3, 
    2, 
    1, 
    1, 
    '5th', 
    1800, 
    45000000, 
    'Bandra West, Mumbai', 
    'Mumbai', 
    'Maharashtra',
    'Swimming Pool, Gym, 24/7 Security, Power Backup, Rain Water Harvesting, Club House, Landscaped Garden, Children\'s Play Area, Jogging Track, Intercom Facility, Lift, Water Storage, Car Parking, Security, Maintenance Staff, Vaastu Compliant',
    '/assets/images/properties/bandra-apartment-1.jpg',
    '/assets/images/properties/bandra-apartment-2.jpg',
    '/assets/images/properties/bandra-apartment-3.jpg',
    '/assets/images/properties/bandra-apartment-4.jpg',
    '/assets/images/properties/bandra-apartment-5.jpg',
    1, 
    'Available', 
    '/assets/images/maps/bandra-map.jpg', 
    1, 
    DATE_ADD(NOW(), INTERVAL 30 DAY),
    NOW(),
    NOW()
),

-- Modern Villa in Bangalore
(
    'Modern 4BHK Villa in Whitefield', 
    'This elegant 4BHK villa in Whitefield offers a perfect blend of modern architecture and comfortable living. The property features a private garden, modern interiors, and premium finishes throughout.',
    'Villa', 
    '4 BHK', 
    'Modern', 
    4, 
    4, 
    3, 
    1, 
    1, 
    'G+1', 
    4000, 
    35000000, 
    'Whitefield, Bangalore', 
    'Bangalore', 
    'Karnataka',
    'Private Garden, Swimming Pool, Modular Kitchen, Home Automation, Power Backup, 24/7 Security, Rain Water Harvesting, Club House, Landscaped Garden, Children\'s Play Area, Jogging Track, Intercom Facility, Lift, Water Storage, Car Parking, Security, Maintenance Staff, Vaastu Compliant',
    '/assets/images/properties/whitefield-villa-1.jpg',
    '/assets/images/properties/whitefield-villa-2.jpg',
    '/assets/images/properties/whitefield-villa-3.jpg',
    '/assets/images/properties/whitefield-villa-4.jpg',
    '/assets/images/properties/whitefield-villa-5.jpg',
    1, 
    'Available', 
    '/assets/images/maps/whitefield-map.jpg', 
    1, 
    DATE_ADD(NOW(), INTERVAL 45 DAY),
    NOW(),
    NOW()
),

-- Residential Plot in Hyderabad
(
    'Premium Residential Plot in Gachibowli', 
    'Premium residential plot in the heart of Gachibowli, Hyderabad. Ideal for building your dream home in one of the most sought-after locations in the city. Clear title and all approvals in place.',
    'Plot', 
    'NA', 
    'Residential', 
    0, 
    0, 
    0, 
    0, 
    0, 
    'NA', 
    3000, 
    15000000, 
    'Gachibowli, Hyderabad', 
    'Hyderabad', 
    'Telangana',
    'Clear Title, All Approvals, Corner Plot, 40ft Road Facing, Water Connection, Electricity Connection, Gated Community, Security, Well Connected, Vaastu Compliant',
    '/assets/images/properties/gachibowli-plot-1.jpg',
    '/assets/images/properties/gachibowli-plot-2.jpg',
    '/assets/images/properties/gachibowli-plot-3.jpg',
    '/assets/images/properties/gachibowli-plot-4.jpg',
    '/assets/images/properties/gachibowli-plot-5.jpg',
    1, 
    'Available', 
    '/assets/images/maps/gachibowli-map.jpg', 
    1, 
    DATE_ADD(NOW(), INTERVAL 60 DAY),
    NOW(),
    NOW()
);

-- Add property amenities
-- Note: This assumes you have a property_amenities table
-- If not, you may need to create it or adjust the structure accordingly
INSERT IGNORE INTO `property_amenities` (`property_id`, `amenity_name`, `amenity_icon`) VALUES
-- Bandra Apartment Amenities
(LAST_INSERT_ID()-2, 'Swimming Pool', 'fa-swimming-pool'),
(LAST_INSERT_ID()-2, 'Gym', 'fa-dumbbell'),
(LAST_INSERT_ID()-2, '24/7 Security', 'fa-shield-alt'),
(LAST_INSERT_ID()-2, 'Power Backup', 'fa-bolt'),
(LAST_INSERT_ID()-2, 'Rain Water Harvesting', 'fa-tint'),
(LAST_INSERT_ID()-2, 'Club House', 'fa-building'),
(LAST_INSERT_ID()-2, 'Landscaped Garden', 'fa-tree'),
(LAST_INSERT_ID()-2, 'Children\'s Play Area', 'fa-child'),
(LAST_INSERT_ID()-2, 'Jogging Track', 'fa-running'),
(LAST_INSERT_ID()-2, 'Car Parking', 'fa-car'),

-- Whitefield Villa Amenities
(LAST_INSERT_ID()-1, 'Private Garden', 'fa-leaf'),
(LAST_INSERT_ID()-1, 'Swimming Pool', 'fa-swimming-pool'),
(LAST_INSERT_ID()-1, 'Modular Kitchen', 'fa-utensils'),
(LAST_INSERT_ID()-1, 'Home Automation', 'fa-lightbulb'),
(LAST_INSERT_ID()-1, 'Power Backup', 'fa-bolt'),
(LAST_INSERT_ID()-1, '24/7 Security', 'fa-shield-alt'),
(LAST_INSERT_ID()-1, 'Rain Water Harvesting', 'fa-tint'),
(LAST_INSERT_ID()-1, 'Club House', 'fa-building'),
(LAST_INSERT_ID()-1, 'Landscaped Garden', 'fa-tree'),
(LAST_INSERT_ID()-1, 'Children\'s Play Area', 'fa-child'),

-- Gachibowli Plot Amenities
(LAST_INSERT_ID(), 'Clear Title', 'fa-file-contract'),
(LAST_INSERT_ID(), 'All Approvals', 'fa-clipboard-check'),
(LAST_INSERT_ID(), 'Corner Plot', 'fa-vector-square'),
(LAST_INSERT_ID(), '40ft Road Facing', 'fa-road'),
(LAST_INSERT_ID(), 'Water Connection', 'fa-faucet'),
(LAST_INSERT_ID(), 'Electricity Connection', 'fa-bolt'),
(LAST_INSERT_ID(), 'Gated Community', 'fa-home'),
(LAST_INSERT_ID(), 'Security', 'fa-shield-alt'),
(LAST_INSERT_ID(), 'Well Connected', 'fa-map-marked-alt'),
(LAST_INSERT_ID(), 'Vaastu Compliant', 'fa-home');
