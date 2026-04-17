<?php

class AdminController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');

        if (!$this->userModel->isLoggedIn() || !$this->userModel->isAdmin()) {
            // Forbidden or redirect
            header('Location: /');
            exit;
        }
    }

    public function index() {
        $this->view('admin/dashboard');
    }
}
