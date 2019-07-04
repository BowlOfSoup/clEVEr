<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Entity\Alliance;
use App\Entity\Character;
use App\Entity\Corporation;
use App\Model\AuthorizationResponse;
use App\Model\Configuration;
use App\Repository\CharacterRepository;
use Doctrine\ORM\EntityRepository;

class CharacterService
{
    /** @var \App\Service\EveEsiService */
    private $eveEsiService;

    /** @var \App\Model\Configuration */
    private $configuration;

    /** @var \App\Service\RefreshAuthorization */
    private $refreshAuthorization;

    /** @var \App\Repository\CharacterRepository */
    private $characterRepository;

    /** @var \Doctrine\ORM\EntityRepository */
    private $corporationRepository;

    /** @var \Doctrine\ORM\EntityRepository */
    private $allianceRepository;

    /**
     * @param \App\Service\EveEsiService $eveEsiService
     * @param \App\Model\Configuration $configuration
     * @param \App\Service\RefreshAuthorization $refreshAuthorization
     * @param \App\Repository\CharacterRepository $characterRepository
     * @param \Doctrine\ORM\EntityRepository $corporationRepository
     * @param \Doctrine\ORM\EntityRepository $allianceRepository
     */
    public function __construct(
        EveEsiService $eveEsiService,
        Configuration $configuration,
        RefreshAuthorization $refreshAuthorization,
        CharacterRepository $characterRepository,
        EntityRepository $corporationRepository,
        EntityRepository $allianceRepository
    ) {
        $this->eveEsiService = $eveEsiService;
        $this->configuration = $configuration;
        $this->refreshAuthorization = $refreshAuthorization;
        $this->characterRepository = $characterRepository;
        $this->corporationRepository = $corporationRepository;
        $this->allianceRepository = $allianceRepository;
    }

    /**
     * @param int $characterId
     *
     * @return \App\Entity\Character|null
     */
    public function getCharacterById(int $characterId): ?Character
    {
        /** @var \App\Entity\Character $character */
        $character = $this->characterRepository->findOneBy(['eveId' => $characterId]);

        return null !== $character ? $character : null;
    }

    /**
     * @param string $discordUserId
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \App\Entity\Character|null
     */
    public function refreshCharacterByDiscordUserId(string $discordUserId): ?Character
    {
        /** @var \App\Entity\Character $character */
        $character = $this->characterRepository->findOneBy(['discordUserId' => $discordUserId]);
        if (null === $character) {
            return null;
        }
        $this->refreshCharacter($character);

        return $character;
    }

    /**
     * @param \App\Entity\Character|null $character
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function refreshCharacter(Character $character)
    {
        $authorizationResponse = $this->refreshAuthorization->refresh($character);
        if (null === $authorizationResponse) {
            $this->setAuthorizationData($character, $authorizationResponse);
        }

        $esiCharacter = $this->eveEsiService->getCharacter($character->getEveId(), $character->getAccessToken());
        $corporationId = $esiCharacter['corporation_id'];

        $alliance = $this->getAlliance($corporationId);
        $corporation = $this->getCorporation($corporationId, $alliance);

        $character
            ->setName($esiCharacter['name'])
            ->setCorporation($corporation)
            ->setBiography($esiCharacter['description']);

        $this->characterRepository->persist($character);
        $this->characterRepository->flush($character);
    }

    /**
     * @param \App\Model\AuthorizationResponse $authorizationResponse
     * @param int $characterId
     * @param \App\Entity\Character|null $character
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \App\Entity\Character
     */
    public function upsertCharacter(
        AuthorizationResponse $authorizationResponse,
        int $characterId,
        Character $character = null
    ): Character {
        $esiCharacter = $this->eveEsiService->getCharacter($characterId, $authorizationResponse->getAccessToken());
        $corporationId = $esiCharacter['corporation_id'];

        $alliance = $this->getAlliance($corporationId);
        $corporation = $this->getCorporation($corporationId, $alliance);

        if (null === $character) {
            $character = (new Character(
                $characterId,
                $esiCharacter['name'],
                $corporation,
                $authorizationResponse->getAccessToken(),
                $authorizationResponse->getExpiryTime(),
                $authorizationResponse->getRefreshToken()
            ))
                ->setBiography($esiCharacter['description']);
        } else {
            $character
                ->setName($esiCharacter['name'])
                ->setCorporation($corporation)
                ->setBiography($esiCharacter['description']);

            $this->setAuthorizationData($character, $authorizationResponse);
        }

        $this->save($character);

        return $character;
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @return array
     */
    public function getAllowedRoles(Character $character): array
    {
        $alliancesWithRoles = $this->configuration->getAllianceRoles();
        $alliance = $character->getCorporation()->getAlliance();

        $corpsWithRoles = $this->configuration->getCorporationRoles();
        $characterCorporationEveId = $character->getCorporation()->getEveId();

        $allowedRoles = [];
        if (null !== $alliance && array_key_exists($alliance->getEveId(), $alliancesWithRoles)) {
            $allowedRoles = array_merge($allowedRoles, $alliancesWithRoles[$alliance->getEveId()]);
        }
        if (array_key_exists($characterCorporationEveId, $corpsWithRoles)) {
            $allowedRoles = array_merge($allowedRoles, $corpsWithRoles[$characterCorporationEveId]);
        }

        return $allowedRoles;
    }

    /**
     * @param \App\Entity\Character $character
     * @param \App\Model\AuthorizationResponse $authorizationResponse
     */
    public function setAuthorizationData(Character $character, AuthorizationResponse $authorizationResponse)
    {
        $character
            ->setAccessToken($authorizationResponse->getAccessToken())
            ->setTokenExpiryTime($authorizationResponse->getExpiryTime())
            ->setRefreshToken($authorizationResponse->getRefreshToken());
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Character $character)
    {
        $this->characterRepository->persist($character);
        $this->characterRepository->flush($character);
    }

    /**
     * @param int $corporationId
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return \App\Entity\Alliance|null
     */
    private function getAlliance(int $corporationId): ?Alliance
    {
        $esiAlliance = $this->eveEsiService->getAllianceByCorporation($corporationId);
        $alliance = null;
        if (null !== $esiAlliance) {
            $alliance = $this->allianceRepository->findOneBy(['eveId' => $corporationId]);
            if (empty($alliance)) {
                $alliance = new Alliance(
                    $esiAlliance['alliance_id'],
                    $esiAlliance['name'],
                    $esiAlliance['ticker']
                );
            }
        }

        return $alliance;
    }

    /**
     * @param int $corporationId
     * @param \App\Entity\Alliance|null $alliance
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return \App\Entity\Corporation|null
     */
    private function getCorporation(int $corporationId, Alliance $alliance = null): ?Corporation
    {
        $esiCorporation = $this->eveEsiService->getCorporation($corporationId);
        $corporation = $this->corporationRepository->findOneBy(['eveId' => $corporationId]);
        if (empty($corporation)) {
            $corporation = (new Corporation(
                $corporationId,
                $esiCorporation['name'],
                $esiCorporation['ticker']
            ))
                ->setAlliance($alliance)
                ->setDescription($esiCorporation['description']);
        }

        return $corporation;
    }
}
