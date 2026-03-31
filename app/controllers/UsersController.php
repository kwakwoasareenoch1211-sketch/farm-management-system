<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/models/User.php';

class UsersController extends Controller
{
    public function index(): void
    {
        $userModel = new User();
        $users = $userModel->all();

        $this->view('users/index', [
            'pageTitle' => 'Users Management',
            'users' => $users,
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('users/create', [
            'pageTitle' => 'Create User',
        ], 'admin');
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/users');
            exit;
        }

        $userModel = new User();
        
        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        if ($userModel->create($data)) {
            $_SESSION['success'] = 'User created successfully';
            header('Location: ' . rtrim(BASE_URL, '/') . '/users');
        } else {
            $_SESSION['error'] = 'Failed to create user';
            header('Location: ' . rtrim(BASE_URL, '/') . '/users/create');
        }
        exit;
    }

    public function edit(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/users');
            exit;
        }

        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: ' . rtrim(BASE_URL, '/') . '/users');
            exit;
        }

        $this->view('users/edit', [
            'pageTitle' => 'Edit User',
            'user' => $user,
        ], 'admin');
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/users');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/users');
            exit;
        }

        $userModel = new User();
        
        $data = [
            'full_name' => $_POST['full_name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        // Only update password if provided
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        if ($userModel->update($id, $data)) {
            $_SESSION['success'] = 'User updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update user';
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/users');
        exit;
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . rtrim(BASE_URL, '/') . '/users');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/users');
            exit;
        }

        // Prevent deleting yourself
        if ($id === Auth::id()) {
            $_SESSION['error'] = 'You cannot delete your own account';
            header('Location: ' . rtrim(BASE_URL, '/') . '/users');
            exit;
        }

        $userModel = new User();
        
        if ($userModel->delete($id)) {
            $_SESSION['success'] = 'User deleted successfully';
        } else {
            $_SESSION['error'] = 'Failed to delete user';
        }

        header('Location: ' . rtrim(BASE_URL, '/') . '/users');
        exit;
    }
}
