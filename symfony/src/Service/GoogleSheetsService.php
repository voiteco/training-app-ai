<?php

namespace App\Service;

use Google\Client;
use Google\Service\Sheets;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleSheetsService
{
    private Sheets $sheetsService;
    private string $credentialsPath;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $projectDir = $parameterBag->get('kernel.project_dir');
        $this->credentialsPath = $projectDir . '/config/google/credentials.json';
        
        $this->initialize();
    }

    private function initialize(): void
    {
        $client = new Client();
        $client->setApplicationName('Symfony Google Sheets Integration');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig($this->credentialsPath);
        $client->setAccessType('offline');

        $this->sheetsService = new Sheets($client);
    }

    /**
     * Get values from a Google Sheet
     *
     * @param string $spreadsheetId The ID of the spreadsheet
     * @param string $range The range of cells to retrieve (e.g., 'Sheet1!A1:D10')
     * @return array The values from the sheet
     */
    public function getSheetValues(string $spreadsheetId, string $range): array
    {
        $response = $this->sheetsService->spreadsheets_values->get($spreadsheetId, $range);
        return $response->getValues() ?? [];
    }

    /**
     * Get all sheets in a spreadsheet
     *
     * @param string $spreadsheetId The ID of the spreadsheet
     * @return array The list of sheet names
     */
    public function getSheets(string $spreadsheetId): array
    {
        $spreadsheet = $this->sheetsService->spreadsheets->get($spreadsheetId);
        $sheets = [];
        
        foreach ($spreadsheet->getSheets() as $sheet) {
            $sheets[] = $sheet->getProperties()->getTitle();
        }
        
        return $sheets;
    }
}