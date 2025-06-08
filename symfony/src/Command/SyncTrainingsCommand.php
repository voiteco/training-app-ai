<?php

namespace App\Command;

use App\Service\TrainingImportService;
use App\Service\TrainingCacheService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Psr\Log\LoggerInterface;

#[AsCommand(
    name: 'app:sync-trainings',
    description: 'Synchronize trainings from Google Sheets',
)]
class SyncTrainingsCommand extends Command
{
    private TrainingImportService $trainingImportService;
    private TrainingCacheService $trainingCacheService;
    private string $spreadsheetId;
    private string $range;
    private LoggerInterface $logger;

    public function __construct(
        TrainingImportService $trainingImportService,
        TrainingCacheService $trainingCacheService,
        ParameterBagInterface $parameterBag,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->trainingImportService = $trainingImportService;
        $this->trainingCacheService = $trainingCacheService;
        $this->spreadsheetId = $parameterBag->get('google_sheets.spreadsheet_id');
        $this->range = $parameterBag->get('google_sheets.trainings_range');
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Synchronizing Trainings from Google Sheets');
        $io->text("Spreadsheet ID: {$this->spreadsheetId}");
        $io->text("Range: {$this->range}");
        
        try {
            // Fetch and synchronize trainings
            $stats = $this->trainingImportService->importTrainings($this->spreadsheetId, $this->range);
            
            // Log the results
            $totalRows = $stats['imported'] + $stats['updated'] + $stats['skipped'];
            $this->logger->info("Found {$totalRows} rows in Google Sheet.");
            $this->logger->info("Inserted: {$stats['imported']}, Updated: {$stats['updated']}, Skipped: {$stats['skipped']}");
            
            // Display results in console
            $io->section('Synchronization Results');
            $io->text("[INFO] Found {$totalRows} rows in Google Sheet.");
            $io->text("[INFO] Inserted: {$stats['imported']}, Updated: {$stats['updated']}, Skipped: {$stats['skipped']}");
            
            if (!empty($stats['errors'])) {
                $io->section('Errors');
                foreach ($stats['errors'] as $error) {
                    $this->logger->error($error);
                    $io->text("[ERROR] {$error}");
                }
            }
            
            if ($stats['imported'] > 0 || $stats['updated'] > 0) {
                $io->section('Updating Cache');
                try {
                    $this->trainingCacheService->refreshTrainingCache();
                    $io->text("[INFO] Training cache refreshed successfully.");
                    $this->logger->info("Training cache refreshed successfully.");
                } catch (\Exception $e) {
                    $cacheError = "Cache refresh failed: " . $e->getMessage();
                    $io->text("[WARNING] {$cacheError}");
                    $this->logger->warning($cacheError);
                    // Continue execution even if cache refresh fails
                }
                
                $io->success('Training synchronization completed successfully!');
            } else {
                $io->warning('No trainings were imported or updated.');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $errorMessage = 'Error: ' . $e->getMessage();
            $this->logger->error($errorMessage);
            $io->error($errorMessage);
            return Command::FAILURE;
        }
    }
}
