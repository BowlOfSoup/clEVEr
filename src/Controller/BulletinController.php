<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CorporationRepository;
use App\Service\ConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BulletinController extends AbstractController
{
    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \App\Service\ConfigurationService */
    private $configurationService;

    /** @var \App\Repository\CorporationRepository */
    private $corporationRepository;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \App\Service\ConfigurationService $configurationService
     * @param \App\Repository\CorporationRepository $corporationRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        ConfigurationService $configurationService,
        CorporationRepository $corporationRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->configurationService = $configurationService;
        $this->corporationRepository = $corporationRepository;
    }

    /**
     * @Route("/corporation/bulletin", name="corporation_bulletin", methods={"POST"})
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postAction(Request $request): Response
    {
        /** @var \App\Entity\Character $character */
        $character = $this->getUser();

        if (false === $this->authorizationChecker->isGranted('ROLE_USER') ||
            !$this->configurationService->isCharacterAllowedToEditTheBulletin($character)
        ) {
            return new JsonResponse('', JsonResponse::HTTP_UNAUTHORIZED);
        }

        $corporation = $character->getCorporation();
        $corporation->setBulletin($request->get('bulletin'));

        $this->corporationRepository->persist($corporation);
        $this->corporationRepository->flush($corporation);

        return new JsonResponse();
    }
}
