<?php

namespace App\Controller\Api;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Service\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api', name: 'api_', methods: 'POST')]
class SecurityController
{
    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        throw new \RuntimeException('login: not supposed to be reached');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): Response
    {
        throw new \RuntimeException('logout: not supposed to be reached');
    }

    #[Route('/token/refresh', name: 'refresh')]
    public function refreshToken(
        Request $request,
        RefreshTokenService $refreshTokenService,
        EntityManagerInterface $entityManager,
        #[CurrentUser] User $currentUser,
        JWTTokenManagerInterface $JWTManager
    ): Response {
        $refreshTokenString = $refreshTokenService->extractRefreshToken($request);
        $response = new JsonResponse();

        if (empty($refreshTokenString)) {
            return $response
                ->setData(['message' => 'Bad credentials', 'code' => Response::HTTP_BAD_REQUEST])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        $refreshToken = $entityManager->getRepository(RefreshToken::class)->find($refreshTokenString);

        if ($refreshToken?->user !== $currentUser) {

            $refreshTokenService->removeCookie($response);
            return $response
                ->setData(['message' => 'Bad credentials', 'code' => Response::HTTP_BAD_REQUEST])
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        }

        return $response
            ->setData(['token' => $JWTManager->create($currentUser)]);
    }
}
