<?php

declare(strict_types=1);

/**
 * Informations sur la requête courante qui dépendent d'un éventuel proxy (CDN, répartiteur de charge).
 * Les en-têtes X-Forwarded-* ne sont lus que si la connexion vient d'un proxy déclaré dans TRUSTED_PROXIES :
 * sinon, n'importe quel client pourrait les falsifier.
 */
final class Request
{
    /**
     * Indique si la connexion directe vient d'un proxy de confiance (liste d'IP séparées par des virgules dans le .env).
     */
    public static function fromTrustedProxy(): bool
    {
        $trusted = array_filter(array_map('trim', explode(',', Env::get('TRUSTED_PROXIES'))));

        return in_array($_SERVER['REMOTE_ADDR'] ?? '', $trusted, true);
    }

    /**
     * Adresse IP du visiteur. Derrière un proxy de confiance, c'est la dernière adresse ajoutée à X-Forwarded-For.
     */
    public static function clientIp(): string
    {
        $remote = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (!self::fromTrustedProxy()) {
            return $remote;
        }

        // Le proxy ajoute l'IP du client à la fin de la liste : les valeurs précédentes peuvent venir du client lui-même
        $chain = array_values(array_filter(array_map('trim', explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '')))));
        $last = $chain === [] ? '' : $chain[count($chain) - 1];

        return filter_var($last, FILTER_VALIDATE_IP) !== false ? $last : $remote;
    }

    /**
     * Détecte HTTPS, y compris derrière un proxy de confiance qui transmet X-Forwarded-Proto.
     */
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        return self::fromTrustedProxy() && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }
}
