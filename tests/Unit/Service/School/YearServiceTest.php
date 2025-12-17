<?php

namespace App\Tests\Unit\Service\School;

use App\Entity\School\School;
use App\Entity\School\Year;
use App\Repository\School\YearRepository;
use App\Service\KeyService;
use App\Service\School\YearService;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class YearServiceTest extends TestCase
{
    private MockObject|YearRepository $yearRepository;
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|CacheInterface $cache;
    private MockObject|KeyService $keyService;
    private MockObject|ObjectMapperInterface $objectMapper;
    private YearService $service;

    protected function setUp(): void
    {
        $this->yearRepository = $this->createMock(YearRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->keyService = $this->createMock(KeyService::class);
        $this->objectMapper = $this->createMock(ObjectMapperInterface::class);

        $this->service = new YearService(
            $this->yearRepository,
            $this->entityManager,
            $this->cache,
            $this->keyService,
            $this->objectMapper
        );
    }

    public function testGetYearByNameFound(): void
    {
        $school = new School();
        $yearName = '2023-2024';
        $year = new Year($school);

        $this->yearRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['name' => $yearName, 'school' => $school])
            ->willReturn($year);

        $result = $this->service->getYear($school, $yearName);
        $this->assertSame($year, $result);
    }

    public function testGetYearByNameNotFound(): void
    {
        $school = new School();
        $yearName = '2099-3000';

        $this->yearRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(NotFoundHttpException::class);
        $this->service->getYear($school, $yearName);
    }

    public function testGetYearDefaultCacheHit(): void
    {
        $school = new School();
        // Setup school ID mock or reflection if needed. Assuming key service works on object.
        $this->keyService->expects($this->once())
            ->method('getYearActiveKey')
            ->with($school)
            ->willReturn('school-active-year');

        $year = new Year($school);
        $yearId = $year->id;

        $this->cache->expects($this->once())
            ->method('get')
            ->with('school-active-year', $this->anything())
            ->willReturn($yearId);

        $this->entityManager->expects($this->once())
            ->method('find')
            ->with(Year::class, $yearId)
            ->willReturn($year);

        $result = $this->service->getYear($school);
        $this->assertSame($year, $result);
    }

    public function testGetYearDefaultCacheMiss(): void
    {
        $school = new School();
        $this->keyService->expects($this->once())
            ->method('getYearActiveKey')
            ->willReturn('school-active-year');

        // Mock school ID access via reflection if YearService uses $school->id in cache callback
        $ref = new \ReflectionProperty(School::class, 'id');
        $ref->setValue($school, Uuid::v4());

        $year = new Year($school);
        // Set IDs via reflection or assume they are set if using Uuid::v4 in previous step fixes
        // But for consistency:
        $refYear = new \ReflectionProperty(Year::class, 'id');
        $refYear->setValue($year, Uuid::v4());

        $item = $this->createMock(ItemInterface::class);

        $this->cache->expects($this->once())
            ->method('get')
            ->with('school-active-year', $this->callback(function ($callback) {
                // Execute the callback to simulate cache miss behavior
                // The callback signature in Service uses `use ($school, $yearName)`
                // Service passes a callback function($item).
                // We simulate the service calling valid logic?
                // Wait, ->with(..., callback checking the closure passed to get)
                // The second arg to cache->get IS the closure.
                // So $callback IS the closure.
                // We just return true to match it.
                return is_callable($callback);
            }))
            ->willReturnCallback(function ($key, $callback) use ($item) {
                return $callback($item);
            });

        $this->yearRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['school' => $school->id], ['active' => Order::Descending->value, 'createdAt' => Order::Descending->value])
            ->willReturn($year);

        // After cache returns ID, it calls entityManager->find
        $this->entityManager->expects($this->once())
            ->method('find')
            ->with(Year::class, $year->id)
            ->willReturn($year);

        $result = $this->service->getYear($school);
        $this->assertSame($year, $result);
    }
}
