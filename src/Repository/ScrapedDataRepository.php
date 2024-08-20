<?php

namespace App\Repository;

use App\Entity\ScrapedData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ScrapedData>
 */
class ScrapedDataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ScrapedData::class);
    }

    public function findLastMonthData()
    {
        $qb = $this->createQueryBuilder('sd')
            ->where('sd.timestamp >= :date_start')
            ->andWhere('sd.timestamp <= :date_end')
            ->setParameter(':date_start', date("Y-m-01", strtotime("first day of previous month")))
            ->setParameter(':date_end', date("Y-m-t", strtotime("last day of previous month")));
        $query = $qb->getQuery();

        return $query->getArrayResult();
    }
}
