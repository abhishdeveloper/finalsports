<?php

class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            // Strict session settings for bank-grade security
            ini_set('session.use_only_cookies', 1);
            ini_set('session.use_strict_mode', 1);

            $cookieParams = session_get_cookie_params();
            session_set_cookie_params([
                'lifetime' => $cookieParams["lifetime"],
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => isset($_SERVER['HTTPS']), // true if HTTPS
                'httponly' => true,
                'samesite' => 'Strict' // Protects against CSRF
            ]);

            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        if (self::has($key)) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
            // Clear session cookie
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

    public static function regenerate() {
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
