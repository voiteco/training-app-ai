<?php

namespace App\Service;

use App\Entity\Training;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;

class TrainingImportService
{
    private GoogleSheetsService $googleSheetsService;
    private TrainingRepository $trainingRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        GoogleSheetsService $googleSheetsService,
        TrainingRepository $trainingRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->googleSheetsService = $googleSheetsService;
        $this->trainingRepository = $trainingRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Import training data from a Google Sheet
     *
     * @param string $spreadsheetId The ID of the spreadsheet
     * @param string $range The range of cells to retrieve (e.g., 'Sheet1!A1:D10')
     * @return array Statistics about the import process
     */
    public function importTrainings(string $spreadsheetId, string $range): array
    {
        $values = $this->googleSheetsService->getSheetValues($spreadsheetId, $range);
        
        if (empty($values)) {
            return [
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => ['No data found in the specified range.']
            ];
        }
        
        $headers = array_shift($values);
        
        // Validate required headers
        $requiredHeaders = ['title', 'date', 'time', 'slots', 'price'];
        $missingHeaders = array_diff($requiredHeaders, array_map('strtolower', $headers));
        
        if (!empty($missingHeaders)) {
            return [
                'imported' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => ['Missing required headers: ' . implode(', ', $missingHeaders)]
            ];
        }
        
        // Map column indices
        $columnMap = [];
        foreach ($headers as $index => $header) {
            $columnMap[strtolower($header)] = $index;
        }
        
        $stats = [
            'imported' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => []
        ];
        
        foreach ($values as $rowIndex => $row) {
            try {
                // Skip rows with insufficient data
                if (count($row) < count($requiredHeaders)) {
                    $stats['skipped']++;
                    $stats['errors'][] = "Row " . ($rowIndex + 2) . " has insufficient data";
                    continue;
                }
                
                $title = $row[$columnMap['title']] ?? null;
                $description = array_key_exists('description', $columnMap) ? ($row[$columnMap['description']] ?? null) : null;
                $dateString = $row[$columnMap['date']] ?? null;
                $timeString = $row[$columnMap['time']] ?? null;
                $slots = $row[$columnMap['slots']] ?? null;
                $price = $row[$columnMap['price']] ?? null;
                if (isset($columnMap['duration']) && isset($row[$columnMap['duration']])) {
                    $duration = $row[$columnMap['duration']];
                } else {
                    $duration = 60; // Default to 60 minutes if not provided
                }
                
                // Skip rows with missing required data
                if (!$title || !$dateString || !$timeString || !$slots || !$price) {
                    $stats['skipped']++;
                    $stats['errors'][] = "Row " . ($rowIndex + 2) . " is missing required data";
                    continue;
                }
                
                // Parse date and time
                try {
                    $date = new \DateTime($dateString);
                    $time = new \DateTime($timeString);
                } catch (\Exception $e) {
                    $stats['skipped']++;
                    $stats['errors'][] = "Row " . ($rowIndex + 2) . " has invalid date/time format";
                    continue;
                }
                
                // Generate a unique identifier for this row
                $rowId = md5($title . $dateString . $timeString);
                
                // Check if training already exists (by googleSheetId)
                $existingTraining = $this->trainingRepository->findOneBy([
                    'googleSheetId' => $rowId
                ]);
                
                if ($existingTraining) {
                    // Update existing training
                    $existingTraining->setDescription($description);
                    $existingTraining->setTime($time);
                    $existingTraining->setSlots((int) $slots);
                    $existingTraining->setSlotsAvailable((int) $slots);
                    $existingTraining->setPrice((float) $price);
                    $existingTraining->setDuration((int) $duration);
                    $stats['updated']++;
                } else {
                    // Create new training
                    $training = new Training();
                    $training->setTitle($title);
                    $training->setDescription($description);
                    $training->setDate($date);
                    $training->setTime($time);
                    $training->setSlots((int) $slots);
                    $training->setSlotsAvailable((int) $slots);
                    $training->setPrice((float) $price);
                    $training->setDuration((int) $duration);
                    $training->setGoogleSheetId($rowId);
                    
                    $this->entityManager->persist($training);
                    $stats['imported']++;
                }
            } catch (\Exception $e) {
                $stats['skipped']++;
                $stats['errors'][] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
            }
        }
        
        $this->entityManager->flush();
        
        return $stats;
    }
}
