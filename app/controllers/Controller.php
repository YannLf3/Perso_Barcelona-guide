<?php

declare(strict_types=1);

/**
 * Classe parente des contrôleurs : accès au corps de la requête et garde-fous communs.
 */
abstract class Controller
{
    /**
     * Corps de la requête : JSON décodé si l'en-tête Content-Type l'indique, sinon $_POST.
     *
     * @return array<string, mixed>
     */
    protected function input(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode((string) file_get_contents('php://input'), true);

            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }

    /**
     * Exige une session administrateur pour une route d'API (réponse 401 sinon).
     * Charge aussi le dictionnaire, qui contient les messages du back-office.
     */
    protected function requireAdminApi(): void
    {
        Lang::init(Lang::DEFAULT);

        if (!Session::isAdmin()) {
            Response::json(['success' => false, 'message' => t('admin.api.unauthorized')], 401);
        }
    }

    /**
     * Exige un jeton CSRF valide pour une route d'API (réponse 403 sinon).
     *
     * @param string               $scope Portée du jeton ("admin" ou "public")
     * @param array<string, mixed> $input Corps de la requête déjà décodé
     */
    protected function requireCsrfApi(string $scope, array $input): void
    {
        if (!Csrf::isValid($scope, Csrf::fromRequest($input))) {
            Response::json(['success' => false, 'message' => Lang::t($scope === 'admin' ? 'admin.log.invalid_token' : 'api.invalid_token')], 403);
        }
    }

    /**
     * Prépare l'aperçu en direct de l'administration (page publique dans un cadre du back-office).
     * Hors aperçu, la page garde ses en-têtes habituels et ne peut pas être encadrée.
     *
     * @return bool true si la page est rendue en mode aperçu
     */
    protected function preparePreview(): bool
    {
        if (!Session::isAdminPreview()) {
            header('Cache-Control: no-cache');

            return false;
        }

        Response::allowSameOriginFrame();
        header('Cache-Control: no-store');

        return true;
    }

    /**
     * Lit une valeur texte du corps de la requête, nettoyée des espaces de début et de fin.
     *
     * @param array<string, mixed> $input
     */
    protected function text(array $input, string $key): string
    {
        $value = $input[$key] ?? '';

        return is_scalar($value) ? trim((string) $value) : '';
    }
}
