<?php

declare(strict_types=1);

namespace App\Service\Character;

use App\Entity\Character;
use App\Service\EveEsiService;
use App\Service\RefreshAuthorization;

class KillboardService
{
    /** @var \App\Service\RefreshAuthorization */
    private $refreshAuthorization;

    /** @var \App\Service\EveEsiService */
    private $eveEsiService;

    /**
     * @param \App\Service\RefreshAuthorization $refreshAuthorization
     * @param \App\Service\EveEsiService $eveEsiService
     */
    public function __construct(
        RefreshAuthorization $refreshAuthorization,
        EveEsiService $eveEsiService
    ) {
        $this->refreshAuthorization = $refreshAuthorization;
        $this->eveEsiService = $eveEsiService;
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \Doctrine\ORM\ORMException
     */
    public function getKillMails(Character $character)
    {
        $this->refreshAuthorization->refresh($character);

        $killMails = $this->eveEsiService->getKillMails($character);

        return;

        dump($killMails); die();
    }
}
