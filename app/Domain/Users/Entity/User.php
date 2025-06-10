<?php


namespace App\Domain\Users\Entity;

class User
{
    private int $id;
    private string $name;
    private string $email;
    private string $password;
    private string $role; // 'user' or 'admin'
    private \DateTime $createdAt;

    public function __construct(
        string $name,
        string $email,
        string $password,
        string $role = 'user'
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->createdAt = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): string { return $this->role; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }

    // Business logic
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canMakeReservation(): bool
    {
        return in_array($this->role, ['user', 'admin']);
    }

    public function changePassword(string $newPassword): void
    {
        $this->password = $newPassword;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}

