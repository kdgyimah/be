<?php

namespace App\Tests\Unit\Service;

use App\Service\RefreshTokenService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenServiceTest extends TestCase
{
    private RefreshTokenService $service;
    private string $paramName = 'refresh_token';
    private int $ttl = 3600;

    protected function setUp(): void
    {
        $this->service = new RefreshTokenService($this->paramName, $this->ttl);
    }

    public function testExtractRefreshToken(): void
    {
        $request = new Request();
        $request->cookies = new InputBag([$this->paramName => 'some-token']);

        $token = $this->service->extractRefreshToken($request);

        $this->assertEquals('some-token', $token);
    }

    public function testRemoveCookie(): void
    {
        $response = new Response();

        $this->service->removeCookie($response);

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $cookie = $cookies[0];
        $this->assertEquals($this->paramName, $cookie->getName());
        $this->assertTrue($cookie->isCleared());
    }

    public function testSetCookie(): void
    {
        $response = new Response();
        $tokenValue = 'new-token';

        $this->service->setCookie($response, $tokenValue);

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $cookie = $cookies[0];

        $this->assertEquals($this->paramName, $cookie->getName());
        $this->assertEquals($tokenValue, $cookie->getValue());
        $this->assertEquals('/', $cookie->getPath());
        // $this->assertEquals(time() + $this->ttl, $cookie->getExpiresTime()); // Hard to test exact time
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertTrue($cookie->isSecure());
        $this->assertEquals(Cookie::SAMESITE_STRICT, $cookie->getSameSite());
    }
}
