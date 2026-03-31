<?php

require_once BASE_PATH . 'app/core/Controller.php';
require_once BASE_PATH . 'app/core/Auth.php';
require_once BASE_PATH . 'app/models/User.php';

class AuthController extends Controller
{
    public function showLogin(): void
    {
        Auth::requireGuest();

        // Handle POST login submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }

        // Show login form (GET)
        $error = $_GET['error'] ?? null;
        $errorMessages = [
            'invalid' => 'Invalid username or password.',
            'empty' => 'Please enter both username and password.',
            'required' => 'You must be logged in to access this page.'
        ];

        $this->view('auth/login', [
            'pageTitle' => 'Login',
            'error' => $error ? ($errorMessages[$error] ?? 'Login failed.') : null
        ], 'guest');
    }

    private function processLogin(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/login?error=empty');
            exit;
        }

        $userModel = new User();
        $user = $userModel->authenticate($username, $password);

        if (!$user) {
            header('Location: ' . rtrim(BASE_URL, '/') . '/login?error=invalid');
            exit;
        }

        Auth::login($user);

        header('Location: ' . rtrim(BASE_URL, '/') . '/admin');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . rtrim(BASE_URL, '/') . '/login');
        exit;
    }
}
