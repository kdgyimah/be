<?php

namespace App\Listener;

use App\Entity\Interface\TimestampableEntityInterface;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use ReflectionProperty;
use Symfony\Component\Clock\DatePoint;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: TimestampableEntityInterface::class)]
#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: TimestampableEntityInterface::class)]
readonly class TimestampEntityListener
{
    public function preUpdate(User $user): void
    {
        $rp = new ReflectionProperty(User::class, 'updatedAt');
        $rp->setValue($user, new DatePoint());
    }

    public function prePersist(User $user): void
    {
        $rp = new ReflectionProperty(User::class, 'createdAt');
        $rp->setValue($user, new DatePoint());
    }
}
