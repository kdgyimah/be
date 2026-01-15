<?php

namespace App\Controller\Trait;

use App\Dto\Input\ListInput;
use App\Enum\ErrorCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\JsonStreamer\StreamWriterInterface;
use Symfony\Component\TypeInfo\Type;

trait ListInputTrait
{
    /**
     * @throws BadRequestHttpException
     */
    private function getListInput(Request $request): ListInput
    {
        $page = $request->query->getInt('page', 1);
        $count = $request->query->getInt('count', 10);

        if ($page < 1) {
            throw new BadRequestHttpException('The page parameter is invalid', code: ErrorCode::SCHOOL_YEAR_STUDENTS_BAD_PAGE->value);
        }

        if ($count < 1 || $count > 100) {
            throw new BadRequestHttpException('The count parameter is invalid', code: ErrorCode::SCHOOL_YEAR_STUDENTS_BAD_COUNT->value);
        }

        return new ListInput($page, $count);
    }

    /**
     * @template T
     *
     * @param iterable<T>     $data
     * @param class-string<T> $class
     */
    private function getListResponse(
        iterable $data,
        int $total,
        StreamWriterInterface $jsonStreamWriter,
        string $class,
    ): Response {
        $json = $jsonStreamWriter->write([
            'data' => $data,
            'total' => $total,
        ],
            Type::arrayShape([
                'data' => Type::iterable(Type::object($class), Type::int()),
                'total' => Type::int(),
            ])
        );

        return new StreamedResponse($json, headers: ['Content-Type' => 'application/json']);
    }
}
