<?php
namespace App\Domain\Notifications\Entity;

class Notification
{
    private int $id;
    private int $userId;
    private string $type; // 'email', 'sms', 'push'
    private string $title;
    private string $message;
    private array $data;
    private bool $sent;
    private \DateTime $createdAt;
    private ?\DateTime $sentAt;

    public function __construct(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = []
    ) {
        $this->userId = $userId;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->sent = false;
        $this->createdAt = new \DateTime();
        $this->sentAt = null;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getType(): string { return $this->type; }
    public function getTitle(): string { return $this->title; }
    public function getMessage(): string { return $this->message; }
    public function getData(): array { return $this->data; }
    public function isSent(): bool { return $this->sent; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getSentAt(): ?\DateTime { return $this->sentAt; }

    // Business logic
    public function markAsSent(): void
    {
        $this->sent = true;
        $this->sentAt = new \DateTime();
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public static function getValidTypes(): array
    {
        return ['email', 'sms', 'push'];
    }
}

