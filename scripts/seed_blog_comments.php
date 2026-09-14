<?php
/**
 * Seed blog_comments with test data
 */
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    // Get first published blog post
    $stmt = $db->query("SELECT id FROM blog_posts WHERE status = 'published' ORDER BY id LIMIT 1");
    $post = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if (!$post) {
        echo "No published blog posts found. Creating one...\n";
        $db->exec("INSERT INTO blog_posts (title, slug, content, status, featured_image, tenant_id) VALUES 
            ('Top 10 Tips for Buying Your First Home', 'top-10-tips-first-home', '<p>Buying your first home is an exciting milestone. Here are our top tips to guide you through the process.</p>', 'published', 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800', 1),
            ('Understanding RERA Compliance in Real Estate', 'understanding-rera-compliance', '<p>RERA (Real Estate Regulatory Authority) compliance is essential for all real estate transactions.</p>', 'published', 'https://images.unsplash.com/photo-1560520653-9e0e4c89eb11?w=800', 1)
        ");
        $postId = $db->lastInsertId();
        echo "  Created 2 blog posts (id={$postId})\n";
    } else {
        $postId = $post['id'];
        echo "  Using existing blog post id={$postId}\n";
    }
    
    // Check existing comments
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM blog_comments WHERE post_id = ?");
    $stmt->execute([$postId]);
    $cnt = $stmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;
    
    if ($cnt == 0) {
        $db->prepare("INSERT INTO blog_comments (post_id, author_name, author_email, comment, status, tenant_id) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$postId, 'Rahul Kumar', 'rahul@example.com', 'Great article! Very informative about real estate trends in Lucknow.', 'approved', 1]);
        
        $db->prepare("INSERT INTO blog_comments (post_id, author_name, author_email, comment, status, tenant_id) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$postId, 'Priya Singh', 'priya@example.com', 'Very helpful for first-time buyers. Thank you for sharing these tips!', 'approved', 1]);
        
        $db->prepare("INSERT INTO blog_comments (post_id, author_name, author_email, comment, status, tenant_id) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$postId, 'Amit Verma', 'amit@example.com', 'I recently booked a plot at Suryoday Colony. The process was smooth.', 'approved', 1]);
        
        $db->prepare("INSERT INTO blog_comments (post_id, author_name, author_email, comment, status, tenant_id) VALUES (?, ?, ?, ?, ?, ?)")
           ->execute([$postId, 'Test Pending', 'test@example.com', 'This is a pending comment awaiting moderation.', 'pending', 1]);
        
        echo "  ✓ Seeded 4 test comments (3 approved, 1 pending)\n";
    } else {
        echo "  ✓ Comments already exist ($cnt)\n";
    }
    
    echo "\nDone!\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
