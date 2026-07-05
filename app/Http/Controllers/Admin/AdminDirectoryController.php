<?php
namespace App\Http\Controllers\Admin;

use App\Services\DirectoryService;

class AdminDirectoryController extends AdminController
{
    private $directoryService;

    public function __construct()
    {
        parent::__construct();
        $this->directoryService = new DirectoryService();
    }

    // ── Dashboard / Stats ──
    public function index()
    {
        $this->requireAdmin();
        $stats = $this->directoryService->getStats();
        $listings = $this->directoryService->getAllListings();

        $this->render('admin/directory/index', [
            'page_title' => 'Directory Management',
            'stats' => $stats,
            'listings' => $listings,
        ]);
    }

    // ── Categories ──
    public function categories()
    {
        $this->requireAdmin();
        $categories = $this->directoryService->getAllCategories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            $this->directoryService->upsertCategory($_POST);
            $this->flashMessage('Category saved', 'success');
            header('Location: ' . BASE_URL . '/admin/directory/categories');
            exit;
        }

        $this->render('admin/directory/categories', [
            'page_title' => 'Directory Categories',
            'categories' => $categories,
        ]);
    }

    public function deleteCategory(int $id)
    {
        $this->requireAdmin();
        $this->directoryService->deleteCategory($id);
        $this->flashMessage('Category deleted', 'success');
        header('Location: ' . BASE_URL . '/admin/directory/categories');
        exit;
    }

    // ── Listings ──
    public function listings()
    {
        $this->requireAdmin();
        $status = $_GET['status'] ?? '';
        $catId = (int)($_GET['category_id'] ?? 0);
        $listings = $this->directoryService->getAllListings($status, $catId);
        $categories = $this->directoryService->getAllCategories();

        $this->render('admin/directory/listings', [
            'page_title' => 'Manage Listings',
            'listings' => $listings,
            'categories' => $categories,
            'filterStatus' => $status,
            'filterCat' => $catId,
        ]);
    }

    public function listingForm(int $id = 0)
    {
        $this->requireAdmin();
        $listing = null;
        if ($id) {
            $listings = $this->directoryService->getAllListings();
            foreach ($listings as $l) {
                if ((int)$l['id'] === $id) { $listing = $l; break; }
            }
            if (!$listing) {
                $this->flashMessage('Listing not found', 'error');
                header('Location: ' . BASE_URL . '/admin/directory/listings');
                exit;
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();
            $data = $_POST;
            if ($id) $data['id'] = $id;
            $result = $this->directoryService->upsertListing($data);
            if ($result) {
                $this->flashMessage('Listing saved', 'success');
            } else {
                $this->flashMessage('Error saving listing', 'error');
            }
            header('Location: ' . BASE_URL . '/admin/directory/listings');
            exit;
        }

        $categories = $this->directoryService->getAllCategories();

        $this->render('admin/directory/listing_form', [
            'page_title' => $id ? 'Edit Listing' : 'New Listing',
            'listing' => $listing,
            'categories' => $categories,
        ]);
    }

    public function deleteListing(int $id)
    {
        $this->requireAdmin();
        $this->directoryService->deleteListing($id);
        $this->flashMessage('Listing deleted', 'success');
        header('Location: ' . BASE_URL . '/admin/directory/listings');
        exit;
    }

    // ── Reviews ──
    public function reviews()
    {
        $this->requireAdmin();
        $reviews = $this->directoryService->getReviewsForAdmin();
        $this->render('admin/directory/reviews', [
            'page_title' => 'Review Moderation',
            'reviews' => $reviews,
        ]);
    }

    public function approveReview(int $id)
    {
        $this->requireAdmin();
        $this->directoryService->updateReviewStatus($id, 'approved');
        $this->flashMessage('Review approved', 'success');
        header('Location: ' . BASE_URL . '/admin/directory/reviews');
        exit;
    }

    public function rejectReview(int $id)
    {
        $this->requireAdmin();
        $this->directoryService->updateReviewStatus($id, 'rejected');
        $this->flashMessage('Review rejected', 'success');
        header('Location: ' . BASE_URL . '/admin/directory/reviews');
        exit;
    }

    // ── Jobs ──
    public function jobs()
    {
        $this->requireAdmin();
        $jobs = $this->directoryService->getAllJobsAdmin();
        $this->render('admin/directory/jobs', [
            'page_title' => 'Job Listings',
            'jobs' => $jobs,
        ]);
    }

    public function deleteJob(int $id)
    {
        $this->requireAdmin();
        $this->directoryService->deleteJob($id);
        $this->flashMessage('Job deleted', 'success');
        header('Location: ' . BASE_URL . '/admin/directory/jobs');
        exit;
    }

    // ── Materials ──
    public function materials()
    {
        $this->requireAdmin();
        $materials = $this->directoryService->getAllMaterialsAdmin();
        $this->render('admin/directory/materials', [
            'page_title' => 'Material Prices',
            'materials' => $materials,
        ]);
    }

    public function deleteMaterial(int $id)
    {
        $this->requireAdmin();
        $this->directoryService->deleteMaterial($id);
        $this->flashMessage('Material entry deleted', 'success');
        header('Location: ' . BASE_URL . '/admin/directory/materials');
        exit;
    }
}
