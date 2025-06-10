<?php
namespace App\Application\Spaces;

use App\Domain\Spaces\Repository\SpaceRepositoryInterface;

class ListAvailableSpacesUseCase
{
    private SpaceRepositoryInterface $spaceRepository;

    public function __construct(SpaceRepositoryInterface $spaceRepository)
    {
        $this->spaceRepository = $spaceRepository;
    }

    public function execute(?string $type = null): array
    {
        if ($type) {
            return $this->spaceRepository->findByType($type);
        }

        return $this->spaceRepository->findAvailable();
    }
}
