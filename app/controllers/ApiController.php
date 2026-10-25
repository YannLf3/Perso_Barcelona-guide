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

        // Réservation atomique : des envois simultanés ne peuvent pas dépasser la limite vérifiée plus haut
        if (!RateLimiter::consume('message', 5, 600)) {
            Response::json(['success' => false, 'message' => Lang::t('api.too_many')], 429);
        }

        $saved = (new Message())->setFullname($fullname)->setEmail($email)->setMessage($body)->save();

        if (!$saved) {
            Response::json(['success' => false, 'message' => Lang::t('api.message_error')], 500);
        }

        Response::json(['success' => true, 'message' => Lang::t('api.message_success')]);
    }

    /**
     * GET /api/admin/session : prolonge la session pendant que la cliente travaille sans enregistrer (session admin).
     * Le tableau de bord ne l'appelle qu'après une action réelle (frappe, clic) : un onglet oublié finit par expirer.
     */
    public function keepAlive(): void
    {
        $this->requireAdminApi();

        Response::json(['success' => true]);
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
            Response::json(['success' => false, 'message' => t('admin.api.tour_english')], 422);
        }

        $created = Tour::create($common['duration'], $common['capacity'], $common['group_type'], $common['image_url'], $translations);

        if (!$created) {
            Response::json(['success' => false, 'message' => t('admin.api.tour_failed')], 500);
        }

        Response::json(['success' => true, 'message' => t('admin.api.tour_added')], 201);
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
            Response::json(['success' => false, 'message' => t('admin.api.tour_not_found')], 404);
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
            Response::json(['success' => false, 'message' => t('admin.api.tour_failed')], 500);
        }

        Response::json(['success' => true, 'message' => t('admin.api.tour_updated')]);
    }

    /**
     * GET /api/admin/messages (session admin).
     */
    public function adminMessages(): void
    {
        $this->requireAdminApi();

        // Les demandes plus anciennes que la durée de conservation annoncée disparaissent avant l'affichage
        Message::purgeExpired();

        Response::json(array_map(fn (Message $message): array => $message->toAdminArray(), Message::findAll()));
    }

    /**
     * DELETE /api/admin/messages/{id} : supprime définitivement un message (session admin + CSRF).
     *
     * @param array<string, string> $params Paramètres de route
     */
    public function deleteMessage(array $params): void
    {
        $this->requireAdminApi();
        $this->requireCsrfApi('admin', $this->input());

        if (!Message::delete($this->routeId($params))) {
            Response::json(['success' => false, 'message' => t('admin.api.message_not_found')], 404);
        }

        Response::json(['success' => true, 'message' => t('admin.api.message_deleted')]);
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
     * GET /api/admin/texts : tous les textes modifiables, regroupés par zone du site (session admin).
     * Chaque champ porte son libellé, le texte d'origine et le texte remplacé dans chaque langue.
     */
    public function adminTexts(): void
    {
        $this->requireAdminApi();

        $texts = Setting::allForAdmin();
        $order = ['nav', 'intro', 'guide', 'tours', 'modal', 'contact', 'footer', 'legal', 'notfound', 'meta', 'api', 'a11y'];
        $advanced = ['meta', 'api', 'a11y'];

        // Chaque clé rejoint le groupe de son premier segment ("nav.brand" va dans "nav")
        $groups = array_map(
            fn (string $group): array => [
                'id' => $group,
                'label' => t('admin.texts.groups.' . $group . '.label'),
                'help' => t('admin.texts.groups.' . $group . '.help'),
                'advanced' => in_array($group, $advanced, true),
                'fields' => array_values(array_map(
                    fn (string $key): array => [
                        'key' => $key,
                        'label' => t('admin.texts.fields.' . $key),
                        'long' => mb_strlen($texts[$key]['defaults'][Lang::DEFAULT]) > 70,
                        'seo' => $key === 'meta.home_description' || $key === 'meta.legal_description',
                        'defaults' => $texts[$key]['defaults'],
                        'values' => $texts[$key]['values'],
                    ],
                    array_filter(array_keys($texts), fn (string $key): bool => explode('.', $key)[0] === $group)
                )),
            ],
            $order
        );

        Response::json(['groups' => $groups, 'max' => Setting::MAX_VALUE]);
    }

    /**
     * PUT /api/admin/texts : enregistre des textes (session admin + CSRF).
     * Corps attendu : {"texts": {"intro.text": {"fr": "...", "en": ""}, ...}}. Une valeur vide rétablit le texte d'origine.
     */
    public function updateTexts(): void
    {
        $this->requireAdminApi();
        $input = $this->input();
        $this->requireCsrfApi('admin', $input);

        $texts = is_array($input['texts'] ?? null) ? $input['texts'] : [];
        $allowed = Setting::keys();

        // Liste blanche des clés et des langues, les autres entrées sont ignorées
        $entries = array_merge([], ...array_map(
            function (string $key) use ($texts): array {
                $byLocale = is_array($texts[$key] ?? null) ? $texts[$key] : [];

                return array_map(
                    fn (string $locale): array => ['key' => $key, 'locale' => $locale, 'value' => $this->text($byLocale, $locale)],
                    array_values(array_filter(Lang::SUPPORTED, fn (string $locale): bool => array_key_exists($locale, $byLocale)))
                );
            },
            array_values(array_filter(array_map('strval', array_keys($texts)), fn (string $key): bool => isset($allowed[$key])))
        ));

        $tooLong = array_filter($entries, fn (array $entry): bool => mb_strlen($entry['value']) > Setting::MAX_VALUE);

        if ($tooLong !== []) {
            Response::json(['success' => false, 'message' => t('admin.api.text_too_long', ['max' => (string) Setting::MAX_VALUE])], 400);
        }

        $saved = array_reduce($entries, fn (bool $carry, array $entry): bool => Setting::save($entry['key'], $entry['locale'], $entry['value']) && $carry, true);

        if (!$saved) {
            Response::json(['success' => false, 'message' => t('admin.api.texts_failed')], 500);
        }

        Response::json(['success' => true, 'message' => t('admin.api.texts_saved')]);
    }

    /**
     * GET /api/admin/design : catalogue de l'apparence, apparence en ligne et historique (session admin).
     */
    public function adminDesign(): void
    {
        $this->requireAdminApi();

        Response::json([
            'catalogue' => $this->designCatalogue(),
            'current' => Design::currentData(),
            'currentId' => Design::currentId(),
            'history' => Design::history(),
        ]);
    }

    /**
     * PUT /api/admin/design : publie une nouvelle apparence (session admin + CSRF).
     * Corps attendu : {"design": {"colors": {...}, "fonts": {...}, "textSize": "100", "radius": {...}, "photos": [...]}}
     */
    public function publishDesign(): void
    {
        $this->requireAdminApi();
        $input = $this->input();
        $this->requireCsrfApi('admin', $input);

        $result = Design::sanitize(is_array($input['design'] ?? null) ? $input['design'] : []);

        if ($result['errors'] !== []) {
            Response::json(['success' => false, 'message' => t('admin.api.design_invalid'), 'errors' => $result['errors']], 422);
        }

        if (!Design::publish($result['data'], t('admin.design.label_publish'))) {
            Response::json(['success' => false, 'message' => t('admin.api.design_failed')], 500);
        }

        Response::json(['success' => true, 'message' => t('admin.api.design_published')]);
    }

    /**
     * POST /api/admin/design/restore/{id} : remet en ligne une ancienne version (session admin + CSRF).
     * La version restaurée est copiée dans une nouvelle révision : l'historique n'est jamais réécrit.
     *
     * @param array<string, string> $params Paramètres de route
     */
    public function restoreDesign(array $params): void
    {
        $this->requireAdminApi();
        $this->requireCsrfApi('admin', $this->input());

        $revision = Design::find($this->routeId($params));

        if ($revision === null) {
            Response::json(['success' => false, 'message' => t('admin.api.design_not_found')], 404);
        }

        $date = date('d/m/Y H:i', strtotime($revision['created_at']) ?: time());
        $data = Design::sanitize($revision['data'])['data'];

        if (!Design::publish($data, t('admin.design.label_restore', ['date' => $date]))) {
            Response::json(['success' => false, 'message' => t('admin.api.design_failed')], 500);
        }

        Response::json(['success' => true, 'message' => t('admin.api.design_published')]);
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
            Response::json(['success' => false, 'message' => t('admin.api.tour_fields')], 400);
        }

        $this->assertLength('duration', $duration);

        // Liste blanche côté serveur pour le type de groupe
        if (!in_array($groupType, Tour::GROUP_TYPES, true)) {
            Response::json(['success' => false, 'message' => t('admin.api.invalid_group')], 400);
        }

        if ($imageUrl !== '' && !ImageUploader::exists($imageUrl)) {
            Response::json(['success' => false, 'message' => t('admin.api.invalid_image')], 400);
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
            Response::json(['success' => false, 'message' => t('admin.api.field_too_long', ['field' => $field])], 400);
        }
    }

    /**
     * Catalogue de l'apparence avec les libellés destinés à la cliente.
     *
     * @return array<string, mixed>
     */
    private function designCatalogue(): array
    {
        $config = Design::config();
        $tokenLabel = fn (string $key): string => t('admin.design.tokens.' . $key);

        return [
            'groups' => array_map(
                fn (string $group, array $tokens): array => [
                    'id' => $group,
                    'label' => t('admin.design.groups.' . $group . '.label'),
                    'help' => t('admin.design.groups.' . $group . '.help'),
                    'tokens' => array_map(
                        fn (string $key, string $default): array => ['key' => $key, 'label' => $tokenLabel($key), 'default' => $default],
                        array_keys($tokens),
                        $tokens
                    ),
                ],
                array_keys($config['groups']),
                $config['groups']
            ),
            'contrast' => array_map(
                fn (array $pair): array => $pair + ['fgLabel' => $tokenLabel($pair['fg']), 'bgLabel' => $tokenLabel($pair['bg'])],
                $config['contrast']
            ),
            'fonts' => array_map(
                fn (string $key, array $font): array => [
                    'key' => $key,
                    'family' => $font['family'],
                    'fallback' => $font['fallback'],
                    'weight' => $font['weight'],
                    'url' => '/assets/fonts/' . $font['files'][0],
                    'description' => t('admin.design.fonts_list.' . $key),
                ],
                array_keys($config['fonts']),
                $config['fonts']
            ),
            'defaultFonts' => $config['defaultFonts'],
            'textSizes' => array_map(fn (string $size): array => ['value' => $size, 'label' => t('admin.design.size_' . $size)], $config['textSizes']),
            'radius' => array_map(
                fn (array $shapes): array => array_map(
                    fn (string $shape, ?string $value): array => ['value' => $shape, 'css' => $value, 'label' => t('admin.design.shape_' . $shape)],
                    array_keys($shapes),
                    $shapes
                ),
                $config['radius']
            ),
            'defaultPhotos' => $config['defaultPhotos'],
            'presets' => array_map(
                fn (string $key, array $colors): array => [
                    'key' => $key,
                    'label' => t('admin.design.preset_' . $key),
                    'description' => t('admin.design.preset_' . $key . '_desc'),
                    'colors' => (object) $colors,
                ],
                array_keys($config['presets']),
                $config['presets']
            ),
        ];
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
            Response::json(['success' => false, 'message' => t('admin.api.invalid_id')], 400);
        }

        return (int) $id;
    }
}
