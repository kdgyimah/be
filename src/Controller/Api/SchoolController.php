<?php

namespace App\Controller\Api;

use App\Constant\SchoolScope;
use App\Controller\Trait\ListInputTrait;
use App\Dto\Output\School\SchoolListedOutput;
use App\Dto\Output\School\StudentListedOutput;
use App\Dto\Output\School\YearListedOutput;
use App\Entity\School\School;
use App\Entity\School\Year;
use App\Entity\User;
use App\Enum\ErrorCode;
use App\Security\Voter\SchoolVoter;
use App\Service\School\SchoolService;
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
    use ListInputTrait;

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
        #[CurrentUser] User $currentUser,
        SchoolService $schoolService,
        StreamWriterInterface $jsonStreamWriter,
    ): Response {
        $res = $schoolService->getSchools($currentUser);

        $json = $jsonStreamWriter->write(
            $res,
            Type::iterable(Type::object(SchoolListedOutput::class), Type::int())
        );

        return new StreamedResponse($json, headers: ['Content-Type' => 'application/json']);
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
    #[IsGranted(attribute: SchoolScope::SHOW, subject: 'school', statusCode: Response::HTTP_NOT_FOUND)]
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
    #[IsGranted(attribute: SchoolScope::SHOW, subject: 'school', statusCode: Response::HTTP_NOT_FOUND)]
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
            minimum: 10
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
            example: ['code' => ErrorCode::PAGINATION_BAD_PAGE, 'message' => 'The page parameter is invalid']
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'the year was not found',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
        )
    )]
    #[Route('/students/year/{id}', name: 'list_students', methods: Request::METHOD_GET)]
    #[IsGranted(
        SchoolScope::MANAGE_STUDENTS,
        new Expression('args["year"].school'),
        'the year is not found',
        Response::HTTP_NOT_FOUND,
    )]
    public function listStudents(
        #[MapEntity(message: 'The year is not found')] Year $year,
        Request $request,
        StudentService $studentService,
        StreamWriterInterface $jsonStreamWriter,
    ): Response {
        $listInput = $this->getListInput($request);

        $students = $studentService->getAllByYear($year, $listInput);

        return $this->getListResponse(
            $students,
            $studentService->countAllByYear($year),
            $jsonStreamWriter,
            StudentListedOutput::class
        );
    }
}
