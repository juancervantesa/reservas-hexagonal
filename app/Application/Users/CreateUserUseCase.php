<?php
namespace App\Application\Users;

use App\Domain\Users\Entity\User;
use App\Domain\Users\Repository\UserRepositoryInterface;

class CreateUserUseCase
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function execute(string $name, string $email, string $password, string $role = 'user'): User
    {
        // Validar que el email no exista
        if ($this->userRepository->existsByEmail($email)) {
            throw new \InvalidArgumentException('Email already exists');
        }

        // Validar rol
        if (!in_array($role, ['user', 'admin'])) {
            throw new \InvalidArgumentException('Invalid role');
        }

        // Crear usuario
        $user = new User($name, $email, password_hash($password, PASSWORD_DEFAULT), $role);

        return $this->userRepository->save($user);
    }
}
