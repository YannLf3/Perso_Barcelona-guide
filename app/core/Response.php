<?php

declare(strict_types=1);

/**
 * Sortie HTTP : JSON, redirections et en-têtes de sécurité.
 */
final class Response
{
    private static ?string $nonce = null;

    private static bool $sameOriginFrame = false;

    /**
     * Autorise l'affichage de la réponse dans un cadre de la même origine (aperçu en direct de l'administration).
     * Par défaut, aucune page ne peut être encadrée.
     */
    public static function allowSameOriginFrame(): void
    {
        self::$sameOriginFrame = true;
    }

    /**
     * Retourne le nonce CSP de la requête courante (utilisé par les scripts en ligne).
     */
    public static function nonce(): string
    {
        return self::$nonce ??= base64_encode(random_bytes(16));
    }

    /**
     * Envoie la politique CSP (elle contient un nonce propre à la requête, donc elle ne peut pas être dans le .htaccess).
     * X-Frame-Options suit la même règle que frame-ancestors, il est donc posé ici et non dans le .htaccess.
     * Les autres en-têtes de sécurité (nosniff, Referrer-Policy, Permissions-Policy, HSTS) sont posés par public/.htaccess.
     */
    public static function securityHeaders(): void
    {
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-" . self::nonce() . "'",
            "style-src 'self'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            self::$sameOriginFrame ? "frame-ancestors 'self'" : "frame-ancestors 'none'",
        ]);

        header('Content-Security-Policy: ' . $csp);
        header('X-Frame-Options: ' . (self::$sameOriginFrame ? 'SAMEORIGIN' : 'DENY'));
    }

    /**
     * Répond en JSON et termine le script.
     *
     * @param mixed $data   Données à encoder
     * @param int   $status Code HTTP
     */
    public static function json(mixed $data, int $status = 200): never
    {
        self::securityHeaders();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit;
    }

    /**
     * Redirige vers une URL et termine le script.
     */
    public static function redirect(string $url, int $status = 302): never
    {
        http_response_code($status);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Répond avec un document HTML et termine le script.
     */
    public static function html(string $body, int $status = 200): never
    {
        self::securityHeaders();
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $body;
        exit;
    }

    /**
     * Répond avec une feuille de style (apparence publiée) et termine le script.
     *
     * @param bool $immutable true si l'URL contient la version : le navigateur peut la garder un an
     */
    public static function css(string $body, bool $immutable): never
    {
        http_response_code(200);
        header('Content-Type: text/css; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: ' . ($immutable ? 'public, max-age=31536000, immutable' : 'no-cache'));
        echo $body;
        exit;
    }

    /**
     * Répond avec un document XML (sitemap) et termine le script.
     */
    public static function xml(string $body, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/xml; charset=utf-8');
        echo $body;
        exit;
    }
}
