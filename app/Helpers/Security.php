<?php

class Security {

    /**
     * Generate a CSRF token and store it in the session
     */
    public static function generateCSRFToken() {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    /**
     * Regenerate CSRF token (useful after privilege escalation/login)
     */
    public static function regenerateCSRFToken() {
        Session::set('csrf_token', bin2hex(random_bytes(32)));
        return Session::get('csrf_token');
    }

    /**
     * Verify the provided CSRF token against the session
     */
    public static function verifyCSRFToken($token) {
        if (!Session::has('csrf_token')) {
            return false;
        }
        return hash_equals(Session::get('csrf_token'), $token);
    }

    /**
     * HTML escape to prevent XSS
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Hash a password securely (Argon2id preferred, fallback to bcrypt)
     */
    public static function hashPassword($password) {
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID);
        }
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify a password against a hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
