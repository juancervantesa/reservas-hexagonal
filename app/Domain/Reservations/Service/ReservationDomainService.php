<?php
namespace App\Domain\Reservations\Service;

use App\Domain\Reservations\Entity\Reservation;
use App\Domain\Reservations\Repository\ReservationRepositoryInterface;
use App\Domain\Spaces\Repository\SpaceRepositoryInterface;
use App\Domain\Users\Repository\UserRepositoryInterface;

class ReservationDomainService
{
    private ReservationRepositoryInterface $reservationRepository;
    private SpaceRepositoryInterface $spaceRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        SpaceRepositoryInterface $spaceRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->spaceRepository = $spaceRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Verifica si hay conflictos de horario para una reserva
     */
    public function hasTimeConflicts(
        int $spaceId,
        \DateTime $date,
        \DateTime $startTime,
        \DateTime $endTime,
        ?int $excludeReservationId = null
    ): bool {
        $conflictingReservations = $this->reservationRepository->findConflictingReservations(
            $spaceId,
            $date,
            $startTime,
            $endTime,
            $excludeReservationId
        );

        return count($conflictingReservations) > 0;
    }

    /**
     * Calcula la duración de una reserva en horas
     */
    public function calculateDuration(Reservation $reservation): float
    {
        $start = $reservation->getStartTime();
        $end = $reservation->getEndTime();

        return ($end->getTimestamp() - $start->getTimestamp()) / 3600;
    }

    /**
     * Verifica si una reserva puede ser cancelada
     */
    public function canBeCancelled(Reservation $reservation): bool
    {
        // No se puede cancelar una reserva ya cancelada
        if ($reservation->getStatus() === 'cancelled') {
            return false;
        }

        // No se puede cancelar una reserva que ya pasó
        $now = new \DateTime();
        if ($reservation->getReservationDate() < $now) {
            return false;
        }

        // Se puede cancelar con al menos 2 horas de anticipación
        $reservationDateTime = clone $reservation->getReservationDate();
        $reservationDateTime->setTime(
            $reservation->getStartTime()->format('H'),
            $reservation->getStartTime()->format('i')
        );

        $hoursUntilReservation = ($reservationDateTime->getTimestamp() - $now->getTimestamp()) / 3600;

        return $hoursUntilReservation >= 2;
    }

    /**
     * Verifica si una reserva puede ser modificada
     */
    public function canBeModified(Reservation $reservation): bool
    {
        // Mismas reglas que para cancelar, pero más restrictivo
        if (!$this->canBeCancelled($reservation)) {
            return false;
        }

        // Solo se puede modificar reservas pendientes o confirmadas
        return in_array($reservation->getStatus(), ['pending', 'confirmed']);
    }

    /**
     * Obtiene las horas disponibles para un espacio en una fecha específica
     */
    public function getAvailableTimeSlots(int $spaceId, \DateTime $date): array
    {
        // Horario de funcionamiento: 8:00 AM a 10:00 PM
        $businessStart = new \DateTime($date->format('Y-m-d') . ' 08:00:00');
        $businessEnd = new \DateTime($date->format('Y-m-d') . ' 22:00:00');

        // Obtener reservas existentes para ese día
        $existingReservations = $this->reservationRepository->findBySpaceAndDateRange(
            $spaceId,
            $date,
            $date
        );

        $availableSlots = [];
        $currentTime = clone $businessStart;

        while ($currentTime < $businessEnd) {
            $slotEnd = clone $currentTime;
            $slotEnd->add(new \DateInterval('PT1H')); // Slots de 1 hora

            $isAvailable = true;
            foreach ($existingReservations as $reservation) {
                if ($reservation->getStatus() === 'cancelled') {
                    continue;
                }

                $reservationStart = $reservation->getStartTime();
                $reservationEnd = $reservation->getEndTime();

                // Verificar si el slot se superpone con alguna reserva
                if ($currentTime < $reservationEnd && $slotEnd > $reservationStart) {
                    $isAvailable = false;
                    break;
                }
            }

            if ($isAvailable) {
                $availableSlots[] = [
                    'start' => $currentTime->format('H:i'),
                    'end' => $slotEnd->format('H:i'),
                    'datetime_start' => clone $currentTime,
                    'datetime_end' => clone $slotEnd
                ];
            }

            $currentTime->add(new \DateInterval('PT1H'));
        }

        return $availableSlots;
    }

