<?php

namespace App\DataFixtures;

use App\Entity\School\School;
use App\Entity\User;
use App\Service\RoleManager\SchoolRoleManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

class SchoolFixtures extends Fixture implements DependentFixtureInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(
        private readonly SchoolRoleManager $schoolRoleManager,
        HttpClientInterface $httpClient,
        TagAwareCacheInterface $cache
    ) {
        $this->httpClient = new CachingHttpClient($httpClient, $cache);
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $director = $this->getReference(UserFixtures::DIRECTOR, User::class);

        $logo = tmpfile();

        $response = $this->httpClient->request('GET', 'https://blog-fr.orson.io/wp-content/uploads/2020/07/logoapple.png');

        foreach ($this->httpClient->stream($response) as $chunk) {
            fwrite($logo, $chunk->getContent());
        }

        $school = new School();
        $school
            ->setName('Pierre et Marie Currie')
            ->setLogo(new ReplacingFile(stream_get_meta_data($logo)['uri']));

        $manager->persist($school);
        $manager->flush();
        fclose($logo);
        $this->schoolRoleManager->setDirector($school, $director);
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
