<?php

namespace App\Command;

use App\Service\GoogleSheetsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:google-sheets:test',
    description: 'Test the Google Sheets API integration',
)]
class GoogleSheetsTestCommand extends Command
{
    private GoogleSheetsService $googleSheetsService;

    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        parent::__construct();
        $this->googleSheetsService = $googleSheetsService;
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

        $io->title('Google Sheets API Test');
        
        try {
            // Get available sheets
            $io->section('Available Sheets');
            $sheets = $this->googleSheetsService->getSheets($spreadsheetId);
            $io->listing($sheets);
            
            // Get values from the specified range
            $io->section("Values from range: $range");
            $values = $this->googleSheetsService->getSheetValues($spreadsheetId, $range);
            
            if (empty($values)) {
                $io->warning('No data found in the specified range.');
                return Command::SUCCESS;
            }
            
            // Display the data in a table
            $headers = array_shift($values);
            $io->table($headers, $values);
            
            $io->success('Google Sheets API integration is working correctly!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
