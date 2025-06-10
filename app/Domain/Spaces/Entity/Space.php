<?php
namespace App\Domain\Spaces\Entity;

class Space
{
    private int $id;
    private string $name;
    private string $type; // 'salon', 'auditorio', 'cancha'
    private int $capacity;
    private string $description;
    private bool $isActive;
    private \DateTime $createdAt;

    public function __construct(
        string $name,
        string $type,
        int $capacity,
        string $description = ''
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->capacity = $capacity;
        $this->description = $description;
        $this->isActive = true;
        $this->createdAt = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getType(): string { return $this->type; }
    public function getCapacity(): int { return $this->capacity; }
    public function getDescription(): string { return $this->description; }
    public function isActive(): bool { return $this->isActive; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }

    // Business logic
    public function isAvailableForCapacity(int $requiredCapacity): bool
    {
        return $this->isActive && $this->capacity >= $requiredCapacity;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public static function getValidTypes(): array
    {
        return ['salon', 'auditorio', 'cancha'];
    }

    public function isValidType(): bool
    {
        return in_array($this->type, self::getValidTypes());
    }
}
