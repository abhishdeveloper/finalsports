<?php

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find a user by either phone or email
     */
    public function findByLogin($loginId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email OR phone = :phone LIMIT 1");
        $stmt->execute([
            'email' => $loginId,
            'phone' => $loginId
        ]);
        return $stmt->fetch();
    }

    /**
     * Register a new user
     */
    public function register($loginId, $password, $isPhone = false) {
        $passwordHash = Security::hashPassword($password);

        $email = $isPhone ? null : $loginId;
        $phone = $isPhone ? $loginId : null;

        $stmt = $this->db->prepare("INSERT INTO users (email, phone, password_hash, role) VALUES (:email, :phone, :password_hash, 'user')");

        try {
            return $stmt->execute([
                'email' => $email,
                'phone' => $phone,
                'password_hash' => $passwordHash
            ]);
        } catch (PDOException $e) {
            // Usually duplicate entry error
            return false;
        }
    }

    /**
     * Attempt to login a user
     */
    public function login($loginId, $password) {
        $user = $this->findByLogin($loginId);

        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            // Prevent session fixation by regenerating ID
            Session::regenerate();
            // Regenerate CSRF on privilege escalation/login
            Security::regenerateCSRFToken();

            Session::set('user_id', $user['id']);
            Session::set('user_role', $user['role']);
            return $user;
        }

        return false;
    }

    /**
     * Logout user
     */
    public function logout() {
        Session::destroy();
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return Session::has('user_id');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return Session::get('user_role') === 'admin';
    }
}
