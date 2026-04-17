<?php

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');

        if (!$this->userModel->isLoggedIn()) {
            header('Location: /');
            exit;
        }
    }

    public function index() {
        $user = $this->userModel->getById(Session::get('user_id'));
        $this->view('user/dashboard', ['user' => $user]);
    }

    public function settings() {
        $user = $this->userModel->getById(Session::get('user_id'));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Security::verifyCSRFToken($_POST['csrf_token'])) {
                die("CSRF token validation failed");
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'update_profile') {
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $phone = trim($_POST['phone']);

                if ($this->userModel->updateProfile($user['id'], $username, $email, $phone)) {
                    $success = "Profile updated successfully.";
                    $user = $this->userModel->getById($user['id']); // refresh
                } else {
                    $error = "Failed to update profile. Email/Phone/Username might be taken.";
                }
            } elseif ($action === 'change_password') {
                $currentPass = $_POST['current_password'];
                $newPass = $_POST['new_password'];
                $confirmPass = $_POST['confirm_password'];

                if (!Security::verifyPassword($currentPass, $user['password_hash'])) {
                    $error = "Current password is incorrect.";
                } elseif ($newPass !== $confirmPass) {
                    $error = "New passwords do not match.";
                } elseif (strlen($newPass) < 6) {
                    $error = "New password must be at least 6 characters.";
                } else {
                    if ($this->userModel->updatePassword($user['id'], $newPass)) {
                        $success = "Password changed successfully.";
                    } else {
                        $error = "Failed to change password.";
                    }
                }
            }
        }

        $this->view('user/settings', [
            'user' => $user,
            'success' => $success ?? null,
            'error' => $error ?? null
        ]);
    }
}
