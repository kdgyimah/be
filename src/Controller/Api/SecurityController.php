<?php

namespace App\Controller\Api;

use App\Entity\RefreshToken;
use App\Service\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Cookie\JWTCookieProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[OA\Tag('auth')]
#[OA\Response(
    response: Response::HTTP_INTERNAL_SERVER_ERROR,
    description: 'Internal error response',
    content: new OA\JsonContent(
        ref: '#/components/schemas/ErrorResponse',
    )
)]
#[Route('/api', name: 'api_', methods: Request::METHOD_POST)]
class SecurityController
{
    #[Security]
    #[OA\Post(
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                    ),
                    new OA\Property(
                        property: 'remember_me',
                        type: 'boolean',
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: Response::HTTP_NO_CONTENT,
                description: 'User is connected',
                headers: [
                    new OA\Header(
                        header: 'Set-Cookie',
                        required: true,
                        schema: new OA\Schema(
                            type: 'string',
                            example: 'yesman_jwt_hp=token_hp; expires=Tue, 16 Dec 2025 21:04:57 GMT; Max-Age=86400; path=/; domain=domain.com; secure; samesite=strictyesman_jwt_s=token_s; expires=Tue, 16 Dec 2025 21:04:57 GMT; Max-Age=86400; path=/; domain=domain.com; secure; httponly; samesite=strictrefresh_token=deleted; expires=Sun, 15 Dec 2024 21:04:56 GMT; Max-Age=0; path=/; secure; httponly; samesite=lax'
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: Response::HTTP_UNAUTHORIZED,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    example: ['code' => Response::HTTP_UNAUTHORIZED, 'message' => 'Invalid credentials.'],
                )
            ),
        ]
    )]
    #[Route('/login', name: 'login')]
    public function login(): JsonResponse
    {
        throw new \RuntimeException('login: not supposed to be reached');
    }

    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Logout success'
    )]
    #[Route('/logout', name: 'logout')]
    public function logout(
        EventDispatcherInterface $eventDispatcher,
        Request $request,
        TokenStorageInterface $tokenStorage,
    ): Response {
        $event = new LogoutEvent($request, $tokenStorage->getToken());
        $eventDispatcher->dispatch($event);

        $response = $event->getResponse();

        if (null === $response) {
            $response = new Response(status: Response::HTTP_NO_CONTENT);
        }

        if ($request->cookies->has('yesman_jwt_hp')) {
            $response->headers->clearCookie('yesman_jwt_hp');
        }

        if ($request->cookies->has('yesman_jwt_s')) {
            $response->headers->clearCookie('yesman_jwt_s');
        }

        return $response;
    }

    #[Security(name: 'cookieRefresh')]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'User is connected',
        headers: [
            new OA\Header(
                header: 'Set-Cookie',
                required: true,
                schema: new OA\Schema(
                    type: 'string',
                    example: 'yesman_jwt_hp=token_hp; expires=Tue, 16 Dec 2025 21:04:57 GMT; Max-Age=86400; path=/; domain=domain.com; secure; samesite=strictyesman_jwt_s=token_s; expires=Tue, 16 Dec 2025 21:04:57 GMT; Max-Age=86400; path=/; domain=domain.com; secure; httponly; samesite=strictrefresh_token=deleted; expires=Sun, 15 Dec 2024 21:04:56 GMT; Max-Age=0; path=/; secure; httponly; samesite=lax'
                )
            ),
        ]
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'refresh token is missing',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['message' => 'Bad credentials', 'code' => Response::HTTP_BAD_REQUEST],
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'invalid refresh token',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['message' => 'Invalid credentials', 'code' => Response::HTTP_UNAUTHORIZED],
        )
    )]
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
        TokenExtractorInterface $tokenExtractor,
    ): Response {
        $refreshTokenString = $refreshTokenService->extractRefreshToken($request);
        $response = new JsonResponse();
        $userEmail = null;

        try {
            $token = $tokenExtractor->extract($request);
            if (is_string($token)) {
                $payload = $JWTManager->parse($token);
                $userEmail = $payload[$JWTManager->getUserIdClaim()] ?? null;
            }
        } catch (\Throwable) {
        }

        if (!empty($refreshTokenString)) {
            $refreshToken = $entityManager->getRepository(RefreshToken::class)->find($refreshTokenString);

            if (null === $refreshToken
                || !$refreshToken->isValid()
                || (null !== $userEmail && $userEmail !== $refreshToken->user->email)
            ) {
                if (null !== $refreshToken) {
                    $entityManager->remove($refreshToken);
                    $entityManager->flush();
                }
                $refreshTokenService->removeCookie($response);

                return $response
                    ->setData(['message' => 'Invalid credentials', 'code' => Response::HTTP_UNAUTHORIZED])
                    ->setStatusCode(Response::HTTP_UNAUTHORIZED);
            }

            $response = new Response();

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
