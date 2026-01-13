<?php

declare(strict_types=1);

namespace KandMailer\Helpers;

use KandMailer\MailerClient;
use KandMailer\Models\File;
use RuntimeException;

class Makers
{
    /**
     * Send a message.
     *
     * @throws RuntimeException When the API responds with an error or invalid JSON.
     *
     * @return string The successful API response body.
     */
    public static function request(
        MailerClient $client,
        string $method,
        string $path
    ): string
    {
        $url = $client->getEndpoint() . $path;

        $headers = [
            'Authorization: Bearer ' . $client->getApiKey(),
            'Content-Type: application/json',
        ];

        $body = json_encode(self::toPayload($client), JSON_THROW_ON_ERROR);

        $response = $client->getHttpClient()->request($method, $url, $headers, $body);

        if (!$response->isSuccessful()) {
            throw new RuntimeException(
                'API error, code ' . $response->getStatusCode() . ': ' . $response->getBody()
            );
        }

        $decoded = json_decode($response->getBody(), true);

        if ($decoded === null || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                'API error, invalid JSON response: ' . $response->getBody()
            );
        }

        return $response->getBody();
    }

    /**
     * Build the payload for a request.
     *
     * @return array<string,mixed>
     */
    public static function toPayload(MailerClient $client): array
    {
        $payload = [];

        // Simple fields
        self::addIfSet($payload, 'template', $client->getTemplate());
        self::addIfSet($payload, 'firstName', $client->getFirstName());
        self::addIfSet($payload, 'lastName', $client->getLastName());
        self::addIfSet($payload, 'email', $client->getEmail());
        self::addIfSet($payload, 'phone', $client->getPhone());
        self::addIfSet($payload, 'scenario', $client->getScenario());
        self::addIfSet($payload, 'account_id', $client->getAccountId());

        // Date
        if ($client->getCreatedAt() !== null) {
            $payload['created_at'] = $client->getCreatedAt()->format(\DateTimeInterface::ATOM);
        }

        // Options (only if not empty)
        if (!empty($client->getOptions())) {
            $payload['options'] = $client->getOptions();
        }

        // Files (only if not empty)
        if (!empty($client->getFiles())) {
            $payload['files'] = array_map(fn(File $f) => $f->toArray(), $client->getFiles());
        }

        // Arrays (only if not empty)
        if (!empty($client->getRemove())) {
            $payload['remove'] = $client->getRemove();
        }

        if (!empty($client->getExists())) {
            $payload['exists'] = $client->getExists();
        }

        return $payload;
    }

    /**
     * Add a value to the payload if it is set.
     */
    public static function addIfSet(array &$payload, string $key, mixed $value): void
    {
        if ($value !== null) {
            $payload[$key] = $value;
        }
    }
}
