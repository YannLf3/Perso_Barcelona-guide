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
