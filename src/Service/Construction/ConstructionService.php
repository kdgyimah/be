<?php

namespace App\Service\Construction;

use App\Dto\Input\ListInput;
use App\Dto\Output\Construction\CompanyListedOutput;
use App\Dto\Output\Construction\EngineerListedOutput;
use App\Dto\Output\Construction\ProjectListedOutput;
use App\Dto\Output\Construction\WorkerListedOutput;
use App\Entity\Construction\Company;
use App\Entity\User;
use App\Repository\Construction\CompanyRepository;
use App\Repository\Construction\EngineerRepository;
use App\Repository\Construction\ProjectRepository;
use App\Repository\Construction\WorkerRepository;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

readonly class ConstructionService
{
    public function __construct(
        private CompanyRepository $companyRepository,
        private ObjectMapperInterface $objectMapper,
        private EngineerRepository $engineerRepository,
        private WorkerRepository $workerRepository,
        private ProjectRepository $projectRepository,
    ) {
    }

    public function getCompanies(User $user): iterable
    {
        $companies = $this->companyRepository->findByUser($user);

        foreach ($companies as $company) {
            yield $this->objectMapper->map($company, CompanyListedOutput::class);
        }
    }

    /**
     * @return iterable<EngineerListedOutput>
     */
    public function listEngineers(Company $company, ListInput $listInput): iterable
    {
        $engineers = $this->engineerRepository->findByCompany($company, $listInput->page, $listInput->count);
        foreach ($engineers as [$engineer, $project]) {
            $res = new EngineerListedOutput();
            $res->lastname = $engineer->lastname;
            $res->firstname = $engineer->firstname;
            $res->project = null !== $project ? $this->objectMapper->map($project, ProjectListedOutput::class) : null;

            yield $res;
        }
    }

    public function countEngineersByCompany(Company $company): int
    {
        return $this->engineerRepository->countByCompany($company);
    }

    /**
     * @return iterable<WorkerListedOutput>
     */
    public function listWorkers(Company $company, ListInput $listInput): iterable
    {
        $workers = $this->workerRepository->findByCompany($company, $listInput->page, $listInput->count);
        foreach ($workers as $worker) {
            yield $this->objectMapper->map($worker, WorkerListedOutput::class);
        }
    }

    public function countWorkersByCompany(Company $company): int
    {
        return $this->workerRepository->countByCompany($company);
    }

    /**
     * @param Company $company
     * @param ListInput $listInput
     * @return iterable<ProjectListedOutput>
     */
    public function listProjects(Company $company, ListInput $listInput): iterable
    {
        $projects = $this->projectRepository->findByCompany($company, $listInput->page, $listInput->count);
        foreach ($projects as $project) {
            yield $this->objectMapper->map($project, ProjectListedOutput::class);
        }
    }

    public function countProjectsByCompany(Company $company): int
    {
        return $this->projectRepository->countByCompany($company);
    }
}
