<?php

namespace App\Controller\Api;

use App\Controller\Trait\ListInputTrait;
use App\Dto\Output\Construction\CompanyListedOutput;
use App\Dto\Output\Construction\EngineerListedOutput;
use App\Dto\Output\Construction\ProjectListedOutput;
use App\Dto\Output\Construction\WorkerListedOutput;
use App\Entity\Construction\Company;
use App\Entity\Construction\Engineer;
use App\Entity\Construction\Project;
use App\Entity\User;
use App\Enum\ConstructionScope;
use App\Enum\ErrorCode;
use App\Service\Construction\ConstructionService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\JsonStreamer\StreamWriterInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\TypeInfo\Type;

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

    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 1,
            minimum: 1
        ),
    )]
    #[OA\Parameter(
        name: 'count',
        description: 'Number of maximum returned students',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 10,
            maximum: 100,
            minimum: 10
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
    #[IsGranted(ConstructionScope::LIST_EMPLOYEES->value, 'company')]
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

    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 1,
            minimum: 1
        ),
    )]
    #[OA\Parameter(
        name: 'count',
        description: 'Number of maximum returned students',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 10,
            maximum: 100,
            minimum: 10
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
    #[IsGranted(ConstructionScope::LIST_EMPLOYEES->value, 'company')]
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

    #[OA\Parameter(
        name: 'page',
        description: 'Page number',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 1,
            minimum: 1
        ),
    )]
    #[OA\Parameter(
        name: 'count',
        description: 'Number of maximum returned students',
        in: 'query',
        required: false,
        schema: new OA\Schema(
            type: 'integer',
            default: 10,
            maximum: 100,
            minimum: 10
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
    #[Route('/assign/{engineer}/{project}', name: 'assign_engineer', methods: Request::METHOD_POST)]
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
}
