<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CharacterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DiscordController extends AbstractController
{
    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \App\Repository\CharacterRepository */
    private $characterRepository;

    /**
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \App\Repository\CharacterRepository $characterRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CharacterRepository $characterRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->characterRepository = $characterRepository;
    }

    /**
     * @Route("/discordauth", name="discord_auth")
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function discordAuthAction()
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_USER')) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        /** @var \App\Entity\Character $character */
        $character = $this->getUser();
        $character->setDiscordAuthToken(uniqid());

        $this->characterRepository->persist($character);
        $this->characterRepository->flush($character);

        return $this->render('discord_token.html.twig', [
            'token' => $character->getDiscordAuthToken(),
        ]);
    }
}
