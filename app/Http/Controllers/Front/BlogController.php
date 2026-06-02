<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

class BlogController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC");
            $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $posts = [];
        }
        $this->render('pages/blog_article_list', [
            'page_title' => 'Blog Articles',
            'posts' => $posts
        ]);
    }

    public function show($slug)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status = 'published'");
            $stmt->execute([$slug]);
            $post = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $post = null;
        }
        if (!$post) {
            $this->redirect('/blog');
            return;
        }
        $this->render('pages/blog_article_detail', [
            'page_title' => $post['title'] ?? 'Blog Article',
            'post' => $post
        ]);
    }
}
