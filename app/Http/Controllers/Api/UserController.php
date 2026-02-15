<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use \Exception;

class UserController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware('role:admin');
        $this->middleware('csrf', ['only' => ['store', 'update', 'delete', 'changeStatus']]);
    }

    /**
     * List users
     */
    public function index()
    {
        try {
            $filters = [
                'role' => $this->request()->input('role'),
                'status' => $this->request()->input('status'),
                'page' => \max(1, (int)$this->request()->input('page', 1)),
                'limit' => \min(50, \max(1, (int)$this->request()->input('limit', 10)))
            ];

            // Implement listing in model
            $userModel = $this->model('User');
            $users = $userModel->list($filters);
            $total = $userModel->countUsers($filters);

            return $this->jsonSuccess([
                'users' => $users,
                'pagination' => [
                    'total' => $total,
                    'page' => $filters['page'],
                    'limit' => $filters['limit']
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to fetch users: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a specific user
     */
    public function show($id)
    {
        try {
            $user = $this->model('User')->find($id);

            if (!$user) {
                return $this->jsonError('User not found', 404);
            }

            return $this->jsonSuccess([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'status' => $user->status,
                'created_at' => $user->created_at
            ]);
        } catch (\Exception $e) {
            return $this->jsonError('Failed to fetch user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Create a new user
     */
    public function store()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed', 405);
        }

        try {
            $email = \filter_var($this->request()->input('email'), \FILTER_SANITIZE_EMAIL);
            $password = $this->request()->input('password');
            $name = strip_tags(\trim($this->request()->input('name')));

            if (empty($name) || empty($email) || empty($password)) {
                return $this->jsonError('Missing required fields: name, email, and password are required', 400);
            }

            // Check if email exists
            $userModel = $this->model('User');
            $existing = $userModel->where('uemail', $email)->first();
            if ($existing) {
                return $this->jsonError('Email already in use', 409);
            }

            $data = [
                'uname' => $name,
                'uemail' => $email,
                'upass' => \password_hash($password, \PASSWORD_DEFAULT),
                'utype' => strip_tags($this->request()->input('role', 'user')),
                'uphone' => strip_tags($this->request()->input('phone', '')),
                'status' => 'active',
                'join_date' => \date('Y-m-d H:i:s')
            ];

            $user = $userModel::create($data);

            if ($user && $user->id) {
                return $this->jsonSuccess(['id' => $user->id], 'User created successfully', 201);
            }

            return $this->jsonError('Failed to create user', 500);

        } catch (\Exception $e) {
            return $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }
}
