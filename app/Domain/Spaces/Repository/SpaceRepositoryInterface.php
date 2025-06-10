<?php
namespace App\Domain\Spaces\Repository;

use App\Domain\Spaces\Entity\Space;

interface SpaceRepositoryInterface
{
    public function save(Space $space): Space;
    public function findById(int $id): ?Space;
    public function findAll(): array;
    public function findByType(string $type): array;
    public function findAvailable(): array;
    public function delete(int $id): bool;
    public function findByCapacityRange(int $minCapacity, int $maxCapacity = null): array;
}
