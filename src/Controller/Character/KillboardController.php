<?php

declare(strict_types=1);

namespace App\Controller\Character;

use App\Service\Character\KillboardService;
use App\Service\ConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class KillboardController extends AbstractController
{
    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \App\Service\ConfigurationService */
    private $configurationService;

    /** @var \App\Service\Character\KillboardService */
    private $killboardService;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \App\Service\ConfigurationService $configurationService
     * @param \App\Service\Character\KillboardService $killboardService
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ConfigurationService $configurationService,
        KillboardService $killboardService
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->configurationService = $configurationService;
        $this->killboardService = $killboardService;
    }

    /**
     * @Route("/character/killboard", name="character_killboard")
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->checkAuthorization();

        /** @var \App\Entity\Character $character */
        $character = $this->getUser();

        $this->killboardService->getKillMails($character);

        return $this->render('character_killboard.html.twig', [
            'character' => $character,
            'corporationConfigured' => $this->configurationService->isCharacterCorporationConfigured($character),
        ]);
    }

    private function checkAuthorization()
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_USER')) {
            $this->redirectToRoute('app_index');
        }
    }
}
