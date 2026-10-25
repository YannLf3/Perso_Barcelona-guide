<?php

declare(strict_types=1);

/**
 * Session PHP sécurisée : cookie httpOnly, secure en HTTPS et sameSite=Lax.
 * La session administrateur expire après 30 minutes d'inactivité, et dans tous les cas 8 heures après la connexion.
 */
final class Session
{
    private const NAME = 'btsession';
    private const IDLE_TIMEOUT = 1800;
    private const ABSOLUTE_TIMEOUT = 28800;

    /**
     * Démarre la session si elle ne l'est pas déjà.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        // Mode strict : un identifiant de session inconnu du serveur est refusé (protection contre la fixation de session)
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) self::ABSOLUTE_TIMEOUT);

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => self::isHttps(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name(self::NAME);
        session_start();
    }

    /**
     * Détecte HTTPS (délègue à Request, qui ne croit X-Forwarded-Proto que venant d'un proxy de confiance).
     */
    public static function isHttps(): bool
    {
        return Request::isHttps();
    }

    /**
     * Indique si un administrateur est connecté et si sa session n'a pas expiré.
     * Chaque appel valide prolonge le délai d'inactivité.
     */
    public static function isAdmin(): bool
    {
        self::start();

        if (($_SESSION['admin_id'] ?? null) === null) {
            return false;
        }

        $now = time();
        $idle = $now - (int) ($_SESSION['last_activity'] ?? 0);
        $age = $now - (int) ($_SESSION['logged_in_at'] ?? 0);

        if ($idle > self::IDLE_TIMEOUT || $age > self::ABSOLUTE_TIMEOUT) {
            self::logout();

            return false;
        }

        $_SESSION['last_activity'] = $now;

        return true;
    }

    /**
     * Indique si la page publique doit être rendue en mode aperçu pour l'administration (?preview=1).
     * La session n'est démarrée que si le navigateur a déjà un cookie de session : aucun cookie n'est posé aux visiteurs.
     */
    public static function isAdminPreview(): bool
    {
        return ($_GET['preview'] ?? '') === '1' && isset($_COOKIE[self::NAME]) && self::isAdmin();
    }

    /**
     * Ouvre la session administrateur et change d'identifiant pour éviter la fixation de session.
     * Le jeton CSRF affiché avant la connexion est supprimé : Csrf::adminToken() en crée un nouveau.
     */
    public static function login(int $userId): void
    {
        self::start();
        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);
        $_SESSION['admin_id'] = $userId;
        $_SESSION['logged_in_at'] = time();
        $_SESSION['last_activity'] = time();
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
