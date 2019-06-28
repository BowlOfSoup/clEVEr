<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CharacterRepository")
 * @ORM\Table(name="`character`", indexes={
 *     @ORM\Index(name="i_discord_auth_token", columns={"discord_auth_token"}),
 *     @ORM\Index(name="i_discord_user_id", columns={"discord_user_id"})
 * })
 */
class Character implements UserInterface
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
     * @var \App\Entity\Corporation
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Corporation", cascade={"persist"})
     */
    private $corporation;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $biography;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $discordAuthToken;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $discordUserId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $accessToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $tokenExpiryTime;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $refreshToken;

    /**
     * @param int $eveId
     * @param string $name
     * @param \App\Entity\Corporation $corporation
     * @param string $accessToken
     * @param \DateTime $tokenExpiryTime
     * @param string $refreshToken
     */
    public function __construct(
        int $eveId,
        string $name,
        Corporation $corporation,
        string $accessToken,
        \DateTime $tokenExpiryTime,
        string $refreshToken
    ) {
        $this->eveId = $eveId;
        $this->name = $name;
        $this->corporation = $corporation;
        $this->accessToken = $accessToken;
        $this->tokenExpiryTime = $tokenExpiryTime;
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->id;
    }

    public function eraseCredentials()
    {
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
    public function setId(int $id): Character
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
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): Character
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \App\Entity\Corporation
     */
    public function getCorporation(): Corporation
    {
        return $this->corporation;
    }

    /**
     * @param \App\Entity\Corporation $corporation
     *
     * @return $this
     */
    public function setCorporation(Corporation $corporation): Character
    {
        $this->corporation = $corporation;

        return $this;
    }

    /**
     * @return string
     */
    public function getBiography(): ?string
    {
        return $this->biography;
    }

    /**
     * @param string $biography
     *
     * @return $this
     */
    public function setBiography(string $biography): Character
    {
        $this->biography = $biography;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDiscordAuthToken(): ?string
    {
        return $this->discordAuthToken;
    }

    /**
     * @param string|null $discordAuthToken
     *
     * @return $this
     */
    public function setDiscordAuthToken(string $discordAuthToken = null): Character
    {
        $this->discordAuthToken = $discordAuthToken;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDiscordUserId(): ?string
    {
        return $this->discordUserId;
    }

    /**
     * @param string $discordUserId
     *
     * @return $this
     */
    public function setDiscordUserId(string $discordUserId): Character
    {
        $this->discordUserId = $discordUserId;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     *
     * @return $this
     */
    public function setAccessToken(string $accessToken): Character
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTokenExpiryTime(): \DateTime
    {
        return $this->tokenExpiryTime;
    }

    /**
     * @param \DateTime $tokenExpiryTime
     *
     * @return $this
     */
    public function setTokenExpiryTime(\DateTime $tokenExpiryTime): Character
    {
        $this->tokenExpiryTime = $tokenExpiryTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     *
     * @return $this
     */
    public function setRefreshToken(string $refreshToken): Character
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }
}
