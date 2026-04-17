<?php

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find a user by email, phone, or username
     */
    public function findByLogin($loginId) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :id OR phone = :id OR username = :id LIMIT 1");
        $stmt->execute(['id' => $loginId]);
        return $stmt->fetch();
    }

    /**
     * Find user by email specifically for password resets
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Set password reset token
     */
    public function setResetToken($email, $token) {
        // Expiration in 1 hour
        $stmt = $this->db->prepare("UPDATE users SET reset_token = :token, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email");
        return $stmt->execute(['token' => $token, 'email' => $email]);
    }

    /**
     * Verify token
     */
    public function verifyResetToken($token) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = :token AND reset_token_expires > NOW() LIMIT 1");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }

    /**
     * Reset password and clear token
     */
    public function resetPasswordWithToken($token, $newPassword) {
        $user = $this->verifyResetToken($token);
        if (!$user) return false;

        $hash = Security::hashPassword($newPassword);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash, reset_token = NULL, reset_token_expires = NULL WHERE id = :id");
        return $stmt->execute(['hash' => $hash, 'id' => $user['id']]);
    }


    /**
     * Register a new user
     */
    public function register($username, $loginId, $password, $isPhone = false) {
        $passwordHash = Security::hashPassword($password);

        $email = $isPhone ? null : $loginId;
        $phone = $isPhone ? $loginId : null;

        // Random coins between 1 and 500
        $initialCoins = rand(1, 500);

        $stmt = $this->db->prepare("INSERT INTO users (username, email, phone, password_hash, role, coins) VALUES (:username, :email, :phone, :password_hash, 'user', :coins)");

        try {
            return $stmt->execute([
                'username' => empty($username) ? null : $username,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => $passwordHash,
                'coins' => $initialCoins
            ]);
        } catch (PDOException $e) {
            // Usually duplicate entry error
            return false;
        }
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
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

    /**
     * Update user profile settings
     */
    public function updateProfile($id, $username, $email, $phone) {
        $stmt = $this->db->prepare("UPDATE users SET username = :username, email = :email, phone = :phone WHERE id = :id");
        try {
            return $stmt->execute([
                'username' => empty($username) ? null : $username,
                'email' => empty($email) ? null : $email,
                'phone' => empty($phone) ? null : $phone,
                'id' => $id
            ]);
        } catch (PDOException $e) {
            return false; // usually duplicate unique key
        }
    }

    /**
     * Update user password
     */
    public function updatePassword($id, $newPassword) {
        $hash = Security::hashPassword($newPassword);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        return $stmt->execute(['hash' => $hash, 'id' => $id]);
    }
}
