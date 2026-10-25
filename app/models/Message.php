<?php

declare(strict_types=1);

/**
 * Demande envoyée par le formulaire de contact (table BT_Message).
 * Le texte est stocké brut : l'échappement se fait à l'affichage.
 */
final class Message
{
    public const MAX_NAME = 120;
    public const MAX_EMAIL = 160;
    public const MAX_MESSAGE = 5000;

    /** Durée de conservation des demandes (RGPD), en mois. Annoncée dans les mentions légales. */
    public const RETENTION_MONTHS = 36;

    private int $id = 0;
    private string $fullname = '';
    private string $email = '';
    private string $message = '';
    private string $createdAt = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFullname(): string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Enregistre le message courant.
     */
    public function save(): bool
    {
        $stmt = Database::getConnection()->prepare('INSERT INTO BT_Message (fullname, email, message) VALUES (:fullname, :email, :message)');

        return $stmt->execute([
            'fullname' => $this->fullname,
            'email' => $this->email,
            'message' => $this->message,
        ]);
    }

    /**
     * Tous les messages, du plus récent au plus ancien.
     *
     * @return array<int, self>
     */
    public static function findAll(): array
    {
        $rows = Database::getConnection()
            ->query('SELECT id, fullname, email, message, created_at FROM BT_Message ORDER BY created_at DESC, id DESC')
            ->fetchAll();

        return array_map(
            fn (array $row): self => (new self())
                ->setId((int) $row['id'])
                ->setFullname((string) $row['fullname'])
                ->setEmail((string) $row['email'])
                ->setMessage((string) $row['message'])
                ->setCreatedAt((string) $row['created_at']),
            $rows
        );
    }

    /**
     * Supprime définitivement un message.
     *
     * @return bool true si une ligne a été supprimée
     */
    public static function delete(int $id): bool
    {
        $stmt = Database::getConnection()->prepare('DELETE FROM BT_Message WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() === 1;
    }

    /**
     * Supprime les messages plus anciens que la durée de conservation (appelé à l'ouverture de la liste dans l'administration).
     * Un échec est journalisé sans bloquer l'affichage des messages.
     *
     * @return int Nombre de messages supprimés
     */
    public static function purgeExpired(): int
    {
        try {
            $stmt = Database::getConnection()->prepare('DELETE FROM BT_Message WHERE created_at < (NOW() - INTERVAL :months MONTH)');
            $stmt->execute(['months' => self::RETENTION_MONTHS]);

            return $stmt->rowCount();
        } catch (Throwable $exception) {
            error_log('[Message::purgeExpired] ' . $exception->getMessage());

            return 0;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'email' => $this->email,
            'message' => $this->message,
            'created_at' => $this->createdAt,
        ];
    }
}
