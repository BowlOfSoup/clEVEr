<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\BadRequestException;
use App\Exception\HttpRequestException;
use App\Model\HttpRequestBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpRequest
{
    /** @var \Symfony\Contracts\HttpClient\HttpClientInterface */
    private $httpClient;

    /**
     * @param \Symfony\Contracts\HttpClient\HttpClientInterface $httpClient
     */
    public function __construct(
        HttpClientInterface $httpClient
    ) {
        $this->httpClient = $httpClient;
    }

    /**
     * Make request, and expect back a JSON response.
     *
     * @param \App\Model\HttpRequestBag $httpRequest
     *
     * @throws \App\Exception\BadRequestException
     * @throws \App\Exception\HttpRequestException
     *
     * @return array
     */
    public function make(HttpRequestBag $httpRequest): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, $httpRequest->getCurlOptions());

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $error = curl_error($curl);
        if (!empty($curlError)) {
            throw new HttpRequestException($error);
        }

        $response = json_decode(curl_exec($curl), true);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (Response::HTTP_OK !== $statusCode) {
            $message = sprintf(
                'Error in request: [%s] \'%s\'; Code %s; "%s"',
                $httpRequest->getMethod(),
                !empty($httpRequest->getQuery()) ? $httpRequest->getUrl() . '/?' . http_build_query($httpRequest->getQuery()) : $httpRequest->getUrl(),
                $statusCode,
                isset($response['error_description']) ? $response['error_description'] : json_encode($response)
            );

            if (Response::HTTP_BAD_REQUEST === $statusCode) {
                throw new BadRequestException($message);
            }

            throw new HttpRequestException($message);
        }

        return $response;
    }
}
