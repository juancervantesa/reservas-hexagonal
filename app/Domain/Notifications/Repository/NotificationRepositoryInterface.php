<?php
namespace App\Domain\Notifications\Repository;

use App\Domain\Notifications\Entity\Notification;

interface NotificationRepositoryInterface
{
    public function save(Notification $notification): Notification;
    public function findById(int $id): ?Notification;
    public function findByUserId(int $userId): array;
    public function findPendingNotifications(): array;
    public function findByType(string $type): array;
    public function delete(int $id): bool;
    public function markAsSent(int $id): bool;
}
