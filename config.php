<?php
// config.php
// Configuration DB : production Hostinger + fallback local.

$host = $_SERVER['HTTP_HOST'] ?? '';
$isProduction = strpos($host, 'barcelonafeelgoodtours.eu') !== false;

if ($isProduction) {
	// Remplacer ces 4 valeurs par celles du hPanel Hostinger (MySQL Databases).
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'u740108753_BTGUIDE');
	define('DB_USER', 'u740108753_leflohic');
	define('DB_PASS', 'z15=tp0Di!');
} else {
	// Fallback local (dev)
	define('DB_HOST', '127.0.0.1');
	define('DB_NAME', 'BTGUIDE');
	define('DB_USER', 'leflohic');
	define('DB_PASS', 'CHANGE_ME_LOCAL_DB_PASSWORD');
}