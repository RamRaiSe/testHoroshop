<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

readonly class ApiUser implements UserInterface
{
    public function __construct(private string $token)
    {
    }

    public function getRoles(): array
    {
        return match ($this->token) {
            'testAdmin' => ['ROLE_ADMIN'],
            'testUser' => ['ROLE_USER'],
            default => []
        };
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->token;
    }
}
