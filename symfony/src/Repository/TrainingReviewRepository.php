<?php

namespace App\Repository;

use App\Entity\TrainingReview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TrainingReview>
 *
 * @method TrainingReview|null find($id, $lockMode = null, $lockVersion = null)
 * @method TrainingReview|null findOneBy(array $criteria, array $orderBy = null)
 * @method TrainingReview[]    findAll()
 * @method TrainingReview[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TrainingReview::class);
    }
}