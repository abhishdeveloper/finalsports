<?php

class App {
    protected $controller = 'AuthController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        require_once CORE_DIR . '/bootstrap.php';
    }

    public function run() {
        $url = $this->parseUrl();

        // Check if controller file exists
        $controllerName = isset($url[0]) ? ucfirst($url[0]) . 'Controller' : $this->controller;
        $controllerFile = APP_DIR . '/Controllers/' . $controllerName . '.php';

        if (file_exists($controllerFile)) {
            $this->controller = $controllerName;
            unset($url[0]);
        }

        require_once APP_DIR . '/Controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Check for method
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // Params
        $this->params = $url ? array_values($url) : [];

        // Call the method
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    protected function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
