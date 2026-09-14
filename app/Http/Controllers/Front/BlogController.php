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

        $comments = [];
        $commentCount = 0;
        try {
            $stmt = $this->db->prepare(
                "SELECT c.*, u.name as user_name FROM blog_comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.post_id = ? AND c.status = 'approved' ORDER BY c.created_at DESC"
            );
            $stmt->execute([$post['id']]);
            $comments = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $cntStmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM blog_comments WHERE post_id = ?");
            $cntStmt->execute([$post['id']]);
            $commentCount = $cntStmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;
        } catch (\Exception $e) {
            error_log("BlogController::show comments error: " . $e->getMessage());
        }

        $success = $_SESSION['comment_success'] ?? null;
        $error = $_SESSION['comment_error'] ?? null;
        unset($_SESSION['comment_success'], $_SESSION['comment_error']);

        $this->render('pages/blog_article_detail', [
            'page_title' => ($post['title'] ?? 'Blog Post') . ' - APS Dream Home',
            'page_description' => $post['excerpt'] ?? '',
            'post' => $post,
            'comments' => $comments,
            'comment_count' => $commentCount,
            'comment_success' => $success,
            'comment_error' => $error,
        ]);
    }

    public function submitComment($postId)
    {
        $postId = (int)$postId;
        if ($postId <= 0) {
            $_SESSION['comment_error'] = 'Invalid post.';
            $this->redirect('/blog');
            return;
        }

        $authorName = trim($_POST['author_name'] ?? '');
        $authorEmail = trim($_POST['author_email'] ?? '');
        $content = trim($_POST['comment'] ?? '');

        if (empty($content)) {
            $_SESSION['comment_error'] = 'Comment cannot be empty.';
            $this->redirect('/blog');
            return;
        }

        try {
            $stmt = $this->db->prepare("SELECT id, slug FROM blog_posts WHERE id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$post) {
                $_SESSION['comment_error'] = 'Post not found.';
                $this->redirect('/blog');
                return;
            }

            $userId = $_SESSION['user_id'] ?? null;

            $stmt = $this->db->prepare(
                "INSERT INTO blog_comments (post_id, user_id, author_name, author_email, comment, status, ip_address, user_agent, tenant_id) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?)"
            );
            $stmt->execute([
                $postId,
                $userId,
                $authorName ?: 'Guest',
                $authorEmail ?: null,
                $content,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null,
                $this->tenantId(),
            ]);

            $_SESSION['comment_success'] = 'Your comment has been submitted and is awaiting moderation.';
        } catch (\Exception $e) {
            error_log("BlogController::submitComment error: " . $e->getMessage());
            $_SESSION['comment_error'] = 'Failed to submit comment. Please try again.';
        }

        $this->redirect('/blog/' . ($post['slug'] ?? ''));
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