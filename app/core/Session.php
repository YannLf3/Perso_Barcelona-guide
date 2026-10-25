<?php

declare(strict_types=1);

/**
 * Session PHP sécurisée : cookie httpOnly, secure en HTTPS et sameSite=Lax.
 */
final class Session
{
    /**
     * Démarre la session si elle ne l'est pas déjà.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('btsession');
        session_start();
    }

    /**
     * Détecte HTTPS, y compris derrière un proxy qui transmet X-Forwarded-Proto.
     */
    public static function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }

    /**
     * Indique si un administrateur est connecté.
     */
    public static function isAdmin(): bool
    {
        self::start();

        return ($_SESSION['admin_id'] ?? null) !== null;
    }

    /**
     * Ouvre la session administrateur et change d'identifiant pour éviter la fixation de session.
     */
    public static function login(int $userId): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $userId;
    }

    /**
     * Détruit la session administrateur.
     */
    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        session_regenerate_id(true);
        session_destroy();
    }
}
