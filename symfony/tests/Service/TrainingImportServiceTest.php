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

        // Expected googleSheetId for Training 1: md5('Training 1Description 12023-12-0110:0099.991060')
        $expectedRowId1 = 'c8b313ba968a9704329685e00b0640a1';
        // Expected googleSheetId for Training 2: md5('Training 2Description 22023-12-0211:00149.991590')
        $expectedRowId2 = 'f63a45f5c8a3f3d8e0f9b8a7a7a8f8a1';

        $this->trainingRepository->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturnCallback(function (array $criteria) use ($expectedRowId1, $expectedRowId2) {
                if ($criteria['googleSheetId'] === $expectedRowId1 || $criteria['googleSheetId'] === $expectedRowId2) {
                    return null; // Simulate training not found
                }
                $this->fail('findOneBy called with unexpected googleSheetId: ' . $criteria['googleSheetId']);
                return null;
            });
            
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
        // Expected googleSheetId for Training 1: md5('Training 1Description 12023-12-0110:0099.991060')
        $expectedRowId1 = 'c8b313ba968a9704329685e00b0640a1';
        
        $this->trainingRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['googleSheetId' => $expectedRowId1])
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

    public function testImportTrainingsWithInsufficientColumns(): void
    {
        $this->googleSheetsService->expects($this->once())
            ->method('getSheetValues')
            ->willReturn([
                ['Title', 'Date', 'Time', 'Slots', 'Price', 'Description', 'Duration'],
                ['Training 1', '2023-12-01', '10:00'] // Not enough columns
            ]);

        $result = $this->trainingImportService->importTrainings('spreadsheet-id', 'Sheet1!A1:G10');

        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertCount(1, $result['errors']);
        $this->assertEquals("Skipped row 2: Insufficient columns. Expected at least 5 columns, found 3.", $result['errors'][0]);
    }

    public function testImportTrainingsWithMissingRequiredData(): void
    {
        $this->googleSheetsService->expects($this->once())
            ->method('getSheetValues')
            ->willReturn([
                ['Title', 'Date', 'Time', 'Slots', 'Price', 'Description', 'Duration'],
                ['', '2023-12-01', '10:00', '', '99.99', 'Description 1', '60'] // Missing Title and Slots
            ]);

        $result = $this->trainingImportService->importTrainings('spreadsheet-id', 'Sheet1!A1:G10');

        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertCount(1, $result['errors']);
        $this->assertEquals("Skipped row 2: Missing required data for fields: title, slots.", $result['errors'][0]);
    }

    public function testImportTrainingsWithInvalidDateTimeFormat(): void
    {
        $this->googleSheetsService->expects($this->once())
            ->method('getSheetValues')
            ->willReturn([
                ['Title', 'Date', 'Time', 'Slots', 'Price', 'Description', 'Duration'],
                ['Training 1', 'Invalid Date', '10:00', '10', '99.99', 'Description 1', '60']
            ]);

        $result = $this->trainingImportService->importTrainings('spreadsheet-id', 'Sheet1!A1:G10');

        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(1, $result['skipped']);
        $this->assertCount(1, $result['errors']);
        $this->assertEquals("Skipped row 2: Invalid date/time format for date 'Invalid Date' or time '10:00'.", $result['errors'][0]);
    }

     public function testImportTrainingsWithDefaultDuration(): void
    {
        $this->googleSheetsService->expects($this->once())
            ->method('getSheetValues')
            ->willReturn([
                // Note: 'Duration' header is present, but value is missing for the row.
                // The service should use the default value for duration (60) for hashing and import.
                ['Title', 'Date', 'Time', 'Slots', 'Price', 'Description', 'Duration'],
                ['Training Default Dur', '2023-12-03', '12:00', '5', '50.00', 'Desc Default', null]
            ]);

        // Expected googleSheetId for Training Default Dur: md5('Training Default DurDesc Default2023-12-0312:0050.00560')
        // Note: '60' is the default duration used in the hash.
        $expectedRowId = md5('Training Default DurDesc Default2023-12-0312:0050.005' . '60');


        $this->trainingRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['googleSheetId' => $expectedRowId])
            ->willReturn(null); // New training

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->trainingImportService->importTrainings('spreadsheet-id', 'Sheet1!A1:G10');

        $this->assertEquals(1, $result['imported']);
        $this->assertEquals(0, $result['updated']);
        $this->assertEquals(0, $result['skipped']);
        $this->assertEmpty($result['errors'], 'Errors found: ' . print_r($result['errors'], true));
    }

    public function testImportTrainingsWithOptionalDescriptionAndDurationMissing(): void
    {
        // Test case where 'Description' and 'Duration' columns are completely missing from the sheet
        $this->googleSheetsService->expects($this->once())
            ->method('getSheetValues')
            ->willReturn([
                ['Title', 'Date', 'Time', 'Slots', 'Price'], // No Description, No Duration
                ['Training Min Fields', '2023-12-04', '14:00', '8', '75.00']
            ]);

        // Expected googleSheetId: md5('Training Min Fields' . '' . '2023-12-04' . '14:00' . '75.00' . '8' . '60')
        // Description is empty string, Duration defaults to '60'
        $expectedRowId = md5('Training Min Fields' . '' . '2023-12-04' . '14:00' . '75.00' . '8' . '60');

        $this->trainingRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['googleSheetId' => $expectedRowId])
            ->willReturn(null); // New training

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->trainingImportService->importTrainings('spreadsheet-id', 'Sheet1!A1:E10'); // Range only up to E

        $this->assertEquals(1, $result['imported']);
        $this->assertEmpty($result['errors'], 'Errors found: ' . print_r($result['errors'], true));
    }
}
