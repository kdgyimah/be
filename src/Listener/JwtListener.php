<?php

namespace App\Listener;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Service\RefreshTokenService;
use DateMalformedStringException;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Uuid;

#[AsEventListener(event: Events::JWT_CREATED, method: 'onJWTCreated')]
#[AsEventListener(event: Events::AUTHENTICATION_SUCCESS, method: 'onAuthenticationSuccess')]
readonly class JwtListener
{
    public function __construct(
        private RequestStack           $requestStack,
        #[Autowire(param: 'refresh_token_ttl')]
        private int                    $refreshTokenTTL,
        private EntityManagerInterface $entityManager,
        private RefreshTokenService    $refreshTokenService,
    )
    {
    }

    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $data = $event->getData();
        if (isset($data['roles'])) {
            unset($data['roles']);
        }
        $event->setData($data);
    }

    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $isRememberMe = $request->getPayload()->getBoolean('remember_me');

        $refreshTokenString = $this->refreshTokenService->extractRefreshToken($request);
        $refreshToken = null;

        if ($refreshTokenString !== null) {
            $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->find($refreshTokenString);

            if ($refreshToken?->isValid() === false || $isRememberMe === false) {
                $this->entityManager->remove($refreshToken);
                $this->entityManager->flush();
                $refreshToken = null;
            }
        }

        if ($isRememberMe === false) {
            $this->refreshTokenService->removeCookie($event->getResponse());
            return;
        }

        if ($refreshToken === null) {
            $refreshToken = new RefreshToken($this->refreshTokenTTL, $user);
            $this->entityManager->persist($refreshToken);
            $this->entityManager->flush();
        }

        $this->refreshTokenService->setCookie($event->getResponse(), $refreshToken->id->hash());
    }
}
