<?php

class UserModel
{
    private PDO $database;

    public function __construct(PDO $database)
    {
        $this->database = $database;
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id_user, password_hash FROM users WHERE username = :username'
        );
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function findPasswordHash(int $userId): ?string
    {
        $statement = $this->database->prepare('SELECT password_hash FROM users WHERE id_user = :id');
        $statement->execute(['id' => $userId]);
        $hash = $statement->fetchColumn();
        return is_string($hash) ? $hash : null;
    }

    public function updatePassword(int $userId, string $passwordHash): bool
    {
        $statement = $this->database->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE id_user = :id'
        );
        return $statement->execute(['password_hash' => $passwordHash, 'id' => $userId]);
    }

    public function create(string $username, string $passwordHash): int
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, \'user\')'
        );
        $statement->execute(['username' => $username, 'password_hash' => $passwordHash]);
        return (int) $this->database->lastInsertId();
    }

    public function getRoleTargetUsername(int $userId): ?string
    {
        $statement = $this->database->prepare('SELECT username FROM users WHERE id_user = :id');
        $statement->execute(['id' => $userId]);
        $username = $statement->fetchColumn();
        return is_string($username) ? $username : null;
    }

    public function updateRole(int $userId, string $role): bool
    {
        $statement = $this->database->prepare('UPDATE users SET role = :role WHERE id_user = :id');
        return $statement->execute(['role' => $role, 'id' => $userId]);
    }

    public function getAll(): array
    {
        return $this->database->query(
            'SELECT id_user, username, role, created_at FROM users ORDER BY username'
        )->fetchAll();
    }

    public function findUser(int $userId): ?array
    {
        $statement = $this->database->prepare('SELECT id_user, username, role, created_at FROM users WHERE id_user = :id');
        $statement->execute(['id' => $userId]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function getSuperadmin(): ?array
    {
        $bootstrap = $this->database->prepare('SELECT id_user, username, role FROM users WHERE role = \'admin\' ORDER BY id_user ASC LIMIT 1');
        $bootstrap->execute();
        return $bootstrap->fetch() ?: null;
    }

    public function isSuperadmin(int $userId, ?string $bootstrapUsername = null): bool
    {
        if ($bootstrapUsername !== null) {
            $statement = $this->database->prepare('SELECT id_user FROM users WHERE username = :username AND role = \'admin\' LIMIT 1');
            $statement->execute(['username' => $bootstrapUsername]);
            $id = $statement->fetchColumn();
            if ($id !== false) return (int) $id === $userId;
        }
        $statement = $this->database->query('SELECT id_user FROM users WHERE role = \'admin\' ORDER BY id_user ASC LIMIT 1');
        return (int) $statement->fetchColumn() === $userId;
    }

    public function updateUsername(int $userId, string $username): bool
    {
        $statement = $this->database->prepare('UPDATE users SET username = :username WHERE id_user = :id');
        return $statement->execute(['username' => $username, 'id' => $userId]);
    }

    public function deleteUser(int $userId): bool
    {
        $statement = $this->database->prepare('DELETE FROM users WHERE id_user = :id');
        return $statement->execute(['id' => $userId]);
    }

    public function hasAdministrator(): bool
    {
        return (int) $this->database->query(
            "SELECT COUNT(*) FROM users WHERE role = 'admin'"
        )->fetchColumn() > 0;
    }

    public function ensureUsersTable(): void
    {
        $this->database->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id_user INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT \'user\',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function findById(int $userId): ?array
    {
        $statement = $this->database->prepare(
            'SELECT id_user, username, role FROM users WHERE id_user = :id'
        );
        $statement->execute(['id' => $userId]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function findBootstrapUser(string $username): ?array
    {
        $statement = $this->database->prepare('SELECT id_user, role FROM users WHERE username = :username');
        $statement->execute(['username' => $username]);
        $user = $statement->fetch();
        return $user ?: null;
    }

    public function promoteBootstrapUser(int $userId, string $passwordHash): void
    {
        $statement = $this->database->prepare(
            'UPDATE users SET password_hash = :password_hash, role = \'admin\' WHERE id_user = :id'
        );
        $statement->execute(['password_hash' => $passwordHash, 'id' => $userId]);
    }

    public function createBootstrapAdmin(string $username, string $passwordHash): void
    {
        $statement = $this->database->prepare(
            'INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, \'admin\')'
        );
        $statement->execute(['username' => $username, 'password_hash' => $passwordHash]);
    }
}
