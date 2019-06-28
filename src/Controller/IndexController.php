<?php

declare(strict_types=1);

namespace App\Controller;

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

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param string $clientId
     * @param string $callbackUrl
     * @param \App\Service\ConfigurationService $configurationService
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        string $clientId,
        string $callbackUrl,
        ConfigurationService $configurationService
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->clientId = $clientId;
        $this->callbackUrl = $callbackUrl;
        $this->configurationService = $configurationService;
    }

    /**
     * @Route("/", name="app_index")
     */
    public function indexAction()
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_USER')) {
            return $this->render('authenticate.html.twig', [
                'clientId' => $this->clientId,
                'callbackUrl' => $this->callbackUrl,
                'uniqueState' => uniqid(),
                'scope' => 'publicData esi-mail.organize_mail.v1 esi-mail.read_mail.v1 esi-skills.read_skillqueue.v1 esi-wallet.read_character_wallet.v1 esi-killmails.read_killmails.v1 esi-planets.manage_planets.v1 esi-industry.read_character_jobs.v1 esi-industry.read_character_mining.v1',
                'poweredBy' => $this->configurationService->getPoweredBy(),
            ]);
        }

        /** @var \App\Entity\Character $character */
        $character = $this->getUser();

        return $this->render('character.html.twig', [
            'character' => $character,
            'corporationConfigured' => $this->configurationService->isCharacterCorporationConfigured($character),
            'corporationBulletin' => (new \Parsedown())->parse($character->getCorporation()->getBulletin()),
            'corporationBulletinRaw' => $character->getCorporation()->getBulletin(),
            'isAllowedToEditTheBulletin' => $this->configurationService->isCharacterAllowedToEditTheBulletin($character),
        ]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logoutAction()
    {
    }
}
