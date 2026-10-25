<?php

declare(strict_types=1);

/**
 * Routes /api/... : réponses JSON uniquement, via Response::json().
 */
final class ApiController extends Controller
{
    private const MAX_FIELD = ['title' => 120, 'tagline' => 5000, 'summary' => 10000, 'duration' => 60, 'name' => 120, 'district' => 120, 'description' => 10000];

    /**
     * GET /api/tours?lang= : visites actives dans la langue demandée (public).
     */
    public function tours(): void
    {
        $lang = $_GET['lang'] ?? null;

        if ($lang !== null && !Lang::isValid((string) $lang)) {
            Lang::init(Lang::DEFAULT);
            Response::json(['success' => false, 'message' => Lang::t('api.invalid_locale')], 400);
        }

        $tours = Tour::findActive($lang === null ? Lang::DEFAULT : (string) $lang);

        Response::json(array_map(fn (Tour $tour): array => $tour->toPublicArray(), $tours));
    }

    /**
     * POST /api/messages : demande envoyée par le formulaire de contact (public).
     * Protégée par jeton CSRF signé, limitation de débit et champ piège anti-spam.
     */
    public function createMessage(): void
    {
        $input = $this->input();
        $lang = Lang::isValid($this->text($input, 'lang')) ? $this->text($input, 'lang') : Lang::DEFAULT;
        Lang::init($lang);

        if (RateLimiter::tooMany('message', 5, 600)) {
            Response::json(['success' => false, 'message' => Lang::t('api.too_many')], 429);
        }

        $this->requireCsrfApi('public', $input);

        // Champ piège : un humain ne le remplit jamais, on répond comme si tout allait bien
        if ($this->text($input, 'website') !== '') {
            Response::json(['success' => true, 'message' => Lang::t('api.message_success')]);
        }

        $fullname = $this->text($input, 'fullname');
        $email = $this->text($input, 'email');
        $body = $this->text($input, 'message');

        if ($fullname === '' || $email === '' || $body === '') {
            Response::json(['success' => false, 'message' => Lang::t('api.missing_fields')], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['success' => false, 'message' => Lang::t('api.invalid_email')], 422);
        }

        if (mb_strlen($fullname) > Message::MAX_NAME || mb_strlen($email) > Message::MAX_EMAIL || mb_strlen($body) > Message::MAX_MESSAGE) {
            Response::json(['success' => false, 'message' => Lang::t('api.field_too_long')], 422);
        }

        RateLimiter::hit('message', 600);

        $saved = (new Message())->setFullname($fullname)->setEmail($email)->setMessage($body)->save();

        if (!$saved) {
            Response::json(['success' => false, 'message' => Lang::t('api.message_error')], 500);
        }

        Response::json(['success' => true, 'message' => Lang::t('api.message_success')]);
    }

    /**
     * GET /api/admin/tours : toutes les visites avec leurs traductions (session admin).
     */
    public function adminTours(): void
    {
        $this->requireAdminApi();

        Response::json(array_map(fn (Tour $tour): array => $tour->toAdminArray(), Tour::findAllWithTranslations()));
    }

    /**
     * POST /api/admin/tours : crée une visite (session admin + CSRF).
     */
    public function createTour(): void
    {
        $this->requireAdminApi();
        $input = $this->input();
        $this->requireCsrfApi('admin', $input);

        $common = $this->validateTourFields($input);
        $translations = $this->collectTranslations($input);

        // L'anglais est la langue de repli : il est obligatoire
        if (!isset($translations['en'])) {
            Response::json(['success' => false, 'message' => 'English title, tagline and summary are required.'], 422);
        }

        $created = Tour::create($common['duration'], $common['capacity'], $common['group_type'], $common['image_url'], $translations);

        if (!$created) {
            Response::json(['success' => false, 'message' => 'Failed to add tour.'], 500);
        }

        Response::json(['success' => true, 'message' => 'Tour added successfully.'], 201);
    }

    /**
     * PUT /api/admin/tours/{id} : met à jour une visite (session admin + CSRF).
     *
     * @param array<string, string> $params Paramètres de route
     */
    public function updateTour(array $params): void
    {
        $this->requireAdminApi();
        $input = $this->input();
        $this->requireCsrfApi('admin', $input);

        $id = $this->routeId($params);

        if (!Tour::exists($id)) {
            Response::json(['success' => false, 'message' => 'Tour not found.'], 404);
        }

        $common = $this->validateTourFields($input);
        $isActive = (int) ($input['is_active'] ?? 0) === 1;

        $tour = (new Tour())
            ->setId($id)
            ->setDuration($common['duration'])
            ->setCapacity($common['capacity'])
            ->setGroupType($common['group_type'])
            ->setImageUrl($common['image_url'])
            ->setIsActive($isActive);

        if (!$tour->update($this->collectTranslations($input))) {
            Response::json(['success' => false, 'message' => 'Failed to update tour.'], 500);
        }

        Response::json(['success' => true, 'message' => 'Tour updated successfully.']);
    }

