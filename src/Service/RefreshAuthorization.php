<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Character;
use App\Model\AuthorizationResponse;

class RefreshAuthorization
{
    /** @var \App\Service\EveSsoService */
    private $eveSsoService;

    /**
     * @param \App\Service\EveSsoService $eveSsoService
     */
    public function __construct(
        EveSsoService $eveSsoService
    ) {
        $this->eveSsoService = $eveSsoService;
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return \App\Model\AuthorizationResponse|null
     */
    public function refresh(Character $character): ?AuthorizationResponse
    {
        if ($character->getTokenExpiryTime() <= new \DateTime()) {
            $authorizationResponse = $this->eveSsoService->refreshAuthentication($character->getRefreshToken());

            return $authorizationResponse;
        }

        return null;
    }
}
