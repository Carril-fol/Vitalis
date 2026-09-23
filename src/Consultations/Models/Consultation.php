<?php
namespace App\Consultations\Models;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use DateTimeImmutable;

use App\Turns\Models\Turn;
use App\Users\Models\User;

#[ORM\Entity]
#[ORM\Table(name: 'consultations')]
class Consultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Turn::class)]
    #[ORM\JoinColumn(name: 'turn_id', unique: true, nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull]
    private ?Turn $turn;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La nota no puede estar vacía')]
    private string $content = '';

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by_id', nullable: false)]
    #[Assert\NotNull]
    private ?User $createdBy;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(?Turn $turn = null, ?User $createdBy = null)
    {
        $this->turn = $turn;
        $this->createdBy = $createdBy;
        $this->createdAt = new DateTimeImmutable();
    }

    public function markEdited(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function wasEdited(): bool
    {
        return $this->updatedAt !== null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTurn(): ?Turn
    {
        return $this->turn;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setContent(?string $content): void
    {
        $this->content = trim((string) $content);
    }
}
