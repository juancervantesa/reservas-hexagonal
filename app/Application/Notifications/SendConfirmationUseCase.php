<?php
namespace App\Application\Notifications;

use App\Domain\Notifications\Entity\Notification;
use App\Domain\Notifications\Repository\NotificationRepositoryInterface;
use App\Domain\Reservations\Entity\Reservation;
use App\Domain\Users\Repository\UserRepositoryInterface;
use App\Domain\Spaces\Repository\SpaceRepositoryInterface;

class SendConfirmationUseCase
{
    private NotificationRepositoryInterface $notificationRepository;
    private UserRepositoryInterface $userRepository;
    private SpaceRepositoryInterface $spaceRepository;

    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        UserRepositoryInterface $userRepository,
        SpaceRepositoryInterface $spaceRepository
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->userRepository = $userRepository;
        $this->spaceRepository = $spaceRepository;
    }

    public function execute(Reservation $reservation): Notification
    {
        $user = $this->userRepository->findById($reservation->getUserId());
        $space = $this->spaceRepository->findById($reservation->getSpaceId());

        $title = 'Reserva Confirmada';
        $message = sprintf(
            'Tu reserva para %s el %s de %s a %s ha sido confirmada.',
            $space->getName(),
            $reservation->getReservationDate()->format('d/m/Y'),
            $reservation->getStartTime()->format('H:i'),
            $reservation->getEndTime()->format('H:i')
        );

        $data = [
            'reservation_id' => $reservation->getId(),
            'space_name' => $space->getName(),
            'date' => $reservation->getReservationDate()->format('Y-m-d'),
            'start_time' => $reservation->getStartTime()->format('H:i'),
            'end_time' => $reservation->getEndTime()->format('H:i'),
        ];

        $notification = new Notification(
            $reservation->getUserId(),
            'email',
            $title,
            $message,
            $data
        );

        return $this->notificationRepository->save($notification);
    }
}
