<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class RefreshTokenService
{
    public function __construct(
        #[Autowire(param: 'refresh_token_parameter_name')]
        private string $refreshTokenParameterName,
        #[Autowire(param: 'refresh_token_ttl')]
        private int $refreshTokenTTL,
    ) {
    }

    public function extractRefreshToken(Request $request): string
    {
        return $request->cookies->getString($this->refreshTokenParameterName);
    }

    public function removeCookie(Response $response): void
    {
        $response->headers->clearCookie(
            $this->refreshTokenParameterName,
            '/',
            null,
            true,
            true,
            Cookie::SAMESITE_LAX
        );
    }

    public function setCookie(Response $response, string $refreshTokenString): void
    {
        $response->headers->setCookie(
            new Cookie(
                $this->refreshTokenParameterName,
                $refreshTokenString,
                time() + $this->refreshTokenTTL,
                '/',
                null,
                true,
                true,
                false,
                Cookie::SAMESITE_STRICT,
                false
            )
        );
    }
}
