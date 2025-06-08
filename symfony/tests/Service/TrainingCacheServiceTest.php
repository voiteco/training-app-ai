<?php

namespace App\Tests\Service;

use App\Entity\Training;
use App\Repository\TrainingRepository;
use App\Service\TrainingCacheService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;

class TrainingCacheServiceTest extends TestCase
{
    private TrainingRepository $trainingRepository;
    private CacheInterface $cache;
    private TrainingCacheService $trainingCacheService;
    
    protected function setUp(): void
    {
        $this->trainingRepository = $this->createMock(TrainingRepository::class);
        $this->cache = new ArrayAdapter();
        $this->trainingCacheService = new TrainingCacheService($this->cache, $this->trainingRepository);
    }
    
    public function testGetUpcomingTrainingsFromCache(): void
    {
        // Mock training data
        $mockTrainings = [
            $this->createMockTraining(1, 'Training 1'),
            $this->createMockTraining(2, 'Training 2'),
        ];
        
        // Mock serialized training data
        $serializedTrainings = [
            ['id' => 1, 'title' => 'Training 1'],
            ['id' => 2, 'title' => 'Training 2'],
        ];
        
        // Set up repository mock
        $this->trainingRepository->expects($this->once())
            ->method('findUpcomingTrainings')
            ->willReturn($mockTrainings);
            
        $this->trainingRepository->expects($this->exactly(2))
            ->method('serializeTraining')
            ->willReturnOnConsecutiveCalls(
                $serializedTrainings[0],
                $serializedTrainings[1]
            );
        
        // First call should query the database
        $result1 = $this->trainingCacheService->getUpcomingTrainings();
        $this->assertEquals($serializedTrainings, $result1);
        
        // Second call should use the cache (repository methods won't be called again)
        $result2 = $this->trainingCacheService->getUpcomingTrainings();
        $this->assertEquals($serializedTrainings, $result2);
    }
    
    public function testRefreshTrainingCache(): void
    {
        // Mock training data
        $mockTrainings = [
            $this->createMockTraining(1, 'Training 1'),
            $this->createMockTraining(2, 'Training 2'),
        ];
        
        // Mock serialized training data
        $serializedTrainings = [
            ['id' => 1, 'title' => 'Training 1'],
            ['id' => 2, 'title' => 'Training 2'],
        ];
        
        // Set up repository mock for serialization
        $this->trainingRepository->expects($this->exactly(2))
            ->method('serializeTraining')
            ->willReturnOnConsecutiveCalls(
                $serializedTrainings[0],
                $serializedTrainings[1]
            );
        
        // Refresh the cache with provided trainings
        $result = $this->trainingCacheService->refreshTrainingCache($mockTrainings);
        $this->assertEquals($serializedTrainings, $result);
        
        // Verify the cache was updated
        $this->assertTrue($this->trainingCacheService->isTrainingListCached());
        
        // Get from cache should return the same data without calling the repository again
        $cachedResult = $this->trainingCacheService->getUpcomingTrainings();
        $this->assertEquals($serializedTrainings, $cachedResult);
    }
    
    private function createMockTraining(int $id, string $title): Training
    {
        $training = $this->createMock(Training::class);
        $training->method('getId')->willReturn($id);
        $training->method('getTitle')->willReturn($title);
        return $training;
    }
}
