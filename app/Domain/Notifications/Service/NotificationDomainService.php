<?php
namespace App\Domain\Notifications\Service;

use App\Domain\Notifications\Entity\Notification;
use App\Domain\Notifications\Repository\NotificationRepositoryInterface;

class NotificationDomainService
{
    private NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Crea una notificación de confirmación de reserva
     */
    public function createReservationConfirmation(
        int $userId,
        string $spaceName,
        \DateTime $date,
        \DateTime $startTime,
        \DateTime $endTime,
        int $reservationId
    ): Notification {
        $title = 'Reserva Confirmada';
        $message = sprintf(
            'Tu reserva para %s el %s de %s a %s ha sido confirmada exitosamente.',
            $spaceName,
            $date->format('d/m/Y'),
            $startTime->format('H:i'),
            $endTime->format('H:i')
        );

        $data = [
            'type' => 'reservation_confirmation',
            'reservation_id' => $reservationId,
            'space_name' => $spaceName,
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime->format('H:i'),
            'end_time' => $endTime->format('H:i')
        ];

        return new Notification($userId, 'email', $title, $message, $data);
    }

    /**
     * Crea una notificación de cancelación de reserva
     */
    public function createReservationCancellation(
        int $userId,
        string $spaceName,
        \DateTime $date,
        \DateTime $startTime,
        int $reservationId
    ): Notification {
        $title = 'Reserva Cancelada';
        $message = sprintf(
            'Tu reserva para %s el %s a las %s ha sido cancelada.',
            $spaceName,
            $date->format('d/m/Y'),
            $startTime->format('H:i')
        );

        $data = [
            'type' => 'reservation_cancellation',
            'reservation_id' => $reservationId,
            'space_name' => $spaceName,
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime->format('H:i')
        ];

        return new Notification($userId, 'email', $title, $message, $data);
    }

    /**
     * Crea una notificación de recordatorio
     */
    public function createReservationReminder(
        int $userId,
        string $spaceName,
        \DateTime $date,
        \DateTime $startTime,
        int $reservationId
    ): Notification {
        $title = 'Recordatorio de Reserva';
        $message = sprintf(
            'Recordatorio: Tienes una reserva para %s mañana %s a las %s.',
            $spaceName,
            $date->format('d/m/Y'),
            $startTime->format('H:i')
        );

        $data = [
            'type' => 'reservation_reminder',
            'reservation_id' => $reservationId,
            'space_name' => $spaceName,
            'date' => $date->format('Y-m-d'),
            'start_time' => $startTime->format('H:i')
        ];

        return new Notification($userId, 'email', $title, $message, $data);
    }

    /**
     * Procesa notificaciones pendientes
     */
    public function processPendingNotifications(): int
    {
        $pendingNotifications = $this->notificationRepository->findPendingNotifications();
        $processedCount = 0;

        foreach ($pendingNotifications as $notification) {
            try {
                // Aquí se integraría con el servicio de envío real
                // Por ahora solo marcamos como enviada
                $this->simulateSending($notification);

                $notification->markAsSent();
                $this->notificationRepository->save($notification);
                $processedCount++;
            } catch (\Exception $e) {
                // Log del error pero continuar con las demás notificaciones
                error_log("Error sending notification {$notification->getId()}: " . $e->getMessage());
            }
        }

        return $processedCount;
    }

    /**
     * Simula el envío de una notificación
     */
    private function simulateSending(Notification $notification): void
    {
        // En un entorno real, aquí se integraría con:
        // - Servicio de email (SendGrid, Mailgun, etc.)
        // - Servicio de SMS (Twilio, etc.)
        // - Servicio de push notifications

        switch ($notification->getType()) {
            case 'email':
                $this->simulateEmailSending($notification);
                break;
            case 'sms':
                $this->simulateSmsSending($notification);
                break;
            case 'push':
                $this->simulatePushSending($notification);
                break;
        }
    }

    /**
     * Simula envío de email
     */
    private function simulateEmailSending(Notification $notification): void
    {
        // Simular delay de envío
        usleep(100000); // 0.1 segundos

        // En desarrollo, podrías escribir a un log
        error_log("EMAIL SENT: {$notification->getTitle()} - {$notification->getMessage()}");
    }

    /**
     * Simula envío de SMS
     */
    private function simulateSmsSending(Notification $notification): void
    {
        usleep(50000); // 0.05 segundos
        error_log("SMS SENT: {$notification->getMessage()}");
    }

    /**
     * Simula envío de push notification
     */
    private function simulatePushSending(Notification $notification): void
    {
        usleep(25000); // 0.025 segundos
        error_log("PUSH SENT: {$notification->getTitle()}");
    }

    /**
     * Obtiene estadísticas de notificaciones
     */
    public function getNotificationStats(): array
    {
        $allNotifications = $this->notificationRepository->findPendingNotifications();

        $stats = [
            'total_pending' => 0,
            'by_type' => [
                'email' => 0,
                'sms' => 0,
                'push' => 0
            ],
            'oldest_pending' => null
        ];

        $oldestDate = null;

        foreach ($allNotifications as $notification) {
            if (!$notification->isSent()) {
                $stats['total_pending']++;
                $stats['by_type'][$notification->getType()]++;

                if (!$oldestDate || $notification->getCreatedAt() < $oldestDate) {
                    $oldestDate = $notification->getCreatedAt();
                }
            }
        }

        $stats['oldest_pending'] = $oldestDate;

        return $stats;
    }
}
