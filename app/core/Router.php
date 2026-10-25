<?php

declare(strict_types=1);

/**
 * Routeur minimal : associe méthode HTTP + chemin à une méthode de contrôleur.
 */
final class Router
{
    /** @var array<int, array{method: string, regex: string, handler: array{0: class-string, 1: string}}> */
    private array $routes = [];

    /**
     * Déclare une route. Les segments {nom} deviennent des paramètres nommés.
     *
     * @param string                       $method  GET, POST, PUT...
     * @param string                       $pattern Chemin, par exemple /{lang}/legal
     * @param array{0: class-string, 1: string} $handler [Classe, méthode]
     */
    public function add(string $method, string $pattern, array $handler): self
    {
        // Chaque {param} est converti en groupe nommé qui n'accepte pas de slash
        $regex = '#^' . preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
        $this->routes[] = ['method' => $method, 'regex' => $regex, 'handler' => $handler];

        return $this;
    }

    /**
     * Cherche la route correspondante et exécute son contrôleur.
     * Retourne false si aucune route ne correspond au chemin.
     *
     * @param string $method Méthode HTTP de la requête
     * @param string $path   Chemin demandé, sans query string
     */
    public function dispatch(string $method, string $path): bool
    {
        // HEAD est traité comme GET : Apache retire le corps de la réponse
        $method = $method === 'HEAD' ? 'GET' : $method;
        $path = $path === '/' ? '/' : rtrim($path, '/');

        // On associe à chaque route les paramètres extraits du chemin (null si le chemin ne correspond pas)
        $candidates = array_filter(
            array_map(fn (array $route): array => ['route' => $route, 'params' => $this->extractParams($route['regex'], $path)], $this->routes),
            fn (array $candidate): bool => $candidate['params'] !== null
        );

        if ($candidates === []) {
            return false;
        }

        $byMethod = array_values(array_filter($candidates, fn (array $candidate): bool => $candidate['route']['method'] === $method));

        if ($byMethod === []) {
            $allowed = implode(', ', array_unique(array_map(fn (array $candidate): string => $candidate['route']['method'], $candidates)));
            header('Allow: ' . $allowed);
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }

        [$class, $action] = $byMethod[0]['route']['handler'];

        (new $class())->$action($byMethod[0]['params']);

        return true;
    }

    /**
     * Extrait les paramètres nommés d'un chemin, ou null si le chemin ne correspond pas.
     *
     * @return array<string, string>|null
     */
    private function extractParams(string $regex, string $path): ?array
    {
        if (preg_match($regex, $path, $found) !== 1) {
            return null;
        }

        // preg_match ajoute aussi des index numériques : on ne garde que les groupes nommés
        return array_filter($found, 'is_string', ARRAY_FILTER_USE_KEY);
    }
}
