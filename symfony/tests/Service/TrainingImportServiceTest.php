<?php

namespace App\Tests\Service;

use App\Entity\Training;
use App\Repository\TrainingRepository;
use App\Service\GoogleSheetsService;
use App\Service\TrainingImportService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TrainingImportServiceTest extends TestCase
{
    private $googleSheetsService;
    private $trainingRepository;
    private $entityManager;
    private $trainingImportService;
    
    protected function setUp(): void
    {
        $this->googleSheetsService = $this->createMock(GoogleSheetsService::class);
        $this->trainingRepository = $this->createMock(TrainingRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->trainingImportService = new TrainingImportService(
            $this->googleSheetsService,
            $this->trainingRepository,
            $this->entityManager
        );
    }
    
    public function testImportTrainingsWithEmptyData(): void
    {
        $this->googleSheetsService->expects($this->once())
            ->method('getSheetValues')
            ->willReturn([]);
            
        $result = $this->trainingImportService->importTrainings('spreadsheet-id', 'Sheet1!A1:F10');
        
        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertNotEmpty($result['errors']);
    }
    
    public function testImportTrainingsWithMissingHeaders(): void
    {
        $this->googleSheetsService->expects($this->once())
            ->method('getSheetValues')
            ->willReturn([
                ['Title', 'Date'] // Missing required headers
            ]);
            
        $result = $this->trainingImportService->importTrainings('spreadsheet-id', 'Sheet1!A1:F10');
        
        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertNotEmpty($result['errors']);
    }
    
    public function testImportTrainingsWithValidData(): void
    {
        $this->googleSheetsService->expects($this->once())
            ->method('getSheetValues')
            ->willReturn([
                ['Title', 'Date', 'Time', 'Slots', 'Price', 'Description', 'Duration'],
                ['Training 1', '2023-12-01', '10:00', '10', '99.99', 'Description 1', '60'],
                ['Training 2', '2023-12-02', '11:00', '15', '149.99', 'Description 2', '90']
            ]);
            
        $this->trainingRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);
            
        $this->entityManager->expects($this->exactly(2))
            ->method('persist');
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        $result = $this->trainingImportService->importTrainings('spreadsheet-id', 'Sheet1!A1:G10');
        
        $this->assertEquals(2, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEmpty($result['errors']);
    }
    
    public function testImportTrainingsWithExistingData(): void
    {
        $this->googleSheetsService->expects($this->once())
            ->method('getSheetValues')
            ->willReturn([
                ['Title', 'Date', 'Time', 'Slots', 'Price', 'Description', 'Duration'],
                ['Training 1', '2023-12-01', '10:00', '10', '99.99', 'Description 1', '60']
            ]);
            
        $existingTraining = new Training();
        
        $this->trainingRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn($existingTraining);
            
        $this->entityManager->expects($this->never())
            ->method('persist');
            
        $this->entityManager->expects($this->once())
            ->method('flush');
            
        $result = $this->trainingImportService->importTrainings('spreadsheet-id', 'Sheet1!A1:G10');
        
        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(1, $result['updated']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEmpty($result['errors']);
    }
}