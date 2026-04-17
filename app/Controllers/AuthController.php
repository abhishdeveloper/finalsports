<?php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function index() {
        if ($this->userModel->isLoggedIn()) {
            $this->redirectBasedOnRole();
        }

        $this->view('auth/login');
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Verification
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $loginId = trim($_POST['login_id']);
            $password = $_POST['password'];

            if (empty($loginId) || empty($password)) {
                $this->view('auth/login', ['error' => 'Please enter all fields.']);
                return;
            }

            $user = $this->userModel->login($loginId, $password);

            if ($user) {
                $this->redirectBasedOnRole();
            } else {
                $this->view('auth/login', ['error' => 'Invalid credentials.']);
            }
        } else {
            $this->index();
        }
    }

    public function register() {
        if ($this->userModel->isLoggedIn()) {
            $this->redirectBasedOnRole();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CSRF Verification
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $loginId = trim($_POST['login_id']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if (empty($loginId) || empty($password) || empty($confirmPassword)) {
                $this->view('auth/register', ['error' => 'Please fill out all fields.']);
                return;
            }

            if ($password !== $confirmPassword) {
                $this->view('auth/register', ['error' => 'Passwords do not match.']);
                return;
            }

            // Basic check if it's a phone number (just digits and optional +) or email
            $isPhone = preg_match('/^[0-9\+\-\s]+$/', $loginId);

            if (!$isPhone && !filter_var($loginId, FILTER_VALIDATE_EMAIL)) {
                $this->view('auth/register', ['error' => 'Please enter a valid email or phone number.']);
                return;
            }

            if ($this->userModel->register($loginId, $password, $isPhone)) {
                // Auto login after register or redirect to login
                $this->view('auth/login', ['success' => 'Registration successful! Please login.']);
            } else {
                $this->view('auth/register', ['error' => 'Registration failed. Email/Phone might already be in use.']);
            }
        } else {
            $this->view('auth/register');
        }
    }

    public function logout() {
        $this->userModel->logout();
        header('Location: /');
        exit;
    }

    private function redirectBasedOnRole() {
        if ($this->userModel->isAdmin()) {
            header('Location: /admin/index');
        } else {
            header('Location: /user/index');
        }
        exit;
    }
}
