<?php

namespace App\Service\School;

use App\Dto\Output\School\YearListedOutput;
use App\Entity\School\School;
use App\Entity\School\Year;
use App\Repository\School\YearRepository;
use App\Service\KeyService;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Contracts\Cache\CacheInterface;

readonly class YearService
{
    public function __construct(
        private YearRepository $yearRepository,
        private EntityManagerInterface $entityManager,
        private CacheInterface $cache,
        private KeyService $keyService,
        private ObjectMapperInterface $objectMapper
    ) {
    }

    public function getYear(School $school, ?string $yearName = null): Year
    {
        if ($yearName !== null) {
            $year = $this->yearRepository->findOneBy(['name' => $yearName, 'school' => $school]);
            if ($year !== null) {
                return $year;
            }
            throw new NotFoundHttpException(sprintf('the year %s does not exist', $yearName));
        }

        $id = $this->cache->get(
            $this->keyService->getYearActiveKey($school),
            function () use ($school, $yearName) {
                $year = $this->yearRepository->findOneBy(
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

    /**
     * @param School $school
     * @return iterable<YearListedOutput>
     */
    public function getAll(School $school): iterable
    {
        $years = $this->yearRepository->findBySchool($school);
        foreach ($years as $year) {
            yield $this->objectMapper->map($year, YearListedOutput::class);
        }
    }
}
