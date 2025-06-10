<?php
namespace App\Application\Reservations;

use App\Domain\Reservations\Entity\Reservation;
use App\Domain\Reservations\Repository\ReservationRepositoryInterface;
use App\Domain\Users\Repository\UserRepositoryInterface;
use App\Domain\Spaces\Repository\SpaceRepositoryInterface;
use App\Application\Notifications\SendConfirmationUseCase;

class CreateReservationUseCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private UserRepositoryInterface $userRepository;
    private SpaceRepositoryInterface $spaceRepository;
    private SendConfirmationUseCase $sendConfirmationUseCase;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        UserRepositoryInterface $userRepository,
        SpaceRepositoryInterface $spaceRepository,
        SendConfirmationUseCase $sendConfirmationUseCase
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->userRepository = $userRepository;
        $this->spaceRepository = $spaceRepository;
        $this->sendConfirmationUseCase = $sendConfirmationUseCase;
    }

    public function execute(
        int $userId,
        int $spaceId,
        \DateTime $reservationDate,
        \DateTime $startTime,
        \DateTime $endTime,
        string $purpose = ''
    ): Reservation {
        // Validar que el usuario existe
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        // Validar que el espacio existe
        $space = $this->spaceRepository->findById($spaceId);
        if (!$space) {
            throw new \InvalidArgumentException('Space not found');
        }

        if (!$space->isActive()) {
            throw new \InvalidArgumentException('Space is not available');
        }

        // Crear reserva
        $reservation = new Reservation($userId, $spaceId, $reservationDate, $startTime, $endTime, $purpose);

        // Validar rango de tiempo
        if (!$reservation->isValidTimeRange()) {
            throw new \InvalidArgumentException('Invalid time range');
        }

        // Validar que sea una reserva futura
        if (!$reservation->isFutureReservation()) {
            throw new \InvalidArgumentException('Cannot make reservations for past dates');
        }

        // Verificar conflictos
        $conflictingReservations = $this->reservationRepository->findConflictingReservations(
            $spaceId,
            $reservationDate,
            $startTime,
            $endTime
        );

        if (!empty($conflictingReservations)) {
            throw new \InvalidArgumentException('Time slot is already reserved');
        }

        // Guardar reserva
        $savedReservation = $this->reservationRepository->save($reservation);

        // Confirmar automáticamente
        $savedReservation->confirm();
        $this->reservationRepository->save($savedReservation);

        // Enviar notificación
        $this->sendConfirmationUseCase->execute($savedReservation);

        return $savedReservation;
    }
}
