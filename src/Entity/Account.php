<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 */
class Account
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
     * @var \App\Entity\Character[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Character", mappedBy="account")
     */
    private $characters;

    public function __construct()
    {
        $this->characters = new ArrayCollection();
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
    public function setId(int $id): Account
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return \App\Entity\Character[]
     */
    public function getCharacters(): array
    {
        return $this->characters;
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @return \App\Entity\Account
     */
    public function addCharacter(Character $character): Account
    {
        if (!$this->characters->contains($character)) {
            $this->characters[] = $character;
            $character->setAccount($this);
        }

        return $this;
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @return \App\Entity\Account
     */
    public function removeCharacter(Character $character): Account
    {
        if ($this->characters->contains($character)) {
            $this->characters->removeElement($character);
            if ($character->getAccount() === $this) {
                $character->setAccount(null);
            }
        }

        return $this;
    }
}
