<?php

declare(strict_types=1);

/**
 * Front Controller : point d'entrée unique de l'application.
 */

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/core/bootstrap.php';

// Aucune erreur détaillée côté visiteur : tout part dans un fichier de log
error_reporting(E_ALL);
ini_set('display_errors', Env::isProduction() ? '0' : '1');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');
date_default_timezone_set('Europe/Madrid');

set_exception_handler(function (Throwable $exception): void {
    error_log('[uncaught] ' . $exception::class . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine());

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    if (str_starts_with($path, '/api/')) {
        Response::json(['success' => false, 'message' => 'Internal server error.'], 500);
    }

    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Internal server error.';
    exit;
});

// Les warnings et notices deviennent des exceptions pour ne jamais produire une sortie à moitié rendue
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

$router = (new Router())
    ->add('GET', '/', [HomeController::class, 'root'])
    ->add('GET', '/sitemap.xml', [HomeController::class, 'sitemap'])
    ->add('GET', '/admin', [AdminController::class, 'dashboard'])
    ->add('GET', '/admin/login', [AuthController::class, 'showLogin'])
    ->add('POST', '/admin/login', [AuthController::class, 'login'])
    ->add('POST', '/admin/logout', [AuthController::class, 'logout'])
    ->add('GET', '/api/tours', [ApiController::class, 'tours'])
    ->add('POST', '/api/messages', [ApiController::class, 'createMessage'])
    ->add('GET', '/api/admin/tours', [ApiController::class, 'adminTours'])
    ->add('POST', '/api/admin/tours', [ApiController::class, 'createTour'])
    ->add('PUT', '/api/admin/tours/{id}', [ApiController::class, 'updateTour'])
    ->add('GET', '/api/admin/messages', [ApiController::class, 'adminMessages'])
    ->add('GET', '/api/admin/images', [ApiController::class, 'adminImages'])
    ->add('POST', '/api/admin/images', [ApiController::class, 'uploadImage'])
    ->add('GET', '/{lang}', [HomeController::class, 'index'])
    ->add('GET', '/{lang}/legal', [LegalController::class, 'show']);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if (!$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path)) {
    (new HomeController())->notFound();
}
