<?php

class Controller {
    public function model($model) {
        require_once APP_DIR . '/Models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $data = []) {
        if (file_exists(APP_DIR . '/Views/' . $view . '.php')) {
            extract($data);
            require_once APP_DIR . '/Views/' . $view . '.php';
        } else {
            die("View does not exist: " . $view);
        }
    }
}
