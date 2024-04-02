<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\SignupMethod;

use App\Modules\Identity\Entity\SignupMethod\Fields\Status;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'identity_signup_methods')]
class SignupMethod
{
    public const TYPE_EMAIL = 0;
    public const TYPE_SMS = 1;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'identity_signup_method_status')]
    private Status $status;

    public function __construct(
        string $name,
        Status $status
    ) {
        $this->name = $name;
        $this->status = $status;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }
}
