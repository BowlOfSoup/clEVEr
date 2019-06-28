<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Corporation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CorporationRepository extends ServiceEntityRepository
{
    /**
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Corporation::class);
    }

    /**
     * @param \App\Entity\Corporation $corporation
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function persist(Corporation $corporation)
    {
        $this->_em->persist($corporation);
    }

    /**
     * @param \App\Entity\Corporation $corporation
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush(Corporation $corporation)
    {
        $this->_em->flush($corporation);
    }
}
