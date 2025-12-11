<?php

namespace App\Controller\Api;

use App\Dto\Output\School\SchoolListedOutput;
use App\Dto\Output\School\SchoolOutput;
use App\Entity\School\School;
use App\Entity\School\SchoolUserScope;
use App\Entity\User;
use App\Repository\SchoolUserScopeRepository;
use App\Security\Voter\SchoolVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Storage\FileSystemStorage;

#[Route('/api/schools', name: 'api_school_', format: 'json')]
class SchoolController
{
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'list', methods: Request::METHOD_GET)]
    public function list(
        SchoolUserScopeRepository $schoolUserScopeRepository,
        ObjectMapperInterface $objectMapper,
        #[CurrentUser]
        User $currentUser
    ): JsonResponse {
        $res = array_map(
            static fn(SchoolUserScope $scope) => $objectMapper->map($scope->school, SchoolListedOutput::class),
            $schoolUserScopeRepository->findSchools($currentUser)
        );

        return new JsonResponse($res);
    }

    #[Route(
        '/{id}',
        name: 'show',
        requirements: ['id' => '^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$'],
        methods: Request::METHOD_GET
    )]
    #[IsGranted(attribute: SchoolVoter::SHOW, subject: 'school', statusCode: Response::HTTP_NOT_FOUND)]
    public function get(
        #[MapEntity] School $school,
        ObjectMapperInterface $objectMapper
    ): JsonResponse {
        return new JsonResponse($objectMapper->map($school, SchoolOutput::class));
    }

    #[Route(
        '/{id}/logo',
        name: 'logo',
        requirements: ['id' => '^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$'],
        methods: Request::METHOD_GET
    )]
    #[IsGranted(attribute: SchoolVoter::SHOW, subject: 'school', statusCode: Response::HTTP_NOT_FOUND)]
    public function getLogo(#[MapEntity] School $school, FileSystemStorage $storage): Response
    {
        if ($school->logoFilename === null || ($path = $storage->resolvePath($school, 'logo', School::class)) === null) {
            return new JsonResponse(['message' => 'There is no logo', 'code' => Response::HTTP_BAD_REQUEST]);
        }

        BinaryFileResponse::trustXSendfileTypeHeader();

        return new BinaryFileResponse($path, public: false);
    }
}
