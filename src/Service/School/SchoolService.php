<?php

namespace App\Service\School;

use App\Dto\Output\School\SchoolListedOutput;
use App\Entity\User;
use App\Repository\School\SchoolRepository;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class SchoolService
{
    public function __construct(
        private SchoolRepository $schoolRepository,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    /**
     * @param User $user
     * @return iterable<SchoolListedOutput>
     */
    public function getSchools(User $user): iterable
    {
        $schools = $this->schoolRepository->findByUser($user);

        foreach ($schools as $school) {
            yield $this->objectMapper->map($school, SchoolListedOutput::class);
        }
    }
}
