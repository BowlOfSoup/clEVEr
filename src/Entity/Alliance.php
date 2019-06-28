<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Alliance
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $eveId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5, nullable=false)
     */
    private $ticker;

    /**
     * @param int $eveId
     * @param string $name
     * @param string $ticker
     */
    public function __construct(
        int $eveId,
        string $name,
        string $ticker
    ) {
        $this->eveId = $eveId;
        $this->name = $name;
        $this->ticker = $ticker;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id): Alliance
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getEveId(): int
    {
        return $this->eveId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTicker(): string
    {
        return $this->ticker;
    }
}
