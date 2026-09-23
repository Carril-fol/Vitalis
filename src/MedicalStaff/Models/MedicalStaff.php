<?php
namespace App\MedicalStaff\Models;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

use App\Users\Models\User;
use App\Specialities\Models\Speciality;


#[ORM\Entity]
#[ORM\Table(name: 'medical_staff')]
#[UniqueEntity('licenseNumber', message: 'Ya hay alguien con esa matrícula')]
class MedicalStaff
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: true, nullable: false, onDelete: 'CASCADE')]
    #[Assert\Valid]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Speciality::class)]
    #[ORM\JoinColumn(name: 'speciality_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Speciality $speciality = null;

    #[ORM\Column(length: 20, unique: true, nullable: true)]
    #[Assert\Length(max: 20, maxMessage: 'La matrícula no puede superar los {{ limit }} caracteres')]
    private ?string $licenseNumber = null;

    public function __construct()
    {
        $this->user = new User();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    public function getLicenseNumber(): ?string
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
        $this->licenseNumber = $licenseNumber === null ? null : mb_strtoupper($licenseNumber);
    }
}