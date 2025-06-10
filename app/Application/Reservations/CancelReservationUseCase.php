<?php
namespace App\Application\Reservations;

use App\Domain\Reservations\Repository\ReservationRepositoryInterface;
use App\Domain\Users\Repository\UserRepositoryInterface;

class CancelReservationUseCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->userRepository = $userRepository;
    }

    public function execute(int $reservationId, int $userId): bool
    {
        $reservation = $this->reservationRepository->findById($reservationId);

        if (!$reservation) {
            throw new \InvalidArgumentException('Reservation not found');
        }

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        // Solo el propietario o un admin puede cancelar
        if ($reservation->getUserId() !== $userId && !$user->isAdmin()) {
            throw new \InvalidArgumentException('Unauthorized to cancel this reservation');
        }

        $reservation->cancel();
        $this->reservationRepository->save($reservation);

        return true;
    }
}
