<?php

declare(strict_types=1);

/**
 * Compte d'administration (table BT_User).
 * Deux rôles : « admin » (accès complet) et « editor » (contenu uniquement).
 */
final class User
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';
    public const ROLES = [self::ROLE_ADMIN, self::ROLE_EDITOR];

    private int $id = 0;
    private string $username = '';
    private string $passwordHash = '';
    private string $role = self::ROLE_EDITOR;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        // Un rôle inconnu retombe sur le moins privilégié
        $this->role = in_array($role, self::ROLES, true) ? $role : self::ROLE_EDITOR;

        return $this;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    /**
     * Cherche un compte par identifiant.
     */
    public static function findByUsername(string $username): ?self
    {
        $stmt = Database::getConnection()->prepare('SELECT id, username, password_hash, role FROM BT_User WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();

        return $row === false ? null : (new self())
            ->setId((int) $row['id'])
            ->setUsername((string) $row['username'])
            ->setPasswordHash((string) $row['password_hash'])
            ->setRole((string) $row['role']);
    }

    /**
     * Vérifie le mot de passe saisi contre le hash stocké.
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * Note la date de dernière connexion et remplace le hash si l'algorithme a évolué.
     */
    public function markLoggedIn(string $password): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE BT_User SET last_login_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $this->id]);

        if (password_needs_rehash($this->passwordHash, PASSWORD_DEFAULT)) {
            $pdo->prepare('UPDATE BT_User SET password_hash = :hash WHERE id = :id')
                ->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $this->id]);
        }
    }

    /**
     * Crée ou remplace un compte : mot de passe et rôle (utilisé par bin/create-admin.php).
     */
    public static function upsert(string $username, string $password, string $role = self::ROLE_ADMIN): void
    {
        if (!in_array($role, self::ROLES, true)) {
            throw new InvalidArgumentException('Rôle inconnu : ' . $role);
        }

        $stmt = Database::getConnection()->prepare(
            'INSERT INTO BT_User (username, password_hash, role) VALUES (:username, :hash, :role)
             ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role)'
        );
        $stmt->execute(['username' => $username, 'hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => $role]);
    }
}
