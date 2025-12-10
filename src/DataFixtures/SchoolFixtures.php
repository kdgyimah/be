<?php

namespace App\DataFixtures;

use App\Entity\School\School;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SchoolFixtures extends Fixture
{
    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $school = new School();
        $school
            ->setName('Pierre et Marie Currie');
    }
}
