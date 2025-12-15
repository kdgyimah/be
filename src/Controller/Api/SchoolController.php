<?php

namespace App\Controller\Api;

use App\Dto\Output\School\SchoolListedOutput;
use App\Dto\Output\School\StudentListedOutput;
use App\Dto\Output\School\YearListedOutput;
use App\Entity\School\School;
use App\Entity\School\Year;
use App\Entity\User;
use App\Repository\School\StudentYearRepository;
use App\Repository\School\UserScopeRepository;
use App\Repository\School\YearRepository;
use App\Security\Voter\SchoolVoter;
use App\Service\School\StudentService;
use App\Service\School\YearService;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\JsonStreamer\StreamWriterInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Vich\UploaderBundle\Storage\FileSystemStorage;

#[Route('/api/schools', name: 'api_school_', requirements: ['id' => '^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$'])]
class SchoolController
{
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'list', methods: Request::METHOD_GET)]
    public function list(
        UserScopeRepository $userScopeRepository,
        ObjectMapperInterface $objectMapper,
        #[CurrentUser]
        User $currentUser
    ): JsonResponse {
        $res = [];

        $userScopes = $userScopeRepository->findSchools($currentUser);

        foreach ($userScopes as $userScope) {
            $res[] = $objectMapper->map($userScope->school, SchoolListedOutput::class);
        }

        return new JsonResponse($res);
    }

    #[Route('/{id}/logo', name: 'logo', methods: Request::METHOD_GET)]
    #[IsGranted(attribute: SchoolVoter::SHOW, subject: 'school', statusCode: Response::HTTP_NOT_FOUND)]
    public function getLogo(#[MapEntity] School $school, FileSystemStorage $storage): Response
    {
        if ($school->logoFilename === null || ($path = $storage->resolvePath(
                $school,
                'logo',
                School::class
            )) === null) {
            return new JsonResponse(['message' => 'There is no logo', 'code' => Response::HTTP_BAD_REQUEST]);
        }

        BinaryFileResponse::trustXSendfileTypeHeader();

        return new BinaryFileResponse($path, public: false);
    }

    #[Route('/{id}/years', name: 'list_years', methods: Request::METHOD_GET)]
    #[IsGranted(attribute: SchoolVoter::SHOW, subject: 'school', statusCode: Response::HTTP_NOT_FOUND)]
    public function getYears(
        #[MapEntity] School $school,
        StreamWriterInterface $jsonStreamWriter,
        YearRepository $yearRepository,
        ObjectMapperInterface $objectMapper
    ): Response {
        $years = $yearRepository->getAll($school);

        $json = $jsonStreamWriter->write(
            array_map(
                static fn (Year $year): YearListedOutput => $objectMapper->map($year, YearListedOutput::class),
                iterator_to_array($years)
            ),
            Type::list(Type::object(YearListedOutput::class))
        );

        return new StreamedResponse($json, headers: ['Content-Type' => 'application/json']);
    }

    #[Route('students/year/{id}', name: 'list_students', methods: Request::METHOD_GET)]
    #[IsGranted(SchoolVoter::MANAGE_STUDENTS, new Expression('args["year"].school'))]
    public function listStudents(
        #[MapEntity] Year $year,
        Request $request,
        StudentYearRepository $studentYearRepository,
        StudentService $studentService,
        StreamWriterInterface $jsonStreamWriter
    ): Response {
        $page = $request->query->getInt('page', 1);
        $count = $request->query->getInt('count', 10);

        $studentsPaginator = $studentYearRepository->findByYear($year, $page, $count);

        $students = $studentService->generateStudentListedOutput($studentsPaginator->getIterator());

        $json = $jsonStreamWriter->write(
            ['data' => iterator_to_array($students), 'total' => $studentYearRepository->countByYear($year)],
            Type::arrayShape([
                'data' => Type::list(Type::object(StudentListedOutput::class)),
                'total' => Type::int()
            ])
        );

        return new StreamedResponse($json, headers: ['Content-Type' => 'application/json']);
    }
}
