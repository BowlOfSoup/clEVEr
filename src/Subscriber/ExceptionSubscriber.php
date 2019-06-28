<?php

declare(strict_types=1);

namespace App\Subscriber;

use App\Exception\SpamException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /** @var string */
    private $environment;

    /** @var \Twig\Environment */
    private $twig;

    /**
     * @param string $environment
     * @param \Twig\Environment $twig
     */
    public function __construct(
        string $environment,
        Environment $twig
    ) {
        $this->environment = $environment;
        $this->twig = $twig;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['responseForException', 10],
            ],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     *
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function responseForException(ExceptionEvent $event)
    {
        if ('dev' === $this->environment) {
            return;
        }

        $message = 'The error is logged. Please contact your IT manager with reproduction steps.';

        $exception = $event->getException();
        if ($exception instanceof SpamException) {
            $message = 'You seem to be spamming us. Please stop! You can try again in 10 minutes.';
        }

        $event->setResponse(
            (new Response())->setContent(
                $this->twig->render('exception.html.twig', ['message' => $message])
            )
        );
    }
}
