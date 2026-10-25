<?php

declare(strict_types=1);

/**
 * Sortie HTTP : JSON, redirections et en-têtes de sécurité.
 */
final class Response
{
    private static ?string $nonce = null;

    /**
     * Retourne le nonce CSP de la requête courante (utilisé par les scripts en ligne).
     */
    public static function nonce(): string
    {
        return self::$nonce ??= base64_encode(random_bytes(16));
    }

    /**
     * Envoie la politique CSP (elle contient un nonce propre à la requête, donc elle ne peut pas être dans le .htaccess).
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
            "frame-ancestors 'none'",
        ]);

        header('Content-Security-Policy: ' . $csp);
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
