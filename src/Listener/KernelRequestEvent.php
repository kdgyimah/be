<?php

namespace App\Listener;

use App\Entity\RefreshToken;
use App\Service\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
readonly class KernelRequestEvent
{
    public function __construct(
        private RefreshTokenService $refreshTokenService,
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();

        if ($this->security->getFirewallConfig($request)?->getName() !== 'api') {
            return;
        }

        $tokenString = $this->refreshTokenService->extractRefreshToken($request);

        if (empty($tokenString)) {
            return;
        }

        /** @var ?RefreshToken $refreshToken */
        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->find($tokenString);

        if ($refreshToken !== null) {
            $this->entityManager->remove($refreshToken);
            $this->entityManager->flush();
        }

        $response = $event->getResponse();

        if ($response === null) {
            $response = new JsonResponse(status: Response::HTTP_NO_CONTENT);
        }

        $this->refreshTokenService->removeCookie($response);
    }
}
