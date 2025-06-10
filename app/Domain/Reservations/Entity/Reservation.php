<?php
namespace App\Domain\Reservations\Entity;

use App\Domain\Users\Entity\User;
use App\Domain\Spaces\Entity\Space;

class Reservation
{
    private int $id;
    private int $userId;
    private int $spaceId;
    private \DateTime $reservationDate;
    private \DateTime $startTime;
    private \DateTime $endTime;
    private string $status; // 'pending', 'confirmed', 'cancelled'
    private string $purpose;
    private \DateTime $createdAt;

    public function __construct(
        int $userId,
        int $spaceId,
        \DateTime $reservationDate,
        \DateTime $startTime,
        \DateTime $endTime,
        string $purpose = ''
    ) {
        $this->userId = $userId;
        $this->spaceId = $spaceId;
        $this->reservationDate = $reservationDate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->purpose = $purpose;
        $this->status = 'pending';
        $this->createdAt = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getSpaceId(): int { return $this->spaceId; }
    public function getReservationDate(): \DateTime { return $this->reservationDate; }
    public function getStartTime(): \DateTime { return $this->startTime; }
    public function getEndTime(): \DateTime { return $this->endTime; }
    public function getStatus(): string { return $this->status; }
    public function getPurpose(): string { return $this->purpose; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }

    // Business logic
    public function confirm(): void
    {
        if ($this->status === 'pending') {
            $this->status = 'confirmed';
        }
    }

    public function cancel(): void
    {
        if (in_array($this->status, ['pending', 'confirmed'])) {
            $this->status = 'cancelled';
        }
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function overlaps(Reservation $other): bool
    {
        if ($this->spaceId !== $other->spaceId) {
            return false;
        }

        if ($this->reservationDate->format('Y-m-d') !== $other->reservationDate->format('Y-m-d')) {
            return false;
        }

        return $this->startTime < $other->endTime && $this->endTime > $other->startTime;
    }

    public function isValidTimeRange(): bool
    {
        return $this->startTime < $this->endTime;
    }

    public function isFutureReservation(): bool
    {
        $now = new \DateTime();
        return $this->reservationDate >= $now;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public static function getValidStatuses(): array
    {
        return ['pending', 'confirmed', 'cancelled'];
    }
}
