<?php

namespace App\Tests\Controller;

use App\Entity\Training;
use App\Repository\TrainingRepository;
use App\Service\TrainingCacheService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerTest extends WebTestCase
{
    public function testGetTrainingsFromCache(): void
    {
        $client = static::createClient();
        
        // Mock the TrainingCacheService
        $trainingCacheService = $this->createMock(TrainingCacheService::class);
        $trainingCacheService->expects($this->once())
            ->method('getUpcomingTrainings')
            ->willReturn([
                [
                    'id' => 1,
                    'title' => 'Test Training',
                    'description' => 'Test Description',
                    'date' => '2023-12-31',
                    'time' => '10:00',
                    'duration' => 60,
                    'slots' => 10,
                    'slotsAvailable' => 5,
                    'price' => 99.99,
                ]
            ]);
        
        // Replace the service in the container
        self::getContainer()->set(TrainingCacheService::class, $trainingCacheService);
        
        // Make the request
        $client->request('GET', '/api/trainings');
        
        // Check response
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('cache', $responseData['source']);
        $this->assertEquals(1, $responseData['count']);
        $this->assertCount(1, $responseData['trainings']);
        $this->assertEquals('Test Training', $responseData['trainings'][0]['title']);
    }
    
    public function testGetTrainingsFallbackToDatabase(): void
    {
        $client = static::createClient();
        
        // Mock the TrainingCacheService to throw an exception
        $trainingCacheService = $this->createMock(TrainingCacheService::class);
        $trainingCacheService->expects($this->once())
            ->method('getUpcomingTrainings')
            ->willThrowException(new \Exception('Cache error'));
        
        // Mock the TrainingRepository
        $mockTraining = $this->createMock(Training::class);
        
        $trainingRepository = $this->createMock(TrainingRepository::class);
        $trainingRepository->expects($this->once())
            ->method('findUpcomingTrainings')
            ->willReturn([$mockTraining]);
        $trainingRepository->expects($this->once())
            ->method('serializeTraining')
            ->willReturn([
                'id' => 2,
                'title' => 'Database Training',
                'description' => 'From Database',
                'date' => '2023-12-31',
                'time' => '14:00',
                'duration' => 90,
                'slots' => 20,
                'slotsAvailable' => 10,
                'price' => 149.99,
            ]);
        
        // Replace the services in the container
        self::getContainer()->set(TrainingCacheService::class, $trainingCacheService);
        self::getContainer()->set(TrainingRepository::class, $trainingRepository);
        
        // Make the request
        $client->request('GET', '/api/trainings');
        
        // Check response
        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('database', $responseData['source']);
        $this->assertEquals(1, $responseData['count']);
        $this->assertCount(1, $responseData['trainings']);
        $this->assertEquals('Database Training', $responseData['trainings'][0]['title']);
    }
}
