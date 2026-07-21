<?php

namespace Admin;

class Auth
{
    public static function login(string $password): bool
    {
        $creds = self::loadCredentials();
        if ($password === $creds['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_login_time'] = time();
            return true;
        }
        return false;
    }

    public static function check(): bool
    {
        if (empty($_SESSION['admin_logged_in'])) {
            return false;
        }
        // Сессия живёт 2 часа
        if (time() - ($_SESSION['admin_login_time'] ?? 0) > 7200) {
            self::logout();
            return false;
        }
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_logged_in'], $_SESSION['admin_login_time']);
    }

    private static function loadCredentials(): array
    {
        $path = __DIR__ . '/../../config/credentials.php';
        if (!file_exists($path)) {
            return ['password' => 'admin'];
        }
        return require $path;
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: ?action=login');
            exit;
        }
    }
}
