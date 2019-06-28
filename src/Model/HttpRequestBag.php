<?php

declare(strict_types=1);

namespace App\Model;

class HttpRequestBag
{
    const AUTH_BASIC = 'auth_basic';
    const AUTH_BEARER = 'auth_bearer';

    /** @var string */
    private $method;

    /** @var string */
    private $url;

    /** @var string */
    private $authMethod;

    /** @var string|array */
    private $authContent;

    /** @var array */
    private $body;

    /** @var array */
    private $query;

    /**
     * @param string $method
     * @param string $url
     */
    public function __construct(
        string $method,
        string $url
    ) {
        $this->method = $method;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param array $body
     *
     * @return $this
     */
    public function setBody(array $body): HttpRequestBag
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getQuery(): ?array
    {
        return $this->query;
    }

    /**
     * @param array $query
     *
     * @return $this
     */
    public function setQuery(array $query): HttpRequestBag
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param string $user
     * @param string $password
     *
     * @return $this
     */
    public function setAuthBasic(string $user, string $password): HttpRequestBag
    {
        $this->authMethod = static::AUTH_BASIC;
        $this->authContent = [$user, $password];

        return $this;
    }

    /**
     * @param string $bearer
     *
     * @return $this
     */
    public function setAuthBearer(string $bearer): HttpRequestBag
    {
        $this->authMethod = static::AUTH_BEARER;
        $this->authContent = $bearer;

        return $this;
    }

    /**
     * @return array
     */
    public function getCurlOptions(): array
    {
        return array(
            CURLOPT_URL => !empty($this->query) ? $this->url . '/?' . http_build_query($this->query) : $this->url,
            CURLOPT_CUSTOMREQUEST => $this->method,
            CURLOPT_POSTFIELDS => !empty($this->body) ? http_build_query($this->body) : null,
            CURLOPT_HTTPHEADER => $this->getHeaders(),
        );
    }

    /**
     * @return array
     */
    private function getHeaders(): array
    {
        $headers = [];

        if (static::AUTH_BASIC === $this->authMethod) {
            $headers[] = sprintf('Authorization: Basic %s', base64_encode(implode(':', $this->authContent)));
        } else if (static::AUTH_BEARER === $this->authMethod) {
            $headers[] = sprintf('Authorization: Bearer %s', $this->authContent);
        }

        if (!empty($this->body)) {
            $headers[] = 'content-type: application/x-www-form-urlencoded';
        }

        return $headers;
    }
}
