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
}
