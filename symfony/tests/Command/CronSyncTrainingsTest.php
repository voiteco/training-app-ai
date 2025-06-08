<?php

namespace App\Tests\Command;

use App\Command\SyncTrainingsCommand;
use App\Service\TrainingImportService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

class CronSyncTrainingsTest extends TestCase
{
    private $trainingImportService;
    private $parameterBag;
    private $logger;
    private $commandTester;

    protected function setUp(): void
    {
        $this->trainingImportService = $this->createMock(TrainingImportService::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // Configure parameter bag mock
        $this->parameterBag->method('get')
            ->willReturnMap([
                ['google_sheets.spreadsheet_id', 'test-spreadsheet-id'],
                ['google_sheets.trainings_range', 'Trainings!A1:H100'],
            ]);

        // Create command
        $command = new SyncTrainingsCommand(
            $this->trainingImportService,
            $this->parameterBag,
            $this->logger
        );

        $application = new Application();
        $application->add($command);
        $command = $application->find('app:sync-trainings');
        $this->commandTester = new CommandTester($command);
    }

    public function testCommandIsRegistered(): void
    {
        // This test verifies that the command is properly registered
        $this->assertNotNull($this->commandTester);
    }

    public function testCommandCanBeExecutedMultipleTimes(): void
    {
        // Configure mock to return successful import stats
        $this->trainingImportService->method('importTrainings')
            ->willReturn([
                'imported' => 1,
                'updated' => 1,
                'skipped' => 1,
                'errors' => []
            ]);

        // Execute the command multiple times to simulate cron behavior
        for ($i = 0; $i < 3; $i++) {
            $this->commandTester->execute([]);
            $output = $this->commandTester->getDisplay();
            $this->assertStringContainsString('Synchronizing Trainings from Google Sheets', $output);
        }
    }

    public function testCommandOutputIsLoggable(): void
    {
        // Configure mock to return successful import stats
        $this->trainingImportService->method('importTrainings')
            ->willReturn([
                'imported' => 2,
                'updated' => 3,
                'skipped' => 1,
                'errors' => []
            ]);

        // Execute the command
        $this->commandTester->execute([]);
        
        // Get the output
        $output = $this->commandTester->getDisplay();
        
        // Verify the output contains information that would be logged
        $this->assertStringContainsString('[INFO] Found 6 rows in Google Sheet', $output);
        $this->assertStringContainsString('[INFO] Inserted: 2, Updated: 3, Skipped: 1', $output);
    }
}
