<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Character;
use App\Model\HttpRequestBag;
use Symfony\Component\HttpFoundation\Request;

class EveEsiService
{
    const ESI_URL = 'https://esi.evetech.net';

    /** @var \App\Service\HttpRequest */
    private $httpRequest;

    /**
     * @param \App\Service\HttpRequest $httpRequest
     */
    public function __construct(
        HttpRequest $httpRequest
    ) {
        $this->httpRequest = $httpRequest;
    }

    /**
     * @param int $characterId
     * @param string $accessToken
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return array
     */
    public function getCharacter(int $characterId, string $accessToken): array
    {
        return $this->httpRequest->make(
            (new HttpRequestBag(Request::METHOD_GET, sprintf('%s/latest/characters/%s', static::ESI_URL, $characterId)))
                ->setAuthBearer($accessToken)
        );
    }

    /**
     * @param int $corporationId
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return array
     */
    public function getCorporation(int $corporationId): array
    {
        return $this->httpRequest->make(
            (new HttpRequestBag(Request::METHOD_GET, sprintf('%s/latest/corporations/%s', static::ESI_URL, $corporationId)))
        );
    }

    /**
     * @param int $allianceId
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return array
     */
    public function getAlliance(int $allianceId): array
    {
        return $this->httpRequest->make(
            (new HttpRequestBag(Request::METHOD_GET, sprintf('%s/latest/alliances/%s', static::ESI_URL, $allianceId)))
        );
    }

    /**
     * @param int $corporationId
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return array|null
     */
    public function getAllianceByCorporation(int $corporationId): ?array
    {
        $allianceHistoryResponse = $this->httpRequest->make(
            (new HttpRequestBag(Request::METHOD_GET, sprintf('%s/latest/corporations/%s/alliancehistory', static::ESI_URL, $corporationId)))
        );

        $currentAlliance = !empty($allianceHistoryResponse) ? reset($allianceHistoryResponse) : null;

        if (isset($currentAlliance['alliance_id'])) {
           return array_merge(
               ['alliance_id' => $currentAlliance['alliance_id']],
               $this->getAlliance($currentAlliance['alliance_id'])
           );
        }

        return null;
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return array
     */
    public function getKillMails(Character $character): array
    {
        $killMails = [];

        $killMailHashes = $this->httpRequest->make(
            (new HttpRequestBag(Request::METHOD_GET, sprintf('%s/latest/characters/%s/killmails/recent', static::ESI_URL, $character->getEveId())))
                ->setQuery(['token' => $character->getAccessToken()])
        );

        if (empty($killMailHashes)) {
            return [];
        }

        foreach ($killMailHashes as $killMailHash) {
            $killMails[] = $this->httpRequest->make(
                (new HttpRequestBag(Request::METHOD_GET, sprintf('%s/latest/killmails/%s/%s', static::ESI_URL, $killMailHash['killmail_id'], $killMailHash['killmail_hash'])))
                    ->setQuery(['token' => $character->getAccessToken()])
            );
        }

        return $killMails;
    }
}
