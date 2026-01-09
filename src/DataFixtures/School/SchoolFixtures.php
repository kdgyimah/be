<?php

namespace App\DataFixtures\School;

use App\DataFixtures\UserFixtures;
use App\Entity\School\School;
use App\Entity\School\UserScope;
use App\Entity\User;
use App\Enum\SchoolScope;
use App\Listener\TimestampEntityListener;
use App\Service\RoleManager\SchoolRoleManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

class SchoolFixtures extends Fixture implements DependentFixtureInterface
{
    public const string PM = 'PM';

    private HttpClientInterface $httpClient;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TimestampEntityListener $timestampEntityListener,
        HttpClientInterface $httpClient,
        TagAwareCacheInterface $cache,
    ) {
        $this->httpClient = new CachingHttpClient($httpClient, $cache);
    }

    public function load(ObjectManager $manager): void
    {
        $director = $this->getReference(UserFixtures::USER, User::class);

        $logo = tmpfile();

        $response = $this->httpClient->request(
            'GET',
            'https://blog-fr.orson.io/wp-content/uploads/2020/07/logoapple.png'
        );

        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($logo, $chunk->getContent());
        }

        $school = new School($director)
            ->setName('Pierre et Marie Currie')
            ->setAddress('11 Boulevard Ayrault 49100 Angers')
            ->setEmail('ecole@example.com')
            ->setPhone('+886765675675')
            ->setTimezone('Europe/Paris')
            ->setCurrency('CFA')
        ;

        foreach (SchoolScope::cases() as $schoolScope) {
            $scope = new UserScope($director, $school, $schoolScope);
            $manager->persist($scope);
        }

        $this->setReference(SchoolFixtures::PM, $school);

        $rp = new \ReflectionProperty($school, 'id');
        $rp->setValue($school, new Uuid('019b1eb4-3ddb-7df2-ba27-c106e0728e63'));
        $this->entityManager->getUnitOfWork()->scheduleForInsert($school);
        $this->timestampEntityListener->prePersist($school);
        $manager->flush();

        $school->setLogo(new ReplacingFile(stream_get_meta_data($logo)['uri']));
        $manager->flush();
        fclose($logo);
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
