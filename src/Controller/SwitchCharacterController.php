<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Authenticator;
use App\Service\CharacterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Routing\Annotation\Route;

class SwitchCharacterController extends AbstractController
{
    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \App\Service\Authenticator */
    private $authenticator;

    /** @var \App\Service\CharacterService */
    private $characterService;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \App\Service\Authenticator $authenticator
     * @param \App\Service\CharacterService $characterService
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        Authenticator $authenticator,
        CharacterService $characterService
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->authenticator = $authenticator;
        $this->characterService = $characterService;
    }

    /**
     * @Route("/switch-character/{eveId}", name="switch_character")
     *
     * @param string $eveId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function switchCharacter(string $eveId): Response
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_USER')) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $character = $this->characterService->getCharacterById((int) $eveId);
        if (null === $character) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $this->authenticator->authenticateCharacterAsUser($character);

        return new Response();
    }
}
