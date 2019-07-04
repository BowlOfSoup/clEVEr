<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\SpamException;
use App\Service\CallbackService;
use App\Service\SpamPreventionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CallbackController extends AbstractController
{
    /** @var \App\Service\SpamPreventionService */
    private $spamPreventionService;

    /** @var \App\Service\CallbackService */
    private $callbackService;

    /**
     * @param \App\Service\SpamPreventionService $spamPreventionService
     * @param \App\Service\CallbackService $callbackService
     */
    public function __construct(
        SpamPreventionService $spamPreventionService,
        CallbackService $callbackService
    ) {
        $this->spamPreventionService = $spamPreventionService;
        $this->callbackService = $callbackService;
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

        $this->callbackService->handleCallbackFromEve($callbackCode);

        return $this->redirectToRoute('app_index');
    }
}
