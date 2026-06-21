<?php

class Csrf {
    public static function token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function field(): string {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::token()) . '">';
    }

    public static function isValid(?string $token): bool {
        return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Verifies the token on POST requests, otherwise redirects back with a flash error.
     * Must be called after session_start() and after flashMessage.php is loaded.
     */
    public static function requireValidOnPost(string $redirectTo): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !self::isValid($_POST['csrf_token'] ?? null)) {
            FlashMessage::setFlash('error', 'Your session has expired. Please try again.');
            header('Location: ' . $redirectTo);
            exit();
        }
    }
}
