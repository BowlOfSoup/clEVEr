<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCharactersCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'clever:reset-characters';

    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $connection = $this->entityManager->getConnection();

        $sql = "DELETE FROM `character`";
        $connection->prepare($sql)->execute();

        $sql = "DELETE FROM `corporation`";
        $connection->prepare($sql)->execute();

        $sql = "DELETE FROM `alliance`";
        $connection->prepare($sql)->execute();

        $output->writeln('[OK]');
    }
}
