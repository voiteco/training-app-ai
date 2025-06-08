<?php

namespace App\Repository;

use App\Entity\Training;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Training>
 *
 * @method Training|null find($id, $lockMode = null, $lockVersion = null)
 * @method Training|null findOneBy(array $criteria, array $orderBy = null)
 * @method Training[]    findAll()
 * @method Training[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Training::class);
    }
    
    /**
     * Find all upcoming trainings ordered by date and time
     * 
     * @return Training[] Returns an array of Training objects
     */
    public function findUpcomingTrainings(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.date >= :today')
            ->setParameter('today', new \DateTime('today'))
            ->orderBy('t.date', 'ASC')
            ->addOrderBy('t.time', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Serialize a training entity to an array format suitable for API responses and caching
     * 
     * @param Training $training
     * @return array
     */
    public function serializeTraining(Training $training): array
    {
        return [
            'id' => $training->getId(),
            'title' => $training->getTitle(),
            'description' => $training->getDescription(),
            'date' => $training->getDate()->format('Y-m-d'),
            'time' => $training->getTime()->format('H:i'),
            'duration' => $training->getDuration(),
            'slots' => $training->getSlots(),
            'slotsAvailable' => $training->getSlotsAvailable(),
            'price' => $training->getPrice(),
        ];
    }
}
