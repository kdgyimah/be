<?php

namespace App\Controller\Api;

use App\Constant\ConstructionScope;
use App\Controller\Trait\ListInputTrait;
use App\Dto\Input\Construction\CreateWorkerInput;
use App\Dto\Output\Construction\CompanyListedOutput;
use App\Dto\Output\Construction\EngineerListedOutput;
use App\Dto\Output\Construction\ProjectListedOutput;
use App\Dto\Output\Construction\WorkerListedOutput;
use App\Entity\Construction\Company;
use App\Entity\Construction\Engineer;
use App\Entity\Construction\Project;
use App\Entity\Construction\Worker;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Service\Construction\ConstructionService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\JsonStreamer\StreamWriterInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[OA\Tag('construction')]
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Internal error response',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorResponse',
    )
)]
#[OA\Response(
    response: Response::HTTP_UNAUTHORIZED,
    description: 'Invalid credentials',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorResponse',
        example: ['code' => Response::HTTP_UNAUTHORIZED, 'message' => 'Invalid JWT Token'],
    )
)]
#[Route('/api/construction', name: 'api_construction_', requirements: ['id' => '^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$'])]
class ConstructionController
{
    use ListInputTrait;

    #[OA\Get(operationId: 'list companies')]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'list of managed constructions sites',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(type: CompanyListedOutput::class)
            )
        )
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('', 'list', methods: Request::METHOD_GET)]
    public function listCompanies(
        #[CurrentUser] User $user,
        ConstructionService $constructionService,
        StreamWriterInterface $jsonStreamWriter,
    ): Response {
        $res = $constructionService->getCompanies($user);

        $json = $jsonStreamWriter->write(
            $res,
            Type::iterable(Type::object(CompanyListedOutput::class), Type::int())
        );

        return new StreamedResponse($json, headers: ['Content-Type' => 'application/json']);
    }

    #[OA\Get(operationId: 'list engineers')]
    #[OA\PathParameter(
        name: 'id',
        description: 'the company id'
    )]
    #[OA\QueryParameter(
        name: 'page',
        description: 'Page number',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 1,
            minimum: 1
        ),
    )]
    #[OA\QueryParameter(
        name: 'count',
        description: 'Number of maximum returned students',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 10,
            maximum: 100,
            minimum: 10
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'list of managed engineers',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: EngineerListedOutput::class))
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'bad parameters',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['code' => ErrorCode::PAGINATION_BAD_PAGE, 'message' => 'The page parameter is invalid']
        )
    )]
    #[IsGranted(ConstructionScope::LIST_EMPLOYEES, 'company')]
    #[Route('/{id}/engineers', name: 'list_engineers', methods: Request::METHOD_GET)]
    public function listEngineers(
        #[MapEntity(message: 'The company is not found')] Company $company,
        Request $request,
        ConstructionService $constructionService,
        StreamWriterInterface $jsonStreamWriter,
    ): Response {
        $listInput = $this->getListInput($request);

        $engineers = $constructionService->listEngineers($company, $listInput);

        return $this->getListResponse(
            $engineers,
            $constructionService->countEngineersByCompany($company),
            $jsonStreamWriter,
            EngineerListedOutput::class
        );
    }

    #[OA\Get(operationId: 'list workers')]
    #[OA\PathParameter(
        name: 'id',
        description: 'the company id'
    )]
    #[OA\QueryParameter(
        name: 'page',
        description: 'Page number',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 1,
            minimum: 1
        ),
    )]
    #[OA\QueryParameter(
        name: 'count',
        description: 'Number of maximum returned students',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 10,
            maximum: 100,
            minimum: 10
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'list of managed workers',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: WorkerListedOutput::class))
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'bad parameters',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['code' => ErrorCode::PAGINATION_BAD_PAGE, 'message' => 'The page parameter is invalid']
        )
    )]
    #[IsGranted(ConstructionScope::LIST_EMPLOYEES, 'company')]
    #[Route('/{id}/workers', name: 'list_workers', methods: Request::METHOD_GET)]
    public function listWorkers(
        #[MapEntity(message: 'The company is not found')]
        Company $company,
        Request $request,
        ConstructionService $constructionService,
        StreamWriterInterface $jsonStreamWriter,
    ): Response {
        $listInput = $this->getListInput($request);

        $workers = $constructionService->listWorkers($company, $listInput);

        return $this->getListResponse(
            $workers,
            $constructionService->countWorkersByCompany($company),
            $jsonStreamWriter,
            WorkerListedOutput::class
        );
    }

    #[OA\Get(operationId: 'list projects')]
    #[OA\PathParameter(
        name: 'id',
        description: 'the company id'
    )]
    #[OA\QueryParameter(
        name: 'page',
        description: 'Page number',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 1,
            minimum: 1
        ),
    )]
    #[OA\QueryParameter(
        name: 'count',
        description: 'Number of maximum returned students',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 10,
            maximum: 100,
            minimum: 10
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'list of projects managed by a company',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: ProjectListedOutput::class))
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'bad parameters',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['code' => ErrorCode::PAGINATION_BAD_PAGE, 'message' => 'The page parameter is invalid']
        )
    )]
    #[Route('{id}/projects', name: 'list_projects', methods: Request::METHOD_GET)]
    public function listProjects(
        #[MapEntity(message: 'The company is not found')]
        Company $company,
        Request $request,
        ConstructionService $constructionService,
        StreamWriterInterface $jsonStreamWriter,
    ): Response {
        $listInput = $this->getListInput($request);

        $projects = $constructionService->listProjects($company, $listInput);

        return $this->getListResponse(
            $projects,
            $constructionService->countProjectsByCompany($company),
            $jsonStreamWriter,
            ProjectListedOutput::class
        );
    }

    #[OA\Patch(operationId: 'assign an engineer to a project')]
    #[OA\PathParameter(
        name: 'engineer',
        description: 'the engineer id'
    )]
    #[OA\PathParameter(
        name: 'project',
        description: 'the project id'
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Engineer is assigned the project',
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'engineer and project have different companies',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
        )
    )]
    #[Route('/assign/{engineer}/{project}', name: 'assign_engineer', methods: Request::METHOD_PATCH)]
    public function assignEngineer(
        #[MapEntity(id: 'engineer', message: "engineer doesn't exist")] Engineer $engineer,
        #[MapEntity(id: 'project', message: "the project doesn't exist")] Project $project,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($engineer->company !== $project->company) {
            throw new BadRequestHttpException('engineer and project have different companies');
        }
        $project->setEngineer($engineer);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[OA\Post(operationId: 'create a worker')]
    #[OA\PathParameter(
        name: 'id',
        description: 'the company id'
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            ref: new Model(type: CreateWorkerInput::class)
        )
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: 'create worker successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
            ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "can't join project and engineer",
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: "values are invalid",
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
        )
    )]
    #[IsGranted(ConstructionScope::MANAGE_EMPLOYEES, 'company')]
    #[Route('/{id}/workers', name: 'create_worker', methods: Request::METHOD_POST)]
    public function createWorker(
        #[MapRequestPayload(acceptFormat: 'json')]
        CreateWorkerInput $createWorkerInput,
        #[MapEntity(message: 'The company is not found')]
        Company $company,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): Response {
        $worker = new Worker($company)
            ->setLastname($createWorkerInput->lastname)
            ->setFirstname($createWorkerInput->firstname)
            ->setPhoneNumber($createWorkerInput->phoneNumber)
            ->setProfession($createWorkerInput->profession)
            ->setDailySalary($createWorkerInput->dailySalary->amount, $createWorkerInput->dailySalary->currency);

        $constraints = $validator->validate($worker);

        if ($constraints->count() > 0) {
            $messages = [];

            foreach ($constraints as $constraint) {
                $messages[] = $constraint->getMessage();
            }

            return new JsonResponse(
                ['message' => implode('\n', $messages), 'code' => 0],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $entityManager->persist($worker);
        $entityManager->flush();

        return new JsonResponse(['id' => $worker->id], status: Response::HTTP_CREATED);
    }
}
