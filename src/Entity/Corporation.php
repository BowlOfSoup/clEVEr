<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CorporationRepository")
 */
class Corporation
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
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var \App\Entity\Alliance|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Alliance", cascade={"persist"})
     */
    private $alliance;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $bulletin;

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
    public function setId(int $id): Corporation
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

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription(string $description): Corporation
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \App\Entity\Alliance|null
     */
    public function getAlliance()
    {
        return $this->alliance;
    }

    /**
     * @param \App\Entity\Alliance|null $alliance
     *
     * @return $this
     */
    public function setAlliance(Alliance $alliance = null): Corporation
    {
        $this->alliance = $alliance;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBulletin(): ?string
    {
        return $this->bulletin;
    }

    /**
     * @param string $bulletin
     *
     * @return $this
     */
    public function setBulletin(string $bulletin): Corporation
    {
        $this->bulletin = $bulletin;

        return $this;
    }
}
