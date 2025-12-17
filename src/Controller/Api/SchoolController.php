<?php

namespace App\Controller\Api;

use App\Dto\Output\School\SchoolListedOutput;
use App\Dto\Output\School\StudentListedOutput;
use App\Dto\Output\School\YearListedOutput;
use App\Entity\School\School;
use App\Entity\School\Year;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Repository\School\StudentYearRepository;
use App\Repository\School\UserScopeRepository;
use App\Repository\School\YearRepository;
use App\Security\Voter\SchoolVoter;
use App\Service\School\StudentService;
use App\Service\School\YearService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\JsonStreamer\StreamWriterInterface;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\TypeInfo\Type;
use Vich\UploaderBundle\Storage\FileSystemStorage;

#[OA\Tag('school')]
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
#[Route('/api/schools', name: 'api_school_', requirements: ['id' => '^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$'], format: 'json')]
class SchoolController
{
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'list of all manages schools',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(type: SchoolListedOutput::class)
            )
        )
    )]
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'list', methods: Request::METHOD_GET)]
    public function list(
        UserScopeRepository $userScopeRepository,
        ObjectMapperInterface $objectMapper,
        #[CurrentUser] User $currentUser,
    ): JsonResponse {
        $res = [];

        $userScopes = $userScopeRepository->findSchools($currentUser);

        foreach ($userScopes as $userScope) {
            $res[] = $objectMapper->map($userScope->school, SchoolListedOutput::class);
        }

        return new JsonResponse($res);
    }

    #[OA\Parameter(
        parameter: 'id',
        name: 'id',
        description: 'Id of the school',
        in: 'path',
        required: true
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'logo image',
        content: new OA\MediaType(mediaType: 'image/*')
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'this school does not have a photo',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['code' => Response::HTTP_BAD_REQUEST, 'message' => 'There is no logo']
        )
    )]
    #[Route('/{id}/logo', name: 'logo', methods: Request::METHOD_GET)]
    #[IsGranted(attribute: SchoolVoter::SHOW, subject: 'school', statusCode: Response::HTTP_NOT_FOUND)]
    public function getLogo(
        #[MapEntity(message: 'The school is not found')] School $school,
        FileSystemStorage $storage,
    ): Response {
        if (
            null === $school->logoFilename
            || ($path = $storage->resolvePath($school, 'logo', School::class)) === null
        ) {
            return new JsonResponse(['message' => 'There is no logo', 'code' => Response::HTTP_BAD_REQUEST]);
        }

        BinaryFileResponse::trustXSendfileTypeHeader();

        return new BinaryFileResponse($path, public: false);
    }

    #[OA\Parameter(
        parameter: 'id',
        name: 'id',
        description: 'Id of the school',
        in: 'path',
        required: true
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'list of years',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                ref: new Model(type: YearListedOutput::class)
            )
        )
    )]
    #[Route('/{id}/years', name: 'list_years', methods: Request::METHOD_GET)]
    #[IsGranted(attribute: SchoolVoter::SHOW, subject: 'school', statusCode: Response::HTTP_NOT_FOUND)]
    public function getYears(
        #[MapEntity(message: 'The school is not found')] School $school,
        StreamWriterInterface $jsonStreamWriter,
        YearService $yearService,
    ): Response {
        $years = $yearService->getAll($school);

        $json = $jsonStreamWriter->write(
            $years,
            Type::iterable(Type::object(YearListedOutput::class), Type::int())
        );

        return new StreamedResponse($json, headers: ['Content-Type' => 'application/json']);
    }

    #[OA\Parameter(
        parameter: 'id',
        name: 'id',
        description: 'Id of the year',
        in: 'path',
        required: true
    )]
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
            minimum: 1
        )
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'list of students in the year',
        content: new OA\JsonContent(
            required: ['data', 'total'],
            properties: [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(type: StudentListedOutput::class)
                ),
                new OA\Property(
                    property: 'total',
                    type: 'integer',
                ),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'bad parameters',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['code' => ErrorCode::SCHOOL_YEAR_STUDENTS_BAD_PAGE, 'message' => 'The page parameter is invalid']
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'the year was not found',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['code' => ErrorCode::SCHOOL_YEAR_STUDENTS_BAD_PAGE, 'message' => 'The page parameter is invalid']
        )
    )]
    #[Route('/students/year/{id}', name: 'list_students', methods: Request::METHOD_GET)]
    #[IsGranted(
        SchoolVoter::MANAGE_STUDENTS,
        new Expression('args["year"].school'),
        'the year is not found',
        Response::HTTP_NOT_FOUND,
    )]
    public function listStudents(
        #[MapEntity(message: 'The year is not found')] Year $year,
        Request $request,
        StudentYearRepository $studentYearRepository,
        StudentService $studentService,
        StreamWriterInterface $jsonStreamWriter,
    ): Response {
        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        if ($page < 1 || !is_int($page)) {
            return new JsonResponse(
                [
                    'code' => ErrorCode::SCHOOL_YEAR_STUDENTS_BAD_PAGE,
                    'message' => 'The page parameter is invalid',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($count < 1 || $count > 100 || !is_int($count)) {
            return new JsonResponse(
                [
                    'code' => ErrorCode::SCHOOL_YEAR_STUDENTS_BAD_COUNT,
                    'message' => 'The count parameter is invalid',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $studentsPaginator = $studentYearRepository->findByYear($year, $page, $count);

        $students = $studentService->generateStudentListedOutput($studentsPaginator->getIterator());

        $json = $jsonStreamWriter->write(
            ['data' => iterator_to_array($students), 'total' => $studentYearRepository->countByYear($year)],
            Type::arrayShape([
                'data' => Type::list(Type::object(StudentListedOutput::class)),
                'total' => Type::int(),
            ])
        );

        return new StreamedResponse($json, headers: ['Content-Type' => 'application/json']);
    }
}
