<?php
namespace App\MedicalStaff\Models;

use Doctrine\ORM\Mapping as ORM;

use App\Users\Models\User;
use App\Specialities\Models\Speciality;

#[ORM\Entity]
#[ORM\Table(name: 'medical_staff')]
class MedicalStaff
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

    #[ORM\ManyToOne(targetEntity: Speciality::class)]
    #[ORM\JoinColumn(
        name: 'speciality_id',
        referencedColumnName: 'id',
        nullable: true,
        onDelete: 'SET NULL'
    )]
    public ?Speciality $speciality = null;

    #[ORM\Column(length: 20, unique: true, nullable: true)]
    private ?string $licenseNumber = null;

    public function __construct(User $user, ?Speciality $speciality, ?string $licenseNumber)
    {
        $this->user = $user;
        $this->speciality = $speciality;
        $this->licenseNumber = $licenseNumber !== null
            ? self::normalizeLicenseNumber($licenseNumber)
            : null;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function user(): User
    {
        return $this->user;
    }

    public function speciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function licenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function setSpeciality(?Speciality $speciality): void
    {
        $this->speciality = $speciality;
    }

    public function setLicenseNumber(?string $licenseNumber): void
    {
        $this->licenseNumber = $licenseNumber !== null
            ? self::normalizeLicenseNumber($licenseNumber)
            : null;
    }

    
    private static function normalizeLicenseNumber(string $licenseNumber): string
    {
        return trim(strtoupper($licenseNumber));
    }
}