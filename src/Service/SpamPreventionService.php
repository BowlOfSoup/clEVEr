<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SpamPreventionService
{
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface */
    private $session;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
        $this->session->start();
    }

    public function registerAttempt()
    {
        $attempt = $this->session->get('attempt');
        $attempt = !empty($attempt) ? ++$attempt : 1;

        $this->session->set('attempt', $attempt);
        $this->session->set('attemptTime', (new \DateTime())->getTimestamp());
    }

    /**
     * @return bool
     */
    public function isValidAttempt(): bool
    {
        $attempt = $this->session->get('attempt');
        if (empty($attempt) || $attempt < 3) {
            return true;
        }

        $lastAttemptTime = $this->session->get('attemptTime');
        $currentTime = (new \DateTime())->getTimestamp();

        $diffInSeconds = $currentTime - $lastAttemptTime;
        if ($diffInSeconds > 600) {
            return true;
        }

        return false;
    }

    public function resetAttempts()
    {
        $this->session->remove('attempt');
        $this->session->remove('attemptTime');
    }
}
