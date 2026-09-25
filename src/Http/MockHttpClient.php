<?php

declare(strict_types=1);

namespace KandMailer\Http;

class MockHttpClient implements HttpClientInterface
{
    private string $responseBody = '{"status":"success"}';
    private int $statusCode = 200;
    
    /** @var array<string,mixed> */
    private array $lastRequest = [];

    /**
     * Set the response to return on next request.
     *
     * @param array<string,mixed>|string $body Response body (will be JSON encoded if array)
     * @param int $statusCode HTTP status code
     */
    public function setResponse(array|string $body, int $statusCode = 200): void
    {
        $this->responseBody = is_array($body) ? json_encode($body) : $body;
        $this->statusCode = $statusCode;
    }

    public function request(
        string $method,
        string $url,
        array $headers,
        ?string $body = null
    ): HttpResponse {
        // Store request details for assertions
        $this->lastRequest = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
            'payload' => $body !== null ? json_decode($body, true) : null,
        ];

        return new HttpResponse($this->responseBody, $this->statusCode);
    }

    /**
     * Get the last request details.
     *
     * @return array<string,mixed>
     */
    public function getLastRequest(): array
    {
        return $this->lastRequest;
    }

    /**
     * Get the payload of the last request.
     *
     * @return array<string,mixed>|null
     */
    public function getLastPayload(): ?array
    {
        return $this->lastRequest['payload'] ?? null;
    }

    /**
     * Get the URL of the last request.
     */
    public function getLastUrl(): string
    {
        return $this->lastRequest['url'] ?? '';
    }

    /**
     * Get the method of the last request.
     */
    public function getLastMethod(): string
    {
        return $this->lastRequest['method'] ?? '';
    }

    /**
     * Get the headers of the last request.
     *
     * @return array<int,string>
     */
    public function getLastHeaders(): array
    {
        return $this->lastRequest['headers'] ?? [];
    }

    /**
     * Reset the mock to default state.
     */
    public function reset(): void
    {
        $this->responseBody = '{"status":"success"}';
        $this->statusCode = 200;
        $this->lastRequest = [];
    }
}

