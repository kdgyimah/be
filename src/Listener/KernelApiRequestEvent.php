<?php

namespace App\Listener;

use App\Entity\RefreshToken;
use App\Service\RefreshTokenService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
#[AsEventListener(event: ExceptionEvent::class, method: 'onException')]
readonly class KernelApiRequestEvent
{
    public function __construct(
        private RefreshTokenService $refreshTokenService,
        private EntityManagerInterface $entityManager,
        private Security $security,
        private LoggerInterface $logger,
    ) {
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();

        if ('api' !== $this->security->getFirewallConfig($request)?->getName()) {
            return;
        }

        $tokenString = $this->refreshTokenService->extractRefreshToken($request);

        if (empty($tokenString)) {
            return;
        }

        /** @var ?RefreshToken $refreshToken */
        $refreshToken = $this->entityManager->getRepository(RefreshToken::class)->find($tokenString);

        if (null !== $refreshToken) {
            $this->entityManager->remove($refreshToken);
            $this->entityManager->flush();
        }

        $response = $event->getResponse();

        if (null === $response) {
            $response = new JsonResponse(status: Response::HTTP_NO_CONTENT);
        }

        $this->refreshTokenService->removeCookie($response);
        $event->setResponse($response);
    }

    public function onException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!in_array($this->security->getFirewallConfig($request)?->getName(), ['api', 'api_login'])
            || 'app.swagger_ui' === $request->attributes->get('_route')
        ) {
            return;
        }

        $exception = $event->getThrowable();

        $data = ['code' => $exception->getCode()];
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        $headers = [];

        if ($exception instanceof HttpException) {
            $data['message'] = $exception->getMessage();
            $statusCode = $exception->getStatusCode();
            $headers = $exception->getHeaders();
        } else {
            $this->logger->error($exception->getMessage(), ['exception' => $exception->getTrace()]);
            $data['message'] = 'internal server error';
        }

        $event->setResponse(new JsonResponse($data, $statusCode, $headers));
    }
}
