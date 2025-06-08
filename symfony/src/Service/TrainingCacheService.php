<?php

namespace App\Service;

use App\Entity\Training;
use App\Repository\TrainingRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TrainingCacheService
{
    private const TRAINING_LIST_CACHE_KEY = 'training_list';
    private const CACHE_EXPIRATION = 900; // 15 minutes in seconds
    
    private CacheInterface $cache;
    private TrainingRepository $trainingRepository;
    
    public function __construct(
        CacheInterface $cache,
        TrainingRepository $trainingRepository
    ) {
        $this->cache = $cache;
        $this->trainingRepository = $trainingRepository;
    }
    
    /**
     * Get all upcoming trainings from cache or database
     * 
     * @return array
     */
    public function getUpcomingTrainings(): array
    {
        return $this->cache->get(self::TRAINING_LIST_CACHE_KEY, function (ItemInterface $item) {
            $item->expiresAfter(self::CACHE_EXPIRATION);
            
            $trainings = $this->trainingRepository->findUpcomingTrainings();
            return $this->serializeTrainings($trainings);
        });
    }
    
    /**
     * Refresh the training list cache with the latest data
     * 
     * @param array|null $trainings Optional array of Training entities to use instead of querying the database
     * @return array The cached training data
     */
    public function refreshTrainingCache(?array $trainings = null): array
    {
        // If trainings are provided, use them; otherwise, fetch from the database
        if ($trainings === null) {
            $trainings = $this->trainingRepository->findUpcomingTrainings();
        }
        
        // Serialize and store in cache
        return $this->storeInCache(self::TRAINING_LIST_CACHE_KEY, $trainings);
    }
    
    /**
     * Check if the training list is cached
     * 
     * @return bool
     */
    public function isTrainingListCached(): bool
    {
        return $this->cache->hasItem(self::TRAINING_LIST_CACHE_KEY);
    }
    
    /**
     * Serialize an array of Training entities to an array format suitable for API responses
     * 
     * @param Training[] $trainings
     * @return array
     */
    private function serializeTrainings(array $trainings): array
    {
        $result = [];
        foreach ($trainings as $training) {
            $result[] = $this->trainingRepository->serializeTraining($training);
        }
        return $result;
    }
}
