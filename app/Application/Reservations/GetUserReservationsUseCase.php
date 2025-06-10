<?php
namespace App\Application\Reservations;

use App\Domain\Reservations\Repository\ReservationRepositoryInterface;

class GetUserReservationsUseCase
{
    private ReservationRepositoryInterface $reservationRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function execute(int $userId): array
    {
        return $this->reservationRepository->findByUserId($userId);
    }
}
