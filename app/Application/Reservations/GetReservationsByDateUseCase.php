<?php
namespace App\Application\Reservations;

use App\Domain\Reservations\Repository\ReservationRepositoryInterface;

class GetReservationsByDateUseCase
{
    private ReservationRepositoryInterface $reservationRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function execute(\DateTime $date): array
    {
        return $this->reservationRepository->findByDate($date);
    }
}
