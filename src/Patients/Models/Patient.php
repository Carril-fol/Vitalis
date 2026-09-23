<?php
namespace App\Patients\Models;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use App\Users\Models\User;
use App\HealthInsurances\Models\HealthInsurance;


#[ORM\Entity]
#[ORM\Table(name: 'patients')]
#[ORM\UniqueConstraint(name: 'insurance_member', columns: ['health_insurance_id', 'member_number'])]
#[UniqueEntity(['healthInsurance', 'memberNumber'], message: 'Ya hay un paciente con ese número de socio en esa obra social')]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: true, nullable: false, onDelete: 'CASCADE')]
    #[Assert\Valid]
    private User $user;

    #[ORM\ManyToOne(targetEntity: HealthInsurance::class)]
    #[ORM\JoinColumn(name: 'health_insurance_id', nullable: false)]
    #[Assert\NotNull(message: 'Elegí una obra social')]
    private ?HealthInsurance $healthInsurance = null;

    #[ORM\Column(name: 'member_number', length: 50)]
    #[Assert\NotBlank(message: 'El número de afiliado es obligatorio')]
    #[Assert\Length(max: 50, maxMessage: 'El número de afiliado no puede superar los {{ limit }} caracteres')]
    private ?string $memberNumber = null;

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

    public function getMemberNumber(): ?string
    {
        return $this->memberNumber;
    }

    public function getHealthInsurance(): ?HealthInsurance
    {
        return $this->healthInsurance;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function setHealthInsurance(?HealthInsurance $healthInsurance): void
    {
        $this->healthInsurance = $healthInsurance;
    }

    public function setMemberNumber(?string $memberNumber): void
    {
        $this->memberNumber = $memberNumber;
    }
}