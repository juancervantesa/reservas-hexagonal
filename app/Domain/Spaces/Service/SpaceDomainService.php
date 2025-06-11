<?php
namespace App\Domain\Spaces\Service;

use App\Domain\Spaces\Entity\Space;
use App\Domain\Spaces\Repository\SpaceRepositoryInterface;

class SpaceDomainService
{
    private SpaceRepositoryInterface $spaceRepository;

    public function __construct(SpaceRepositoryInterface $spaceRepository)
    {
        $this->spaceRepository = $spaceRepository;
    }

    /**
     * Busca espacios disponibles según criterios específicos
     */
    public function findAvailableSpacesByCriteria(
        ?string $type = null,
        ?int $minCapacity = null,
        ?int $maxCapacity = null
    ): array {
        $spaces = $this->spaceRepository->findAvailable();

        return array_filter($spaces, function (Space $space) use ($type, $minCapacity, $maxCapacity) {
            // Filtrar por tipo si se especifica
            if ($type && $space->getType() !== $type) {
                return false;
            }

            // Filtrar por capacidad mínima
            if ($minCapacity && $space->getCapacity() < $minCapacity) {
                return false;
            }

            // Filtrar por capacidad máxima
            if ($maxCapacity && $space->getCapacity() > $maxCapacity) {
                return false;
            }

            return true;
        });
    }

    /**
     * Valida si un espacio puede ser reservado
     */
    public function canBeReserved(Space $space): bool
    {
        return $space->isActive();
    }

    /**
     * Calcula la ocupación promedio de un espacio
     */
    public function calculateOccupancyRate(Space $space, array $reservations): float
    {
        if (empty($reservations)) {
            return 0.0;
        }

        // Contar horas reservadas vs horas disponibles en un período
        $totalHoursAvailable = 24 * 30; // Asumiendo 30 días, 24 horas por día
        $totalHoursReserved = 0;

        foreach ($reservations as $reservation) {
            $start = $reservation->getStartTime();
            $end = $reservation->getEndTime();
            $hoursReserved = ($end->getTimestamp() - $start->getTimestamp()) / 3600;
            $totalHoursReserved += $hoursReserved;
        }

        return ($totalHoursReserved / $totalHoursAvailable) * 100;
    }

    /**
     * Obtiene espacios similares basado en tipo y capacidad
     */
    public function findSimilarSpaces(Space $referenceSpace): array
    {
        $allSpaces = $this->spaceRepository->findByType($referenceSpace->getType());

        return array_filter($allSpaces, function (Space $space) use ($referenceSpace) {
            // Excluir el espacio de referencia
            if ($space->getId() === $referenceSpace->getId()) {
                return false;
            }

            // Buscar espacios con capacidad similar (±20%)
            $capacityDifference = abs($space->getCapacity() - $referenceSpace->getCapacity());
            $maxDifference = $referenceSpace->getCapacity() * 0.2;

            return $capacityDifference <= $maxDifference && $space->isActive();
        });
    }

    /**
     * Valida si el nombre del espacio es único
     */
    public function isNameUnique(string $name, ?int $excludeId = null): bool
    {
        $allSpaces = $this->spaceRepository->findAll();

        foreach ($allSpaces as $space) {
            if ($excludeId && $space->getId() === $excludeId) {
                continue;
            }

            if (strtolower($space->getName()) === strtolower($name)) {
                return false;
            }
        }

        return true;
    }
}
