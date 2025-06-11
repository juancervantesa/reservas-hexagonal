<?php
namespace App\Domain\Users\Service;

use App\Domain\Users\Entity\User;
use App\Domain\Users\Repository\UserRepositoryInterface;

class UserDomainService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Valida si un email es único en el sistema
     */
    public function isEmailUnique(string $email): bool
    {
        return !$this->userRepository->existsByEmail($email);
    }

    /**
     * Valida si una contraseña cumple con los requisitos de seguridad
     */
    public function isValidPassword(string $password): bool
    {
        // Mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/\d/', $password);
    }

    /**
     * Valida si un email tiene formato válido
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Verifica si un usuario puede acceder a funciones administrativas
     */
    public function canAccessAdminFunctions(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Verifica si un usuario puede modificar otro usuario
     */
    public function canModifyUser(User $currentUser, User $targetUser): bool
    {
        // Un admin puede modificar cualquier usuario
        if ($currentUser->isAdmin()) {
            return true;
        }

        // Un usuario solo puede modificarse a sí mismo
        return $currentUser->getId() === $targetUser->getId();
    }

    /**
     * Genera un hash seguro para la contraseña
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);
    }

    /**
     * Verifica si una contraseña coincide con su hash
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
