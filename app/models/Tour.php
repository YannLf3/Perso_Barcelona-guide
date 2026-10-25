<?php

declare(strict_types=1);

/**
 * Visite guidée (table BT_Tour) et ses traductions (table BT_Tour_Translation).
 */
final class Tour
{
    public const GROUP_TYPES = ['private', 'small', 'school'];

    private int $id = 0;
    private string $duration = '';
    private string $capacity = '';
    private string $groupType = 'small';
    private string $imageUrl = '';
    private bool $isActive = true;
    private string $title = '';
    private string $tagline = '';
    private string $summary = '';

    /** @var array<string, array{title: string, tagline: string, summary: string}> */
    private array $translations = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCapacity(): string
    {
        return $this->capacity;
    }

    public function setCapacity(string $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getGroupType(): string
    {
        return $this->groupType;
    }

    public function setGroupType(string $groupType): self
    {
        $this->groupType = $groupType;

        return $this;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): self
    {
        $this->imageUrl = $imageUrl ?? '';

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTagline(): string
    {
        return $this->tagline;
    }

    public function setTagline(string $tagline): self
    {
        $this->tagline = $tagline;

        return $this;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * @return array<string, array{title: string, tagline: string, summary: string}>
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * @param array<string, array{title: string, tagline: string, summary: string}> $translations
     */
    public function setTranslations(array $translations): self
    {
        $this->translations = $translations;

        return $this;
    }

    /**
     * Visites actives dans une langue, avec repli sur l'anglais quand la traduction manque.
     *
     * @return array<int, self>
     */
    public static function findActive(string $locale): array
    {
        // COALESCE : on prend la traduction demandée, sinon la traduction anglaise
        $sql = 'SELECT t.id, t.duration, t.price, t.group_type, t.image_url, t.is_active,
                       COALESCE(tr.title, en.title) AS title,
                       COALESCE(tr.tagline, en.tagline) AS tagline,
                       COALESCE(tr.summary, en.summary) AS summary
                FROM BT_Tour t
                LEFT JOIN BT_Tour_Translation tr ON tr.tour_id = t.id AND tr.locale = :locale
                LEFT JOIN BT_Tour_Translation en ON en.tour_id = t.id AND en.locale = \'en\'
                WHERE t.is_active = 1 AND COALESCE(tr.title, en.title) IS NOT NULL
                ORDER BY t.id DESC';

        $stmt = Database::getConnection()->prepare($sql);
        $stmt->execute(['locale' => $locale]);

        return array_map(fn (array $row): self => self::fromRow($row), $stmt->fetchAll());
    }

    /**
     * Toutes les visites (actives ou non) avec l'ensemble de leurs traductions, pour l'administration.
     *
     * @return array<int, self>
     */
    public static function findAllWithTranslations(): array
    {
        $pdo = Database::getConnection();
        $rows = $pdo->query('SELECT id, duration, price, group_type, image_url, is_active FROM BT_Tour ORDER BY id DESC')->fetchAll();

        if ($rows === []) {
            return [];
        }

        $ids = array_map(fn (array $row): int => (int) $row['id'], $rows);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare('SELECT tour_id, locale, title, tagline, summary FROM BT_Tour_Translation WHERE tour_id IN (' . $placeholders . ') ORDER BY locale ASC');
        $stmt->execute($ids);

        // Index [tour_id => [locale => textes]] construit sans boucle
        $byTour = array_reduce(
            $stmt->fetchAll(),
            function (array $carry, array $row): array {
                $carry[(int) $row['tour_id']][$row['locale']] = [
                    'title' => (string) $row['title'],
                    'tagline' => (string) $row['tagline'],
                    'summary' => (string) $row['summary'],
                ];

                return $carry;
            },
            []
        );

        return array_map(
            fn (array $row): self => self::fromRow($row)->setTranslations($byTour[(int) $row['id']] ?? []),
            $rows
        );
    }

    /**
     * Crée une visite et ses traductions dans une transaction.
     *
     * @param array<string, array{title: string, tagline: string, summary: string}> $translations
     */
    public static function create(string $duration, string $capacity, string $groupType, string $imageUrl, array $translations): bool
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare('INSERT INTO BT_Tour (duration, price, group_type, image_url, is_active) VALUES (:duration, :price, :group_type, :image_url, 1)');
            $stmt->execute([
                'duration' => $duration,
                'price' => $capacity,
                'group_type' => $groupType,
                'image_url' => $imageUrl,
            ]);

            $tourId = (int) $pdo->lastInsertId();
            self::saveTranslations($pdo, $tourId, $translations);

            return $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[Tour::create] ' . $exception->getMessage());

            return false;
        }
    }

    /**
     * Met à jour les champs communs d'une visite et enregistre (ou remplace) les traductions fournies.
     *
     * @param array<string, array{title: string, tagline: string, summary: string}> $translations
     */
    public function update(array $translations): bool
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            // COALESCE(NULLIF(...)) : une image vide conserve l'image actuelle
            $stmt = $pdo->prepare(
                'UPDATE BT_Tour
                 SET duration = :duration, price = :price, group_type = :group_type,
                     image_url = COALESCE(NULLIF(:image_url, \'\'), image_url), is_active = :is_active
                 WHERE id = :id'
            );
            $stmt->execute([
                'duration' => $this->duration,
                'price' => $this->capacity,
                'group_type' => $this->groupType,
                'image_url' => $this->imageUrl,
                'is_active' => $this->isActive ? 1 : 0,
                'id' => $this->id,
            ]);

            self::saveTranslations($pdo, $this->id, $translations);

            return $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[Tour::update] ' . $exception->getMessage());

            return false;
        }
    }

    /**
     * Indique si une visite existe.
     */
    public static function exists(int $id): bool
    {
        $stmt = Database::getConnection()->prepare('SELECT 1 FROM BT_Tour WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * Version minimale pour les routes publiques (aucun champ interne).
     *
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        $dimensions = ImageUploader::dimensions($this->imageUrl);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'tagline' => $this->tagline,
            'summary' => $this->summary,
            'duration' => $this->duration,
            'capacity' => $this->capacity,
            'group_type' => $this->groupType,
            'image' => ImageUploader::publicUrl($this->imageUrl),
            'image_width' => $dimensions['width'] ?? null,
            'image_height' => $dimensions['height'] ?? null,
        ];
    }

    /**
     * Version complète pour l'administration.
     *
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'duration' => $this->duration,
            'capacity' => $this->capacity,
            'group_type' => $this->groupType,
            'is_active' => $this->isActive ? 1 : 0,
            'image_url' => $this->imageUrl,
            'translations' => $this->translations,
        ];
    }

    /**
     * Insère ou remplace les traductions fournies (une ligne par langue).
     *
     * @param array<string, array{title: string, tagline: string, summary: string}> $translations
     */
    private static function saveTranslations(PDO $pdo, int $tourId, array $translations): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO BT_Tour_Translation (tour_id, locale, title, tagline, summary)
             VALUES (:tour_id, :locale, :title, :tagline, :summary)
             ON DUPLICATE KEY UPDATE title = VALUES(title), tagline = VALUES(tagline), summary = VALUES(summary)'
        );

        array_map(
            fn (string $locale, array $texts): bool => $stmt->execute([
                'tour_id' => $tourId,
                'locale' => $locale,
                'title' => $texts['title'],
                'tagline' => $texts['tagline'],
                'summary' => $texts['summary'],
            ]),
            array_keys($translations),
            $translations
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function fromRow(array $row): self
    {
        return (new self())
            ->setId((int) $row['id'])
            ->setDuration((string) $row['duration'])
            ->setCapacity((string) $row['price'])
            ->setGroupType((string) $row['group_type'])
            ->setImageUrl($row['image_url'] ?? null)
            ->setIsActive((int) ($row['is_active'] ?? 1) === 1)
            ->setTitle((string) ($row['title'] ?? ''))
            ->setTagline((string) ($row['tagline'] ?? ''))
            ->setSummary((string) ($row['summary'] ?? ''));
    }
}
