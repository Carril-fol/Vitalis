<?php
namespace App\Administratives\Models;

use Doctrine\ORM\Mapping as ORM;

use App\Users\Models\User;

#[ORM\Entity]
#[ORM\Table(name: 'administratives')]
class Administrative
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(
        name: 'user_id',
        referencedColumnName: 'id',
        unique: true,
        nullable: false,
        onDelete: 'CASCADE'
    )]
    private User $user;

    #[ORM\Column(length: 100)]
    private string $sector;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;


    public function __construct(User $user, string $sector, ?string $notes = null)
    {
        $this->user = $user;
        $this->sector = trim($sector);
        $this->notes = self::normalizeNotes($notes);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function sector(): string
    {
        return $this->sector;
    }

    public function notes(): ?string
    {
        return $this->notes;
    }

    public function setSector(string $sector): void
    {
        $this->sector = trim($sector);
    }

    public function setNotes(?string $notes): void
    {
        $this->notes = self::normalizeNotes($notes);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sector' => $this->sector,
            'role_id' => $this->user->role()->id(),
            'role_name' => $this->user->role()->name(),
            'notes' => $this->notes,
            'user_id' => $this->user->id(),
            'user_name' => $this->user->fullName(),
            'user_email' => $this->user->email(),
            'user_dni' => $this->user->dni(),
            'user_active' => $this->user->isActive(),
        ];
    }

    private static function normalizeNotes(?string $notes): ?string
    {
        if ($notes === null) {
            return null;
        }

        $notes = trim($notes);

        return $notes === '' ? null : $notes;
    }
}
