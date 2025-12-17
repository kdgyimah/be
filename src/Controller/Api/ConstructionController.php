<?php

namespace App\Controller\Api;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
