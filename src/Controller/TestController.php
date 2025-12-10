<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class TestController
{
    #[Route('/api/test', format: 'json')]
    public function test(): JsonResponse
    {
        return new JsonResponse(['te' => 'rtr']);
    }
}
