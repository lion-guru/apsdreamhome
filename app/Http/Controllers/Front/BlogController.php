<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class BlogController extends PageController
{
    use TenantAwareTrait;
    public function index()
    {
        $blog_posts = [];
        try {
            $stmt = $this->db->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC");
            $blog_posts = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log("BlogController::index error: " . $e->getMessage());
        }

        // Get categories from blog posts
        $categories = [];
        foreach ($blog_posts as $post) {
            if (!empty($post['category']) && !in_array($post['category'], array_column($categories, 'category'))) {
                $categories[] = ['category' => $post['category']];
            }
        }
        // Add default categories if none found
        if (empty($categories)) {
            $categories = [
                ['category' => 'Market Trends'],
                ['category' => 'Buying Guide'],
                ['category' => 'Interior Design'],
                ['category' => 'Investment'],
            ];
        }

        $data = [
            'page_title' => 'Blog - APS Dream Home',
            'page_description' => 'Latest news and articles from our blog',
            'blog_posts' => $blog_posts,
            'categories' => $categories
        ];
        $this->render('pages/blog', $data);
    }

    public function blog()
    {
        return $this->index();
    }

    public function show($slug)
    {
        $post = null;
        try {
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
            $stmt->execute([$slug]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("BlogController::show error: " . $e->getMessage());
        }
        if (!$post) {
            $this->redirect('/blog');
            return;
        }
        $this->render('pages/blog_article_detail', [
            'page_title' => ($post['title'] ?? 'Blog Post') . ' - APS Dream Home',
            'page_description' => $post['excerpt'] ?? '',
            'post' => $post,
        ]);
    }

    public function blogPost($slug = null)
    {
        return $this->show($slug);
    }

    public function blogDetail($slug = null)
    {
        return $this->show($slug);
    }
}