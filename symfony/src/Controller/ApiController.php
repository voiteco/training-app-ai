<?php

namespace App\Controller;

use App\Repository\TrainingRepository;
use App\Service\TrainingCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): Response
    {
        return $this->json([
            'message' => 'API test endpoint',
            'status' => 'success',
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
        ]);
    }
    
    #[Route('/trainings', name: 'trainings_list', methods: ['GET'])]
    public function getTrainings(
        TrainingCacheService $trainingCacheService,
        TrainingRepository $trainingRepository,
        LoggerInterface $logger
    ): Response {
        try {
            // Try to get trainings from cache first
            try {
                $trainings = $trainingCacheService->getUpcomingTrainings();
                $source = 'cache';
            } catch (\Exception $e) {
                // If cache fails, fall back to database
                $logger->warning('Failed to retrieve trainings from cache: ' . $e->getMessage());
                $trainings = [];
                foreach ($trainingRepository->findUpcomingTrainings() as $training) {
                    $trainings[] = $trainingRepository->serializeTraining($training);
                }
                $source = 'database';
            }
            
            return $this->json([
                'status' => 'success',
                'source' => $source,
                'count' => count($trainings),
                'trainings' => $trainings,
            ]);
        } catch (\Exception $e) {
            $logger->error('Error retrieving trainings: ' . $e->getMessage());
            
            return $this->json([
                'status' => 'error',
                'message' => 'Failed to retrieve trainings',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
