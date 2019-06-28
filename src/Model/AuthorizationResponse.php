<?php

declare(strict_types=1);

namespace App\Model;

class AuthorizationResponse
{
    /** @var string */
    private $accessToken;

    /** @var \DateTime */
    private $expiryTime;

    /** @var string */
    private $refreshToken;

    /**
     * @param string $accessToken
     * @param \DateTime $expiryTime
     * @param string $refreshToken
     */
    public function __construct(
        string $accessToken,
        \DateTime $expiryTime,
        string $refreshToken
    ) {
        $this->accessToken = $accessToken;
        $this->expiryTime = $expiryTime;
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return \DateTime
     */
    public function getExpiryTime(): \DateTime
    {
        return $this->expiryTime;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }
}
