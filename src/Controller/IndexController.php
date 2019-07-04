<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CharacterService;
use App\Service\ConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class IndexController extends AbstractController
{
    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var string */
    private $clientId;

    /** @var string */
    private $callbackUrl;

    /** @var \App\Service\ConfigurationService */
    private $configurationService;

    /** @var \App\Service\CharacterService */
    private $characterService;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param string $clientId
     * @param string $callbackUrl
     * @param \App\Service\ConfigurationService $configurationService
     * @param \App\Service\CharacterService $characterService
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        string $clientId,
        string $callbackUrl,
        ConfigurationService $configurationService,
        CharacterService $characterService
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->clientId = $clientId;
        $this->callbackUrl = $callbackUrl;
        $this->configurationService = $configurationService;
        $this->characterService = $characterService;
    }

    /**
     * @Route("/", name="app_index")
     */
    public function indexAction()
    {
        $ssoConfiguration = [
            'clientId' => $this->clientId,
            'callbackUrl' => $this->callbackUrl,
            'uniqueState' => uniqid(),
            'scope' => 'publicData esi-calendar.read_calendar_events.v1 esi-skills.read_skillqueue.v1 esi-wallet.read_character_wallet.v1 esi-killmails.read_killmails.v1 esi-planets.manage_planets.v1 esi-mail.organize_mail.v1 esi-mail.read_mail.v1 esi-industry.read_character_jobs.v1 esi-industry.read_character_mining.v1 esi-industry.read_corporation_mining.v1 esi-characters.read_corporation_roles.v1 esi-killmails.read_corporation_killmails.v1 esi-corporations.read_contacts.v1 esi-corporations.read_corporation_membership.v1 esi-corporations.read_starbases.v1 esi-corporations.read_structures.v1 esi-corporations.read_facilities.v1 esi-characters.read_fw_stats.v1 esi-corporations.read_fw_stats.v1',
        ];

        if (false === $this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->render('authenticate.html.twig', array_merge($ssoConfiguration, [
               'poweredBy' => $this->configurationService->getPoweredBy(),
            ]));
        }

        /** @var \App\Entity\Character $character */
        $character = $this->getUser();

        return $this->render('character.html.twig', array_merge($ssoConfiguration, [
            'character' => $character,
            'otherCharacters' => $this->characterService->getOtherCharactersForAccount($character),
            'corporationConfigured' => $this->configurationService->isCharacterCorporationConfigured($character),
            'corporationBulletin' => (new \Parsedown())->parse($character->getCorporation()->getBulletin()),
            'corporationBulletinRaw' => $character->getCorporation()->getBulletin(),
            'isAllowedToEditTheBulletin' => $this->configurationService->isCharacterAllowedToEditTheBulletin($character),
        ]));
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logoutAction()
    {
    }
}
