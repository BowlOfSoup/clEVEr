<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Character;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CharacterRepository extends ServiceEntityRepository
{
    /**
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function persist(Character $character)
    {
        $this->_em->persist($character);
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function flush(Character $character)
    {
        $this->_em->flush($character);
    }
}
