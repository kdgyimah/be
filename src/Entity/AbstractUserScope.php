<?php

namespace App\Entity;

use App\Entity\Construction\UserScope as ConstructionUserScope;
use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\School\UserScope as SchoolUserScope;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Repository\School\UserScopeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserScopeRepository::class)]
#[ORM\Table(name: 'user_scope')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'disc', type: Types::STRING)]
#[ORM\DiscriminatorMap(['school' => SchoolUserScope::class, 'construction' => ConstructionUserScope::class])]
abstract class AbstractUserScope implements TimestampableEntityInterface
{
    use PrimaryKeyTrait;
    use TimestampableEntityTrait;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    public private(set) User $user;

    #[ORM\Column(type: Types::STRING, length: 40)]
    public private(set) string $scope;

    public function __construct(User $user, \BackedEnum $scope)
    {
        $this->scope = $scope->value;
        $this->user = $user;
    }

    abstract public function getScope(): mixed;

    protected function getStringScope(): string
    {
        return $this->scope;
    }
}
