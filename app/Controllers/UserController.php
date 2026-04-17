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
        $this->view('user/dashboard');
    }
}
