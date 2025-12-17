<?php

namespace App\Tests\Unit\Service;

use App\Entity\School\School;
use App\Service\KeyService;
use PHPUnit\Framework\TestCase;

class KeyServiceTest extends TestCase
{
    public function testGetYearActiveKey(): void
    {
        $school = new School();
        // School initializes ID in constructor with Uuid::v7()
        // We can just assert that the key contains the ID.

        $service = new KeyService();

        $key = $service->getYearActiveKey($school);
        $this->assertEquals("$school->id-active-year", $key);
    }
}
