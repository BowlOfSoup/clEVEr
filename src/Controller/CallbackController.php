<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\SpamException;
use App\Service\Authenticator;
use App\Service\CharacterService;
use App\Service\SpamPreventionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CallbackController extends AbstractController
{
    /** @var \App\Service\SpamPreventionService */
    private $spamPreventionService;

    /** @var \App\Service\Authenticator */
    private $authenticator;

    /** @var \App\Service\CharacterService */
    private $characterService;

    /**
     * @param \App\Service\SpamPreventionService $spamPreventionService
     * @param \App\Service\Authenticator $authenticator
     * @param \App\Service\CharacterService $characterService
     */
    public function __construct(
        SpamPreventionService $spamPreventionService,
        Authenticator $authenticator,
        CharacterService $characterService
    ) {
        $this->spamPreventionService = $spamPreventionService;
        $this->authenticator = $authenticator;
        $this->characterService = $characterService;
    }

    /**
     * @Route("/callback")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \App\Exception\AuthenticationException
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \App\Exception\SpamException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callbackAction(Request $request): Response
    {
        if (!$this->spamPreventionService->isValidAttempt()) {
            throw new SpamException();
        }

        $callbackCode = $request->get('code');
        if (empty($callbackCode)) {
            $this->spamPreventionService->registerAttempt();

            return $this->forward('App\Controller\IndexController::indexAction');
        }

        $authorizationResponse = $this->authenticator->authenticateWithCallbackCode($callbackCode);
        $characterId = $this->authenticator->verifyAndGetCharacterId($authorizationResponse->getAccessToken());

        $character = $this->characterService->getCharacterById($characterId);
        $character = $this->characterService->upsertCharacter($authorizationResponse, $characterId, $character);

        $this->authenticator->authenticateCharacterAsUser($character);

        return $this->redirectToRoute('app_index');
    }
}
