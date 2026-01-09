<?php

namespace App\Listener;

use App\Entity\Interface\TimestampableEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\Clock\DatePoint;

#[AsEntityListener(lazy: true)]
readonly class TimestampEntityListener
{
    public function preUpdate(TimestampableEntityInterface $entity): void
    {
        $rp = $this->getReflectionProperty($entity, 'updatedAt');
        $rp->setValue($entity, new DatePoint());
    }

    public function prePersist(TimestampableEntityInterface $entity): void
    {
        $rp = $this->getReflectionProperty($entity, 'createdAt');

        $rp->setValue($entity, new DatePoint());
    }

    private function getReflectionProperty(TimestampableEntityInterface $entity, string $propertyName): \ReflectionProperty
    {
        $rc = new \ReflectionClass($entity);
        $className = $rc->getName();

        foreach ($rc->getProperties() as $property) {
            if ($propertyName === $property->getName()) {
                return $property;
            }
        }

        $rc = $rc->getParentClass();

        while (false !== $rc) {
            foreach ($rc->getProperties() as $property) {
                if ($propertyName === $property->getName()) {
                    return $property;
                }
            }
            $rc = $rc->getParentClass();
        }

        throw new \LogicException("$className doesn't have $propertyName attribute");
    }
}
