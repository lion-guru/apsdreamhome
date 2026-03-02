<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Project;
use App\Models\Enquiry;

class DashboardController extends Controller
{
    private $userModel;
    private $propertyModel;
    private $projectModel;
    private $enquiryModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->propertyModel = new Property();
        $this->projectModel = new Project();
        $this->enquiryModel = new Enquiry();
    }

    public function index()
    {
        $userId = $_SESSION['user_id'];

        // Get user data
        $user = $this->userModel->getUserById($userId);

        // Get user's saved properties
        $savedProperties = $this->propertyModel->getUserSavedProperties($userId);

        // Get user's enquiries
        $enquiries = $this->enquiryModel->getUserEnquiries($userId);

        // Get recent properties for recommendations
        $recentProperties = $this->propertyModel->getRecentProperties(4);

        $data = [
            'title' => 'Dashboard - APS Dream Home',
            'user' => $user,
            'savedProperties' => $savedProperties,
            'enquiries' => $enquiries,
            'recentProperties' => $recentProperties
        ];

        $this->view('user/dashboard', $data);
    }

    public function profile()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        $data = [
            'title' => 'My Profile - APS Dream Home',
            'user' => $user,
            'error' => $this->getFlash('error'),
            'success' => $this->getFlash('success')
        ];

        $this->view('user/profile', $data);
    }

    public function settings()
    {
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        $data = [
            'title' => 'Settings - APS Dream Home',
            'user' => $user,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error')
        ];

        $this->view('user/settings', $data);
    }

    public function notifications()
    {
        $userId = $_SESSION['user_id'];

        // Fetch notifications from model (assuming a Notification model exists or using User model)
        // For now, let's just use dummy data if the model isn't ready
        $notifications = [];

        $data = [
            'title' => 'Notifications - APS Dream Home',
            'notifications' => $notifications
        ];

        $this->view('user/notifications', $data);
    }

    public function updateProfile()
    {
        // Validate CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/dashboard/profile');
            return;
        }

        $userId = $_SESSION['user_id'];

        // Get and validate input
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $zipcode = trim($_POST['zipcode'] ?? '');

        if (!$name || strlen($name) < 2) {
            $this->setFlash('error', 'Please enter a valid name (at least 2 characters).');
            $this->redirect('/dashboard/profile');
            return;
        }

        $updateData = [
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'zipcode' => $zipcode,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $result = $this->userModel->updateUser($userId, $updateData);

            if ($result) {
                // Update session name
                $_SESSION['user_name'] = $name;
                $this->setFlash('success', 'Profile updated successfully!');
            } else {
                $this->setFlash('error', 'Failed to update profile. Please try again.');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'An error occurred while updating your profile.');
        }

        $this->redirect('/dashboard/profile');
    }

    public function changePassword()
    {
        // Validate CSRF token
        if (!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/dashboard/profile');
            return;
        }

        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Get current user to verify password
        $user = $this->userModel->getUserById($userId);

        if (!password_verify($currentPassword, $user['password'])) {
            $this->setFlash('error', 'Current password is incorrect.');
            $this->redirect('/dashboard/profile');
            return;
        }

        if (!$newPassword || strlen($newPassword) < 6) {
            $this->setFlash('error', 'New password must be at least 6 characters long.');
            $this->redirect('/dashboard/profile');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'New passwords do not match.');
            $this->redirect('/dashboard/profile');
            return;
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        try {
            $result = $this->userModel->updatePassword($userId, $hashedPassword);

            if ($result) {
                $this->setFlash('success', 'Password changed successfully!');
            } else {
                $this->setFlash('error', 'Failed to change password. Please try again.');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'An error occurred while changing your password.');
        }

        $this->redirect('/dashboard/profile');
    }

    public function savedProperties()
    {
        $userId = $_SESSION['user_id'];

        // Get user's saved properties with details
        $savedProperties = $this->propertyModel->getUserSavedProperties($userId);

        $data = [
            'title' => 'Saved Properties - APS Dream Home',
            'savedProperties' => $savedProperties
        ];

        $this->view('user/saved-properties', $data);
    }

    public function saveProperty()
    {
        // Validate CSRF token
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $propertyId = intval($_POST['property_id'] ?? 0);

        if (!$propertyId) {
            echo json_encode(['success' => false, 'message' => 'Invalid property ID.']);
            return;
        }

        try {
            $result = $this->propertyModel->savePropertyForUser($userId, $propertyId);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Property saved successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Property already saved or not found.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred.']);
        }
    }

    public function unsaveProperty()
    {
        // Validate CSRF token
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $userId = $_SESSION['user_id'];
        $propertyId = intval($_POST['property_id'] ?? 0);

        if (!$propertyId) {
            echo json_encode(['success' => false, 'message' => 'Invalid property ID.']);
            return;
        }

        try {
            $result = $this->propertyModel->unsavePropertyForUser($userId, $propertyId);

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Property removed from saved list!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Property not found in saved list.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred.']);
        }
    }

    public function enquiries()
    {
        $userId = $_SESSION['user_id'];

        // Get user's enquiries
        $enquiries = $this->enquiryModel->getUserEnquiries($userId);

        $data = [
            'title' => 'My Enquiries - APS Dream Home',
            'enquiries' => $enquiries
        ];

        $this->view('user/enquiries', $data);
    }

    public function submitEnquiry()
    {
        // Validate CSRF token
        if (!$this->verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }

        $userId = $_SESSION['user_id'];

        // Get and validate input
        $propertyId = intval($_POST['property_id'] ?? 0);
        $projectId = intval($_POST['project_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $enquiryType = $_POST['enquiry_type'] ?? 'general';

        if (!$message || strlen($message) < 10) {
            echo json_encode(['success' => false, 'message' => 'Please enter a detailed message (at least 10 characters).']);
            return;
        }

        if (!$propertyId && !$projectId) {
            echo json_encode(['success' => false, 'message' => 'Please select a property or project.']);
            return;
        }

        $enquiryData = [
            'user_id' => $userId,
            'property_id' => $propertyId ?: null,
            'project_id' => $projectId ?: null,
            'message' => $message,
            'enquiry_type' => $enquiryType,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $enquiryId = $this->enquiryModel->createEnquiry($enquiryData);

            if ($enquiryId) {
                echo json_encode(['success' => true, 'message' => 'Enquiry submitted successfully! We will get back to you soon.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit enquiry. Please try again.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred while submitting your enquiry.']);
        }
    }
}
