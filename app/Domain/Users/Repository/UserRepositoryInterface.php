<?php


namespace App\Domain\Users\Repository;

use App\Domain\Users\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): User;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findAll(): array;
    public function delete(int $id): bool;
    public function existsByEmail(string $email): bool;
}
