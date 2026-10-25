<?php

declare(strict_types=1);

/**
 * Connexion PDO unique partagée pendant toute la requête.
 */
final class Database
{
    private static ?PDO $connection = null;

    /**
     * Retourne la connexion partagée, créée à la première demande.
     *
     * @throws PDOException si la connexion échoue
     */
    public static function getConnection(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        /** @var array<string, string> $config */
        $config = require BASE_PATH . '/app/config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['name'],
            $config['charset']
        );

        self::$connection = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }
}
