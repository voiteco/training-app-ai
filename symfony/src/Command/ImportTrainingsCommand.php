<?php

namespace App\Command;

use App\Service\TrainingImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import:trainings',
    description: 'Import trainings from Google Sheets',
)]
class ImportTrainingsCommand extends Command
{
    private TrainingImportService $trainingImportService;

    public function __construct(TrainingImportService $trainingImportService)
    {
        parent::__construct();
        $this->trainingImportService = $trainingImportService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('spreadsheet-id', InputArgument::REQUIRED, 'The ID of the Google Spreadsheet')
            ->addArgument('range', InputArgument::OPTIONAL, 'The range of cells to retrieve (e.g., Sheet1!A1:D10)', 'Sheet1!A1:D10');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $spreadsheetId = $input->getArgument('spreadsheet-id');
        $range = $input->getArgument('range');

        $io->title('Importing Trainings from Google Sheets');
        $io->text("Spreadsheet ID: $spreadsheetId");
        $io->text("Range: $range");
        
        try {
            $stats = $this->trainingImportService->importTrainings($spreadsheetId, $range);
            
            $io->section('Import Results');
            $io->definitionList(
                ['Imported' => $stats['imported']],
                ['Updated' => $stats['updated']],
                ['Skipped' => $stats['skipped']]
            );
            
            if (!empty($stats['errors'])) {
                $io->section('Errors');
                $io->listing($stats['errors']);
            }
            
            if ($stats['imported'] > 0 || $stats['updated'] > 0) {
                $io->success('Training import completed successfully!');
            } else {
                $io->warning('No trainings were imported or updated.');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
