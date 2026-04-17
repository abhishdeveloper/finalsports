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

            $username = trim($_POST['username'] ?? '');
            $loginId = trim($_POST['login_id']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if (empty($loginId) || empty($password) || empty($confirmPassword)) {
                $this->view('auth/register', ['error' => 'Please fill out all required fields.']);
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

            if ($this->userModel->register($username, $loginId, $password, $isPhone)) {

                // Send Welcome Email if an email was provided
                if (!$isPhone) {
                    $subject = "Welcome to College Sports App!";
                    $body = "<h3>Hello " . Security::escape($username ?: 'Player') . ",</h3>
                             <p>Welcome to our app! You have been awarded a random coin bonus.</p>
                             <p>Log in and start predicting or playing mini-games now.</p>";
                    MailHelper::sendEmail($loginId, $subject, $body);
                }

                $this->view('auth/login', ['success' => 'Registration successful! Please login.']);
            } else {
                $this->view('auth/register', ['error' => 'Registration failed. Username/Email/Phone might already be in use.']);
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

    public function forgot() {
        if ($this->userModel->isLoggedIn()) {
            $this->redirectBasedOnRole();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $email = trim($_POST['email']);

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->view('auth/forgot', ['error' => 'Please enter a valid email address.']);
                return;
            }

            $user = $this->userModel->findByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $this->userModel->setResetToken($email, $token);

                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                $resetLink = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/auth/reset/" . $token;

                $subject = "Password Reset Request";
                $body = "<h3>Password Reset Request</h3>
                         <p>You requested a password reset. Click the link below to set a new password:</p>
                         <p><a href='" . $resetLink . "'>Reset Password</a></p>
                         <p>If you did not request this, please ignore this email. The link expires in 1 hour.</p>";

                MailHelper::sendEmail($email, $subject, $body);
            }

            // Always show success to prevent email enumeration
            $this->view('auth/forgot', ['success' => 'If an account with that email exists, a password reset link has been sent.']);
        } else {
            $this->view('auth/forgot');
        }
    }

    public function reset($token = null) {
        if ($this->userModel->isLoggedIn()) {
            $this->redirectBasedOnRole();
        }

        if (!$token) {
            header('Location: /auth/login');
            exit;
        }

        $user = $this->userModel->verifyResetToken($token);

        if (!$user) {
            $this->view('auth/login', ['error' => 'Invalid or expired password reset link.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if (empty($password) || empty($confirmPassword)) {
                $this->view('auth/reset', ['token' => $token, 'error' => 'Please fill out all fields.']);
                return;
            }

            if ($password !== $confirmPassword) {
                $this->view('auth/reset', ['token' => $token, 'error' => 'Passwords do not match.']);
                return;
            }

            if (strlen($password) < 6) {
                $this->view('auth/reset', ['token' => $token, 'error' => 'Password must be at least 6 characters.']);
                return;
            }

            if ($this->userModel->resetPasswordWithToken($token, $password)) {
                $this->view('auth/login', ['success' => 'Password has been successfully reset. Please log in.']);
            } else {
                $this->view('auth/reset', ['token' => $token, 'error' => 'Failed to reset password.']);
            }
        } else {
            $this->view('auth/reset', ['token' => $token]);
        }
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
