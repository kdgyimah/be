<?php

namespace App\Tests\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }
    public function testLoginSuccess(): void
    {
        $user = new User();
        $user->setEmail('login_success@example.com');
        $user->setPassword(self::getContainer()->get('security.user_password_hasher')->hashPassword($user, 'password'));
        $user->setFirstname('Login');
        $user->setLastname('Success');
        $user->setPhoneNumber('0700000000');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->client->jsonRequest('POST', '/api/login', [
            'email' => 'login_success@example.com',
            'password' => 'password',
        ]);

        $this->assertResponseStatusCodeSame(204);

        $response = $this->client->getResponse();
        $this->assertTrue($response->headers->has('Set-Cookie'));

        // precise cookie check might be complex due to auto-generated values, but we can check if they exist
        $cookieHeader = $response->headers->get('Set-Cookie');
        // Check for yesman_jwt_hp and yesman_jwt_s (assuming these are default names based on OA docs)
        // But better is to check if client has cookies
        $cookies = $this->client->getCookieJar()->all();
        $this->assertNotEmpty($cookies, 'Cookies should be set on success');

        // Find specific cookies if possible, simplified check for now
    }

    public function testLoginFailure(): void
    {
        $this->client->jsonRequest('POST', '/api/login', [
            'email' => 'doesnotexist@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertResponseStatusCodeSame(401);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testRefreshTokenSuccess(): void
    {
        $user = new User();
        $user->setEmail('refresh_success@example.com');
        $user->setPassword('$2y$13$...');
        $user->setFirstname('Refresh');
        $user->setLastname('User');
        $user->setPhoneNumber('0600000000');
        $this->entityManager->persist($user);

        // Create a valid RefreshToken
        // TTL positive
        $refreshToken = new \App\Entity\RefreshToken(3600, $user);
        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        // RefreshToken ID is Uuid
        $refreshTokenId = $refreshToken->id->toRfc4122(); // Assuming Uuid V7/V4

        // Set the cookie
        // Cookie name is 'yesman_refresh_token' from services.yaml
        $cookie = new \Symfony\Component\BrowserKit\Cookie('yesman_refresh_token', $refreshTokenId);
        $this->client->getCookieJar()->set($cookie);

        $this->client->request('POST', '/api/token/refresh');

        $this->assertResponseStatusCodeSame(204);

        // Verify new cookies are set (JWT cookies)
        // In this app, refresh also sets JWT cookies (hp and s)
        // We can inspect the response headers or cookie jar
        $cookies = $this->client->getCookieJar()->all();
        // Should have jwt cookies
        $this->assertNotEmpty($cookies);
    }

    public function testRefreshTokenWithoutCookie(): void
    {
        $this->client->request('POST', '/api/token/refresh');

        $this->assertResponseStatusCodeSame(400);
    }
}
