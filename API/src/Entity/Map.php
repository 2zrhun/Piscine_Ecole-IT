<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'UNIQ_MAP_NAME', fields: ['name'])]
#[UniqueEntity(fields: ['name'], message: 'Ce nom de ville est déjà utilisé.')]
#[ApiResource(
    operations: [
        new Get(
            security: "is_granted('ROLE_USER') and (object.getUser() == user or is_granted('ROLE_ADMIN'))"
        ),
        new GetCollection(
            security: "is_granted('ROLE_USER')"
        ),
        new Post(
            security: "is_granted('ROLE_USER')"
        ),
        new Put(
            security: "is_granted('ROLE_USER') and (object.getUser() == user or is_granted('ROLE_ADMIN'))"
        ),
        new Delete(
            security: "is_granted('ROLE_USER') and (object.getUser() == user or is_granted('ROLE_ADMIN'))"
        )
    ],
    normalizationContext: ['groups' => ['map:read']],
    denormalizationContext: ['groups' => ['map:write']]
)]
#[Groups(['map:read', 'map:write'])]
class Map
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'map')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de la map ne peut pas être vide")]
    #[Assert\Length(
        min: 2, 
        max: 255, 
        minMessage: "Le nom de la map doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom de la map ne peut pas dépasser {{ limit }} caractères"
    )]

    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $config = null;

    #[ORM\Column(length: 7, nullable: true)]
    #[Groups(['map:read'])]
    private ?string $color = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getConfig(): ?array{
        return $this->config;
    }

    public function setConfig(?array $config): static
    {
        $this->config = $config;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
        return $this;
    }
}