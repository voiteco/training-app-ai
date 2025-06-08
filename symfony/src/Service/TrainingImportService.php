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
                $description = $row[$columnMap['description'] ?? -1] ?? null;
                $dateString = $row[$columnMap['date']] ?? null;
                $timeString = $row[$columnMap['time']] ?? null;
                $slots = $row[$columnMap['slots']] ?? null;
                $price = $row[$columnMap['price']] ?? null;
                $duration = $row[$columnMap['duration'] ?? -1] ?? 60; // Default to 60 minutes if not provided
                
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
                
                // Check if training already exists (by title and date)
                $existingTraining = $this->trainingRepository->findOneBy([
                    'title' => $title,
                    'date' => $date
                ]);
                
                if ($existingTraining) {
                    // Update existing training
                    $existingTraining->setDescription($description);
                    $existingTraining->setTime($time);
                    $existingTraining->setSlots((int) $slots);
                    $existingTraining->setSlotsAvailable((int) $slots);
                    $existingTraining->setPrice((float) $price);
                    $existingTraining->setDuration((int) $duration);
                    $existingTraining->setGoogleSheetId($spreadsheetId);
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
                    $training->setGoogleSheetId($spreadsheetId);
                    
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