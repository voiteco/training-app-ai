<?php

namespace App\Service;

use App\Entity\Training;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service to import training data from a Google Sheet.
 *
 * The `googleSheetId` for each training is generated as an MD5 hash of several training
 * properties (title, description, date, time, price, slots, duration).
 * While this minimizes collisions, it's recommended to ensure unique combinations
 * of these properties in the source sheet or, ideally, use a dedicated unique ID
 * column in the Google Sheet if possible, and map that to `googleSheetId`.
 */
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
        $headersLower = array_map('strtolower', $headers);
        
        // Validate required headers
        $requiredHeaders = ['title', 'date', 'time', 'slots', 'price'];
        $missingHeaders = array_diff($requiredHeaders, $headersLower);
        
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
        foreach ($headersLower as $index => $header) {
            $columnMap[$header] = $index;
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
                if (count($row) < count($requiredHeaders)) { // Check against actual required headers count for core functionality
                    $stats['skipped']++;
                    $stats['errors'][] = "Skipped row " . ($rowIndex + 2) . ": Insufficient columns. Expected at least " . count($requiredHeaders) . " columns, found " . count($row) . ".";
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
                
                // Check for missing required data
                $missingFieldsInRow = [];
                if (!$title) $missingFieldsInRow[] = 'title';
                if (!$dateString) $missingFieldsInRow[] = 'date';
                if (!$timeString) $missingFieldsInRow[] = 'time';
                if ($slots === null || $slots === '') $missingFieldsInRow[] = 'slots'; // Check for null or empty string
                if ($price === null || $price === '') $missingFieldsInRow[] = 'price'; // Check for null or empty string

                if (!empty($missingFieldsInRow)) {
                    $stats['skipped']++;
                    $stats['errors'][] = "Skipped row " . ($rowIndex + 2) . ": Missing required data for fields: " . implode(', ', $missingFieldsInRow) . ".";
                    continue;
                }
                
                // Parse date and time
                try {
                    $date = new \DateTime($dateString);
                    $time = new \DateTime($timeString);
                } catch (\Exception $e) {
                    $stats['skipped']++;
                    $stats['errors'][] = "Skipped row " . ($rowIndex + 2) . ": Invalid date/time format for date '" . $dateString . "' or time '" . $timeString . "'.";
                    continue;
                }
                
                // Generate a unique identifier for this row using multiple fields to reduce collision probability.
                // Fields included: title, description, dateString, timeString, price, slots, duration.
                $rowId = md5(
                    ($title ?? '') .
                    ($description ?? '') .
                    ($dateString ?? '') .
                    ($timeString ?? '') .
                    ($price ?? '') .
                    ($slots ?? '') .
                    ($duration ?? '60') // Ensure duration has a value for the hash
                );
                
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
