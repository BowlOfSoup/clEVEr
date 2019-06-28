<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Character;
use App\Exception\BadRequestException;
use App\Model\AuthorizationResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class Authenticator
{
    /** @var \App\Service\EveSsoService */
    private $eveSsoService;

    /** @var \App\Service\SpamPreventionService */
    private $spamPreventionService;

    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    private $tokenStorage;

    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface */
    private $session;

    /**
     * @param \App\Service\SpamPreventionService $spamPreventionService
     * @param \App\Service\EveSsoService $eveSsoService
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(
        SpamPreventionService $spamPreventionService,
        EveSsoService $eveSsoService,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session
    ) {
        $this->eveSsoService = $eveSsoService;
        $this->spamPreventionService = $spamPreventionService;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    /**
     * @param string $callbackCode
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return \App\Model\AuthorizationResponse
     */
    public function authenticateWithCallbackCode(string $callbackCode): AuthorizationResponse
    {
        try {
            $authorizationResponse = $this->eveSsoService->authenticate($callbackCode);
        } catch (BadRequestException $e) {
            $this->spamPreventionService->registerAttempt();

            throw $e;
        }
        $this->spamPreventionService->resetAttempts();

        return $authorizationResponse;
    }

    /**
     * @param string $accessToken
     *
     * @throws \App\Exception\AuthenticationException
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return int
     */
    public function verifyAndGetCharacterId(string $accessToken): int
    {
        return $this->eveSsoService->getCharacterIdWithAccessToken($accessToken);
    }

    /**
     * @param \App\Entity\Character $character
     */
    public function authenticateCharacterAsUser(Character $character)
    {
        $token = new UsernamePasswordToken($character, null, 'main', $character->getRoles());
        $this->tokenStorage->setToken($token);
        $this->session->set('_security_main', serialize($token));
    }
}