    /**
     * GET /api/admin/messages (session admin).
     */
    public function adminMessages(): void
    {
        $this->requireAdminApi();

        Response::json(array_map(fn (Message $message): array => $message->toAdminArray(), Message::findAll()));
    }

    /**
     * GET /api/admin/images : images disponibles (nom et URL) (session admin).
     */
    public function adminImages(): void
    {
        $this->requireAdminApi();

        // Chaque image est décrite par son nom de fichier et son URL publique
        Response::json(array_map(
            fn (string $name): array => ['name' => $name, 'url' => ImageUploader::publicUrl($name)],
            ImageUploader::listImages()
        ));
    }

    /**
     * POST /api/admin/images : envoie une image (session admin + CSRF).
     */
    public function uploadImage(): void
    {
        $this->requireAdminApi();
        $this->requireCsrfApi('admin', $_POST);

        $result = ImageUploader::upload($_FILES['image'] ?? []);
        $status = $result['status'];
        unset($result['status']);

        Response::json($result, $status);
    }

    /**
     * Valide les champs communs d'une visite et retourne leurs valeurs nettoyées.
     *
     * @param array<string, mixed> $input
     * @return array{duration: string, capacity: string, group_type: string, image_url: string}
     */
    private function validateTourFields(array $input): array
    {
        $duration = $this->text($input, 'duration');
        $capacity = $this->text($input, 'capacity');
        $groupType = $this->text($input, 'group_type') ?: 'small';
        $imageUrl = $this->text($input, 'image_url');

        if ($duration === '' || $capacity === '' || !is_numeric($capacity) || (float) $capacity < 1) {
            Response::json(['success' => false, 'message' => 'Please fill in all fields.'], 400);
        }

        $this->assertLength('duration', $duration);

        // Liste blanche côté serveur pour le type de groupe
        if (!in_array($groupType, Tour::GROUP_TYPES, true)) {
            Response::json(['success' => false, 'message' => 'Invalid group type.'], 400);
        }

        if ($imageUrl !== '' && !ImageUploader::exists($imageUrl)) {
            Response::json(['success' => false, 'message' => 'Invalid image name.'], 400);
        }

        return ['duration' => $duration, 'capacity' => $capacity, 'group_type' => $groupType, 'image_url' => $imageUrl];
    }

    /**
     * Extrait les traductions complètes (titre, accroche et résumé tous remplis) pour chaque langue supportée.
     *
     * @param array<string, mixed> $input
     * @return array<string, array{title: string, tagline: string, summary: string}>
     */
    private function collectTranslations(array $input): array
    {
        $raw = is_array($input['translations'] ?? null) ? $input['translations'] : [];

        $byLocale = array_reduce(
            Lang::SUPPORTED,
            function (array $carry, string $locale) use ($raw): array {
                $texts = is_array($raw[$locale] ?? null) ? $raw[$locale] : [];
                $entry = [
                    'title' => $this->text($texts, 'title'),
                    'tagline' => $this->text($texts, 'tagline'),
                    'summary' => $this->text($texts, 'summary'),
                ];

                // Une langue n'est enregistrée que si ses trois champs sont remplis
                if ($entry['title'] !== '' && $entry['tagline'] !== '' && $entry['summary'] !== '') {
                    $this->assertLength('title', $entry['title']);
                    $this->assertLength('tagline', $entry['tagline']);
                    $this->assertLength('summary', $entry['summary']);
                    $carry[$locale] = $entry;
                }

                return $carry;
            },
            []
        );

        return $byLocale;
    }

    /**
     * Refuse une valeur trop longue pour sa colonne.
     */
    private function assertLength(string $field, string $value): void
    {
        if (mb_strlen($value) > self::MAX_FIELD[$field]) {
            Response::json(['success' => false, 'message' => 'The field "' . $field . '" is too long.'], 400);
        }
    }

    /**
     * Identifiant numérique de la route, réponse 400 s'il est invalide.
     *
     * @param array<string, string> $params
     */
    private function routeId(array $params): int
    {
        $id = $params['id'] ?? '';

        if (!ctype_digit($id)) {
            Response::json(['success' => false, 'message' => 'Invalid id.'], 400);
        }

        return (int) $id;
    }
}
