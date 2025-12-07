<?php

namespace App\Listener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
readonly class UserEntityListener
{
    public function __construct(private PropertyAccessorInterface $propertyAccessor)
    {
    }

    public function preUpdate(User $user): void
    {
        $this->propertyAccessor->setValue($user, 'updatedAt', new DatePoint());
    }
}
