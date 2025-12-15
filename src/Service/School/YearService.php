<?php

namespace App\Service\School;

use App\Entity\School\School;
use App\Entity\School\Year;
use App\Service\KeyService;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\CacheInterface;

readonly class YearService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache,
        private KeyService $keyService
    ) {
    }

    public function getYear(School $school, ?string $yearName = null): Year
    {
        $yearRepository = $this->entityManager->getRepository(Year::class);

        if ($yearName !== null) {
            $year = $yearRepository->findOneBy(['name' => $yearName, 'school' => $school]);
            if ($year !== null) {
                return $year;
            }
            throw new NotFoundHttpException(sprintf('the year %s does not exist', $yearName));
        }

        $id = $this->cache->get(
            $this->keyService->getYearActiveKey($school),
            static function () use ($school, $yearName, $yearRepository) {
                $year = $yearRepository->findOneBy(
                    ['school' => $school->id],
                    ['active' => Order::Descending->value, 'createdAt' => Order::Descending->value]
                );

                if ($year !== null) {
                    return $year->id;
                }

                throw new \LogicException('school must have a year');
            }
        );

        return $this->entityManager->find(Year::class, $id);
    }
}
