<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\AuthenticationException;
use App\Model\AuthorizationResponse;
use App\Model\HttpRequestBag;
use Symfony\Component\HttpFoundation\Request;

class EveSsoService
{
    /** @var \App\Service\HttpRequest */
    private $httpRequest;

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /**
     * @param \App\Service\HttpRequest $httpRequest
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(
        HttpRequest $httpRequest,
        string $clientId,
        string $clientSecret
    ) {
        $this->httpRequest = $httpRequest;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param string $authorizationToken
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \Exception
     *
     * @return \App\Model\AuthorizationResponse
     */
    public function authenticate(string $authorizationToken): AuthorizationResponse
    {
        $response = $this->httpRequest->make(
            (new HttpRequestBag(Request::METHOD_POST, 'https://login.eveonline.com/oauth/token'))
                ->setAuthBasic($this->clientId, $this->clientSecret)
                ->setBody(['grant_type' => 'authorization_code', 'code' => $authorizationToken])
        );

        return new AuthorizationResponse(
            $response['access_token'],
            (new \DateTime())->add(new \DateInterval(sprintf('PT%sS', $response['expires_in']))),
            $response['refresh_token']
        );
    }

    /**
     * @param string $refreshToken
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \Exception
     *
     * @return \App\Model\AuthorizationResponse
     */
    public function refreshAuthentication(string $refreshToken): AuthorizationResponse
    {
        $response = $this->httpRequest->make(
            (new HttpRequestBag(Request::METHOD_POST, 'https://login.eveonline.com/oauth/token'))
                ->setAuthBasic($this->clientId, $this->clientSecret)
                ->setBody(['grant_type' => 'refresh_token', 'refresh_token' => $refreshToken])
        );

        return new AuthorizationResponse(
            $response['access_token'],
            (new \DateTime())->add(new \DateInterval(sprintf('PT%sS', $response['expires_in']))),
            $response['refresh_token']
        );
    }

    /**
     * @param string $accessToken
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     * @throws \App\Exception\AuthenticationException
     *
     * @return int
     */
    public function getCharacterIdWithAccessToken(string $accessToken): int
    {
        $verifyResponse = $this->httpRequest->make(
            (new HttpRequestBag(Request::METHOD_GET, EveEsiService::ESI_URL . '/verify'))
                ->setAuthBearer($accessToken)
        );

        if (empty($verifyResponse) || !isset($verifyResponse['CharacterID'])) {
            throw new AuthenticationException();
        }

        return $verifyResponse['CharacterID'];
    }
}
