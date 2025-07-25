<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\UniqueConstraint(columns: ['login', 'pass'])]
#[UniqueEntity(fields: ['login', 'pass'], message: 'Login or password is invalid')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[Groups(['user:edit'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    #[ORM\Column(length: 8)]
    private ?string $login = null;

    #[Groups(['user:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 12)]
    #[ORM\Column(length: 12)]
    private ?string $phone = null;

    #[Groups(['user:read'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 8)]
    #[ORM\Column(length: 8)]
    private ?string $pass = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(string $pass): static
    {
        $this->pass = $pass;

        return $this;
    }
}
