<?php

namespace App\Controller\Api;

use App\Controller\Trait\ListInputTrait;
use App\Dto\Output\Construction\CompanyListedOutput;
use App\Dto\Output\Construction\EngineerListedOutput;
use App\Dto\Output\Construction\WorkerListedOutput;
use App\Entity\Construction\Company;
use App\Entity\Construction\Engineer;
use App\Entity\Construction\Project;
use App\Entity\User;
use App\Enum\ConstructionScope;
use App\Repository\Construction\ProjectRepository;
use App\Service\Construction\ConstructionService;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
class ConstructionController extends AbstractController
{
    use ListInputTrait;

    // -------------------------
    // List companies
    // -------------------------
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

    // -------------------------
    // List engineers for a company
    // -------------------------
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

    // -------------------------
    // List workers for a company
    // -------------------------
    #[IsGranted(ConstructionScope::LIST_EMPLOYEES->value, 'company')]
    #[Route('/{id}/workers', name: 'list_workers', methods: Request::METHOD_GET)]
    public function listWorkers(
        #[MapEntity(message: 'The company is not found')] Company $company,
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

    // -------------------------
    // Assign engineer to project
    // -------------------------
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

    // -------------------------
    // /api/construction/me endpoint
    // -------------------------
     #[IsGranted('ROLE_USER')]
    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(
        #[CurrentUser] ?User $user
    ): JsonResponse {
        if (!$user) {
            return new JsonResponse(['code' => 401, 'message' => 'Unauthenticated'], 401);
        }

       return $this->json([
        'id' => $user->id?->toRfc4122(), // Convert Uuid object to string
        'email' => $user->email, // Direct property access
        'firstname' => $user->firstname,
        'lastname' => $user->lastname,
        'phoneNumber' => $user->phoneNumber,
        'roles' => $user->getRoles(), // This method exists
        ]);
    }
}