    /**
     * Valida las reglas de negocio para una nueva reserva
     */
    public function validateReservationRules(
        int $userId,
        int $spaceId,
        \DateTime $reservationDate,
        \DateTime $startTime,
        \DateTime $endTime
    ): array {
        $errors = [];

        // Validar que el usuario existe y puede hacer reservas
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            $errors[] = 'Usuario no encontrado';
        } elseif (!$user->canMakeReservation()) {
            $errors[] = 'Usuario no autorizado para hacer reservas';
        }

        // Validar que el espacio existe y está disponible
        $space = $this->spaceRepository->findById($spaceId);
        if (!$space) {
            $errors[] = 'Espacio no encontrado';
        } elseif (!$space->isActive()) {
            $errors[] = 'Espacio no disponible';
        }

        // Validar horarios de negocio
        $businessStart = 8; // 8:00 AM
        $businessEnd = 22;  // 10:00 PM

        if ($startTime->format('H') < $businessStart || $startTime->format('H') >= $businessEnd) {
            $errors[] = 'Hora de inicio fuera del horario de funcionamiento (8:00 AM - 10:00 PM)';
        }

        if ($endTime->format('H') > $businessEnd || ($endTime->format('H') == $businessEnd && $endTime->format('i') > 0)) {
            $errors[] = 'Hora de fin fuera del horario de funcionamiento (8:00 AM - 10:00 PM)';
        }

        // Validar duración máxima (4 horas)
        $duration = ($endTime->getTimestamp() - $startTime->getTimestamp()) / 3600;
        if ($duration > 4) {
            $errors[] = 'La duración de la reserva no puede exceder 4 horas';
        }

        if ($duration < 1) {
            $errors[] = 'La duración mínima de una reserva es 1 hora';
        }

        // Validar que no sea un día pasado
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        if ($reservationDate < $today) {
            $errors[] = 'No se pueden hacer reservas para fechas pasadas';
        }

        // Validar que no sea más de 30 días en el futuro
        $maxFutureDate = clone $today;
        $maxFutureDate->add(new \DateInterval('P30D'));
        if ($reservationDate > $maxFutureDate) {
            $errors[] = 'No se pueden hacer reservas con más de 30 días de anticipación';
        }

        // Validar conflictos de horario
        if ($this->hasTimeConflicts($spaceId, $reservationDate, $startTime, $endTime)) {
            $errors[] = 'El horario solicitado ya está reservado';
        }

        return $errors;
    }

    /**
     * Obtiene estadísticas de reservas para un período
     */
    public function getReservationStats(\DateTime $startDate, \DateTime $endDate): array
    {
        $reservations = $this->reservationRepository->findByDateRange($startDate, $endDate);

        $stats = [
            'total_reservations' => count($reservations),
            'confirmed_reservations' => 0,
            'cancelled_reservations' => 0,
            'pending_reservations' => 0,
            'total_hours_reserved' => 0,
            'most_popular_spaces' => [],
            'busiest_days' => []
        ];

        $spaceCount = [];
        $dayCount = [];

        foreach ($reservations as $reservation) {
            // Contar por estado
            switch ($reservation->getStatus()) {
                case 'confirmed':
                    $stats['confirmed_reservations']++;
                    break;
                case 'cancelled':
                    $stats['cancelled_reservations']++;
                    break;
                case 'pending':
                    $stats['pending_reservations']++;
                    break;
            }

            // Calcular horas reservadas
            if ($reservation->getStatus() !== 'cancelled') {
                $duration = $this->calculateDuration($reservation);
                $stats['total_hours_reserved'] += $duration;
            }

            // Contar por espacio
            $spaceId = $reservation->getSpaceId();
            $spaceCount[$spaceId] = ($spaceCount[$spaceId] ?? 0) + 1;

            // Contar por día
            $day = $reservation->getReservationDate()->format('Y-m-d');
            $dayCount[$day] = ($dayCount[$day] ?? 0) + 1;
        }

        // Ordenar espacios más populares
        arsort($spaceCount);
        $stats['most_popular_spaces'] = array_slice($spaceCount, 0, 5, true);

        // Ordenar días más ocupados
        arsort($dayCount);
        $stats['busiest_days'] = array_slice($dayCount, 0, 7, true);

        return $stats;
    }
}
