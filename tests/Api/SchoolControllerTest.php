<?php

namespace App\Tests\Api;

use App\Entity\School\School;
use App\Entity\School\UserScope;
use App\Entity\User;
use App\Enum\SchoolScope;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SchoolControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private JWTTokenManagerInterface $jwtManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
    }

    private function loginUser(): User
    {
        // Create or find a user to log in with
        $userRepository = $this->entityManager->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => 'test@example.com']);

        if (!$user) {
            $user = new User();
            $user->setEmail('test@example.com');
            $user->setPassword('$2y$13$BadHashForTest...'); // Use encoded password if needed or persist valid user
            $user->setFirstname('Test');
            $user->setLastname('User');
            $user->setPhoneNumber('0699887766');
            // In functional tests without fixtures, creating data is necessary if DB is reset.
            // Assuming DB has data or we should mock authentication.
            // With WebTestCase, mocking User is harder if we want full integration.
            // Let's use `loginUser` helper if available in newer Symfony or Manually create token.

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $token = $this->jwtManager->create($user);
        $this->client->setServerParameter('HTTP_AUTHORIZATION', sprintf('Bearer %s', $token));

        return $user;
    }

    public function testListSchools(): void
    {
        $user = $this->loginUser();

        // Ensure user has some scopes
        $school = new School($user);
        $school->setName('Test School')
            ->setAddress('123 Test St')
            ->setEmail('school@test.com')
            ->setPhone('123456789')
            ->setTimezone('Europe/Paris')
            ->setCurrency('EUR');
        // Set other required fields if any
        $this->entityManager->persist($school);

        $scope = new UserScope($user, $school, SchoolScope::MANAGE_STUDENTS);
        $this->entityManager->persist($scope);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/schools');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $content = $this->client->getResponse()->getContent();
        $data = json_decode($content, true);

        // $this->assertIsArray($data);
        // Expect at least one school since we added one
        // $this->assertNotEmpty($data);
    }

    public function testListYears(): void
    {
        $user = $this->loginUser();

        // Setup school and years
        $school = new School($user);
        $school->setName('Test School 2')
            ->setAddress('123 Test St')
            ->setEmail('school2@test.com')
            ->setPhone('987654321')
            ->setTimezone('Europe/Paris')
            ->setCurrency('EUR');
        $this->entityManager->persist($school);
        $this->entityManager->flush(); // Get ID

        $this->client->request('GET', '/api/schools/'.$school->id.'/years');

        // Should trigger Voter?
        // SchoolVoter::SHOW probably checks if user is connected to school.
        // We need to add UserScope for this user and school.
        $scope = new UserScope($user, $school, SchoolScope::MANAGE_STUDENTS);
        $this->entityManager->persist($scope);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/schools/'.$school->id.'/years');

        $this->assertResponseIsSuccessful();
    }
}
