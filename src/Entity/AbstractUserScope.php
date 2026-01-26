<?php

namespace App\Entity;

use App\Entity\Construction\UserScope as ConstructionUserScope;
use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\School\UserScope as SchoolUserScope;
use App\Entity\Trait\PrimaryKeyTrait;
use App\Entity\Trait\TimestampableEntityTrait;
use App\Repository\UserScopeRepository;
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

    /** @var list<string> */
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    public private(set) array $scopes;

    public function __construct(User $user, array $scopes)
    {
        $this->scopes = $scopes;
        $this->user = $user;
    }
}
