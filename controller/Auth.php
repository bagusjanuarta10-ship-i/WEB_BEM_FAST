<?php

class Auth
{
    private static ?PDO $database = null;

    public static function initialize(PDO $database): void
    {
        self::$database = $database;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            ]);
            ini_set('session.use_strict_mode', '1');
            session_start();
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        (new UserModel(self::$database))->ensureUsersTable();

        self::bootstrapAdmin();
    }

    public static function currentUser(): ?array
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $user = (new UserModel(self::database()))->findById((int) $_SESSION['user_id']);

        if (!$user) {
            unset($_SESSION['user_id']);
            return null;
        }

        return $user;
    }

    public static function csrfToken(): string
    {
        return $_SESSION['csrf_token'];
    }

    public static function isValidCsrfToken($token): bool
    {
        return is_string($token)
            && hash_equals(self::csrfToken(), $token);
    }

    public static function isAdmin(): bool
    {
        $user = self::currentUser();
        return $user !== null && $user['role'] === 'admin';
    }

    public static function hasAdministrator(): bool
    {
        return (new UserModel(self::database()))->hasAdministrator();
    }

    public static function database(): PDO
    {
        if (self::$database === null) {
            throw new LogicException('Authentication has not been initialized.');
        }

        return self::$database;
    }

    public static function bootstrapUsername(): ?string
    {
        $username = getenv('BEM_ADMIN_USERNAME');
        return is_string($username) && $username !== '' ? $username : null;
    }

    private static function bootstrapAdmin(): void
    {
        $configuration = require __DIR__ . '/../config/auth.php';
        $username = $configuration['admin_username'];
        $password = $configuration['admin_password'];

        if ($username === null && ($password === null || $password === false || $password === '')) {
            return;
        }

        if (!is_string($username)
            || !preg_match('/\A[a-zA-Z0-9._-]{3,50}\z/', $username)
            || !is_string($password)
            || strlen($password) < 12) {
            throw new RuntimeException(
                'BEM_ADMIN_USERNAME dan BEM_ADMIN_PASSWORD harus dikonfigurasi; password minimal 12 karakter.'
            );
        }

        $model = new UserModel(self::database());
        $existingUser = $model->findBootstrapUser($username);
        if ($existingUser) {
            if ($existingUser['role'] !== 'admin') {
                $model->promoteBootstrapUser(
                    (int) $existingUser['id_user'],
                    password_hash($password, PASSWORD_DEFAULT)
                );
            }
            return;
        }

        $model->createBootstrapAdmin($username, password_hash($password, PASSWORD_DEFAULT));
    }
}
