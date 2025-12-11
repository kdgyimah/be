<?php

namespace App\Listener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Clock\DatePoint;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: User::class)]
readonly class UpdatedAtListener
{
    public function preUpdate(User $user): void
    {
        $rp = new \ReflectionProperty(User::class, 'updatedAt');
        $rp->setValue($user, new DatePoint());
    }
}
