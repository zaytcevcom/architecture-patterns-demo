<?php

declare(strict_types=1);

namespace App\Modules\Identity\Entity\Restore;

use App\Modules\Identity\Entity\User\Fields\Code;
use App\Modules\Identity\Entity\User\Fields\Email;
use App\Modules\Identity\Entity\User\Fields\FirstName;
use App\Modules\Identity\Entity\User\Fields\LastName;
use App\Modules\Identity\Entity\User\Fields\Phone;
use App\Modules\Identity\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use DomainException;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'identity_restore')]
class Restore
{
    public const TIME_INTERVAL = 3 * 60;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', unique: true)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', unique: false, nullable: true)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private ?string $uniqueId = null;

    #[ORM\Column(type: 'integer')]
    private int $time;

    #[ORM\Column(type: 'user_phone', nullable: true)]
    private ?Phone $phone = null;

    #[ORM\Column(type: 'user_email', nullable: true)]
    private ?Email $email = null;

    #[ORM\Column(type: 'user_firstname')]
    private FirstName $firstName;

    #[ORM\Column(type: 'user_lastname')]
    private LastName $lastName;

    #[ORM\Column(type: 'identity_user_code', length: 10, nullable: true)]
    private ?Code $code = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $codeTime = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isConfirm;

    #[ORM\Column(type: 'boolean')]
    private bool $isDone;

    public function __construct(
        FirstName $firstName,
        LastName $lastName
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->time = time();
        $this->isConfirm = false;
        $this->isDone = false;
    }

    public static function createByEmail(
        Email $email,
        FirstName $firstName,
        LastName $lastName,
    ): self {
        $restore = new self($firstName, $lastName);
        $restore->email = $email;

        return $restore;
    }

    public static function createByPhone(
        Phone $phone,
        FirstName $firstName,
        LastName $lastName,
    ): self {
        $restore = new self($firstName, $lastName);
        $restore->phone = $phone;

        return $restore;
    }

    public function generateUniqueId(): void
    {
        $this->uniqueId = Uuid::uuid4()->toString();
    }

    public function getTimeInterval(): int
    {
        return self::TIME_INTERVAL;
    }

    public function generateCode(): void
    {
        $code = (string)rand(10000, 99999);

        $this->code = new Code($code);
        $this->codeTime = time();
    }

    public function isValidCode(string $code): bool
    {
        if ($this->code === null) {
            return false;
        }

        return $this->code->getValue() === $code && !empty($code);
    }

    public function isValidInterval(): bool
    {
        if (empty($this->codeTime)) {
            return true;
        }

        return time() - $this->codeTime > $this->getTimeInterval();
    }

    public function isConfirmed(): bool
    {
        return $this->isConfirm;
    }

    public function confirm(): void
    {
        $this->isConfirm = true;
    }

    public function done(): void
    {
        $this->isDone = true;
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new DomainException('Id not set');
        }
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getUniqueId(): ?string
    {
        return $this->uniqueId;
    }

    public function setUniqueId(?string $uniqueId): void
    {
        $this->uniqueId = $uniqueId;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function setTime(int $time): void
    {
        $this->time = $time;
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function setPhone(?Phone $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?Email
    {
        return $this->email;
    }

    public function setEmail(?Email $email): void
    {
        $this->email = $email;
    }

    public function getFirstName(): FirstName
    {
        return $this->firstName;
    }

    public function setFirstName(FirstName $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): LastName
    {
        return $this->lastName;
    }

    public function setLastName(LastName $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getCode(): Code
    {
        if (null === $this->code) {
            throw new DomainException('Code not set');
        }
        return $this->code;
    }

    public function setCode(Code $code): void
    {
        $this->code = $code;
    }

    public function getCodeTime(): ?int
    {
        return $this->codeTime;
    }

    public function setCodeTime(?int $codeTime): void
    {
        $this->codeTime = $codeTime;
    }

    public function getIsConfirm(): bool
    {
        return $this->isConfirm;
    }

    public function setIsConfirm(bool $isConfirm): void
    {
        $this->isConfirm = $isConfirm;
    }

    public function getIsDone(): bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): void
    {
        $this->isDone = $isDone;
    }
}
