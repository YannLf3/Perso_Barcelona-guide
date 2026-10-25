<?php

declare(strict_types=1);

/**
 * Protection CSRF.
 * Admin : jeton aléatoire stocké en session.
 * Public : jeton signé (HMAC + horodatage), sans session pour ne pas poser de cookie aux visiteurs.
 */
final class Csrf
{
    private const PUBLIC_LIFETIME = 14400;

    /**
     * Jeton lié à la session administrateur.
     */
    public static function adminToken(): string
    {
        Session::start();

        return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
    }

    /**
     * Jeton signé pour le formulaire de contact public.
     */
    public static function publicToken(): string
    {
        $issuedAt = (string) time();

        return $issuedAt . '.' . self::sign($issuedAt);
    }

    /**
     * Valide le jeton reçu pour la portée demandée ("admin" ou "public").
     *
     * @param string $scope Portée du jeton
     * @param string $token Jeton reçu (en-tête X-CSRF-Token ou champ du formulaire)
     */
    public static function isValid(string $scope, string $token): bool
    {
        if ($token === '') {
            return false;
        }

        if ($scope === 'admin') {
            Session::start();

            return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        }

        $parts = explode('.', $token, 2);

        if (count($parts) !== 2 || !ctype_digit($parts[0])) {
            return false;
        }

        $age = time() - (int) $parts[0];

        return $age >= 0 && $age <= self::PUBLIC_LIFETIME && hash_equals(self::sign($parts[0]), $parts[1]);
    }

    /**
     * Lit le jeton envoyé par la requête courante.
     *
     * @param array<string, mixed> $input Corps de la requête déjà décodé
     */
    public static function fromRequest(array $input): string
    {
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        return $header !== '' ? (string) $header : (string) ($input['_csrf'] ?? '');
    }

    private static function sign(string $payload): string
    {
        return hash_hmac('sha256', 'csrf|' . $payload, Env::get('APP_KEY'));
    }
}
