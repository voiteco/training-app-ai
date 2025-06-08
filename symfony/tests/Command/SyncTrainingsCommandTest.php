<?php

namespace App\Tests\Command;

use App\Command\SyncTrainingsCommand;
use App\Service\TrainingImportService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SyncTrainingsCommandTest extends TestCase
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

    public function testExecuteSuccessful(): void
    {
        // Configure mock to return successful import stats
        $this->trainingImportService->method('importTrainings')
            ->willReturn([
                'imported' => 3,
                'updated' => 2,
                'skipped' => 1,
                'errors' => []
            ]);

        // Configure logger expectations
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                [$this->stringContains('Found 6 rows in Google Sheet')],
                [$this->stringContains('Inserted: 3, Updated: 2, Skipped: 1')]
            );

        // Execute the command
        $this->commandTester->execute([]);

        // Verify the command output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Synchronizing Trainings from Google Sheets', $output);
        $this->assertStringContainsString('[INFO] Found 6 rows in Google Sheet', $output);
        $this->assertStringContainsString('[INFO] Inserted: 3, Updated: 2, Skipped: 1', $output);
        $this->assertStringContainsString('Training synchronization completed successfully', $output);
    }

    public function testExecuteWithErrors(): void
    {
        // Configure mock to return import stats with errors
        $this->trainingImportService->method('importTrainings')
            ->willReturn([
                'imported' => 1,
                'updated' => 1,
                'skipped' => 2,
                'errors' => ['Row 3 has invalid date/time format', 'Row 4 is missing required data']
            ]);

        // Configure logger expectations
        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->withConsecutive(
                [$this->stringContains('Found 4 rows in Google Sheet')],
                [$this->stringContains('Inserted: 1, Updated: 1, Skipped: 2')]
            );

        $this->logger->expects($this->exactly(2))
            ->method('error')
            ->withConsecutive(
                [$this->stringContains('Row 3 has invalid date/time format')],
                [$this->stringContains('Row 4 is missing required data')]
            );

        // Execute the command
        $this->commandTester->execute([]);

        // Verify the command output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('[INFO] Found 4 rows in Google Sheet', $output);
        $this->assertStringContainsString('[INFO] Inserted: 1, Updated: 1, Skipped: 2', $output);
        $this->assertStringContainsString('[ERROR] Row 3 has invalid date/time format', $output);
        $this->assertStringContainsString('[ERROR] Row 4 is missing required data', $output);
    }

    public function testExecuteWithException(): void
    {
        // Configure mock to throw an exception
        $this->trainingImportService->method('importTrainings')
            ->willThrowException(new \Exception('API connection failed'));

        // Configure logger expectations
        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Error: API connection failed'));

        // Execute the command
        $this->commandTester->execute([]);

        // Verify the command output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Error: API connection failed', $output);
    }
}
