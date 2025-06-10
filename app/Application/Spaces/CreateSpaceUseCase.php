<?php
namespace App\Application\Spaces;

use App\Domain\Spaces\Entity\Space;
use App\Domain\Spaces\Repository\SpaceRepositoryInterface;

class CreateSpaceUseCase
{
    private SpaceRepositoryInterface $spaceRepository;

    public function __construct(SpaceRepositoryInterface $spaceRepository)
    {
        $this->spaceRepository = $spaceRepository;
    }

    public function execute(
        string $name,
        string $type,
        int $capacity,
        string $description = ''
    ): Space {
        // Validar tipo
        if (!in_array($type, Space::getValidTypes())) {
            throw new \InvalidArgumentException('Invalid space type');
        }

        // Validar capacidad
        if ($capacity <= 0) {
            throw new \InvalidArgumentException('Capacity must be greater than 0');
        }

        $space = new Space($name, $type, $capacity, $description);

        return $this->spaceRepository->save($space);
    }
}
