<?php
namespace App\Domain\Reservations\Repository;

use App\Domain\Reservations\Entity\Reservation;

interface ReservationRepositoryInterface
{
    public function save(Reservation $reservation): Reservation;
    public function findById(int $id): ?Reservation;
    public function findByUserId(int $userId): array;
    public function findBySpaceId(int $spaceId): array;
    public function findByDate(\DateTime $date): array;
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array;
    public function findConflictingReservations(
        int $spaceId,
        \DateTime $date,
        \DateTime $startTime,
        \DateTime $endTime,
        int $excludeReservationId = null
    ): array;
    public function findBySpaceAndDateRange(
        int $spaceId,
        \DateTime $startDate,
        \DateTime $endDate
    ): array;
    public function delete(int $id): bool;
    public function findActiveReservations(): array;
    public function findByStatus(string $status): array;
}
