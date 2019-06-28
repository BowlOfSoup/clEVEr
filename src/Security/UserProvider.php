<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Character;
use App\Repository\CharacterRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /** @var \App\Repository\CharacterRepository */
    private $characterRepository;

    /**
     * @param \App\Repository\CharacterRepository $characterRepository
     */
    public function __construct(
        CharacterRepository $characterRepository
    ) {
        $this->characterRepository = $characterRepository;
    }

    /**
     * Symfony calls this method if you use features like switch_user
     * or remember_me.
     *
     * If you're not using these features, you do not need to implement
     * this method.
     *
     * @param $username
     */
    public function loadUserByUsername($username)
    {
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }

        /** @var UserInterface $character */
        $character = $this->characterRepository->find($user->getUsername());

        return $character;
    }

    /**
     * Tells Symfony to use this provider for this User class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return Character::class === $class;
    }
}
