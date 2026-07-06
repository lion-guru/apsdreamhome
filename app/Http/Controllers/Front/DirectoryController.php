<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\DirectoryService;

class DirectoryController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    private $directoryService;

    public function __construct()
    {
        parent::__construct();
        $this->directoryService = new DirectoryService();
    }

    public function index()
    {
        $categories = $this->directoryService->getActiveCategories();
        $featured = $this->directoryService->getFeaturedListings(8);
        $stats = $this->directoryService->getStats();

        $this->render('pages/directory/index', [
            'page_title' => 'Real Estate Services Directory',
            'meta_description' => 'Find trusted real estate service providers - masons, plumbers, electricians, architects, material suppliers and more',
            'categories' => $categories,
            'featured' => $featured,
            'stats' => $stats,
        ]);
    }

    public function category(string $slug)
    {
        $cat = $this->directoryService->getCategoryBySlug($slug);
        if (!$cat) {
            header('HTTP/1.0 404 Not Found');
            $this->render('pages/directory/index', ['page_title' => 'Category Not Found', 'error' => 'Category not found']);
            return;
        }

        $search = $_GET['search'] ?? '';
        $city = $_GET['city'] ?? '';
        $sort = $_GET['sort'] ?? 'latest';
        $page = max(1, (int)($_GET['page'] ?? 1));

        $listings = $this->directoryService->getListings($cat['id'], $search, $city, $sort, $page);

        $this->render('pages/directory/category', [
            'page_title' => $cat['name'] . ' - Real Estate Services',
            'category' => $cat,
            'listings' => $listings,
            'search' => $search,
            'city' => $city,
            'sort' => $sort,
        ]);
    }

    public function detail(int $id)
    {
        $listing = $this->directoryService->getListing($id);
        if (!$listing) {
            header('HTTP/1.0 404 Not Found');
            $this->render('pages/directory/index', ['page_title' => 'Not Found', 'error' => 'Listing not found']);
            return;
        }

        $reviews = $this->directoryService->getListingReviews($id);

        $this->render('pages/directory/detail', [
            'page_title' => $listing['business_name'] . ' - ' . ($listing['category_name'] ?? 'Services'),
            'listing' => $listing,
            'reviews' => $reviews,
        ]);
    }

    public function submitListing()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['user_id'] = $_SESSION['user_id'] ?? null;
            $data['status'] = 'pending';
            $result = $this->directoryService->upsertListing($data);
            if ($result) {
                $_SESSION['success'] = 'Your listing has been submitted for review!';
            } else {
                $_SESSION['error'] = 'Error submitting listing. Please try again.';
            }
            header('Location: ' . BASE_URL . '/services');
            exit;
        }

        $categories = $this->directoryService->getActiveCategories();
        $this->render('pages/directory/submit', [
            'page_title' => 'Submit Your Business/Service',
            'categories' => $categories,
        ]);
    }

    public function jobs()
    {
        $type = $_GET['type'] ?? '';
        $category = $_GET['category'] ?? '';
        $seek = isset($_GET['seeking']) ? (int)$_GET['seeking'] : -1;
        $page = max(1, (int)($_GET['page'] ?? 1));

        $jobs = $this->directoryService->getJobs($type, $category, $seek, $page);
        $jobCategories = ['Mason', 'Plumber', 'Electrician', 'Carpenter', 'Painter', 'Laborer', 'Driver', 'Security', 'Cleaner', 'Office Staff', 'Sales', 'Other'];

        $this->render('pages/directory/jobs', [
            'page_title' => 'Real Estate Jobs & Employment',
            'jobs' => $jobs,
            'jobCategories' => $jobCategories,
            'type' => $type,
            'category' => $category,
            'seek' => $seek,
        ]);
    }

    public function postJob()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['user_id'] = $_SESSION['user_id'] ?? null;
            $result = $this->directoryService->upsertJob($data);
            if ($result) {
                $_SESSION['success'] = 'Job posted successfully!';
            } else {
                $_SESSION['error'] = 'Error posting job.';
            }
            header('Location: ' . BASE_URL . '/services/jobs');
            exit;
        }

        $listings = $this->directoryService->getAllListings('approved');
        $jobCategories = ['Mason', 'Plumber', 'Electrician', 'Carpenter', 'Painter', 'Laborer', 'Driver', 'Security', 'Cleaner', 'Office Staff', 'Sales', 'Other'];

        $this->render('pages/directory/post_job', [
            'page_title' => 'Post a Job',
            'listings' => $listings,
            'jobCategories' => $jobCategories,
        ]);
    }

    public function materials()
    {
        $cat = $_GET['category'] ?? '';
        $search = $_GET['search'] ?? '';
        $materials = $this->directoryService->getMaterials($cat, $search);
        $materialCategories = ['Cement', 'Steel', 'Bricks', 'Sand', 'Aggregate', 'Paint', 'Tiles', 'Hardware', 'Wood', 'Plumbing', 'Electrical', 'Other'];

        $this->render('pages/directory/materials', [
            'page_title' => 'Construction Material Price Comparison',
            'materials' => $materials,
            'materialCategories' => $materialCategories,
            'selectedCategory' => $cat,
            'search' => $search,
        ]);
    }

    public function addReview()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/services');
            exit;
        }
        $data = [
            'listing_id' => (int)($_POST['listing_id'] ?? 0),
            'user_id' => $_SESSION['user_id'] ?? null,
            'reviewer_name' => $_POST['reviewer_name'] ?? 'Anonymous',
            'rating' => (int)($_POST['rating'] ?? 5),
            'review' => $_POST['review'] ?? '',
            'status' => 'approved',
        ];
        $this->directoryService->addReview($data);
        $_SESSION['success'] = 'Thank you for your review!';
        header('Location: ' . BASE_URL . '/services/listing/' . $data['listing_id']);
        exit;
    }

    public function apiCategories()
    {
        header('Content-Type: application/json');
        echo json_encode($this->directoryService->getActiveCategories());
        exit;
    }
}
