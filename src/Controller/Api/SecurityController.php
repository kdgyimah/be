<?php

namespace App\Controller\Api;

use App\Entity\RefreshToken;
use App\Service\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie\JWTCookieProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_', methods: 'POST')]
class SecurityController
{
    #[Route('/login', name: 'login')]
    public function login(): JsonResponse
    {
        throw new \RuntimeException('login: not supposed to be reached');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): JsonResponse
    {
        throw new \RuntimeException('logout: not supposed to be reached');
    }

    #[Route('/token/refresh', name: 'refresh')]
    public function refreshToken(
        Request $request,
        RefreshTokenService $refreshTokenService,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $JWTManager,
        #[Autowire(service: 'lexik_jwt_authentication.cookie_provider.yesman_jwt_hp')]
        JWTCookieProvider $JWTCookieProviderHp,
        #[Autowire(service: 'lexik_jwt_authentication.cookie_provider.yesman_jwt_s')]
        JWTCookieProvider $JWTCookieProviderS,
    ): JsonResponse {
        $refreshTokenString = $refreshTokenService->extractRefreshToken($request);
        $response = new JsonResponse();

        if (!empty($refreshTokenString)) {
            $refreshToken = $entityManager->getRepository(RefreshToken::class)->find($refreshTokenString);

            $cookieProviders = [$JWTCookieProviderHp, $JWTCookieProviderS];
            $token = $JWTManager->create($refreshToken->user);

            foreach ($cookieProviders as $cookieProvider) {
                $response->headers->setCookie($cookieProvider->createCookie($token));
            }

            return $response
                ->setStatusCode(Response::HTTP_NO_CONTENT);
        }

        return $response
            ->setData(['message' => 'Bad credentials', 'code' => Response::HTTP_BAD_REQUEST])
            ->setStatusCode(Response::HTTP_BAD_REQUEST);
    }
}
