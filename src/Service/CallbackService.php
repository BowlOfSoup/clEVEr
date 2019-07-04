<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Entity\Character;
use App\Exception\AuthenticationException;
use App\Repository\AccountRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CallbackService
{
    /** @var \App\Service\Authenticator */
    private $authenticator;

    /** @var \App\Service\CharacterService */
    private $characterService;

    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface */
    private $session;

    /** @var \App\Repository\AccountRepository */
    private $accountRepository;

    /**
     * @param \App\Service\Authenticator $authenticator
     * @param \App\Service\CharacterService $characterService
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Repository\AccountRepository $accountRepository
     */
    public function __construct(
        Authenticator $authenticator,
        CharacterService $characterService,
        SessionInterface $session,
        AccountRepository $accountRepository
    ) {
        $this->authenticator = $authenticator;
        $this->characterService = $characterService;
        $this->session = $session;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param string $callbackCode
     *
     * @throws \App\Exception\AuthenticationException
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function handleCallbackFromEve(string $callbackCode)
    {
        $authorizationResponse = $this->authenticator->authenticateWithCallbackCode($callbackCode);
        $characterId = $this->authenticator->verifyAndGetCharacterId($authorizationResponse->getAccessToken());

        $character = $this->characterService->getCharacterById($characterId);
        $character = $this->characterService->upsertCharacter($authorizationResponse, $characterId, $character);

        $this->handleAccount($character);

        $this->authenticator->authenticateCharacterAsUser($character);
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @throws \App\Exception\AuthenticationException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function handleAccount(Character $character)
    {
        if (null === $character->getAccount()) {
            if ($this->session->has('clever_account')) {
                $character->setAccount($this->getAccount());
            } else {
                (new Account())->addCharacter($character);
            }
            $this->characterService->save($character);

        } else if ($this->session->has('clever_account')) {
            // Update character with account from session.
            $account = $this->getAccount();
            $account->addCharacter($character);
            $this->characterService->save($character);
        }

        $this->session->set('clever_account', $character->getAccount()->getId());
    }

    /**
     * @throws \App\Exception\AuthenticationException
     *
     * @return \App\Entity\Account
     */
    private function getAccount(): Account
    {
        $accountId = $this->session->get('clever_account');
        /** @var \App\Entity\Account $account */
        $account = $this->accountRepository->find($accountId);
        if (null === $account) {
            throw new AuthenticationException('Account session is not valid');
        }

        return $account;
    }
}
